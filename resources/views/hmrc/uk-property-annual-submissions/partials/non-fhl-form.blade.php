<div class="alert alert-info">
    <strong><i class="fas fa-info-circle me-2"></i>Non-FHL Property</strong>
    <p class="mb-0 mt-2 small">Standard residential and commercial property rentals that don't qualify as FHL. Leave blank if not applicable.</p>
</div>

<!-- Non-FHL Income Section -->
<h5 class="mb-3 mt-4">Non-FHL Income</h5>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <label for="non_fhl_income_period_amount" class="form-label">
            Rental Income
            <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip" title="Total rental income received from non-FHL property"></i>
        </label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_income[period_amount]" id="non_fhl_income_period_amount"
                   class="form-control @error('non_fhl_income.period_amount') is-invalid @enderror"
                   value="{{ old('non_fhl_income.period_amount') }}"
                   step="0.01" min="0" max="99999999999.99"
                   placeholder="0.00">
        </div>
        @error('non_fhl_income.period_amount')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="non_fhl_income_tax_deducted" class="form-label">
            Tax Deducted
            <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip" title="Tax deducted at source from rental income"></i>
        </label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_income[tax_deducted]" id="non_fhl_income_tax_deducted"
                   class="form-control"
                   value="{{ old('non_fhl_income.tax_deducted') }}"
                   step="0.01" min="0" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="non_fhl_income_premiums_of_lease_grant" class="form-label">
            Premiums of Lease Grant
            <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip" title="Premiums received for granting a lease"></i>
        </label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_income[premiums_of_lease_grant]" id="non_fhl_income_premiums_of_lease_grant"
                   class="form-control"
                   value="{{ old('non_fhl_income.premiums_of_lease_grant') }}"
                   step="0.01" min="0" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="non_fhl_income_reverse_premiums" class="form-label">
            Reverse Premiums
            <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip" title="Payments received to take on a lease"></i>
        </label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_income[reverse_premiums]" id="non_fhl_income_reverse_premiums"
                   class="form-control"
                   value="{{ old('non_fhl_income.reverse_premiums') }}"
                   step="0.01" min="0" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="non_fhl_income_other_income" class="form-label">
            Other Income
            <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip" title="Any other property income"></i>
        </label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_income[other_income]" id="non_fhl_income_other_income"
                   class="form-control"
                   value="{{ old('non_fhl_income.other_income') }}"
                   step="0.01" min="0" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="non_fhl_income_rent_a_room" class="form-label">
            Rent a Room - Rents Received
            <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip" title="Income received under the Rent a Room scheme"></i>
        </label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_income[rent_a_room][rents_received]" id="non_fhl_income_rent_a_room"
                   class="form-control"
                   value="{{ old('non_fhl_income.rent_a_room.rents_received') }}"
                   step="0.01" min="0" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>
</div>

<!-- Non-FHL Expenses Section -->
<h5 class="mb-3 mt-4">Non-FHL Expenses</h5>
<div class="alert alert-warning">
    <small><strong>Note:</strong> You can use either consolidated expenses OR itemized expenses, but not both.</small>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <label for="non_fhl_expenses_consolidated" class="form-label">
            Consolidated Expenses
            <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip" title="Total expenses claimed as a single figure"></i>
        </label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_expenses[consolidated_expenses]" id="non_fhl_expenses_consolidated"
                   class="form-control"
                   value="{{ old('non_fhl_expenses.consolidated_expenses') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
        <small class="text-muted">For TY 2024-25+, negative values are allowed</small>
    </div>

    <div class="col-12"><hr></div>
    <div class="col-12"><h6 class="text-muted">OR Itemized Expenses</h6></div>

    <div class="col-md-6">
        <label for="non_fhl_expenses_premises_running_costs" class="form-label">Premises Running Costs</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_expenses[premises_running_costs]" id="non_fhl_expenses_premises_running_costs"
                   class="form-control"
                   value="{{ old('non_fhl_expenses.premises_running_costs') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="non_fhl_expenses_repairs_and_maintenance" class="form-label">Repairs and Maintenance</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_expenses[repairs_and_maintenance]" id="non_fhl_expenses_repairs_and_maintenance"
                   class="form-control"
                   value="{{ old('non_fhl_expenses.repairs_and_maintenance') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="non_fhl_expenses_financial_costs" class="form-label">Financial Costs</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_expenses[financial_costs]" id="non_fhl_expenses_financial_costs"
                   class="form-control"
                   value="{{ old('non_fhl_expenses.financial_costs') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="non_fhl_expenses_professional_fees" class="form-label">Professional Fees</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_expenses[professional_fees]" id="non_fhl_expenses_professional_fees"
                   class="form-control"
                   value="{{ old('non_fhl_expenses.professional_fees') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="non_fhl_expenses_cost_of_services" class="form-label">Cost of Services</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_expenses[cost_of_services]" id="non_fhl_expenses_cost_of_services"
                   class="form-control"
                   value="{{ old('non_fhl_expenses.cost_of_services') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="non_fhl_expenses_travel_costs" class="form-label">Travel Costs</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_expenses[travel_costs]" id="non_fhl_expenses_travel_costs"
                   class="form-control"
                   value="{{ old('non_fhl_expenses.travel_costs') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="non_fhl_expenses_other" class="form-label">Other Expenses</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_expenses[other]" id="non_fhl_expenses_other"
                   class="form-control"
                   value="{{ old('non_fhl_expenses.other') }}"
                   step="0.01" min="-99999999999.99" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="non_fhl_expenses_residential_financial_cost" class="form-label">
            Residential Financial Cost
            <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip" title="Mortgage interest and other financial costs on residential property"></i>
        </label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_expenses[residential_financial_cost]" id="non_fhl_expenses_residential_financial_cost"
                   class="form-control"
                   value="{{ old('non_fhl_expenses.residential_financial_cost') }}"
                   step="0.01" min="0" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="non_fhl_expenses_residential_financial_costs_carried_forward" class="form-label">
            Residential Financial Costs Carried Forward
            <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip" title="Residential finance costs brought forward from previous years"></i>
        </label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_expenses[residential_financial_costs_carried_forward]" id="non_fhl_expenses_residential_financial_costs_carried_forward"
                   class="form-control"
                   value="{{ old('non_fhl_expenses.residential_financial_costs_carried_forward') }}"
                   step="0.01" min="0" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-md-6">
        <label for="non_fhl_expenses_rent_a_room" class="form-label">Rent a Room - Amount Claimed</label>
        <div class="input-group">
            <span class="input-group-text">£</span>
            <input type="number" name="non_fhl_expenses[rent_a_room][amount_claimed]" id="non_fhl_expenses_rent_a_room"
                   class="form-control"
                   value="{{ old('non_fhl_expenses.rent_a_room.amount_claimed') }}"
                   step="0.01" min="0" max="99999999999.99"
                   placeholder="0.00">
        </div>
    </div>
</div>
