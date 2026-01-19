<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">
            <i class="fas fa-check-circle me-2"></i>
            Review Your Annual Submission
        </h5>
    </div>
    <div class="card-body">
        <div id="review-summary">
            <!-- Will be populated by JavaScript -->
            <div class="text-center py-5">
                <i class="fas fa-spinner fa-spin fa-3x text-muted"></i>
                <p class="mt-3 text-muted">Loading summary...</p>
            </div>
        </div>

        <!-- Notes -->
        <div class="mt-4">
            <label for="notes" class="form-label">Notes (Optional)</label>
            <textarea name="notes" id="notes" rows="3" 
                      class="form-control @error('notes') is-invalid @enderror"
                      placeholder="Add any notes about this annual submission...">{{ old('notes') }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="alert alert-warning mt-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note:</strong> This will create a draft submission. You can review it before submitting to HMRC.
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <button type="button" class="btn btn-secondary prev-step">
                <i class="fas fa-arrow-left me-2"></i> Previous
            </button>
            <button type="submit" class="btn btn-success btn-lg">
                <i class="fas fa-save me-2"></i> Save Draft
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateReviewSummary() {
    const businessSelect = document.getElementById('business_id');
    const businessName = businessSelect.options[businessSelect.selectedIndex].text;
    const taxYear = document.getElementById('tax_year').value;
    
    // Calculate totals
    let totalAllowances = 0;
    document.querySelectorAll('[name^="allowances["]').forEach(input => {
        totalAllowances += parseFloat(input.value) || 0;
    });

    let incomeAdjustments = 0;
    document.querySelectorAll('[name^="adjustments[income_adjustment]"]').forEach(input => {
        incomeAdjustments += parseFloat(input.value) || 0;
    });

    let expenseAdjustments = 0;
    document.querySelectorAll('[name^="adjustments[expense_adjustment]"]').forEach(input => {
        expenseAdjustments += parseFloat(input.value) || 0;
    });

    // Build summary HTML
    const summary = `
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card border">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Business</h6>
                        <p class="mb-0 fw-bold">${businessName}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Tax Year</h6>
                        <p class="mb-0 fw-bold">${taxYear}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Total Allowances</h6>
                        <h4 class="mb-0 text-success">£${formatNumber(totalAllowances)}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-info">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Income Adjustments</h6>
                        <h4 class="mb-0 text-info">£${formatNumber(incomeAdjustments)}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-warning">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Expense Adjustments</h6>
                        <h4 class="mb-0 text-warning">£${formatNumber(expenseAdjustments)}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <h6 class="mb-3">Summary of Entries</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th class="text-end">Count</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Income Adjustments</td>
                            <td class="text-end">${countNonZeroFields('[name^="adjustments[income_adjustment]"]')}</td>
                            <td class="text-end">£${formatNumber(incomeAdjustments)}</td>
                        </tr>
                        <tr>
                            <td>Expense Adjustments</td>
                            <td class="text-end">${countNonZeroFields('[name^="adjustments[expense_adjustment]"]')}</td>
                            <td class="text-end">£${formatNumber(expenseAdjustments)}</td>
                        </tr>
                        <tr>
                            <td>Allowances</td>
                            <td class="text-end">${countNonZeroFields('[name^="allowances["]')}</td>
                            <td class="text-end">£${formatNumber(totalAllowances)}</td>
                        </tr>
                        <tr class="table-light fw-bold">
                            <td colspan="2">Net Impact</td>
                            <td class="text-end">£${formatNumber(totalAllowances + incomeAdjustments - expenseAdjustments)}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        ${getAddressSection()}
        ${getNicsExemptionSection()}
    `;

    document.getElementById('review-summary').innerHTML = summary;
}

function formatNumber(num) {
    return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function countNonZeroFields(selector) {
    let count = 0;
    document.querySelectorAll(selector).forEach(input => {
        if (parseFloat(input.value) > 0) count++;
    });
    return count;
}

function getAddressSection() {
    const line1 = document.getElementById('business_address_line_1')?.value || '';
    const line2 = document.getElementById('business_address_line_2')?.value || '';
    const line3 = document.getElementById('business_address_line_3')?.value || '';
    const postcode = document.getElementById('business_address_postcode')?.value || '';
    
    if (!line1 && !line2 && !line3 && !postcode) {
        return '';
    }

    return `
        <div class="mt-4">
            <h6 class="mb-3">Business Address</h6>
            <div class="card border">
                <div class="card-body">
                    ${line1 ? `<p class="mb-1">${line1}</p>` : ''}
                    ${line2 ? `<p class="mb-1">${line2}</p>` : ''}
                    ${line3 ? `<p class="mb-1">${line3}</p>` : ''}
                    ${postcode ? `<p class="mb-0"><strong>${postcode}</strong></p>` : ''}
                </div>
            </div>
        </div>
    `;
}

function getNicsExemptionSection() {
    const exemptionSelect = document.getElementById('class_4_nics_exemption_reason');
    const exemptionValue = exemptionSelect?.value;
    
    if (!exemptionValue) {
        return '';
    }

    const exemptionText = exemptionSelect.options[exemptionSelect.selectedIndex].text;
    
    return `
        <div class="mt-4">
            <h6 class="mb-3">Class 4 NICs Exemption</h6>
            <div class="card border-info">
                <div class="card-body">
                    <p class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        ${exemptionText}
                    </p>
                </div>
            </div>
        </div>
    `;
}
</script>
@endpush



