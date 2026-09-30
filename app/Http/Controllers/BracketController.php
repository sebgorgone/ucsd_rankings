<?php

namespace App\Http\Controllers;

use App\BracketStatus;
use App\Http\Requests\StoreBracketRequest;
use App\Http\Requests\UpdateBracketRequest;
use App\MatchupStatus;
use App\Models\Bracket;
use App\Models\Vote;
use App\Services\BracketProgressor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BracketController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('status')->toString();

        $brackets = Bracket::query()
            ->with(['user', 'winner'])
            ->withCount('candidates')
            ->when($filter === 'completed', fn ($query) => $query->where('status', BracketStatus::Completed))
            ->when($filter === 'ongoing', fn ($query) => $query->whereIn('status', [
                BracketStatus::Candidate,
                BracketStatus::Ongoing,
                BracketStatus::Tied,
            ]))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('landing', compact('brackets', 'filter'));
    }

    public function create(): View
    {
        $this->authorize('create', Bracket::class);

        return view('brackets.create');
    }

    public function store(StoreBracketRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $candidatePhaseDuration = $request->durationInMinutes('candidate_phase');
        $matchupDuration = $request->durationInMinutes('matchup');

        $bracket = $request->user()->brackets()->create([
            'name' => $data['name'],
            'allow_candidate_submissions' => $request->boolean('allow_candidate_submissions'),
            'candidate_phase_duration_minutes' => $candidatePhaseDuration,
            'candidate_phase_ends_at' => now()->addMinutes($candidatePhaseDuration),
            'matchup_duration_minutes' => $matchupDuration,
            'status' => BracketStatus::Candidate,
        ]);

        return redirect()
            ->route('brackets.edit', $bracket)
            ->with('status', 'Bracket created. Add candidates before the candidate phase ends.');
    }

    public function show(Bracket $bracket, BracketProgressor $progressor): View
    {
        $progressor->progress($bracket);

        $bracket->refresh()->load([
            'user',
            'winner',
            'candidates' => fn ($query) => $query->orderByRaw('seed IS NULL')->orderBy('seed')->orderBy('name'),
            'matchups' => fn ($query) => $query
                ->with(['candidateOne', 'candidateTwo', 'winner'])
                ->orderBy('round')
                ->orderBy('position'),
        ]);

        $voteTotals = Vote::query()
            ->select('matchup_id', 'candidate_id', DB::raw('COUNT(*) as total'))
            ->whereIn('matchup_id', $bracket->matchups->pluck('id'))
            ->groupBy('matchup_id', 'candidate_id')
            ->get()
            ->groupBy('matchup_id')
            ->map(fn ($votes) => $votes->pluck('total', 'candidate_id'));

        $currentMatchup = $bracket->matchups->first(
            fn ($matchup) => in_array($matchup->status, [MatchupStatus::Open, MatchupStatus::Tied], true)
        );
        $mobileMatchup = $currentMatchup
            ?? $bracket->matchups
                ->sortByDesc('sequence')
                ->first(fn ($matchup) => $matchup->winner_candidate_id !== null);

        $currentVoteCandidateId = auth()->user() && $currentMatchup
            ? auth()->user()->votes()->where('matchup_id', $currentMatchup->id)->value('candidate_id')
            : null;

        return view('brackets.show', [
            'bracket' => $bracket,
            'rounds' => $bracket->matchups->groupBy('round'),
            'voteTotals' => $voteTotals,
            'currentMatchup' => $currentMatchup,
            'mobileMatchup' => $mobileMatchup,
            'currentVoteCandidateId' => $currentVoteCandidateId,
            'canEdit' => auth()->user()?->can('update', $bracket) ?? false,
            'canAddCandidate' => auth()->user()?->can('addCandidate', $bracket) ?? false,
            'canResolveTie' => auth()->user()?->can('resolveTie', $bracket) ?? false,
        ]);
    }

    public function edit(Bracket $bracket, BracketProgressor $progressor): View
    {
        $this->authorize('update', $bracket);
        $progressor->progress($bracket);
        $bracket->refresh()->load([
            'candidates' => fn ($query) => $query->orderBy('name'),
            'matchups' => fn ($query) => $query
                ->with(['candidateOne', 'candidateTwo'])
                ->orderBy('sequence'),
        ]);

        return view('brackets.edit', compact('bracket'));
    }

    public function update(
        UpdateBracketRequest $request,
        Bracket $bracket,
        BracketProgressor $progressor,
    ): RedirectResponse {
        $progressor->progress($bracket);
        $bracket->refresh();
        $data = $request->validated();
        $candidatePhaseDuration = $request->durationInMinutes('candidate_phase');
        $matchupDuration = $request->durationInMinutes('matchup');

        if (
            $bracket->status === BracketStatus::Candidate
            && $candidatePhaseDuration < $bracket->candidate_phase_duration_minutes
        ) {
            throw ValidationException::withMessages([
                'candidate_phase_days' => 'The candidate phase may only be extended.',
            ]);
        }

        $updates = [
            'name' => $data['name'],
            'matchup_duration_minutes' => $matchupDuration,
        ];

        if ($bracket->status === BracketStatus::Candidate) {
            $updates['candidate_phase_duration_minutes'] = $candidatePhaseDuration;
            $updates['candidate_phase_ends_at'] = $bracket->created_at
                ->copy()
                ->addMinutes($candidatePhaseDuration);
            $updates['allow_candidate_submissions'] = $request->boolean('allow_candidate_submissions');
        }

        $bracket->update($updates);

        $extension = $request->durationInMinutes('extend_current_matchup');

        if ($extension > 0) {
            $currentMatchup = $bracket->matchups()
                ->where('status', MatchupStatus::Open)
                ->first();

            if ($currentMatchup !== null) {
                $currentMatchup->update([
                    'closes_at' => $currentMatchup->closes_at->addMinutes($extension),
                ]);
            }
        }

        return redirect()
            ->route('brackets.edit', $bracket)
            ->with('status', 'Bracket settings updated.');
    }
}
