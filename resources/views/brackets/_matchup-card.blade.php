@php
    $firstVotes = (int) ($voteTotals->get($matchup->id)?->get($matchup->candidate_one_id) ?? 0);
    $secondVotes = (int) ($voteTotals->get($matchup->id)?->get($matchup->candidate_two_id) ?? 0);
    $isCurrent = $currentMatchup?->id === $matchup->id;
@endphp

<article @class([
    'overflow-hidden rounded-xl border-2 bg-white shadow-sm',
    'border-slate-900 ring-4 ring-slate-300' => $isCurrent,
    'border-slate-300' => ! $isCurrent,
])>
    <div class="flex items-center justify-between bg-slate-50 px-3 py-2 text-xs font-bold uppercase tracking-wide text-slate-500">
        <span>Match {{ $matchup->sequence + 1 }}</span>
        <span>{{ str($matchup->status->value)->headline() }}</span>
    </div>

    @if ($matchup->status === \App\MatchupStatus::Open)
        <div x-data="{ deadline: new Date('{{ $matchup->closes_at->toIso8601String() }}').getTime(), paused: @js($bracket->paused_at !== null), remaining: '' }"
            x-init="const tick = () => { if (paused) { remaining = 'Paused'; return; } const distance = deadline - Date.now(); if (distance <= 0) { remaining = 'Closing…'; window.location.reload(); return; } const hours = Math.floor(distance / 3600000); const minutes = Math.floor((distance % 3600000) / 60000); const seconds = Math.floor((distance % 60000) / 1000); remaining = `${hours}h ${minutes}m ${seconds}s`; }; tick(); setInterval(tick, 1000)"
            class="border-t border-slate-200 bg-slate-100 px-3 py-1.5 text-center text-xs font-black tabular-nums text-slate-700"
            x-text="remaining"></div>
    @endif

    <div class="divide-y divide-slate-200">
        @foreach ([$matchup->candidateOne, $matchup->candidateTwo] as $candidate)
            @php
                $candidateVotes = $candidate
                    ? ($candidate->id === $matchup->candidate_one_id ? $firstVotes : $secondVotes)
                    : 0;
            @endphp
            <div @class([
                'flex min-h-14 items-center gap-3 px-3 py-2',
                'bg-slate-50 font-black text-slate-950' => $candidate && $matchup->winner_candidate_id === $candidate->id,
                'text-slate-500' => ! $candidate,
            ])>
                <span class="min-w-0 flex-1 truncate">
                    @if ($candidate)
                        <span class="mr-2 text-xs text-slate-400">{{ $candidate->seed }}</span>{{ $candidate->name }}
                    @else
                        TBD
                    @endif
                </span>

                @if ($candidate && $matchup->status === \App\MatchupStatus::Open)
                    @auth
                        <form method="POST" action="{{ route('matchups.vote', $matchup) }}">
                            @csrf
                            @method('PUT')
                            <button name="candidate_id" value="{{ $candidate->id }}"
                                @class([
                                    'shrink-0 rounded-lg px-3 py-1.5 text-xs font-black transition',
                                    'bg-slate-900 text-white' => $currentVoteCandidateId === $candidate->id,
                                    'bg-slate-200 text-slate-900 hover:bg-slate-300' => $currentVoteCandidateId !== $candidate->id,
                                ])>
                                {{ $currentVoteCandidateId === $candidate->id ? 'Voted' : 'Vote' }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="shrink-0 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-black text-white">Vote</a>
                    @endauth
                @elseif ($candidate && $matchup->status === \App\MatchupStatus::Tied && $canResolveTie)
                    <form method="POST" action="{{ route('matchups.resolve-tie', $matchup) }}">
                        @csrf
                        @method('PUT')
                        <button name="candidate_id" value="{{ $candidate->id }}" class="shrink-0 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-black text-white hover:bg-slate-700">
                            Advance
                        </button>
                    </form>
                @elseif ($candidate && in_array($matchup->status, [\App\MatchupStatus::Tied, \App\MatchupStatus::Completed], true))
                    <span class="shrink-0 rounded bg-slate-100 px-2 py-1 text-xs font-bold text-slate-600">{{ $candidateVotes }}</span>
                @endif
            </div>
        @endforeach
    </div>
</article>
