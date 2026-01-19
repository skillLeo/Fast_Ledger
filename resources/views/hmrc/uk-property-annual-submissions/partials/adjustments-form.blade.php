@php
    $propertyType = $propertyType ?? null;
    $existingData = $existingData ?? [];

    // For TY 2024-25: Use fhl_ or non_fhl_ prefix
    // For TY 2025-26+: Use adjustments (no prefix, unified)
    // For TY < 2024-25: Use fhl_ or non_fhl_ prefix
    $fieldPrefix = $propertyType ? "{$propertyType}_adjustments" : 'adjustments';
    $idPrefix = $propertyType ? "{$propertyType}_adj" : 'adj';
@endphp

<div class="alert alert-info mb-4">
    <i class="fas fa-info-circle me-2"></i>
    @if($propertyType === 'fhl')
        These adjustments apply to your FHL (Furnished Holiday Lettings) properties.
    @elseif($propertyType === 'non_fhl')
        These adjustments apply to your Non-FHL (Non-Furnished Holiday Lettings) properties.
    @else
        These adjustments apply to your UK property business.
    @endif
</div>

    <div class="row g-4">
        <!-- Private Use Adjustment - Available for FHL all years, Non-FHL before TY 2025-26 only -->
        @if($propertyType === 'fhl' || $propertyType === 'non_fhl' || !$propertyType)
        <div class="col-md-6 private-use-adjustment-field">
            <label for="{{ $idPrefix }}_private_use_adjustment" class="form-label">
                Private Use Adjustment
                <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="Adjustment for private use of property"></i>
            </label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="{{ $fieldPrefix }}[private_use_adjustment]" id="{{ $idPrefix }}_private_use_adjustment"
                       class="form-control @error("{$fieldPrefix}.private_use_adjustment") is-invalid @enderror"
                       value="{{ old("{$fieldPrefix}.private_use_adjustment", $existingData['private_use_adjustment'] ?? '') }}"
                       step="0.01" min="0" max="99999999999.99"
                       placeholder="0.00">
            </div>
            <small class="text-muted">Adjustment for private use of property</small>
            @error("{$fieldPrefix}.private_use_adjustment")
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        @endif

        <!-- Balancing Charge -->
        <div class="col-md-6">
            <label for="{{ $idPrefix }}_balancing_charge" class="form-label">
                Balancing Charge
                <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="Balancing charges on disposal of assets"></i>
            </label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="{{ $fieldPrefix }}[balancing_charge]" id="{{ $idPrefix }}_balancing_charge"
                       class="form-control @error("{$fieldPrefix}.balancing_charge") is-invalid @enderror"
                       value="{{ old("{$fieldPrefix}.balancing_charge", $existingData['balancing_charge'] ?? '') }}"
                       step="0.01" min="0" max="99999999999.99"
                       placeholder="0.00">
            </div>
            <small class="text-muted">Balancing charges on disposal of assets</small>
            @error("{$fieldPrefix}.balancing_charge")
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <!-- BPRA Balancing Charges -->
        <div class="col-md-6">
            <label for="{{ $idPrefix }}_bpra_balancing_charges" class="form-label">
                BPRA Balancing Charges
                <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="Business Premises Renovation Allowance balancing charges"></i>
            </label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="{{ $fieldPrefix }}[business_premises_renovation_allowance_balancing_charges]" id="{{ $idPrefix }}_bpra_balancing_charges"
                       class="form-control @error("{$fieldPrefix}.business_premises_renovation_allowance_balancing_charges") is-invalid @enderror"
                       value="{{ old("{$fieldPrefix}.business_premises_renovation_allowance_balancing_charges", $existingData['business_premises_renovation_allowance_balancing_charges'] ?? '') }}"
                       step="0.01" min="0" max="99999999999.99"
                       placeholder="0.00">
            </div>
            <small class="text-muted">Business Premises Renovation Allowance balancing charges</small>
            @error("{$fieldPrefix}.business_premises_renovation_allowance_balancing_charges")
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <!-- TY 2024-25+ New Adjustment Fields -->
    <div class="ty-2024-25-fields mt-4" style="display: none;">
        <div class="alert alert-info border-start border-4 border-info bg-light mb-4">
            <i class="fas fa-sparkles me-2 text-info"></i>
            <strong>TY 2024-25+ Fields</strong> - New adjustment fields for tax year 2024-25 onwards
        </div>

        <div class="row g-3">
            <!-- Period of Grace Adjustment - FHL ONLY -->
            @if($propertyType === 'fhl')
            <div class="col-md-12">
                <div class="switch-item">
                    <div class="switch-info">
                        <label for="{{ $idPrefix }}_period_of_grace">Period of Grace Adjustment</label>
                        <p>Adjustment for period of grace (FHL properties only)</p>
                    </div>
                    <div class="form-check form-switch">
                        <input type="checkbox" name="{{ $fieldPrefix }}[period_of_grace_adjustment]" id="{{ $idPrefix }}_period_of_grace"
                               class="form-check-input" role="switch"
                               value="1"
                               {{ old("{$fieldPrefix}.period_of_grace_adjustment", $existingData['period_of_grace_adjustment'] ?? false) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
            @endif

            <!-- Non-Resident Landlord -->
            <div class="col-md-12">
                <div class="switch-item">
                    <div class="switch-info">
                        <label for="{{ $idPrefix }}_non_resident">Non-Resident Landlord</label>
                        <p>Are you a non-resident landlord?</p>
                    </div>
                    <div class="form-check form-switch">
                        <input type="checkbox" name="{{ $fieldPrefix }}[non_resident_landlord]" id="{{ $idPrefix }}_non_resident"
                               class="form-check-input" role="switch"
                               value="1"
                               {{ old("{$fieldPrefix}.non_resident_landlord", $existingData['non_resident_landlord'] ?? false) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            <!-- Rent a Room Jointly Let -->
            <div class="col-md-12">
                <div class="switch-item">
                    <div class="switch-info">
                        <label for="{{ $idPrefix }}_rent_a_room_joint">Rent a Room Jointly Let</label>
                        <p>Is the rent a room jointly let?</p>
                    </div>
                    <div class="form-check form-switch">
                        <input type="checkbox" name="{{ $fieldPrefix }}[rent_a_room_jointly_let]" id="{{ $idPrefix }}_rent_a_room_joint"
                               class="form-check-input" role="switch"
                               value="1"
                               {{ old("{$fieldPrefix}.rent_a_room_jointly_let", $existingData['rent_a_room_jointly_let'] ?? false) ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
        </div>
    </div>
