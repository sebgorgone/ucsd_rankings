<x-guest-layout>
    <div class="mb-7">
        <p class="text-xs font-bold uppercase tracking-[0.25em] text-slate-400">Join the community</p>
        <h1 class="mt-2 text-2xl font-black text-white">Create your account</h1>
        <p class="mt-2 text-sm leading-6 text-slate-400">Create public brackets, submit candidates, and vote in active matchups.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="grid gap-5">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="mt-2 block w-full border-slate-600 bg-slate-800 text-white placeholder:text-slate-500 focus:border-slate-400 focus:ring-slate-400"
                type="text" name="name" :value="old('name')" placeholder="Your name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-2 block w-full border-slate-600 bg-slate-800 text-white placeholder:text-slate-500 focus:border-slate-400 focus:ring-slate-400"
                type="email" name="email" :value="old('email')" placeholder="you@example.com" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-2 block w-full border-slate-600 bg-slate-800 text-white placeholder:text-slate-500 focus:border-slate-400 focus:ring-slate-400"
                type="password" name="password" placeholder="Create a password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="mt-2 block w-full border-slate-600 bg-slate-800 text-white placeholder:text-slate-500 focus:border-slate-400 focus:ring-slate-400"
                type="password" name="password_confirmation" placeholder="Repeat your password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full justify-center !bg-white py-3 !text-slate-950 hover:!bg-slate-200 focus:ring-white focus:ring-offset-slate-900">
            {{ __('Create account') }}
        </x-primary-button>

        <p class="text-center text-sm text-slate-400">
            Already have an account?
            <a href="{{ route('login') }}" class="font-bold text-white hover:text-slate-200">Log in</a>
        </p>
    </form>
</x-guest-layout>
