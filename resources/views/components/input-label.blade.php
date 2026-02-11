@props(['value'])

<label {{ $attributes->merge(['class' => 'kt-form-label font-medium text-mono']) }}>
    {{ $value ?? $slot }}
</label>
