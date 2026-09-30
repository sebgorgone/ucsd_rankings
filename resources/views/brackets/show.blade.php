<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-slate-500">
                    {{ $bracket->paused_at ? 'Paused' : str($bracket->status->value)->headline() }}
                </p>
                <h1 class="text-2xl font-bold text-slate-950">{{ $bracket->name }}</h1>
                <p class="mt-1 text-sm text-slate-600">Created by {{ $bracket->user?->name ?? 'Deleted user' }}</p>
            </div>
            @if ($canEdit)
                <div class="flex gap-2">
                    <a href="{{ route('brackets.stats', $bracket) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-bold text-slate-900 hover:bg-slate-300">Matchup stats</a>
                    <a href="{{ route('brackets.edit', $bracket) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-700">Edit bracket</a>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="mx-auto max-w-[96rem] px-4 py-8 sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-6 rounded-xl bg-slate-800 px-4 py-3 text-sm font-semibold text-white">{{ session('status') }}</div>
        @endif

        @if ($bracket->status === \App\BracketStatus::Candidate)
            <section class="grid gap-6 lg:grid-cols-3">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-2">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-950">Candidate phase</h2>
                            <p class="mt-1 text-slate-600">Voting begins automatically after the deadline.</p>
                        </div>
                        <div x-data="{ deadline: new Date('{{ $bracket->candidate_phase_ends_at->toIso8601String() }}').getTime(), paused: @js($bracket->paused_at !== null), remaining: '' }"
                            x-init="const tick = () => { if (paused) { remaining = 'Paused'; return; } const distance = deadline - Date.now(); if (distance <= 0) { remaining = 'Starting…'; window.location.reload(); return; } const days = Math.floor(distance / 86400000); const hours = Math.floor((distance % 86400000) / 3600000); const minutes = Math.floor((distance % 3600000) / 60000); remaining = `${days}d ${hours}h ${minutes}m`; }; tick(); setInterval(tick, 30000)"
                            class="rounded-xl bg-slate-100 px-4 py-3 text-right">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-500">Time remaining</span>
                            <span class="font-bold text-slate-950" x-text="remaining"></span>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        @forelse ($bracket->candidates as $candidate)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-semibold text-slate-800">{{ $candidate->name }}</div>
                        @empty
                            <p class="text-slate-500">No candidates have been added.</p>
                        @endforelse
                    </div>
                </div>

                <aside class="rounded-2xl bg-slate-900 p-6 text-white">
                    <h2 class="text-lg font-bold">Add a candidate</h2>
                    @auth
                        @if ($canAddCandidate)
                            <form method="POST" action="{{ route('brackets.candidates.store', $bracket) }}" class="mt-4 grid gap-3">
                                @csrf
                                <input name="name" value="{{ old('name') }}" required maxlength="120"
                                    class="rounded-lg border-slate-600 bg-slate-800 text-white placeholder:text-slate-400 focus:border-white focus:ring-white"
                                    placeholder="Candidate name">
                                <x-input-error :messages="$errors->get('name')" />
                                <button class="rounded-lg bg-white px-4 py-2 font-bold text-slate-900 hover:bg-slate-200">Submit candidate</button>
                            </form>
                        @else
                            <p class="mt-3 text-sm text-slate-300">Only the bracket owner can add candidates.</p>
                        @endif
                    @else
                        <p class="mt-3 text-sm text-slate-300">Sign in to submit a candidate when community submissions are enabled.</p>
                        <a href="{{ route('login') }}" class="mt-4 inline-flex rounded-lg bg-white px-4 py-2 font-bold text-slate-900">Sign in</a>
                    @endauth
                </aside>
            </section>
        @elseif ($bracket->matchups->isNotEmpty())
            @if ($bracket->status === \App\BracketStatus::Completed)
                <div class="mb-6 rounded-2xl bg-slate-900 p-6 text-center text-white">
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-300">Champion</p>
                    <p class="mt-2 text-3xl font-black">{{ $bracket->winner?->name ?? 'No winner' }}</p>
                </div>
            @endif

            <div class="lg:hidden">
                @if ($mobileMatchup)
                    <div class="mb-3">
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">
                            {{ $currentMatchup ? 'Current matchup' : 'Final result' }}
                        </p>
                        <h2 class="mt-1 text-xl font-black text-slate-950">
                            {{ $currentMatchup ? 'Cast your vote' : 'This bracket has concluded' }}
                        </h2>
                    </div>
                    @include('brackets._matchup-card', ['matchup' => $mobileMatchup])
                @else
                    <div class="rounded-2xl bg-white p-6 text-center shadow-sm ring-1 ring-slate-200">
                        <p class="font-bold text-slate-600">The next matchup is being prepared.</p>
                    </div>
                @endif
            </div>

            <div class="hidden overflow-x-auto rounded-2xl bg-slate-200 p-6 shadow-inner lg:block">
                <div class="mb-5 flex min-w-max gap-16">
                    @foreach ($rounds as $roundNumber => $matchups)
                        <h2 class="w-72 shrink-0 text-center text-sm font-black uppercase tracking-widest text-slate-600">
                                {{ $roundNumber == $bracket->total_rounds ? 'Final' : 'Round '.$roundNumber }}
                        </h2>
                    @endforeach
                </div>
                <div class="bracket-grid min-w-max" style="--bracket-size: {{ $bracket->bracket_size }}">
                    @foreach ($rounds as $roundNumber => $matchups)
                        <section class="bracket-round-grid">
                            @foreach ($matchups as $matchup)
                                @php
                                    $rowSpan = 2 ** $roundNumber;
                                    $rowStart = ($matchup->position * $rowSpan) + 1;
                                    $hasNextRound = $roundNumber < $bracket->total_rounds;
                                    $connectorOffset = (2 ** ($roundNumber - 1)) * 6;
                                @endphp
                                <div @class([
                                    'bracket-match',
                                    'has-next bracket-match-upper' => $hasNextRound && $matchup->position % 2 === 0,
                                    'has-next bracket-match-lower' => $hasNextRound && $matchup->position % 2 === 1,
                                ])
                                    style="grid-row: {{ $rowStart }} / span {{ $rowSpan }}; --connector-offset: {{ $connectorOffset }}rem">
                                    @include('brackets._matchup-card', ['matchup' => $matchup])
                                    @if ($hasNextRound)
                                        <span class="bracket-connector" aria-hidden="true"></span>
                                    @endif
                                </div>
                            @endforeach
                        </section>
                    @endforeach
                </div>
            </div>
        @else
            <div class="rounded-2xl bg-white p-10 text-center shadow-sm ring-1 ring-slate-200">
                <h2 class="text-xl font-bold text-slate-950">Completed without a winner</h2>
                <p class="mt-2 text-slate-600">The candidate phase ended before two candidates were submitted.</p>
            </div>
        @endif
    </div>
</x-app-layout>
