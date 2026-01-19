{{-- ========================================================================
     TRANSACTION CODE INPUT
     Shows prefix + editable suffix with validation
     ======================================================================== --}}

@if (!($type === 'office' && in_array($paymentType, ['sales_invoice', 'purchase','journal'])))
    
    <div class="mb-2">
        <div class="d-flex align-items-center gap-2">
            
            {{-- Prefix + Suffix Input --}}
            <div class="input-group" style="max-width: 150px;">
                <span class="input-group-text bg-light fw-bold rounded-0"
                      style="padding: 3px 12px !important;"
                      id="codePrefix">{{ $currentPrefix }}</span>
                
                <input type="text" 
                       id="codeSuffix"
                       class="form-control @error('Transaction_Code') is-invalid @enderror"
                       value="{{ str_pad($minSuffixNum, $suffixLen, '0', STR_PAD_LEFT) }}"
                       inputmode="numeric" 
                       pattern="\d{{ $suffixLen }}" 
                       autocomplete="off"
                       aria-describedby="codeHelp codeValidationMessage">
            </div>
            
        </div>

        {{-- Validation Message --}}
        <div id="codeValidationMessage" class="mt-1"></div>

        {{-- Laravel Validation Error --}}
        @error('Transaction_Code')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

@endif