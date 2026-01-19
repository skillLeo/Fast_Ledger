{{--
    Partial for displaying a single allowance row in the show page

    @param string $key - The allowance field key
    @param mixed $value - The allowance value
--}}

@php
    // Format the field name from snake_case to Title Case
    $fieldName = ucwords(str_replace('_', ' ', $key));

    // Handle camelCase keys from API
    $fieldName = preg_replace('/([a-z])([A-Z])/', '$1 $2', $fieldName);
    $fieldName = ucwords($fieldName);
@endphp

@if(in_array($key, ['structured_building_allowance', 'enhanced_structured_building_allowance']) && is_array($value))
    {{-- Special handling for array-based structured building allowances --}}
    <tr>
        <td colspan="2" class="pt-4">
            <h6 class="text-primary mb-3">
                <i class="fas fa-building me-2"></i>
                {{ $fieldName }}
            </h6>
        </td>
    </tr>
    @foreach($value as $index => $building)
        <tr class="bg-light">
            <td colspan="2" class="py-2">
                <strong class="text-dark">Building #{{ $index + 1 }}</strong>
            </td>
        </tr>
        @if(isset($building['amount']))
            <tr>
                <td class="text-muted ps-4">Amount</td>
                <td class="text-end"><strong class="text-dark">£{{ number_format($building['amount'], 2) }}</strong></td>
            </tr>
        @endif
        @if(isset($building['first_year_qualifying_date']))
            <tr>
                <td class="text-muted ps-4">First Year Qualifying Date</td>
                <td class="text-end"><strong class="text-dark">{{ $building['first_year_qualifying_date'] }}</strong></td>
            </tr>
        @endif
        @if(isset($building['first_year_qualifying_amount']))
            <tr>
                <td class="text-muted ps-4">First Year Qualifying Amount</td>
                <td class="text-end"><strong class="text-dark">£{{ number_format($building['first_year_qualifying_amount'], 2) }}</strong></td>
            </tr>
        @endif
        @if(isset($building['building_name']))
            <tr>
                <td class="text-muted ps-4">Building Name</td>
                <td class="text-end"><strong class="text-dark">{{ $building['building_name'] }}</strong></td>
            </tr>
        @endif
        @if(isset($building['building_number']))
            <tr>
                <td class="text-muted ps-4">Building Number</td>
                <td class="text-end"><strong class="text-dark">{{ $building['building_number'] }}</strong></td>
            </tr>
        @endif
        @if(isset($building['building_postcode']))
            <tr>
                <td class="text-muted ps-4">Building Postcode</td>
                <td class="text-end"><strong class="text-dark">{{ strtoupper($building['building_postcode']) }}</strong></td>
            </tr>
        @endif
    @endforeach
@else
    {{-- Regular allowance field --}}
    <tr>
        <td class="text-muted">{{ $fieldName }}</td>
        <td class="text-end">
            @if(is_bool($value))
                {{-- Boolean values displayed as badges --}}
                <span class="badge {{ $value ? 'bg-success' : 'bg-secondary' }}">
                    {{ $value ? 'Yes' : 'No' }}
                </span>
            @elseif(is_array($value))
                {{-- Handle other nested objects --}}
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
@endif
