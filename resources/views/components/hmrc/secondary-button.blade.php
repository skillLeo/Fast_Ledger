@props([
    'type' => 'button',
    'size' => 'md',
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'fullWidth' => false
])

@php
    $sizeClasses = [
        'sm' => 'py-2 px-3 text-sm',
        'md' => 'py-3 px-6',
        'lg' => 'py-4 px-8 text-lg'
    ];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => 'btn-hmrc-secondary ' . $sizeClass . ($fullWidth ? ' w-100' : '') . ($disabled ? ' disabled' : '')
    ]) }}
    {{ $disabled ? 'disabled' : '' }}
>
    @if($icon && $iconPosition === 'left')
        <i class="{{ $icon }} me-2"></i>
    @endif

    {{ $slot }}

    @if($icon && $iconPosition === 'right')
        <i class="{{ $icon }} ms-2"></i>
    @endif
</button>

<style>
    .btn-hmrc-secondary {
        background-color: white;
        color: #6c757d;
        border: 1px solid #dee2e6;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.2s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        text-decoration: none;
    }

    .btn-hmrc-secondary:hover:not(.disabled) {
        background-color: #f8f9fa;
        border-color: #adb5bd;
        color: #495057;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-hmrc-secondary:active:not(.disabled) {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .btn-hmrc-secondary:focus {
        outline: 2px solid #17848e;
        outline-offset: 2px;
    }

    .btn-hmrc-secondary.disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>
