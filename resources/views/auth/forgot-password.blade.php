<x-guest-layout>
    <div class="mb-10">
        <h1 class="text-dark fw-bolder mb-3">{{ __('Forgot password') }}</h1>
        <p class="text-muted fw-semibold fs-6 mb-0">
            {{ __('Enter your email address and we will send you a link to reset your password.') }}
        </p>
    </div>

    <x-auth-session-status :status="session('status')" class="mb-8" />

    <form method="POST" action="{{ route('password.email') }}" class="form">
        @csrf

        <div class="fv-row mb-8">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="d-flex justify-content-end">
            <x-primary-button>{{ __('Email Password Reset Link') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
