@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'kt-alert kt-alert-success']) }}>
        {{ $status }}
    </div>
@endif
