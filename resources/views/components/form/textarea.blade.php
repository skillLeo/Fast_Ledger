@props([
    'label',
    'name',
    'rows' => 3,
    'required' => false,
    'optional' => false,
    'placeholder' => '',
    'helpText' => '',
    'value' => '',
    'isPreview' => false,
    'fieldClass' => ''  // ✅ Add this
])

<div class="form-group mb-2">
    <label for="{{ $name }}">
        {{ $label }}
        @if($required && !$isPreview)
            <span class="text-danger">*</span>
        @endif
        @if($optional && !$isPreview)
            <small class="text-muted">(Optional)</small>
        @endif
    </label>
    
    <textarea 
        id="{{ $name }}" 
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        class="form-control {{ $fieldClass }} @error($name) is-invalid @enderror @if($isPreview) bg-light @endif"
        @if($isPreview) readonly @endif
        {{ $attributes }}
    >{{ old($name, $value) }}</textarea>
    
    @if($helpText && !$isPreview)
        <small class="d-block mt-1 text-muted">{{ $helpText }}</small>
    @endif
    
    @error($name)
        <span class="text-danger small">{{ $message }}</span>
    @enderror
</div>