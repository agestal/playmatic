@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'mb-4 text-sm font-medium text-green-600']) }}>
        {{ $status }}
    </div>
@endif
