{{--
    Partial for displaying a single adjustment row in the show page

    @param string $key - The adjustment field key
    @param mixed $value - The adjustment value
--}}

@php
    // Format the field name from snake_case to Title Case
    $fieldName = ucwords(str_replace('_', ' ', $key));

    // Handle camelCase keys from API
    $fieldName = preg_replace('/([a-z])([A-Z])/', '$1 $2', $fieldName);
    $fieldName = ucwords($fieldName);
@endphp

<tr>
    <td class="text-muted">{{ $fieldName }}</td>
    <td class="text-end">
        @if(is_bool($value))
            {{-- Boolean values displayed as badges --}}
            <span class="badge {{ $value ? 'bg-success' : 'bg-secondary' }}">
                {{ $value ? 'Yes' : 'No' }}
            </span>
        @elseif(is_array($value))
            {{-- Handle nested objects like rentARoom --}}
            <div class="d-flex flex-column align-items-end gap-1">
                @foreach($value as $nestedKey => $nestedValue)
                    <div class="small">
                        <span class="text-muted">{{ ucwords(str_replace('_', ' ', $nestedKey)) }}:</span>
                        @if(is_bool($nestedValue))
                            <span class="badge badge-sm {{ $nestedValue ? 'bg-success' : 'bg-secondary' }}">
                                {{ $nestedValue ? 'Yes' : 'No' }}
                            </span>
                        @elseif(is_numeric($nestedValue))
                            <strong>£{{ number_format($nestedValue, 2) }}</strong>
                        @else
                            <strong>{{ $nestedValue }}</strong>
                        @endif
                    </div>
                @endforeach
            </div>
        @elseif(is_numeric($value))
            {{-- Numeric values displayed as currency --}}
            <strong class="text-dark">£{{ number_format($value, 2) }}</strong>
        @else
            {{-- String or other values --}}
            <strong class="text-dark">{{ $value }}</strong>
        @endif
    </td>
</tr>
