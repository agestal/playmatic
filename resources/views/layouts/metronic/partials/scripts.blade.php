<script src="{{ asset('assets/metronic8/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('assets/metronic8/js/scripts.bundle.js') }}"></script>

@stack('vendor_scripts')
@stack('m8_vendor_scripts')
@stack('scripts')
@stack('m8_scripts')

<script>
(() => {
    const initMetronic = () => {
        try {
            window.KTMenu?.createInstances?.();
            window.KTDrawer?.createInstances?.();
            window.KTToggle?.createInstances?.();
            window.KTScroll?.createInstances?.();
            window.KTImageInput?.createInstances?.();
            window.KTPasswordMeter?.createInstances?.();
            window.KTUtil?.onDOMContentLoaded?.(() => {});
        } catch (_) {
            // no-op
        }
    };

    document.addEventListener('DOMContentLoaded', initMetronic);
    document.addEventListener('livewire:navigated', () => setTimeout(initMetronic, 0));
    document.addEventListener('livewire:initialized', () => setTimeout(initMetronic, 0));
})();
</script>

@livewireScripts
