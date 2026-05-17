@props(['label', 'value' => null, 'wide' => false])

<div class="{{ $wide ? 'col-12' : 'col-md-4' }}">
    <div class="text-secondary small">{{ $label }}</div>
    <div class="fw-semibold">{{ filled($value) ? $value : '-' }}</div>
</div>
