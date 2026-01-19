<div class="card shadow-sm border-0 rounded-3">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">
            <i class="fas fa-info-circle me-2"></i>
            Business Information & Non-Financials
        </h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Provide additional business information (all fields are optional).</p>

        <!-- Business Address -->
        <h6 class="mb-3">Business Address (Optional)</h6>
        <div class="row g-3 mb-4">
            <div class="col-md-12">
                <label for="business_address_line_1" class="form-label">Address Line 1</label>
                <input type="text" name="non_financials[business_address_line_1]" 
                       id="business_address_line_1"
                       class="form-control @error('non_financials.business_address_line_1') is-invalid @enderror"
                       value="{{ old('non_financials.business_address_line_1') }}"
                       maxlength="35"
                       placeholder="Enter address line 1">
                @error('non_financials.business_address_line_1')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label for="business_address_line_2" class="form-label">Address Line 2</label>
                <input type="text" name="non_financials[business_address_line_2]" 
                       id="business_address_line_2"
                       class="form-control @error('non_financials.business_address_line_2') is-invalid @enderror"
                       value="{{ old('non_financials.business_address_line_2') }}"
                       maxlength="35"
                       placeholder="Enter address line 2">
                @error('non_financials.business_address_line_2')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="business_address_line_3" class="form-label">Address Line 3 / Town</label>
                <input type="text" name="non_financials[business_address_line_3]" 
                       id="business_address_line_3"
                       class="form-control @error('non_financials.business_address_line_3') is-invalid @enderror"
                       value="{{ old('non_financials.business_address_line_3') }}"
                       maxlength="35"
                       placeholder="Enter town/city">
                @error('non_financials.business_address_line_3')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="business_address_line_4" class="form-label">Address Line 4 / County</label>
                <input type="text" name="non_financials[business_address_line_4]" 
                       id="business_address_line_4"
                       class="form-control @error('non_financials.business_address_line_4') is-invalid @enderror"
                       value="{{ old('non_financials.business_address_line_4') }}"
                       maxlength="35"
                       placeholder="Enter county">
                @error('non_financials.business_address_line_4')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="business_address_postcode" class="form-label">Postcode</label>
                <input type="text" name="non_financials[business_address_postcode]" 
                       id="business_address_postcode"
                       class="form-control @error('non_financials.business_address_postcode') is-invalid @enderror"
                       value="{{ old('non_financials.business_address_postcode') }}"
                       maxlength="10"
                       placeholder="e.g., SW1A 1AA"
                       style="text-transform: uppercase;">
                @error('non_financials.business_address_postcode')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="business_address_country_code" class="form-label">
                    Country Code
                    <i class="fas fa-info-circle text-muted" 
                       data-bs-toggle="tooltip" 
                       title="Two letter ISO country code (e.g., GB, FR, US)"></i>
                </label>
                <input type="text" name="non_financials[business_address_country_code]" 
                       id="business_address_country_code"
                       class="form-control @error('non_financials.business_address_country_code') is-invalid @enderror"
                       value="{{ old('non_financials.business_address_country_code', 'GB') }}"
                       maxlength="2"
                       placeholder="GB"
                       style="text-transform: uppercase;">
                <small class="text-muted">2-letter ISO code (GB, FR, US, etc.)</small>
                @error('non_financials.business_address_country_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Class 4 NICs Exemption -->
        <h6 class="mb-3">Class 4 National Insurance Contributions</h6>
        <div class="row g-3">
            <div class="col-md-12">
                <label for="class_4_nics_exemption_reason" class="form-label">
                    Exemption Reason (if applicable)
                    <i class="fas fa-info-circle text-muted" 
                       data-bs-toggle="tooltip" 
                       title="Select if you are exempt from Class 4 NICs"></i>
                </label>
                <select name="non_financials[class_4_nics_exemption_reason]"
                        id="class_4_nics_exemption_reason"
                        class="form-select @error('non_financials.class_4_nics_exemption_reason') is-invalid @enderror">
                    <option value="">No exemption</option>
                    <option value="001" {{ old('non_financials.class_4_nics_exemption_reason') == '001' ? 'selected' : '' }}>
                        001 - Non-resident
                    </option>
                    <option value="002" {{ old('non_financials.class_4_nics_exemption_reason') == '002' ? 'selected' : '' }}>
                        002 - Trustee
                    </option>
                    <option value="003" {{ old('non_financials.class_4_nics_exemption_reason') == '003' ? 'selected' : '' }}>
                        003 - Diver
                    </option>
                    <option value="004" {{ old('non_financials.class_4_nics_exemption_reason') == '004' ? 'selected' : '' }}>
                        004 - Employed earner taxed under ITTOIA 2005
                    </option>
                    <option value="005" {{ old('non_financials.class_4_nics_exemption_reason') == '005' ? 'selected' : '' }}>
                        005 - Over state pension age
                    </option>
                    <option value="006" {{ old('non_financials.class_4_nics_exemption_reason') == '006' ? 'selected' : '' }}>
                        006 - Under 16
                    </option>
                </select>
                @error('non_financials.class_4_nics_exemption_reason')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Select only if you qualify for an exemption from Class 4 NICs</small>
            </div>
        </div>

        <div class="alert alert-info mt-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note:</strong> All fields in this section are optional. Complete only if the information is relevant to your submission.
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <button type="button" class="btn btn-secondary prev-step">
                <i class="fas fa-arrow-left me-2"></i> Previous
            </button>
            <button type="button" class="btn btn-primary next-step">
                Next: Review <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-uppercase postcode and country code
    document.getElementById('business_address_postcode').addEventListener('input', function(e) {
        this.value = this.value.toUpperCase();
    });

    document.getElementById('business_address_country_code').addEventListener('input', function(e) {
        this.value = this.value.toUpperCase();
    });
});
</script>
@endpush



