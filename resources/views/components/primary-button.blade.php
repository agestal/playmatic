<button {{ $attributes->merge(['type' => 'submit', 'class' => 'kt-btn kt-btn-primary']) }}>
    {{ $slot }}
</button>
