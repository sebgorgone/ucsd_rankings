<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-slate-500">User details</p>
                <h1 class="text-2xl font-black text-slate-950">{{ $user->name }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ $user->email }}</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm font-bold text-slate-900 hover:bg-slate-300">All users</a>
        </div>
    </x-slot>

    <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:px-6 lg:px-8">
        <section>
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-slate-500">Owned brackets</p>
                    <h2 class="mt-1 text-2xl font-black text-slate-950">{{ $user->brackets->count() }} created</h2>
                </div>
            </div>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($user->brackets as $bracket)
                    <a href="{{ route('brackets.show', $bracket) }}" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 hover:shadow-md">
                        <span class="text-xs font-black uppercase tracking-wide text-slate-500">{{ str($bracket->status->value)->headline() }}</span>
                        <h3 class="mt-2 text-lg font-black text-slate-950">{{ $bracket->name }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ $bracket->candidates_count }} candidates</p>
                    </a>
                @empty
                    <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-slate-500 sm:col-span-2 lg:col-span-3">This user has not created a bracket.</p>
                @endforelse
            </div>
        </section>

        <section>
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-slate-500">Voting activity</p>
                <h2 class="mt-1 text-2xl font-black text-slate-950">{{ $user->votes_count }} total votes</h2>
            </div>
            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($votedBrackets as $bracket)
                    <a href="{{ route('brackets.show', $bracket) }}" class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200 hover:shadow-md">
                        <span class="text-xs font-black uppercase tracking-wide text-slate-500">{{ str($bracket->status->value)->headline() }}</span>
                        <h3 class="mt-2 text-lg font-black text-slate-950">{{ $bracket->name }}</h3>
                        <p class="mt-2 text-sm text-slate-500">Voted in {{ $bracket->voted_matchups_count }} matchup(s)</p>
                    </a>
                @empty
                    <p class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-slate-500 sm:col-span-2 lg:col-span-3">This user has not voted in a bracket.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
