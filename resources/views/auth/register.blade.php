<x-guest-layout>
    <div class="mb-10 text-center">
        <h1 class="text-dark fw-bolder mb-3">{{ __('Create account') }}</h1>
        <p class="text-muted fw-semibold fs-6 mb-0">{{ __('Use your work email to access the platform.') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="form">
        @csrf

        <div class="fv-row mb-8">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="fv-row mb-8">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="fv-row mb-8">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="fv-row mb-8">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="d-flex flex-stack">
            <a class="pm-auth-link fw-semibold" href="{{ route('login') }}">{{ __('Already registered?') }}</a>
            <x-primary-button>{{ __('Register') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
