@props([
    'label',
    'value' => 0,
    'icon' => 'bi-circle',
    'variant' => 'primary',
])

<div class="sim-stat-card {{ $variant }}">
    <div class="d-flex justify-content-between align-items-start gap-2">
        <div>
            <div class="label">{{ $label }}</div>
            <div class="value">{{ $value }}</div>
        </div>
        <div class="icon-wrap">
            <i class="bi {{ $icon }}"></i>
        </div>
    </div>
</div>
