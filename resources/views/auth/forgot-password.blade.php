<x-guest-layout>
    <div class="pm-auth-stack">
        <div>
            <h1 class="text-lg font-semibold text-mono">{{ __('Forgot password') }}</h1>
            <p class="mt-1 text-sm text-secondary-foreground">
                {{ __('Enter your email address and we will send you a link to reset your password.') }}
            </p>
        </div>

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="pm-auth-stack">
            @csrf

            <div class="space-y-1.5">
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" />
            </div>

            <div class="flex justify-end">
                <x-primary-button>{{ __('Email Password Reset Link') }}</x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
