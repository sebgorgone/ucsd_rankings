<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-slate-500">Manage bracket</p>
                <h1 class="text-2xl font-bold text-slate-950">{{ $bracket->name }}</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('brackets.stats', $bracket) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-300">Matchup stats</a>
                <a href="{{ route('brackets.show', $bracket) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">View bracket</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-5xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-5 lg:px-8">
        @if (session('status'))
            <div class="rounded-xl bg-slate-800 px-4 py-3 text-sm font-semibold text-white lg:col-span-5">{{ session('status') }}</div>
        @endif
        <x-input-error :messages="$errors->get('advance')" class="rounded-xl bg-red-50 px-4 py-3 lg:col-span-5" />
        <x-input-error :messages="$errors->get('pause')" class="rounded-xl bg-red-50 px-4 py-3 lg:col-span-5" />
        <x-input-error :messages="$errors->get('reset')" class="rounded-xl bg-red-50 px-4 py-3 lg:col-span-5" />

        @if ($bracket->status !== \App\BracketStatus::Completed)
            <section class="rounded-2xl border border-slate-700 bg-slate-900 p-5 text-white lg:col-span-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Stage controls</p>
                        <h2 class="mt-1 text-lg font-black">
                            @if ($bracket->status === \App\BracketStatus::Candidate)
                                End candidate submissions and start voting
                            @elseif ($bracket->status === \App\BracketStatus::Tied)
                                Resolve the tied matchup
                            @else
                                Close the current matchup and tally votes
                            @endif
                        </h2>
                        <p class="mt-1 text-sm text-slate-300">This action advances immediately and cannot reopen a completed stage.</p>
                    </div>

                    @if ($bracket->status === \App\BracketStatus::Tied)
                        @php($tiedMatchup = $bracket->matchups->firstWhere('status', \App\MatchupStatus::Tied))
                        @if ($tiedMatchup)
                            <div class="flex flex-wrap gap-2">
                                @foreach ([$tiedMatchup->candidateOne, $tiedMatchup->candidateTwo] as $candidate)
                                    <form method="POST" action="{{ route('matchups.resolve-tie', $tiedMatchup) }}">
                                        @csrf
                                        @method('PUT')
                                        <button name="candidate_id" value="{{ $candidate->id }}" class="rounded-lg bg-white px-4 py-2 text-sm font-black text-slate-950 hover:bg-slate-200">
                                            Advance {{ $candidate->name }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <div class="flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('brackets.pause', $bracket) }}">
                                @csrf
                                @method('PUT')
                                <button class="rounded-lg border border-slate-500 px-5 py-3 text-sm font-black text-white hover:bg-slate-800">
                                    {{ $bracket->paused_at ? 'Resume timer' : 'Pause timer' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('brackets.advance', $bracket) }}">
                                @csrf
                                @method('PUT')
                                <button class="rounded-lg bg-white px-5 py-3 text-sm font-black text-slate-950 hover:bg-slate-200">
                                    Advance now
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if ($bracket->matchups->isNotEmpty())
            <section class="rounded-2xl border border-red-200 bg-red-50 p-5 lg:col-span-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-red-600">Reset tournament</p>
                        <h2 class="mt-1 text-lg font-black text-red-950">Return to matchup one</h2>
                        <p class="mt-1 text-sm text-red-700">Keeps the candidates and original seeds, but permanently removes every vote and matchup result.</p>
                    </div>
                    <form method="POST" action="{{ route('brackets.reset', $bracket) }}" onsubmit="return confirm('Reset this bracket to matchup one and permanently remove all votes?')">
                        @csrf
                        @method('PUT')
                        <button class="rounded-lg bg-red-700 px-5 py-3 text-sm font-black text-white hover:bg-red-800">
                            Reset to matchup one
                        </button>
                    </form>
                </div>
            </section>
        @endif

        <form method="POST" action="{{ route('brackets.update', $bracket) }}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 lg:col-span-3">
            @csrf
            @method('PATCH')
            @include('brackets._form')

            <div class="mt-8 flex justify-end">
                <x-primary-button>Save settings</x-primary-button>
            </div>
        </form>

        <aside class="rounded-2xl bg-slate-900 p-6 text-white shadow-sm lg:col-span-2">
            <h2 class="text-lg font-bold">Candidates ({{ $bracket->candidates->count() }})</h2>
            <p class="mt-1 text-sm text-slate-300">
                @if ($bracket->status === \App\BracketStatus::Candidate)
                    @if ($bracket->paused_at)
                        Candidate phase timer is paused.
                    @else
                        Candidate phase closes {{ $bracket->candidate_phase_ends_at->diffForHumans() }}.
                    @endif
                @else
                    Seeding is locked because voting has started.
                @endif
            </p>

            @if ($bracket->isAcceptingCandidates())
                <form method="POST" action="{{ route('brackets.candidates.store', $bracket) }}" class="mt-5 flex gap-2">
                    @csrf
                    <input name="name" value="{{ old('name') }}" required maxlength="120"
                        class="min-w-0 flex-1 rounded-lg border-slate-600 bg-slate-800 text-white placeholder:text-slate-400 focus:border-white focus:ring-white"
                        placeholder="Candidate name">
                    <button class="rounded-lg bg-white px-4 py-2 text-sm font-bold text-slate-900 hover:bg-slate-200">Add</button>
                </form>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            @endif

            <ol class="mt-6 grid gap-2">
                @forelse ($bracket->candidates as $candidate)
                    <li class="rounded-lg bg-slate-800 px-3 py-2 text-sm">{{ $candidate->name }}</li>
                @empty
                    <li class="text-sm text-slate-400">No candidates yet.</li>
                @endforelse
            </ol>
        </aside>
    </div>
</x-app-layout>
