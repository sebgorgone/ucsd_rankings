<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-slate-500">Private vote details</p>
                <h1 class="text-2xl font-black text-slate-950">{{ $bracket->name }} stats</h1>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('brackets.edit', $bracket) }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-bold text-slate-900 hover:bg-slate-300">Manage</a>
                <a href="{{ route('brackets.show', $bracket) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-700">View bracket</a>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-6">
            @forelse ($bracket->matchups as $matchup)
                <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 bg-slate-50 px-5 py-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-widest text-slate-500">Round {{ $matchup->round }} · Match {{ $matchup->sequence + 1 }}</p>
                            <h2 class="mt-1 font-black text-slate-950">
                                {{ $matchup->candidateOne?->name ?? 'TBD' }} vs. {{ $matchup->candidateTwo?->name ?? 'TBD' }}
                            </h2>
                        </div>
                        <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-black uppercase tracking-wide text-white">{{ str($matchup->status->value)->headline() }}</span>
                    </div>

                    <div class="grid divide-y divide-slate-200 md:grid-cols-2 md:divide-x md:divide-y-0">
                        @foreach ([$matchup->candidateOne, $matchup->candidateTwo] as $candidate)
                            @php($candidateVotes = $candidate ? $matchup->votes->where('candidate_id', $candidate->id) : collect())
                            <div class="p-5">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="text-lg font-black text-slate-950">{{ $candidate?->name ?? 'TBD' }}</h3>
                                    <span class="rounded-lg bg-slate-100 px-3 py-1 text-sm font-black text-slate-700">{{ $candidateVotes->count() }} votes</span>
                                </div>

                                <div class="mt-4 grid gap-2">
                                    @forelse ($candidateVotes as $vote)
                                        <div class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 px-3 py-2">
                                            <span class="font-semibold text-slate-900">{{ $vote->user?->name ?? 'Deleted user' }}</span>
                                            <span class="text-sm text-slate-500">{{ $vote->user?->email ?? 'Account deleted' }}</span>
                                        </div>
                                    @empty
                                        <p class="rounded-lg border border-dashed border-slate-200 px-3 py-4 text-center text-sm text-slate-500">No votes for this candidate.</p>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                    <h2 class="font-black text-slate-950">No matchups yet</h2>
                    <p class="mt-2 text-slate-500">Matchup statistics will appear after the candidate phase ends.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
