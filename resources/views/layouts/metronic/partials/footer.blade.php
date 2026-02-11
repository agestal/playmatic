<footer class="kt-footer border-t border-border bg-background">
    <div class="kt-container-fluid">
        <div class="flex flex-col md:flex-row justify-between items-center gap-3 py-4">
            <div class="text-sm text-secondary-foreground">
                {{ date('Y') }} © {{ config('app.name') }}
            </div>

            <nav class="flex items-center gap-4 text-sm text-secondary-foreground">
                <a class="hover:text-primary" href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                @isset($currentTenant)
                    <a class="hover:text-primary" href="{{ route('profile.edit') }}">{{ __('Profile') }}</a>
                @endisset
                <a class="hover:text-primary" href="https://keenthemes.com/metronic/docs" target="_blank" rel="noopener noreferrer">{{ __('Metronic docs') }}</a>
            </nav>
        </div>
    </div>
</footer>
