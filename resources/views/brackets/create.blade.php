<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-slate-500">New tournament</p>
                <h1 class="text-2xl font-bold text-slate-950">Create a bracket</h1>
            </div>
            <a href="{{ route('brackets.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-950">Browse brackets</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('brackets.store') }}" class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
            @csrf
            @include('brackets._form')

            <div class="mt-8 flex justify-end">
                <x-primary-button>Create bracket</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
