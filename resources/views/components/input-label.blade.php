@props(['value'])

<label {{ $attributes->merge(['class' => 'form-label fw-semibold fs-6']) }}>
    {{ $value ?? $slot }}
</label>
