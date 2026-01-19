<div class="hmrc-card">
    <h2 class="step-heading mb-2">Review & Submit</h2>
    <p class="text-muted mb-4">Review your submission before creating the draft</p>

    <div class="alert alert-info border-start border-4 border-info bg-light mb-4">
        <i class="fas fa-info-circle me-2 text-info"></i>
        This will create a draft submission. You can review it before submitting to HMRC.
    </div>

    <div id="review-summary" class="review-summary">
        <!-- Summary will be populated by JavaScript -->
        <div class="text-center py-4">
            <i class="fas fa-spinner fa-spin me-2 text-hmrc"></i>
            <span class="text-muted">Loading summary...</span>
        </div>
    </div>

    <!-- Notes Section -->
    <div class="mt-4">
        <label for="notes" class="form-label">Notes (Optional)</label>
        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror"
                  rows="3"
                  maxlength="5000"
                  placeholder="Add any notes about this submission...">{{ old('notes') }}</textarea>
        <small class="text-muted"><span id="notes-count">0</span> / 5000 characters</small>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('annual-submission-form');
    const notesInput = document.getElementById('notes');

    // Notes character counter
    if (notesInput) {
        notesInput.addEventListener('input', function() {
            document.getElementById('notes-count').textContent = this.value.length;
        });
        // Initialize counter
        document.getElementById('notes-count').textContent = notesInput.value.length;
    }

    window.buildReviewSummary = function() {
        const formData = new FormData(form);
        let html = '';
        let hasAnyData = false;

        html += '<h3 class="summary-title mb-3">Summary</h3>';

        // Business & Tax Year
        html += '<div class="summary-section mb-4">';
        html += '<div class="row g-3">';
        const businessSelect = document.getElementById('business_id');
        if (businessSelect && businessSelect.value) {
            html += '<div class="col-md-6"><div class="summary-item">';
            html += '<span class="summary-label">Business:</span>';
            html += '<span class="summary-value">' + businessSelect.options[businessSelect.selectedIndex].text + '</span>';
            html += '</div></div>';
        }
        if (formData.get('tax_year')) {
            html += '<div class="col-md-6"><div class="summary-item">';
            html += '<span class="summary-label">Tax Year:</span>';
            html += '<span class="summary-value">' + formData.get('tax_year') + '</span>';
            html += '</div></div>';
        }
        if (formData.get('nino')) {
            html += '<div class="col-md-6"><div class="summary-item">';
            html += '<span class="summary-label">NINO:</span>';
            html += '<span class="summary-value">' + formData.get('nino') + '</span>';
            html += '</div></div>';
        }
        html += '</div></div>';

        // Helper function to format field names
        function formatFieldName(field) {
            return field.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        }

        // Helper function to collect adjustments/allowances for a prefix
        function collectFields(prefix, fieldList) {
            const collected = {};
            fieldList.forEach(field => {
                const value = formData.get(`${prefix}[${field}]`);
                if (value && parseFloat(value) > 0) {
                    collected[field] = value;
                }
            });
            return collected;
        }

        // Helper function to collect building allowances
        function collectBuildingAllowances(prefix) {
            const buildings = [];
            let index = 0;
            while (true) {
                const amount = formData.get(`${prefix}[${index}][amount]`);
                if (!amount) break;

                buildings.push({
                    amount: amount,
                    first_year_qualifying_date: formData.get(`${prefix}[${index}][first_year_qualifying_date]`) || '',
                    first_year_qualifying_amount: formData.get(`${prefix}[${index}][first_year_qualifying_amount]`) || '',
                    building_name: formData.get(`${prefix}[${index}][building_name]`) || '',
                    building_number: formData.get(`${prefix}[${index}][building_number]`) || '',
                    building_postcode: formData.get(`${prefix}[${index}][building_postcode]`) || ''
                });
                index++;
            }
            return buildings;
        }

        const adjustmentFields = [
            'private_use_adjustment',
            'balancing_charge',
            'business_premises_renovation_allowance_balancing_charges',
            'period_of_grace_adjustment',
            'non_resident_landlord',
            'rent_a_room_exempt',
            'rent_a_room_amount'
        ];

        const allowanceFields = [
            'property_income_allowance',
            'annual_investment_allowance',
            'zero_emissions_car_allowance',
            'zero_emissions_goods_vehicle_allowance',
            'business_premises_renovation_allowance',
            'replacement_of_domestic_goods',
            'other_capital_allowance',
            'cost_of_replacing_domestic_items',
            'electric_charge_point_allowance',
            'zero_emission_car_allowance'
        ];

        // FHL Adjustments
        const fhlAdjustments = collectFields('fhl_adjustments', adjustmentFields);
        if (Object.keys(fhlAdjustments).length > 0) {
            hasAnyData = true;
            html += '<div class="summary-section mb-4">';
            html += '<h4 class="summary-subtitle mb-3"><i class="fas fa-umbrella-beach me-2 text-hmrc"></i>FHL Adjustments</h4>';
            html += '<div class="row g-3">';
            Object.entries(fhlAdjustments).forEach(([field, value]) => {
                html += '<div class="col-md-6"><div class="summary-item">';
                html += '<span class="summary-label">' + formatFieldName(field) + ':</span>';
                html += '<span class="summary-value">£' + parseFloat(value).toFixed(2) + '</span>';
                html += '</div></div>';
            });
            html += '</div></div>';
        }

        // Non-FHL Adjustments
        const nonFhlAdjustments = collectFields('non_fhl_adjustments', adjustmentFields);
        if (Object.keys(nonFhlAdjustments).length > 0) {
            hasAnyData = true;
            html += '<div class="summary-section mb-4">';
            html += '<h4 class="summary-subtitle mb-3"><i class="fas fa-building me-2 text-hmrc"></i>Non-FHL Adjustments</h4>';
            html += '<div class="row g-3">';
            Object.entries(nonFhlAdjustments).forEach(([field, value]) => {
                html += '<div class="col-md-6"><div class="summary-item">';
                html += '<span class="summary-label">' + formatFieldName(field) + ':</span>';
                html += '<span class="summary-value">£' + parseFloat(value).toFixed(2) + '</span>';
                html += '</div></div>';
            });
            html += '</div></div>';
        }

        // Unified Adjustments (TY 2025-26+)
        const unifiedAdjustments = collectFields('adjustments', adjustmentFields);
        if (Object.keys(unifiedAdjustments).length > 0) {
            hasAnyData = true;
            html += '<div class="summary-section mb-4">';
            html += '<h4 class="summary-subtitle mb-3"><i class="fas fa-balance-scale me-2 text-hmrc"></i>Adjustments</h4>';
            html += '<div class="row g-3">';
            Object.entries(unifiedAdjustments).forEach(([field, value]) => {
                html += '<div class="col-md-6"><div class="summary-item">';
                html += '<span class="summary-label">' + formatFieldName(field) + ':</span>';
                html += '<span class="summary-value">£' + parseFloat(value).toFixed(2) + '</span>';
                html += '</div></div>';
            });
            html += '</div></div>';
        }

        // FHL Allowances
        const fhlAllowances = collectFields('fhl_allowances', allowanceFields);
        const fhlSBA = collectBuildingAllowances('fhl_allowances[structured_building_allowance]');
        const fhlESBA = collectBuildingAllowances('fhl_allowances[enhanced_structured_building_allowance]');

        if (Object.keys(fhlAllowances).length > 0 || fhlSBA.length > 0 || fhlESBA.length > 0) {
            hasAnyData = true;
            html += '<div class="summary-section mb-4">';
            html += '<h4 class="summary-subtitle mb-3"><i class="fas fa-umbrella-beach me-2 text-hmrc"></i>FHL Allowances</h4>';
            html += '<div class="row g-3">';
            Object.entries(fhlAllowances).forEach(([field, value]) => {
                html += '<div class="col-md-6"><div class="summary-item">';
                html += '<span class="summary-label">' + formatFieldName(field) + ':</span>';
                html += '<span class="summary-value text-success">£' + parseFloat(value).toFixed(2) + '</span>';
                html += '</div></div>';
            });
            html += '</div>';

            // FHL Structured Building Allowances
            if (fhlSBA.length > 0) {
                html += '<div class="mt-3"><h6 class="text-muted mb-2">Structured Building Allowances</h6>';
                fhlSBA.forEach((building, idx) => {
                    html += '<div class="card mb-2 border-start border-4 border-info">';
                    html += '<div class="card-body py-2">';
                    html += '<h6 class="mb-2">Building #' + (idx + 1) + '</h6>';
                    html += '<div class="row small">';
                    html += '<div class="col-md-6"><strong>Amount:</strong> £' + parseFloat(building.amount).toFixed(2) + '</div>';
                    if (building.building_name) html += '<div class="col-md-6"><strong>Name:</strong> ' + building.building_name + '</div>';
                    if (building.building_number) html += '<div class="col-md-6"><strong>Number:</strong> ' + building.building_number + '</div>';
                    if (building.building_postcode) html += '<div class="col-md-6"><strong>Postcode:</strong> ' + building.building_postcode + '</div>';
                    html += '</div></div></div>';
                });
                html += '</div>';
            }

            // FHL Enhanced Structured Building Allowances
            if (fhlESBA.length > 0) {
                html += '<div class="mt-3"><h6 class="text-muted mb-2">Enhanced Structured Building Allowances</h6>';
                fhlESBA.forEach((building, idx) => {
                    html += '<div class="card mb-2 border-start border-4 border-success">';
                    html += '<div class="card-body py-2">';
                    html += '<h6 class="mb-2">Building #' + (idx + 1) + '</h6>';
                    html += '<div class="row small">';
                    html += '<div class="col-md-6"><strong>Amount:</strong> £' + parseFloat(building.amount).toFixed(2) + '</div>';
                    if (building.building_name) html += '<div class="col-md-6"><strong>Name:</strong> ' + building.building_name + '</div>';
                    if (building.building_number) html += '<div class="col-md-6"><strong>Number:</strong> ' + building.building_number + '</div>';
                    if (building.building_postcode) html += '<div class="col-md-6"><strong>Postcode:</strong> ' + building.building_postcode + '</div>';
                    html += '</div></div></div>';
                });
                html += '</div>';
            }
            html += '</div>';
        }

        // Non-FHL Allowances
        const nonFhlAllowances = collectFields('non_fhl_allowances', allowanceFields);
        const nonFhlSBA = collectBuildingAllowances('non_fhl_allowances[structured_building_allowance]');
        const nonFhlESBA = collectBuildingAllowances('non_fhl_allowances[enhanced_structured_building_allowance]');

        if (Object.keys(nonFhlAllowances).length > 0 || nonFhlSBA.length > 0 || nonFhlESBA.length > 0) {
            hasAnyData = true;
            html += '<div class="summary-section mb-4">';
            html += '<h4 class="summary-subtitle mb-3"><i class="fas fa-building me-2 text-hmrc"></i>Non-FHL Allowances</h4>';
            html += '<div class="row g-3">';
            Object.entries(nonFhlAllowances).forEach(([field, value]) => {
                html += '<div class="col-md-6"><div class="summary-item">';
                html += '<span class="summary-label">' + formatFieldName(field) + ':</span>';
                html += '<span class="summary-value text-success">£' + parseFloat(value).toFixed(2) + '</span>';
                html += '</div></div>';
            });
            html += '</div>';

            // Non-FHL Structured Building Allowances
            if (nonFhlSBA.length > 0) {
                html += '<div class="mt-3"><h6 class="text-muted mb-2">Structured Building Allowances</h6>';
                nonFhlSBA.forEach((building, idx) => {
                    html += '<div class="card mb-2 border-start border-4 border-info">';
                    html += '<div class="card-body py-2">';
                    html += '<h6 class="mb-2">Building #' + (idx + 1) + '</h6>';
                    html += '<div class="row small">';
                    html += '<div class="col-md-6"><strong>Amount:</strong> £' + parseFloat(building.amount).toFixed(2) + '</div>';
                    if (building.building_name) html += '<div class="col-md-6"><strong>Name:</strong> ' + building.building_name + '</div>';
                    if (building.building_number) html += '<div class="col-md-6"><strong>Number:</strong> ' + building.building_number + '</div>';
                    if (building.building_postcode) html += '<div class="col-md-6"><strong>Postcode:</strong> ' + building.building_postcode + '</div>';
                    html += '</div></div></div>';
                });
                html += '</div>';
            }

            // Non-FHL Enhanced Structured Building Allowances
            if (nonFhlESBA.length > 0) {
                html += '<div class="mt-3"><h6 class="text-muted mb-2">Enhanced Structured Building Allowances</h6>';
                nonFhlESBA.forEach((building, idx) => {
                    html += '<div class="card mb-2 border-start border-4 border-success">';
                    html += '<div class="card-body py-2">';
                    html += '<h6 class="mb-2">Building #' + (idx + 1) + '</h6>';
                    html += '<div class="row small">';
                    html += '<div class="col-md-6"><strong>Amount:</strong> £' + parseFloat(building.amount).toFixed(2) + '</div>';
                    if (building.building_name) html += '<div class="col-md-6"><strong>Name:</strong> ' + building.building_name + '</div>';
                    if (building.building_number) html += '<div class="col-md-6"><strong>Number:</strong> ' + building.building_number + '</div>';
                    if (building.building_postcode) html += '<div class="col-md-6"><strong>Postcode:</strong> ' + building.building_postcode + '</div>';
                    html += '</div></div></div>';
                });
                html += '</div>';
            }
            html += '</div>';
        }

        // Unified Allowances (TY 2025-26+)
        const unifiedAllowances = collectFields('allowances', allowanceFields);
        const unifiedSBA = collectBuildingAllowances('allowances[structured_building_allowance]');
        const unifiedESBA = collectBuildingAllowances('allowances[enhanced_structured_building_allowance]');

        if (Object.keys(unifiedAllowances).length > 0 || unifiedSBA.length > 0 || unifiedESBA.length > 0) {
            hasAnyData = true;
            html += '<div class="summary-section mb-4">';
            html += '<h4 class="summary-subtitle mb-3"><i class="fas fa-award me-2 text-hmrc"></i>Allowances</h4>';
            html += '<div class="row g-3">';
            Object.entries(unifiedAllowances).forEach(([field, value]) => {
                html += '<div class="col-md-6"><div class="summary-item">';
                html += '<span class="summary-label">' + formatFieldName(field) + ':</span>';
                html += '<span class="summary-value text-success">£' + parseFloat(value).toFixed(2) + '</span>';
                html += '</div></div>';
            });
            html += '</div>';

            // Unified Structured Building Allowances
            if (unifiedSBA.length > 0) {
                html += '<div class="mt-3"><h6 class="text-muted mb-2">Structured Building Allowances</h6>';
                unifiedSBA.forEach((building, idx) => {
                    html += '<div class="card mb-2 border-start border-4 border-info">';
                    html += '<div class="card-body py-2">';
                    html += '<h6 class="mb-2">Building #' + (idx + 1) + '</h6>';
                    html += '<div class="row small">';
                    html += '<div class="col-md-6"><strong>Amount:</strong> £' + parseFloat(building.amount).toFixed(2) + '</div>';
                    if (building.building_name) html += '<div class="col-md-6"><strong>Name:</strong> ' + building.building_name + '</div>';
                    if (building.building_number) html += '<div class="col-md-6"><strong>Number:</strong> ' + building.building_number + '</div>';
                    if (building.building_postcode) html += '<div class="col-md-6"><strong>Postcode:</strong> ' + building.building_postcode + '</div>';
                    html += '</div></div></div>';
                });
                html += '</div>';
            }

            // Unified Enhanced Structured Building Allowances
            if (unifiedESBA.length > 0) {
                html += '<div class="mt-3"><h6 class="text-muted mb-2">Enhanced Structured Building Allowances</h6>';
                unifiedESBA.forEach((building, idx) => {
                    html += '<div class="card mb-2 border-start border-4 border-success">';
                    html += '<div class="card-body py-2">';
                    html += '<h6 class="mb-2">Building #' + (idx + 1) + '</h6>';
                    html += '<div class="row small">';
                    html += '<div class="col-md-6"><strong>Amount:</strong> £' + parseFloat(building.amount).toFixed(2) + '</div>';
                    if (building.building_name) html += '<div class="col-md-6"><strong>Name:</strong> ' + building.building_name + '</div>';
                    if (building.building_number) html += '<div class="col-md-6"><strong>Number:</strong> ' + building.building_number + '</div>';
                    if (building.building_postcode) html += '<div class="col-md-6"><strong>Postcode:</strong> ' + building.building_postcode + '</div>';
                    html += '</div></div></div>';
                });
                html += '</div>';
            }
            html += '</div>';
        }

        if (!hasAnyData) {
            html = '<div class="text-center py-4"><p class="text-muted">No data entered yet. Please complete the previous steps.</p></div>';
        }

        document.getElementById('review-summary').innerHTML = html;
    };
});
</script>
@endpush
