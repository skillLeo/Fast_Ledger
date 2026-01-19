@props([
    'label' => null,
    'name',
    'options' => [],
    'value' => '',
    'helpText' => '',
    'isPreview' => false,
    'fieldClass' => ''  // ✅ Add this
])

<div class="form-group mb-2">
    @if($label)
        <label style="font-weight: 600;">{{ $label }}</label>
    @endif
    
    @foreach($options as $optionValue => $optionLabel)
        <div class="radio-group" style="align-items: flex-start;">
            <input 
                type="radio" 
                id="{{ $name }}_{{ $loop->index }}" 
                name="{{ $name }}" 
                class="{{ $fieldClass }}"
                value="{{ $optionValue }}"
                {{ old($name, $value) == $optionValue ? 'checked' : '' }}
                @if($isPreview) disabled @endif
                style="margin-top: 3px;"
            >
            <label for="{{ $name }}_{{ $loop->index }}" class="@if($isPreview) text-muted @endif">
                {!! $optionLabel !!}
            </label>
        </div>
    @endforeach
    
    @if($helpText && !$isPreview)
        <small class="d-block mt-1 text-muted">{{ $helpText }}</small>
    @endif
    
    @error($name)
        <span class="text-danger small">{{ $message }}</span>
    @enderror
</div>