<x-guest-layout>
    <form method="POST" action="{{ route('password.confirm') }}" class="pm-auth-stack">
        @csrf

        <div>
            <h1 class="text-lg font-semibold text-mono">{{ __('Confirm password') }}</h1>
            <p class="mt-1 text-sm text-secondary-foreground">
                {{ __('This is a secure area of the application. Confirm your password to continue.') }}
            </p>
        </div>

        <div class="space-y-1.5">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex justify-end">
            <x-primary-button>{{ __('Confirm') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
