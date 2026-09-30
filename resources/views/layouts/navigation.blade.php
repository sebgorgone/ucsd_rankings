<nav x-data="{ open: false }" class="border-b border-slate-700 bg-slate-900 text-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex gap-8">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3 font-black">
                    <x-application-logo class="h-9 w-auto" />
                    <span class="hidden sm:inline">UCSD Rankings</span>
                </a>

                <div class="hidden items-center gap-6 sm:flex">
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link>
                    @endauth
                    <x-nav-link :href="route('brackets.index')" :active="request()->routeIs('brackets.index', 'brackets.show')">Browse</x-nav-link>
                    @auth
                        <x-nav-link :href="route('brackets.create')" :active="request()->routeIs('brackets.create')">Create</x-nav-link>
                        @if (Auth::user()->is_admin)
                            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">Users</x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="hidden items-center sm:flex">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-700 hover:text-white">
                                <span>{{ Auth::user()->name }}</span>
                                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log out</x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="text-sm font-bold text-slate-300 hover:text-white">Log in</a>
                        <a href="{{ route('register') }}" class="rounded-lg bg-white px-4 py-2 text-sm font-bold text-slate-900">Register</a>
                    </div>
                @endauth
            </div>

            <button @click="open = ! open" class="my-auto rounded-lg p-2 text-slate-300 hover:bg-slate-800 sm:hidden" aria-label="Toggle navigation">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /><path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </div>

    <div x-show="open" x-cloak class="border-t border-slate-700 px-4 py-4 sm:hidden">
        <div class="grid gap-2">
            @auth
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-responsive-nav-link>
            @endauth
            <x-responsive-nav-link :href="route('brackets.index')" :active="request()->routeIs('brackets.index', 'brackets.show')">Browse brackets</x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('brackets.create')" :active="request()->routeIs('brackets.create')">Create bracket</x-responsive-nav-link>
                @if (Auth::user()->is_admin)
                    <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">Users</x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">Profile</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log out</x-responsive-nav-link>
                </form>
            @else
                <x-responsive-nav-link :href="route('login')" :active="request()->routeIs('login')">Log in</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('register')" :active="request()->routeIs('register')">Register</x-responsive-nav-link>
            @endauth
        </div>
    </div>
</nav>
