<x-guest-layout>
    <div class="mb-10">
        <h1 class="text-dark fw-bolder mb-3">{{ __('Verify your email') }}</h1>
        <p class="text-muted fw-semibold fs-6 mb-0">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?') }}
        </p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success mb-8">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>{{ __('Resend Verification Email') }}</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-light">{{ __('Log Out') }}</button>
        </form>
    </div>
</x-guest-layout>
