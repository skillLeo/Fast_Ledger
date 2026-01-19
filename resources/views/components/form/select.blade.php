@props([
    'label',
    'name',
    'options' => [],
    'required' => false,
    'optional' => false,
    'placeholder' => 'Select an option',
    'value' => '',
    'helpText' => '',
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
    
    <select 
        id="{{ $name }}" 
        name="{{ $name }}"
        class="form-control {{ $fieldClass }} @error($name) is-invalid @enderror @if($isPreview) bg-light @endif"
        @if($isPreview) disabled @endif
        {{ $attributes }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" {{ old($name, $value) == $optionValue ? 'selected' : '' }}>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
    
    @if($helpText && !$isPreview)
        <small class="form-text text-muted">{{ $helpText }}</small>
    @endif
    
    @error($name)
        <span class="text-danger small">{{ $message }}</span>
    @enderror
</div>