<script src="{{ asset('assets/js/core.bundle.js') }}"></script>
<script src="{{ asset('assets/vendors/ktui/ktui.min.js') }}"></script>

{{-- opcionales pero típicos en demo1 --}}
<script src="{{ asset('assets/js/widgets/general.js') }}"></script>
<script src="{{ asset('assets/js/layouts/demo1.js') }}"></script>

<script>
(() => {
    const initAccessControlMenuToggle = () => {
        const menuItem = document.getElementById('access_control_menu_item');
        const toggle = document.getElementById('access_control_menu_toggle');
        const submenu = document.getElementById('access_control_menu_submenu');

        if (!menuItem || !toggle || !submenu) {
            return;
        }

        const setExpandedState = (isExpanded) => {
            menuItem.classList.toggle('show', isExpanded);
            submenu.classList.toggle('show', isExpanded);
            toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
        };

        setExpandedState(menuItem.classList.contains('show'));

        if (toggle.dataset.manualAccordionBound === 'true') {
            return;
        }

        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            setExpandedState(!menuItem.classList.contains('show'));
        });

        toggle.dataset.manualAccordionBound = 'true';
    };

    document.addEventListener('DOMContentLoaded', initAccessControlMenuToggle);
    document.addEventListener('livewire:navigated', initAccessControlMenuToggle);
    initAccessControlMenuToggle();
})();
</script>

@livewireScripts
<script src="{{ asset('assets/vendors/livewire-powergrid/powergrid.js') }}"></script>
@stack('scripts')
