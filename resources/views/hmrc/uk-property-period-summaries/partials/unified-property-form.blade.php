{{-- Unified Property Form for Tax Year 2025-26+ --}}
<div id="unified-property-form">
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Tax Year 2025-26+</strong><br>
        For this tax year, all property income and expenses are reported together (FHL and Non-FHL combined).
    </div>

    <!-- Unified Property Income -->
    <h5 class="mb-3">UK Property Income</h5>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label">Premiums of Lease Grant</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_income[premiums_of_lease_grant]" class="form-control"
                       value="{{ old('uk_property_income.premiums_of_lease_grant', $ukPropertyIncome['premiums_of_lease_grant'] ?? '') }}"
                       step="0.01" min="0" max="99999999999.99" placeholder="0.00">
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Reverse Premiums</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_income[reverse_premiums]" class="form-control"
                       value="{{ old('uk_property_income.reverse_premiums', $ukPropertyIncome['reverse_premiums'] ?? '') }}"
                       step="0.01" min="0" max="99999999999.99" placeholder="0.00">
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Rental Income (Period Amount)</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_income[period_amount]" class="form-control"
                       value="{{ old('uk_property_income.period_amount', $ukPropertyIncome['period_amount'] ?? '') }}"
                       step="0.01" min="0" max="99999999999.99" placeholder="0.00">
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Tax Deducted</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_income[tax_deducted]" class="form-control"
                       value="{{ old('uk_property_income.tax_deducted', $ukPropertyIncome['tax_deducted'] ?? '') }}"
                       step="0.01" min="0" max="99999999999.99" placeholder="0.00">
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Other Income</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_income[other_income]" class="form-control"
                       value="{{ old('uk_property_income.other_income', $ukPropertyIncome['other_income'] ?? '') }}"
                       step="0.01" min="0" max="99999999999.99" placeholder="0.00">
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Rent a Room - Rents Received</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_income[rent_a_room][rents_received]" class="form-control"
                       value="{{ old('uk_property_income.rent_a_room.rents_received', $ukPropertyIncome['rent_a_room']['rents_received'] ?? '') }}"
                       step="0.01" min="0" max="99999999999.99" placeholder="0.00">
            </div>
        </div>
    </div>

    <!-- Unified Property Expenses -->
    <h5 class="mb-3 mt-4">UK Property Expenses</h5>

    @error('uk_property_expenses')
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i> {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @enderror

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Consolidated Expenses</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_expenses[consolidated_expenses]"
                       id="unified_consolidated_expenses"
                       class="form-control @error('uk_property_expenses') is-invalid @enderror"
                       value="{{ old('uk_property_expenses.consolidated_expenses', $ukPropertyExpenses['consolidated_expenses'] ?? '') }}"
                       step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00">
            </div>
            <small class="text-muted">Use this OR itemize expenses below (not both)</small>
        </div>
        <div class="col-12">
            <hr>
            <p class="text-muted mb-2"><strong>OR itemize individual expenses:</strong></p>
        </div>
        <div class="col-md-6">
            <label class="form-label">Premises Running Costs</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_expenses[premises_running_costs]"
                       class="form-control unified-itemized-expense"
                       value="{{ old('uk_property_expenses.premises_running_costs', $ukPropertyExpenses['premises_running_costs'] ?? '') }}"
                       step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00">
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Repairs and Maintenance</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_expenses[repairs_and_maintenance]"
                       class="form-control unified-itemized-expense"
                       value="{{ old('uk_property_expenses.repairs_and_maintenance', $ukPropertyExpenses['repairs_and_maintenance'] ?? '') }}"
                       step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00">
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Financial Costs</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_expenses[financial_costs]"
                       class="form-control unified-itemized-expense"
                       value="{{ old('uk_property_expenses.financial_costs', $ukPropertyExpenses['financial_costs'] ?? '') }}"
                       step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00">
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Professional Fees</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_expenses[professional_fees]"
                       class="form-control unified-itemized-expense"
                       value="{{ old('uk_property_expenses.professional_fees', $ukPropertyExpenses['professional_fees'] ?? '') }}"
                       step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00">
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Cost of Services</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_expenses[cost_of_services]"
                       class="form-control unified-itemized-expense"
                       value="{{ old('uk_property_expenses.cost_of_services', $ukPropertyExpenses['cost_of_services'] ?? '') }}"
                       step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00">
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Travel Costs</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_expenses[travel_costs]"
                       class="form-control unified-itemized-expense"
                       value="{{ old('uk_property_expenses.travel_costs', $ukPropertyExpenses['travel_costs'] ?? '') }}"
                       step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00">
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Other Expenses</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_expenses[other]"
                       class="form-control unified-itemized-expense"
                       value="{{ old('uk_property_expenses.other', $ukPropertyExpenses['other'] ?? '') }}"
                       step="0.01" min="-99999999999.99" max="99999999999.99" placeholder="0.00">
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Residential Financial Cost</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_expenses[residential_financial_cost]" class="form-control"
                       value="{{ old('uk_property_expenses.residential_financial_cost', $ukPropertyExpenses['residential_financial_cost'] ?? '') }}"
                       step="0.01" min="0" max="99999999999.99" placeholder="0.00">
            </div>
            <small class="text-muted">Can be used with consolidated expenses</small>
        </div>
        <div class="col-md-6">
            <label class="form-label">Residential Financial Costs Carried Forward</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_expenses[residential_financial_costs_carried_forward]" class="form-control"
                       value="{{ old('uk_property_expenses.residential_financial_costs_carried_forward', $ukPropertyExpenses['residential_financial_costs_carried_forward'] ?? '') }}"
                       step="0.01" min="0" max="99999999999.99" placeholder="0.00">
            </div>
            <small class="text-muted">Can be used with consolidated expenses</small>
        </div>
        <div class="col-md-6">
            <label class="form-label">Rent a Room - Amount Claimed</label>
            <div class="input-group">
                <span class="input-group-text">£</span>
                <input type="number" name="uk_property_expenses[rent_a_room][amount_claimed]" class="form-control"
                       value="{{ old('uk_property_expenses.rent_a_room.amount_claimed', $ukPropertyExpenses['rent_a_room']['amount_claimed'] ?? '') }}"
                       step="0.01" min="0" max="99999999999.99" placeholder="0.00">
            </div>
            <small class="text-muted">Can be used with consolidated expenses</small>
        </div>
    </div>
</div>
