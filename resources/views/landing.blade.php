<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'UCSD Rankings') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        <header class="relative overflow-hidden bg-slate-950 text-white">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -left-24 top-20 h-80 w-80 rounded-full bg-slate-700/30 blur-3xl"></div>
                <div class="absolute -right-24 top-0 h-96 w-96 rounded-full bg-slate-800/60 blur-3xl"></div>
                <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-slate-600 to-transparent"></div>
            </div>

            <nav class="relative z-10 mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-5 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <x-application-logo class="h-10 w-auto" />
                    <span class="hidden text-base font-black tracking-wide sm:inline">UCSD Rankings</span>
                </a>

                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="#brackets" class="hidden px-3 py-2 text-sm font-bold text-slate-300 hover:text-white sm:block">Explore</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-sm font-bold text-slate-300 hover:bg-slate-800 hover:text-white sm:px-4">Dashboard</a>
                        <a href="{{ route('brackets.create') }}" class="rounded-lg bg-white px-3 py-2 text-sm font-black text-slate-950 hover:bg-slate-200 sm:px-4">Create bracket</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-bold text-slate-300 hover:bg-slate-800 hover:text-white sm:px-4">Log in</a>
                        <a href="{{ route('register') }}" class="rounded-lg bg-white px-3 py-2 text-sm font-black text-slate-950 hover:bg-slate-200 sm:px-4">Get started</a>
                    @endauth
                </div>
            </nav>

            <div class="relative z-10 mx-auto grid max-w-7xl items-center gap-12 px-4 pb-20 pt-12 sm:px-6 sm:pb-24 sm:pt-16 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:pb-28">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-slate-700 bg-slate-900/80 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.2em] text-slate-300">
                        <span class="h-2 w-2 rounded-full bg-white"></span>
                        Live Brackets for the chuds
                    </div>

                    <h1 class="mt-6 text-2xl font-black leading-[1.05] tracking-tight sm:text-6xl lg:text-7xl">
                        holon holon,<br>
                        <span class="text-slate-400 text-2xl">... LEMME WRITE THIS DOWN</span>
                    </h1>



                    <div class="mt-8 flex flex-wrap gap-3">
                        @auth
                            <a href="{{ route('brackets.create') }}" class="rounded-xl bg-white px-5 py-3 font-black text-slate-950 shadow-lg shadow-black/20 transition hover:-translate-y-0.5 hover:bg-slate-200">Create a bracket</a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-xl bg-white px-5 py-3 font-black text-slate-950 shadow-lg shadow-black/20 transition hover:-translate-y-0.5 hover:bg-slate-200">Create your first bracket</a>
                        @endauth
                        <a href="#brackets" class="rounded-xl border border-slate-600 bg-slate-900/60 px-5 py-3 font-bold text-white transition hover:border-slate-400 hover:bg-slate-800">Browse live brackets</a>
                    </div>

                    <div class="mt-10 flex flex-wrap gap-x-8 gap-y-4 border-t border-slate-800 pt-6 text-sm text-slate-400">
                        <span><strong class="block text-lg font-black text-white">Public</strong>Anyone can follow</span>
                        <span><strong class="block text-lg font-black text-white">Timed</strong>Every vote matters</span>
                        <span><strong class="block text-lg font-black text-white">Persistent</strong>Winners stay recorded</span>
                    </div>
                </div>

                <div class="relative mx-auto w-full max-w-xl">
                    <div class="absolute -inset-6 rounded-[2rem] bg-slate-700/20 blur-2xl"></div>
                    <div class="relative overflow-hidden rounded-3xl border border-slate-700 bg-slate-900/90 p-5 shadow-2xl shadow-black/30 backdrop-blur sm:p-7">
                        <div class="flex items-center justify-between border-b border-slate-700 pb-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-500">Featured bracket</p>
                                <p class="mt-1 font-black text-white">Malfunctions</p>
                            </div>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-black text-slate-950">Voting now</span>
                        </div>

                        <div class="mt-6 grid grid-cols-[1fr_2rem_1fr_2rem_1fr] items-center gap-y-4 text-xs sm:text-sm">
                            <div class="grid gap-3">
                                <div class="rounded-xl border border-slate-600 bg-slate-800 p-3 font-bold">PC in Tow</div>
                                <div class="rounded-xl border border-slate-700 bg-slate-800/60 p-3 text-slate-400">Bow Tie</div>
                                <div class="rounded-xl border border-slate-700 bg-slate-800/60 p-3 text-slate-400">Premature Deployment</div>
                                <div class="rounded-xl border border-slate-700 bg-slate-800/60 p-3 text-slate-400">Toggle Lock</div>
                            </div>
                            <div class="grid h-full grid-rows-2 items-center text-center text-slate-600">›<span>›</span></div>
                            <div class="grid gap-12">
                                <div class="rounded-xl border border-white bg-slate-700 p-3 font-bold text-white shadow-lg">Bow Tie</div>
                                <div class="rounded-xl border border-slate-600 bg-slate-800 p-3 font-bold">Premature Deployment</div>
                            </div>
                            <div class="text-center text-slate-600">›</div>
                            <div class="rounded-xl border border-dashed border-slate-500 bg-slate-800/50 p-3 text-center font-bold text-slate-400">Final</div>
                        </div>

                        <div class="mt-6 flex items-center justify-between rounded-xl bg-slate-800 px-4 py-3">
                            <span class="text-sm text-slate-400">Current matchup closes in</span>
                            <span class="font-black tabular-nums text-white">00:42:18</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main id="brackets" class="mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-16 lg:px-8">
            <div class="flex flex-col gap-5 border-b border-slate-200 pb-7 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-500">Community brackets</p>
                    <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Pick a bracket and cast your vote.</h2>
                </div>

                <div class="flex w-fit rounded-xl bg-slate-200/70 p-1">
                    <a href="{{ route('brackets.index') }}" @class(['rounded-lg px-4 py-2 text-sm font-black transition', 'bg-slate-950 text-white shadow-sm' => ! $filter, 'text-slate-600 hover:text-slate-950' => $filter])>All</a>
                    <a href="{{ route('brackets.index', ['status' => 'ongoing']) }}" @class(['rounded-lg px-4 py-2 text-sm font-black transition', 'bg-slate-950 text-white shadow-sm' => $filter === 'ongoing', 'text-slate-600 hover:text-slate-950' => $filter !== 'ongoing'])>Ongoing</a>
                    <a href="{{ route('brackets.index', ['status' => 'completed']) }}" @class(['rounded-lg px-4 py-2 text-sm font-black transition', 'bg-slate-950 text-white shadow-sm' => $filter === 'completed', 'text-slate-600 hover:text-slate-950' => $filter !== 'completed'])>Completed</a>
                </div>
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($brackets as $bracket)
                    <a href="{{ route('brackets.show', $bracket) }}" class="group flex min-h-64 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:border-slate-300 hover:shadow-xl">
                        <div class="h-1.5 bg-slate-900"></div>
                        <div class="flex flex-1 flex-col p-6">
                            <div class="flex items-center justify-between gap-3">
                                <span @class([
                                    'rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide',
                                    'bg-slate-900 text-white' => $bracket->status !== \App\BracketStatus::Completed,
                                    'bg-slate-200 text-slate-700' => $bracket->status === \App\BracketStatus::Completed,
                                ])>{{ str($bracket->status->value)->headline() }}</span>
                                <span class="text-xs font-bold text-slate-400">{{ $bracket->candidates_count }} candidates</span>
                            </div>

                            <h3 class="mt-6 text-2xl font-black leading-tight text-slate-950 transition group-hover:text-slate-600">{{ $bracket->name }}</h3>
                            <p class="mt-2 text-sm text-slate-500">Created by {{ $bracket->user?->name ?? 'Deleted user' }}</p>

                            <div class="mt-auto pt-6">
                                @if ($bracket->winner)
                                    <div class="rounded-xl bg-slate-100 px-4 py-3">
                                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Winner</span>
                                        <span class="mt-1 block font-black text-slate-900">{{ $bracket->winner->name }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between border-t border-slate-100 pt-4 text-sm font-bold text-slate-600">
                                        <span>View bracket</span>
                                        <span class="transition group-hover:translate-x-1">→</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center sm:col-span-2 lg:col-span-3">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-900 text-2xl text-white">＋</div>
                        <h3 class="mt-5 text-xl font-black text-slate-950">No brackets here yet</h3>
                        <p class="mt-2 text-slate-500">Be the first to start a community ranking.</p>
                        @auth
                            <a href="{{ route('brackets.create') }}" class="mt-5 inline-flex rounded-xl bg-slate-950 px-5 py-3 font-black text-white hover:bg-slate-700">Create a bracket</a>
                        @else
                            <a href="{{ route('register') }}" class="mt-5 inline-flex rounded-xl bg-slate-950 px-5 py-3 font-black text-white hover:bg-slate-700">Get started</a>
                        @endauth
                    </div>
                @endforelse
            </div>

            <div class="mt-10">{{ $brackets->links() }}</div>
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-8 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <div class="flex items-center gap-3 font-black text-slate-900">
                    <x-application-logo class="h-8 w-auto" />
                    UCSD Rankings
                </div>
                <p>Public brackets powered by community votes.</p>
            </div>
        </footer>
    </body>
</html>
