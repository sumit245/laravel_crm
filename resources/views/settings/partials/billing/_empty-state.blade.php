@props([
    'title',
    'message',
    'actionLabel' => null,
    'actionTarget' => null,
])

<div class="billing-empty-state text-center py-4 px-3" role="status">
    <p class="fw-semibold mb-1">{{ $title }}</p>
    <p class="text-muted small mb-3">{{ $message }}</p>
    @if ($actionLabel && $actionTarget)
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#{{ $actionTarget }}">
            {{ $actionLabel }}
        </button>
    @endif
</div>
