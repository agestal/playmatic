<a href="{{ route('users.edit', $user->id) }}" class="btn btn-icon btn-active-light-primary btn-sm me-1">
    <i class="ki-outline ki-notepad-edit fs-2"></i>
</a>
<button wire:click="delete({{ $user->id }})"
        wire:confirm="Are you sure?"
        class="btn btn-icon btn-active-light-danger btn-sm">
    <i class="ki-outline ki-trash fs-2"></i>
</button>
