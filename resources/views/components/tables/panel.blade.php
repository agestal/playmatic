@props([
    'title' => '',
    'description' => null,
])

<div class="kt-card overflow-hidden border border-gray-200 shadow-sm">
    <div class="kt-card-header table-panel-header">
        <div class="flex flex-col gap-1">
            <h3 class="kt-card-title text-base font-semibold text-gray-900">{{ $title }}</h3>
            @if (filled($description))
                <p class="text-sm text-gray-600">{{ $description }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="kt-card-toolbar">
                {{ $actions }}
            </div>
        @endisset
    </div>

    <div class="kt-card-content p-0">
        {{ $slot }}
    </div>
</div>
