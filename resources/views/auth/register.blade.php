<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="pm-auth-stack">
        @csrf

        <div class="text-center">
            <h1 class="text-lg font-semibold text-mono">{{ __('Create account') }}</h1>
            <p class="mt-1 text-sm text-secondary-foreground">{{ __('Use your work email to access the platform.') }}</p>
        </div>

        <div class="space-y-4">
            <div class="space-y-1.5">
                <x-input-label for="name" :value="__('Name')" />
                <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" />
            </div>

            <div class="space-y-1.5">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
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
        </div>

        <div class="pm-auth-actions">
            <a class="pm-auth-link" href="{{ route('login') }}">{{ __('Already registered?') }}</a>
            <x-primary-button>{{ __('Register') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
