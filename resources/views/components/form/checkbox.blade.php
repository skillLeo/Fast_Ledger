@props([
    'label',
    'name',
    'value' => '1',
    'checked' => false,
    'helpText' => '',
    'isPreview' => false,
    'fieldClass' => ''  // ✅ Add this
])

<div class="form-group mb-2">
    <div class="checkbox-group">
        <input 
            type="checkbox" 
            id="{{ $name }}" 
            name="{{ $name }}" 
            class="{{ $fieldClass }}"
            value="{{ $value }}"
            {{ old($name, $checked) ? 'checked' : '' }}
            @if($isPreview) disabled @endif
            {{ $attributes }}
        >
        <label for="{{ $name }}" class="@if($isPreview) text-muted @endif">
            {{ $label }}
        </label>
    </div>
    
    @if($helpText && !$isPreview)
        <small class="d-block mt-1 text-muted">{{ $helpText }}</small>
    @endif
    
    @error($name)
        <span class="text-danger small">{{ $message }}</span>
    @enderror
</div>