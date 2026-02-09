<a href="{{ route('users.edit', $user->id) }}" class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light">
    <i class="ki-outline ki-notepad-edit"></i>
</a>
<button
    wire:click="delete({{ $user->id }})"
    wire:confirm="Are you sure?"
    class="kt-btn kt-btn-sm kt-btn-icon kt-btn-light-danger">
    <i class="ki-outline ki-trash"></i>
</button>
