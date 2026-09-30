<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-slate-500">Your workspace</p>
                <h1 class="text-2xl font-black text-slate-950">Dashboard</h1>
            </div>
            <a href="{{ route('brackets.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-bold text-white hover:bg-slate-700">Create bracket</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($brackets as $bracket)
                <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                    <div class="flex items-center justify-between gap-3">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase tracking-wide text-slate-600">{{ str($bracket->status->value)->headline() }}</span>
                        <span class="text-sm text-slate-500">{{ $bracket->candidates_count }} candidates</span>
                    </div>
                    <h2 class="mt-5 text-xl font-black text-slate-950">{{ $bracket->name }}</h2>
                    <div class="mt-6 flex gap-3">
                        <a href="{{ route('brackets.show', $bracket) }}" class="flex-1 rounded-lg bg-slate-900 px-4 py-2 text-center text-sm font-bold text-white hover:bg-slate-700">View</a>
                        <a href="{{ route('brackets.edit', $bracket) }}" class="flex-1 rounded-lg bg-slate-200 px-4 py-2 text-center text-sm font-bold text-slate-900 hover:bg-slate-300">Edit</a>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl bg-white p-10 text-center shadow-sm ring-1 ring-slate-200 sm:col-span-2 lg:col-span-3">
                    <h2 class="text-xl font-black text-slate-950">Create your first bracket</h2>
                    <p class="mt-2 text-slate-600">Set a candidate window and matchup timer, then invite the community to vote.</p>
                    <a href="{{ route('brackets.create') }}" class="mt-5 inline-flex rounded-lg bg-slate-900 px-5 py-3 font-bold text-white hover:bg-slate-700">Get started</a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
