<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteRequest;
use App\MatchupStatus;
use App\Models\Matchup;
use App\Models\Vote;
use App\Services\BracketProgressor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class VoteController extends Controller
{
    public function __invoke(
        StoreVoteRequest $request,
        Matchup $matchup,
        BracketProgressor $progressor,
    ): RedirectResponse {
        $progressor->progress($matchup->bracket);
        $matchup->refresh()->load('bracket');
        $candidateId = (int) $request->validated('candidate_id');

        if (
            $matchup->status !== MatchupStatus::Open
            || ($matchup->bracket->paused_at === null && $matchup->closes_at->isPast())
            || ! $matchup->containsCandidate($candidateId)
        ) {
            throw ValidationException::withMessages([
                'candidate_id' => 'Voting is closed or that candidate is not in this matchup.',
            ]);
        }

        Vote::query()->updateOrCreate(
            [
                'matchup_id' => $matchup->id,
                'user_id' => $request->user()->id,
            ],
            ['candidate_id' => $candidateId],
        );

        return back()->with('status', 'Your vote has been saved.');
    }
}
