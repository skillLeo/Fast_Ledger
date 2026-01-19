<div class="alert alert-info">
    <strong><i class="fas fa-info-circle me-2"></i>Furnished Holiday Lettings (FHL)</strong>
    <p class="mb-0 mt-2 small">Properties that qualify as Furnished Holiday Lettings under HMRC rules. Leave blank if not applicable.</p>
</div>

<!-- FHL Income Section -->
<h5 class="mb-3 mt-4">FHL Income</h5>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label for="fhl_income_period_amount" class="form-label">
            Rental Income
            <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip" title="Total rental income received from FHL property"></i>
        </label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="fhl_income[period_amount]" id="fhl_income_period_amount"
                   class="form-control @error('fhl_income.period_amount') is-invalid @enderror"
                   value="{{ old('fhl_income.period_amount') }}"
                   step="0.01" min="0" max="99999999999.99"
                   placeholder="0.00">
        </div>
        @error('fhl_income.period_amount')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="fhl_income_tax_deducted" class="form-label">
            Tax Deducted
            <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip" title="Tax deducted at source from rental income"></i>
        </label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="fhl_income[tax_deducted]" id="fhl_income_tax_deducted"
                   class="form-control"
                   value="{{ old('fhl_income.tax_deducted') }}"
                   step="0.01" min="0" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="fhl_income_rent_a_room" class="form-label">
            Rent a Room - Rents Received
            <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip" title="Income received under the Rent a Room scheme"></i>
        </label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="fhl_income[rent_a_room][rents_received]" id="fhl_income_rent_a_room"
                   class="form-control"
                   value="{{ old('fhl_income.rent_a_room.rents_received') }}"
                   step="0.01" min="0" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>
</div>

<!-- FHL Expenses Section -->
<h5 class="mb-3 mt-4">FHL Expenses</h5>
<div class="alert alert-warning">
    <small><strong>Note:</strong> You can use either consolidated expenses OR itemized expenses, but not both.</small>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label for="fhl_expenses_consolidated" class="form-label">
            Consolidated Expenses
            <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip" title="Total expenses claimed as a single figure"></i>
        </label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="fhl_expenses[consolidated_expenses]" id="fhl_expenses_consolidated"
                   class="form-control"
                   value="{{ old('fhl_expenses.consolidated_expenses') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
        <small class="text-muted">For TY 2024-25+, negative values are allowed</small>
    </div>

    <div class="col-12"><hr></div>
    <div class="col-12"><h6 class="text-muted">OR Itemized Expenses</h6></div>

    <div class="col-md-6">
        <label for="fhl_expenses_premises_running_costs" class="form-label">Premises Running Costs</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="fhl_expenses[premises_running_costs]" id="fhl_expenses_premises_running_costs"
                   class="form-control"
                   value="{{ old('fhl_expenses.premises_running_costs') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="fhl_expenses_repairs_and_maintenance" class="form-label">Repairs and Maintenance</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="fhl_expenses[repairs_and_maintenance]" id="fhl_expenses_repairs_and_maintenance"
                   class="form-control"
                   value="{{ old('fhl_expenses.repairs_and_maintenance') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="fhl_expenses_financial_costs" class="form-label">Financial Costs</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="fhl_expenses[financial_costs]" id="fhl_expenses_financial_costs"
                   class="form-control"
                   value="{{ old('fhl_expenses.financial_costs') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="fhl_expenses_professional_fees" class="form-label">Professional Fees</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="fhl_expenses[professional_fees]" id="fhl_expenses_professional_fees"
                   class="form-control"
                   value="{{ old('fhl_expenses.professional_fees') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="fhl_expenses_cost_of_services" class="form-label">Cost of Services</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="fhl_expenses[cost_of_services]" id="fhl_expenses_cost_of_services"
                   class="form-control"
                   value="{{ old('fhl_expenses.cost_of_services') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="fhl_expenses_travel_costs" class="form-label">Travel Costs</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="fhl_expenses[travel_costs]" id="fhl_expenses_travel_costs"
                   class="form-control"
                   value="{{ old('fhl_expenses.travel_costs') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="fhl_expenses_other" class="form-label">Other Expenses</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="fhl_expenses[other]" id="fhl_expenses_other"
                   class="form-control"
                   value="{{ old('fhl_expenses.other') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="fhl_expenses_rent_a_room" class="form-label">Rent a Room - Amount Claimed</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="fhl_expenses[rent_a_room][amount_claimed]" id="fhl_expenses_rent_a_room"
                   class="form-control"
                   value="{{ old('fhl_expenses.rent_a_room.amount_claimed') }}"
                   step="0.01" min="0" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>
</div>
