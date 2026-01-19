{{-- Split Dropdown Component --}}
@props([
    'id' => '',
    'label' => 'Select',
    'items' => [],
    'width' => '150px'
])

<div class="split-dropdown-wrapper" style="width: {{ $width }};">
    <button type="button" class="split-dropdown-btn" id="{{ $id }}" 
        data-bs-toggle="dropdown" aria-expanded="false">
        <span class="dropdown-text">{{ $label }}</span>
        <span class="dropdown-icon">
            <i class="fas fa-chevron-down"></i>
        </span>
    </button>
    <ul class="dropdown-menu">
        {{ $slot }}
    </ul>
</div>