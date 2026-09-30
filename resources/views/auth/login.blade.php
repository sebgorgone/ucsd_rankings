<x-guest-layout>
    <div class="mb-7">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Welcome back</p>
        <h1 class="mt-2 text-2xl font-black text-white">Log in to your account</h1>
        <p class="mt-2 text-sm leading-6 text-slate-400">Vote in active matchups and manage the brackets you create.</p>
    </div>

    <x-auth-session-status class="mb-5 rounded-xl bg-slate-800 px-4 py-3 text-slate-200" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="grid gap-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-2 block w-full border-slate-600 bg-slate-800 text-white placeholder:text-slate-500 focus:border-slate-400 focus:ring-slate-400"
                type="email" name="email" :value="old('email')" placeholder="you@example.com" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-2 block w-full border-slate-600 bg-slate-800 text-white placeholder:text-slate-500 focus:border-slate-400 focus:ring-slate-400"
                type="password" name="password" placeholder="Your password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center gap-2">
                <input id="remember_me" type="checkbox" class="rounded border-slate-600 bg-slate-800 text-slate-200 shadow-sm focus:ring-slate-400 focus:ring-offset-slate-900" name="remember">
                <span class="text-sm text-slate-300">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="rounded text-sm font-semibold text-slate-300 hover:text-white focus:outline-none focus:ring-2 focus:ring-slate-400" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <x-primary-button class="w-full justify-center !bg-white py-3 !text-slate-950 hover:!bg-slate-200 focus:ring-white focus:ring-offset-slate-900">
            {{ __('Log in') }}
        </x-primary-button>

        <p class="text-center text-sm text-slate-400">
            New to UCSD Rankings?
            <a href="{{ route('register') }}" class="font-bold text-white hover:text-slate-200">Create an account</a>
        </p>
    </form>
</x-guest-layout>
