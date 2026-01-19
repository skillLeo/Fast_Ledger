@props([
    'name',
    'label',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'optional' => false,
    'readonly' => false,
    'helpText' => '',
    'maxlength' => null,
    'step' => null,
    'isPreview' => false,
    'fieldClass' => ''  // ✅ Add this
])

@php
    $isReadonly = $readonly || (isset($isPreview) && $isPreview);
@endphp

<div class="form-group mb-2">
    <label for="{{ $name }}">
        {{ $label }}
        @if($required && !$isReadonly)
            <span class="text-danger">*</span>
        @endif
        @if($optional && !$isReadonly)
            <small class="text-muted">(Optional)</small>
        @endif
    </label>
    
    <input 
        type="{{ $type }}" 
        id="{{ $name }}" 
        name="{{ $name }}" 
        class="form-control {{ $fieldClass }} @error($name) is-invalid @enderror @if($isReadonly) bg-light @endif" 
        value="{{ old($name, $value) }}" 
        placeholder="{{ $placeholder }}"
        @if($isReadonly) readonly @endif
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        @if($step) step="{{ $step }}" @endif
        {{ $attributes }}
    >
    
    @if($helpText)
        <small class="form-text text-muted">{{ $helpText }}</small>
    @endif
    
    @error($name)
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>