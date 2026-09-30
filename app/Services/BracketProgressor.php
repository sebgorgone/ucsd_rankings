<?php

namespace App\Services;

use App\BracketStatus;
use App\MatchupStatus;
use App\Models\Bracket;
use App\Models\Candidate;
use App\Models\Matchup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BracketProgressor
{
    public function progressDue(): int
    {
        $progressed = 0;

        Bracket::query()
            ->whereNull('paused_at')
            ->where(function (Builder $query): void {
                $query
                    ->where(function (Builder $candidateQuery): void {
                        $candidateQuery
                            ->where('status', BracketStatus::Candidate)
                            ->where('candidate_phase_ends_at', '<=', now());
                    })
                    ->orWhere(function (Builder $ongoingQuery): void {
                        $ongoingQuery
                            ->where('status', BracketStatus::Ongoing)
                            ->whereHas('matchups', fn (Builder $matchups): Builder => $matchups
                                ->where('status', MatchupStatus::Open)
                                ->where('closes_at', '<=', now()));
                    });
            })
            ->eachById(function (Bracket $bracket) use (&$progressed): void {
                $this->progress($bracket);
                $progressed++;
            });

        return $progressed;
    }

    public function progress(Bracket $bracket): void
    {
        DB::transaction(function () use ($bracket): void {
            $lockedBracket = Bracket::query()->lockForUpdate()->findOrFail($bracket->id);

            if ($lockedBracket->paused_at !== null) {
                return;
            }

            if ($lockedBracket->status === BracketStatus::Candidate) {
                if ($lockedBracket->candidate_phase_ends_at->isFuture()) {
                    return;
                }

                $this->startTournament($lockedBracket);
            }

            if ($lockedBracket->status !== BracketStatus::Ongoing) {
                return;
            }

            $openMatchup = $lockedBracket->matchups()
                ->where('status', MatchupStatus::Open)
                ->lockForUpdate()
                ->first();

            if ($openMatchup === null || $openMatchup->closes_at->isFuture()) {
                return;
            }

            $this->closeMatchup($lockedBracket, $openMatchup);
        });
    }

    public function resolveTie(Matchup $matchup, Candidate $winner): void
    {
        DB::transaction(function () use ($matchup, $winner): void {
            $lockedMatchup = Matchup::query()->lockForUpdate()->findOrFail($matchup->id);
            $bracket = Bracket::query()->lockForUpdate()->findOrFail($lockedMatchup->bracket_id);

            if ($lockedMatchup->status !== MatchupStatus::Tied || ! $lockedMatchup->containsCandidate($winner->id)) {
                throw ValidationException::withMessages([
                    'candidate_id' => 'Select a candidate from the tied matchup.',
                ]);
            }

            $bracket->update(['status' => BracketStatus::Ongoing]);
            $this->advanceWinner($bracket, $lockedMatchup, $winner, MatchupStatus::Completed);
            $this->advanceByesAndOpenNext($bracket);
        });
    }

    public function advanceNow(Bracket $bracket): void
    {
        $bracket->refresh();

        if ($bracket->status === BracketStatus::Candidate) {
            if ($bracket->candidates()->count() < 2) {
                throw ValidationException::withMessages([
                    'advance' => 'Add at least two candidates before starting the bracket.',
                ]);
            }

            $bracket->update([
                'candidate_phase_ends_at' => now(),
                'paused_at' => null,
            ]);
            $this->progress($bracket);

            return;
        }

        if ($bracket->status === BracketStatus::Ongoing) {
            $matchup = $bracket->matchups()
                ->where('status', MatchupStatus::Open)
                ->first();

            if ($matchup === null) {
                throw ValidationException::withMessages([
                    'advance' => 'There is no open matchup to advance.',
                ]);
            }

            $bracket->update(['paused_at' => null]);
            $matchup->update(['closes_at' => now()]);
            $this->progress($bracket);

            return;
        }

        if ($bracket->status === BracketStatus::Tied) {
            throw ValidationException::withMessages([
                'advance' => 'Resolve the tied matchup before advancing.',
            ]);
        }

        throw ValidationException::withMessages([
            'advance' => 'This bracket is already complete.',
        ]);
    }

    public function togglePause(Bracket $bracket): bool
    {
        return DB::transaction(function () use ($bracket): bool {
            $lockedBracket = Bracket::query()->lockForUpdate()->findOrFail($bracket->id);

            if (! in_array($lockedBracket->status, [BracketStatus::Candidate, BracketStatus::Ongoing], true)) {
                throw ValidationException::withMessages([
                    'pause' => 'Only candidate phases and open matchups can be paused.',
                ]);
            }

            if ($lockedBracket->paused_at === null) {
                if (
                    $lockedBracket->status === BracketStatus::Ongoing
                    && ! $lockedBracket->matchups()->where('status', MatchupStatus::Open)->exists()
                ) {
                    throw ValidationException::withMessages([
                        'pause' => 'There is no open matchup to pause.',
                    ]);
                }

                $lockedBracket->update(['paused_at' => now()]);

                return true;
            }

            $pausedSeconds = max(0, (int) $lockedBracket->paused_at->diffInSeconds(now(), true));

            if ($lockedBracket->status === BracketStatus::Candidate) {
                $lockedBracket->update([
                    'candidate_phase_ends_at' => $lockedBracket->candidate_phase_ends_at->addSeconds($pausedSeconds),
                    'paused_at' => null,
                ]);
            } else {
                $matchup = $lockedBracket->matchups()
                    ->where('status', MatchupStatus::Open)
                    ->lockForUpdate()
                    ->firstOrFail();

                $matchup->update([
                    'closes_at' => $matchup->closes_at->addSeconds($pausedSeconds),
                ]);
                $lockedBracket->update(['paused_at' => null]);
            }

            return false;
        });
    }

    public function resetTournament(Bracket $bracket): void
    {
        DB::transaction(function () use ($bracket): void {
            $lockedBracket = Bracket::query()->lockForUpdate()->findOrFail($bracket->id);

            if ($lockedBracket->matchups()->doesntExist()) {
                throw ValidationException::withMessages([
                    'reset' => 'This bracket has not started yet.',
                ]);
            }

            if ($lockedBracket->candidates()->count() < 2) {
                throw ValidationException::withMessages([
                    'reset' => 'At least two candidates are required to reset the tournament.',
                ]);
            }

            $lockedBracket->matchups()->delete();
            $lockedBracket->update([
                'status' => BracketStatus::Candidate,
                'candidate_phase_ends_at' => now(),
                'bracket_size' => null,
                'total_rounds' => null,
                'started_at' => null,
                'completed_at' => null,
                'paused_at' => null,
                'winner_candidate_id' => null,
            ]);
        });

        $this->progress($bracket);
    }

    private function startTournament(Bracket $bracket): void
    {
        $candidates = $bracket->candidates()->get();
        $candidates = $candidates->every(fn (Candidate $candidate): bool => $candidate->seed !== null)
            ? $candidates->sortBy('seed')->values()
            : $candidates->shuffle()->values();

        if ($candidates->count() < 2) {
            $bracket->update([
                'status' => BracketStatus::Completed,
                'completed_at' => now(),
                'winner_candidate_id' => null,
            ]);

            return;
        }

        if ($bracket->matchups()->exists()) {
            $bracket->update(['status' => BracketStatus::Ongoing]);
            $this->advanceByesAndOpenNext($bracket);

            return;
        }

        $bracketSize = $this->nextPowerOfTwo($candidates->count());
        $rounds = (int) log($bracketSize, 2);
        $sequence = 0;

        $candidates->each(function (Candidate $candidate, int $index): void {
            $candidate->update(['seed' => $index + 1]);
        });

        $firstRoundSlots = $this->firstRoundSlots($candidates, $bracketSize);

        for ($round = 1; $round <= $rounds; $round++) {
            $matchupCount = intdiv($bracketSize, 2 ** $round);

            for ($position = 0; $position < $matchupCount; $position++) {
                $attributes = [
                    'round' => $round,
                    'position' => $position,
                    'sequence' => $sequence++,
                    'status' => MatchupStatus::Pending,
                ];

                if ($round === 1) {
                    $attributes['candidate_one_id'] = $firstRoundSlots[$position][0]?->id;
                    $attributes['candidate_two_id'] = $firstRoundSlots[$position][1]?->id;
                }

                $bracket->matchups()->create($attributes);
            }
        }

        $bracket->update([
            'status' => BracketStatus::Ongoing,
            'bracket_size' => $bracketSize,
            'total_rounds' => $rounds,
            'started_at' => now(),
        ]);

        $this->advanceByesAndOpenNext($bracket);
    }

    /**
     * @param  Collection<int, Candidate>  $candidates
     * @return array<int, array{0: Candidate|null, 1: Candidate|null}>
     */
    private function firstRoundSlots(Collection $candidates, int $bracketSize): array
    {
        $slots = [];
        $byeCount = $bracketSize - $candidates->count();
        $matchupCount = intdiv($bracketSize, 2);
        $fullMatchupCount = $matchupCount - $byeCount;
        $index = 0;

        for ($position = 0; $position < $matchupCount; $position++) {
            $first = $candidates->get($index++);
            $second = $position < $fullMatchupCount ? $candidates->get($index++) : null;
            $slots[] = [$first, $second];
        }

        return $slots;
    }

    private function closeMatchup(Bracket $bracket, Matchup $matchup): void
    {
        $totals = $matchup->votes()
            ->selectRaw('candidate_id, COUNT(*) as total')
            ->groupBy('candidate_id')
            ->pluck('total', 'candidate_id');

        $firstVotes = (int) ($totals[$matchup->candidate_one_id] ?? 0);
        $secondVotes = (int) ($totals[$matchup->candidate_two_id] ?? 0);

        if ($firstVotes === $secondVotes) {
            $matchup->update(['status' => MatchupStatus::Tied]);
            $bracket->update(['status' => BracketStatus::Tied]);

            return;
        }

        $winnerId = $firstVotes > $secondVotes
            ? $matchup->candidate_one_id
            : $matchup->candidate_two_id;

        $winner = Candidate::query()->findOrFail($winnerId);
        $this->advanceWinner($bracket, $matchup, $winner, MatchupStatus::Completed);
        $this->advanceByesAndOpenNext($bracket);
    }

    private function advanceByesAndOpenNext(Bracket $bracket): void
    {
        do {
            $advancedBye = false;

            $bracket->matchups()
                ->where('status', MatchupStatus::Pending)
                ->where('round', 1)
                ->orderBy('sequence')
                ->get()
                ->each(function (Matchup $matchup) use ($bracket, &$advancedBye): void {
                    $candidateIds = collect([
                        $matchup->candidate_one_id,
                        $matchup->candidate_two_id,
                    ])->filter()->values();

                    if ($candidateIds->count() === 1) {
                        $winner = Candidate::query()->findOrFail($candidateIds->first());
                        $this->advanceWinner($bracket, $matchup, $winner, MatchupStatus::Bye);
                        $advancedBye = true;
                    }
                });
        } while ($advancedBye && $bracket->fresh()->status !== BracketStatus::Completed);

        if ($bracket->fresh()->status === BracketStatus::Completed) {
            return;
        }

        $next = $bracket->matchups()
            ->where('status', MatchupStatus::Pending)
            ->whereNotNull('candidate_one_id')
            ->whereNotNull('candidate_two_id')
            ->orderBy('sequence')
            ->first();

        if ($next !== null) {
            $next->update([
                'status' => MatchupStatus::Open,
                'opens_at' => now(),
                'closes_at' => now()->addMinutes($bracket->matchup_duration_minutes),
            ]);
        }
    }

    private function advanceWinner(
        Bracket $bracket,
        Matchup $matchup,
        Candidate $winner,
        MatchupStatus $status,
    ): void {
        $matchup->update([
            'winner_candidate_id' => $winner->id,
            'status' => $status,
        ]);

        if ($matchup->round === $bracket->total_rounds) {
            $bracket->update([
                'status' => BracketStatus::Completed,
                'winner_candidate_id' => $winner->id,
                'completed_at' => now(),
            ]);

            return;
        }

        $next = $bracket->matchups()
            ->where('round', $matchup->round + 1)
            ->where('position', intdiv($matchup->position, 2))
            ->firstOrFail();

        $column = $matchup->position % 2 === 0 ? 'candidate_one_id' : 'candidate_two_id';
        $next->update([$column => $winner->id]);
    }

    private function nextPowerOfTwo(int $value): int
    {
        $power = 1;

        while ($power < $value) {
            $power *= 2;
        }

        return $power;
    }
}
