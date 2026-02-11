@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'list-unstyled mb-0']) }}>
        @foreach ((array) $messages as $message)
            <li class="text-danger fs-7 mt-1">{{ $message }}</li>
        @endforeach
    </ul>
@endif
