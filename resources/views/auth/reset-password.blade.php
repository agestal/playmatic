<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}" class="pm-auth-stack">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <h1 class="text-lg font-semibold text-mono">{{ __('Reset password') }}</h1>
            <p class="mt-1 text-sm text-secondary-foreground">{{ __('Set a new password for your account.') }}</p>
        </div>

        <div class="space-y-1.5">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="space-y-1.5">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="space-y-1.5">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="flex justify-end">
            <x-primary-button>{{ __('Reset Password') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
