<x-guest-layout>
    <div class="mb-10">
        <h1 class="text-dark fw-bolder mb-3">{{ __('Confirm password') }}</h1>
        <p class="text-muted fw-semibold fs-6 mb-0">
            {{ __('This is a secure area of the application. Confirm your password to continue.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="form">
        @csrf

        <div class="fv-row mb-8">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="d-flex justify-content-end">
            <x-primary-button>{{ __('Confirm') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
