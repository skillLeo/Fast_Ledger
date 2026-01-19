<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">
            <i class="fas fa-money-bill-wave me-2"></i>
            Capital Allowances
        </h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Enter any capital allowances you are claiming for this tax year.</p>

        <div class="row g-3">
            <!-- Annual Investment Allowance -->
            <div class="col-md-6">
                <label for="annual_investment_allowance" class="form-label">
                    Annual Investment Allowance (AIA)
                    <i class="fas fa-info-circle text-muted" 
                       data-bs-toggle="tooltip" 
                       title="100% allowance on qualifying plant and machinery (up to £1m)"></i>
                </label>
                <div class="input-group">
                    <span class="input-group-text">£</span>
                    <input type="number" name="allowances[annual_investment_allowance]" 
                           id="annual_investment_allowance"
                           class="form-control @error('allowances.annual_investment_allowance') is-invalid @enderror"
                           value="{{ old('allowances.annual_investment_allowance') }}"
                           step="0.01" min="0" max="99999999999.99"
                           placeholder="0.00">
                </div>
                @error('allowances.annual_investment_allowance')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Capital Allowance Main Pool -->
            <div class="col-md-6">
                <label for="capital_allowance_main_pool" class="form-label">
                    Capital Allowance Main Pool
                    <i class="fas fa-info-circle text-muted" 
                       data-bs-toggle="tooltip" 
                       title="18% writing down allowance on general plant and machinery"></i>
                </label>
                <div class="input-group">
                    <span class="input-group-text">£</span>
                    <input type="number" name="allowances[capital_allowance_main_pool]" 
                           id="capital_allowance_main_pool"
                           class="form-control"
                           value="{{ old('allowances.capital_allowance_main_pool') }}"
                           step="0.01" min="0" max="99999999999.99"
                           placeholder="0.00">
                </div>
            </div>

            <!-- Capital Allowance Special Rate Pool -->
            <div class="col-md-6">
                <label for="capital_allowance_special_rate_pool" class="form-label">
                    Capital Allowance Special Rate Pool
                    <i class="fas fa-info-circle text-muted" 
                       data-bs-toggle="tooltip" 
                       title="6% writing down allowance on integral features and long-life assets"></i>
                </label>
                <div class="input-group">
                    <span class="input-group-text">£</span>
                    <input type="number" name="allowances[capital_allowance_special_rate_pool]" 
                           id="capital_allowance_special_rate_pool"
                           class="form-control"
                           value="{{ old('allowances.capital_allowance_special_rate_pool') }}"
                           step="0.01" min="0" max="99999999999.99"
                           placeholder="0.00">
                </div>
            </div>

            <!-- Zero Emission Goods Vehicle Allowance -->
            <div class="col-md-6">
                <label for="zero_emission_goods_vehicle_allowance" class="form-label">
                    Zero Emission Goods Vehicle Allowance
                    <i class="fas fa-info-circle text-muted" 
                       data-bs-toggle="tooltip" 
                       title="100% FYA on new zero emission goods vehicles"></i>
                </label>
                <div class="input-group">
                    <span class="input-group-text">£</span>
                    <input type="number" name="allowances[zero_emission_goods_vehicle_allowance]" 
                           id="zero_emission_goods_vehicle_allowance"
                           class="form-control"
                           value="{{ old('allowances.zero_emission_goods_vehicle_allowance') }}"
                           step="0.01" min="0" max="99999999999.99"
                           placeholder="0.00">
                </div>
            </div>

            <!-- Zero Emission Car Allowance -->
            <div class="col-md-6">
                <label for="zero_emission_car_allowance" class="form-label">
                    Zero Emission Car Allowance
                    <i class="fas fa-info-circle text-muted" 
                       data-bs-toggle="tooltip" 
                       title="100% FYA on new zero emission cars"></i>
                </label>
                <div class="input-group">
                    <span class="input-group-text">£</span>
                    <input type="number" name="allowances[zero_emission_car_allowance]" 
                           id="zero_emission_car_allowance"
                           class="form-control"
                           value="{{ old('allowances.zero_emission_car_allowance') }}"
                           step="0.01" min="0" max="99999999999.99"
                           placeholder="0.00">
                </div>
            </div>

            <!-- Electric Charge Point Allowance -->
            <div class="col-md-6">
                <label for="electric_charge_point_allowance" class="form-label">
                    Electric Charge Point Allowance
                    <i class="fas fa-info-circle text-muted" 
                       data-bs-toggle="tooltip" 
                       title="100% FYA on electric vehicle charging points"></i>
                </label>
                <div class="input-group">
                    <span class="input-group-text">£</span>
                    <input type="number" name="allowances[electric_charge_point_allowance]" 
                           id="electric_charge_point_allowance"
                           class="form-control"
                           value="{{ old('allowances.electric_charge_point_allowance') }}"
                           step="0.01" min="0" max="99999999999.99"
                           placeholder="0.00">
                </div>
            </div>

            <!-- Business Premises Renovation Allowance -->
            <div class="col-md-6">
                <label for="business_premises_renovation_allowance" class="form-label">
                    Business Premises Renovation Allowance
                    <i class="fas fa-info-circle text-muted" 
                       data-bs-toggle="tooltip" 
                       title="100% allowance on renovating qualifying business premises"></i>
                </label>
                <div class="input-group">
                    <span class="input-group-text">£</span>
                    <input type="number" name="allowances[business_premises_renovation_allowance]" 
                           id="business_premises_renovation_allowance"
                           class="form-control"
                           value="{{ old('allowances.business_premises_renovation_allowance') }}"
                           step="0.01" min="0" max="99999999999.99"
                           placeholder="0.00">
                </div>
            </div>

            <!-- Enhanced Capital Allowance -->
            <div class="col-md-6">
                <label for="enhanced_capital_allowance" class="form-label">
                    Enhanced Capital Allowance
                    <i class="fas fa-info-circle text-muted" 
                       data-bs-toggle="tooltip" 
                       title="100% FYA on qualifying energy/water efficient equipment"></i>
                </label>
                <div class="input-group">
                    <span class="input-group-text">£</span>
                    <input type="number" name="allowances[enhanced_capital_allowance]" 
                           id="enhanced_capital_allowance"
                           class="form-control"
                           value="{{ old('allowances.enhanced_capital_allowance') }}"
                           step="0.01" min="0" max="99999999999.99"
                           placeholder="0.00">
                </div>
            </div>

            <!-- Structure and Buildings Allowance -->
            <div class="col-md-6">
                <label for="structure_and_buildings_allowance" class="form-label">
                    Structure and Buildings Allowance
                    <i class="fas fa-info-circle text-muted" 
                       data-bs-toggle="tooltip" 
                       title="3% annual allowance on qualifying non-residential structures"></i>
                </label>
                <div class="input-group">
                    <span class="input-group-text">£</span>
                    <input type="number" name="allowances[structure_and_buildings_allowance]" 
                           id="structure_and_buildings_allowance"
                           class="form-control"
                           value="{{ old('allowances.structure_and_buildings_allowance') }}"
                           step="0.01" min="0" max="99999999999.99"
                           placeholder="0.00">
                </div>
            </div>

            <!-- Enhanced Structure and Buildings Allowance -->
            <div class="col-md-6">
                <label for="enhanced_structure_and_buildings_allowance" class="form-label">
                    Enhanced Structure and Buildings Allowance
                    <i class="fas fa-info-circle text-muted" 
                       data-bs-toggle="tooltip" 
                       title="Freeport enhanced allowance - 10% per year"></i>
                </label>
                <div class="input-group">
                    <span class="input-group-text">£</span>
                    <input type="number" name="allowances[enhanced_structure_and_buildings_allowance]" 
                           id="enhanced_structure_and_buildings_allowance"
                           class="form-control"
                           value="{{ old('allowances.enhanced_structure_and_buildings_allowance') }}"
                           step="0.01" min="0" max="99999999999.99"
                           placeholder="0.00">
                </div>
            </div>

            <!-- Allowance on Sales -->
            <div class="col-md-6">
                <label for="allowance_on_sales" class="form-label">
                    Allowance on Sales
                    <i class="fas fa-info-circle text-muted" 
                       data-bs-toggle="tooltip" 
                       title="Allowance arising from disposal of assets"></i>
                </label>
                <div class="input-group">
                    <span class="input-group-text">£</span>
                    <input type="number" name="allowances[allowance_on_sales]" 
                           id="allowance_on_sales"
                           class="form-control"
                           value="{{ old('allowances.allowance_on_sales') }}"
                           step="0.01" min="0" max="99999999999.99"
                           placeholder="0.00">
                </div>
            </div>
        </div>

        <div class="mt-3">
            <div class="alert alert-light border">
                <strong>Total Allowances:</strong> 
                <span id="total-allowances" class="text-primary fs-5">£0.00</span>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <button type="button" class="btn btn-secondary prev-step">
                <i class="fas fa-arrow-left me-2"></i> Previous
            </button>
            <button type="button" class="btn btn-primary next-step">
                Next: Non-Financials <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate total allowances
    function updateAllowancesTotal() {
        let total = 0;
        document.querySelectorAll('[name^="allowances["]').forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        document.getElementById('total-allowances').textContent = '£' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // Listen to allowances inputs
    document.querySelectorAll('[name^="allowances["]').forEach(input => {
        input.addEventListener('input', updateAllowancesTotal);
    });

    updateAllowancesTotal();
});
</script>
@endpush



