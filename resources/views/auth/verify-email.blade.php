<x-guest-layout>
    <div class="pm-auth-stack">
        <div>
            <h1 class="text-lg font-semibold text-mono">{{ __('Verify your email') }}</h1>
            <p class="mt-1 text-sm text-secondary-foreground">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?') }}
            </p>
        </div>

        @if (session('status') === 'verification-link-sent')
            <div class="kt-alert kt-alert-success">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        <div class="pm-auth-actions">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-primary-button>{{ __('Resend Verification Email') }}</x-primary-button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="kt-btn kt-btn-light">{{ __('Log Out') }}</button>
            </form>
        </div>
    </div>
</x-guest-layout>
