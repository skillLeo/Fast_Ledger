@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h4 class="page-title">{{ __('company.create_new_customer') }}</h4>
                        </div>

                        <form method="POST" action="{{ route('company.customers.store', $company) }}" id="customerForm">
                            @csrf

                            {{-- ROW 1: IDENTITY & ADDRESS --}}
                            <div class="row g-3 mt-1">

                                {{-- IDENTITY INFORMATION --}}
                                <div class="col-12 col-xl-6">
                                    <div class="h-100">
                                        <div class="card-header">
                                            <div class="card-title">{{ __('company.identity_information') }}</div>
                                        </div>

                                        <div class="card-body">
                                            <div class="row g-2">

                                                <div class="col-12">
                                                    <div class="form-group mb-2">
                                                        <label style="font-weight: 600;">{{ __('company.customer_type') }} <span class="text-danger">*</span></label>
                                                        
                                                        <div class="radio-group mb-2">
                                                            <input type="radio" id="customer_type_individual" name="Customer_Type" 
                                                                value="Individual" {{ old('Customer_Type') == 'Individual' ? 'checked' : '' }}>
                                                            <label for="customer_type_individual">{{ __('company.individual') }}</label>
                                                        </div>
                                                        
                                                        <div class="radio-group">
                                                            <input type="radio" id="customer_type_company" name="Customer_Type" 
                                                                value="Company" {{ old('Customer_Type') == 'Company' ? 'checked' : '' }}>
                                                            <label for="customer_type_company">{{ __('company.company_type') }}</label>
                                                        </div>
                                                        
                                                        @error('Customer_Type')
                                                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-12" id="company_fields_row" style="display: none;">
                                                    <div class="row g-2">
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-2">
                                                                <label for="Company_Name">{{ __('company.company_name') }} <span class="text-danger">*</span></label>
                                                                <input type="text" id="Company_Name" name="Legal_Name_Company" 
                                                                    class="form-control @error('Legal_Name_Company_Name') is-invalid @enderror" 
                                                                    placeholder="{{ __('company.enter_company_name') }}"
                                                                    value="{{ old('Legal_Name_Company_Name') }}">
                                                                @error('Legal_Name_Company_Name')
                                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-2">
                                                                <label for="Contact_Person_Name">{{ __('company.contact_person_name') }}</label>
                                                                <input type="text" id="Contact_Person_Name" name="Contact_Person_Name" 
                                                                    class="form-control @error('Contact_Person_Name') is-invalid @enderror" 
                                                                    placeholder="{{ __('company.enter_contact_person_name') }}"
                                                                    value="{{ old('Contact_Person_Name') }}">
                                                                @error('Contact_Person_Name')
                                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12" id="individual_field" style="display: none;">
                                                    <div class="form-group mb-2">
                                                        <label for="Legal_Name">{{ __('company.legal_name') }} <span class="text-danger">*</span></label>
                                                        <input type="text" id="Legal_Name" name="Legal_Name_Individual" 
                                                            class="form-control @error('Legal_Name_Company_Name') is-invalid @enderror" 
                                                            placeholder="{{ __('company.enter_legal_name') }}"
                                                            value="{{ old('Legal_Name_Company_Name') }}">
                                                        @error('Legal_Name_Company_Name')
                                                            <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <input type="hidden" name="Legal_Name_Company_Name" id="Legal_Name_Company_Name">

                                                <div class="col-12">
                                                    <div class="row g-2">
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-2">
                                                                <label for="Tax_ID_Type">{{ __('company.tax_identification_type') }} <span class="text-danger">*</span></label>
                                                                <select id="Tax_ID_Type" name="Tax_ID_Type"
                                                                    class="form-control @error('Tax_ID_Type') is-invalid @enderror" required>
                                                                    <option value="">{{ __('company.select_tax_id_type') }}</option>
                                                                    <option value="NIF" {{ old('Tax_ID_Type') == 'NIF' ? 'selected' : '' }}>{{ __('company.nif') }}</option>
                                                                    <option value="CIF" {{ old('Tax_ID_Type') == 'CIF' ? 'selected' : '' }}>{{ __('company.cif') }}</option>
                                                                    <option value="NIE" {{ old('Tax_ID_Type') == 'NIE' ? 'selected' : '' }}>{{ __('company.nie') }}</option>
                                                                    <option value="EU_VAT" {{ old('Tax_ID_Type') == 'EU_VAT' ? 'selected' : '' }}>{{ __('company.eu_vat') }}</option>
                                                                </select>
                                                                @error('Tax_ID_Type')
                                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6" id="tax_id_number_wrapper" style="display: none;">
                                                            <div class="form-group mb-2">
                                                                <label for="Tax_ID_Number">
                                                                    <span id="tax_id_label">{{ __('company.tax_id_number') }}</span>
                                                                    <span class="text-danger">*</span>
                                                                </label>
                                                                <input type="text" id="Tax_ID_Number" name="Tax_ID_Number" 
                                                                    class="form-control @error('Tax_ID_Number') is-invalid @enderror" 
                                                                    value="{{ old('Tax_ID_Number') }}" 
                                                                    placeholder="{{ __('company.enter_tax_id_number') }}" required>
                                                                @error('Tax_ID_Number')
                                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ADDRESS INFORMATION --}}
                                <div class="col-12 col-xl-6">
                                    <div class="h-100">
                                        <div class="card-header">
                                            <div class="card-title">{{ __('company.address_information') }}</div>
                                        </div>

                                        <div class="card-body">
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <div class="form-group mb-2">
                                                        <label for="Street_Address">{{ __('company.street_address') }} <span class="text-danger">*</span></label>
                                                        <input type="text" id="Street_Address" name="Street_Address" 
                                                            class="form-control @error('Street_Address') is-invalid @enderror"
                                                            placeholder="{{ __('company.enter_street_address') }}"
                                                            value="{{ old('Street_Address') }}" required>
                                                        @error('Street_Address')
                                                            <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label for="City">{{ __('company.city') }} <span class="text-danger">*</span></label>
                                                        <input type="text" id="City" name="City" 
                                                            class="form-control @error('City') is-invalid @enderror"
                                                            placeholder="{{ __('company.enter_city') }}"
                                                            value="{{ old('City') }}" required>
                                                        @error('City')
                                                            <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label for="Postal_Code">{{ __('company.postal_code') }} <span class="text-danger">*</span></label>
                                                        <input type="text" id="Postal_Code" name="Postal_Code" 
                                                            class="form-control @error('Postal_Code') is-invalid @enderror"
                                                            placeholder="{{ __('company.enter_postal_code') }}"
                                                            value="{{ old('Postal_Code') }}" required>
                                                        @error('Postal_Code')
                                                            <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label for="Province">{{ __('company.province') }} <span class="text-danger">*</span></label>
                                                        <input type="text" id="Province" name="Province" 
                                                            class="form-control @error('Province') is-invalid @enderror"
                                                            placeholder="{{ __('company.enter_province') }}"
                                                            value="{{ old('Province') }}" required>
                                                        @error('Province')
                                                            <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <div class="form-group mb-2">
                                                        <label for="Country">{{ __('company.country') }} <span class="text-danger">*</span></label>
                                                        <input type="text" id="Country" name="Country" 
                                                            class="form-control @error('Country') is-invalid @enderror"
                                                            placeholder="{{ __('company.enter_country') }}"
                                                            value="{{ old('Country') }}" required>
                                                        @error('Country')
                                                            <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ROW 2: CONTACT & TAX CONFIG --}}
                            <div class="row g-3 mt-1">
                                
                                {{-- CONTACT INFORMATION --}}
                                <div class="col-12 col-xl-6">
                                    <div class="h-100">
                                        <div class="card-header">
                                            <div class="card-title">{{ __('company.contact_information') }}</div>
                                        </div>

                                        <div class="card-body">
                                            <div class="row g-2">
                                                <div class="col-12">
                                                    <div class="form-group mb-2">
                                                        <label for="Email">{{ __('company.email') }} <span class="text-danger">*</span></label>
                                                        <input type="email" id="Email" name="Email" 
                                                            class="form-control @error('Email') is-invalid @enderror"
                                                            placeholder="{{ __('company.enter_email_address') }}"
                                                            value="{{ old('Email') }}" required>
                                                        @error('Email')
                                                            <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="form-group mb-2">
                                                        <label for="Phone">{{ __('company.phone') }} <small class="text-muted">({{ __('company.optional') }})</small></label>
                                                        <input type="tel" id="Phone" name="Phone" 
                                                            class="form-control @error('Phone') is-invalid @enderror"
                                                            placeholder="{{ __('company.enter_phone_number') }}"
                                                            value="{{ old('Phone') }}">
                                                        @error('Phone')
                                                            <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- TAX CONFIGURATION --}}
                                <div class="col-12 col-xl-6">
                                    <div class="h-100">
                                        <div class="card-header">
                                            <div class="card-title">{{ __('company.tax_configuration') }}</div>
                                        </div>

                                        <div class="card-body">
                                            <div class="row g-2">
                                                
                                                <div class="col-12">
                                                    <div class="form-group mb-2">
                                                        <label style="font-weight: 600;">{{ __('company.tax_type') }}</label>
                                                        
                                                        <div class="radio-group mb-2">
                                                            <input type="radio" id="tax_none" name="Tax_Type" value="None"
                                                                {{ old('Tax_Type', 'None') == 'None' ? 'checked' : '' }}>
                                                            <label for="tax_none">{{ __('company.no_tax') }}</label>
                                                        </div>
                                                        
                                                        <div class="radio-group mb-2">
                                                            <input type="radio" id="tax_vat" name="Tax_Type" value="VAT"
                                                                {{ old('Tax_Type') == 'VAT' ? 'checked' : '' }}>
                                                            <label for="tax_vat">{{ __('company.enable_vat') }}</label>
                                                        </div>
                                                        
                                                        <div class="radio-group">
                                                            <input type="radio" id="tax_irpf" name="Tax_Type" value="IRPF"
                                                                {{ old('Tax_Type') == 'IRPF' ? 'checked' : '' }}>
                                                            <label for="tax_irpf">{{ __('company.enable_irpf') }}</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-12" id="vat_rate_wrapper" style="display: none;">
                                                    <div class="form-group mb-2">
                                                        <label for="VAT_Rate">{{ __('company.vat_rate') }}</label>
                                                        <select id="VAT_Rate" name="VAT_Rate"
                                                            class="form-control @error('VAT_Rate') is-invalid @enderror">
                                                            <option value="">{{ __('company.select_vat_rate') }}</option>
                                                            <option value="Standard_21" {{ old('VAT_Rate') == 'Standard_21' ? 'selected' : '' }}>{{ __('company.standard_21') }}</option>
                                                            <option value="Reduced_10" {{ old('VAT_Rate') == 'Reduced_10' ? 'selected' : '' }}>{{ __('company.reduced_10') }}</option>
                                                            <option value="Super_Reduced_4" {{ old('VAT_Rate') == 'Super_Reduced_4' ? 'selected' : '' }}>{{ __('company.super_reduced_4') }}</option>
                                                            <option value="Exempt_0" {{ old('VAT_Rate') == 'Exempt_0' ? 'selected' : '' }}>{{ __('company.exempt_0') }}</option>
                                                            <option value="Intra_EU" {{ old('VAT_Rate') == 'Intra_EU' ? 'selected' : '' }}>{{ __('company.intra_eu') }}</option>
                                                            <option value="Export" {{ old('VAT_Rate') == 'Export' ? 'selected' : '' }}>{{ __('company.export') }}</option>
                                                        </select>
                                                        @error('VAT_Rate')
                                                            <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="col-12" id="irpf_rate_wrapper" style="display: none;">
                                                    <div class="form-group mb-2">
                                                        <label for="IRPF_Rate">{{ __('company.irpf_rate') }}</label>
                                                        <select id="IRPF_Rate" name="IRPF_Rate"
                                                            class="form-control @error('IRPF_Rate') is-invalid @enderror">
                                                            <option value="">{{ __('company.select_irpf_rate') }}</option>
                                                            <option value="7" {{ old('IRPF_Rate') == '7' ? 'selected' : '' }}>{{ __('company.irpf_7') }}</option>
                                                            <option value="15" {{ old('IRPF_Rate') == '15' ? 'selected' : '' }}>{{ __('company.irpf_15') }}</option>
                                                        </select>
                                                        @error('IRPF_Rate')
                                                            <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <input type="hidden" name="Has_VAT" id="Has_VAT" value="0">
                                                <input type="hidden" name="Has_IRPF" id="Has_IRPF" value="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ROW 3: PAYMENT SETTINGS --}}
                            <div class="row g-3 mt-1">
                                <div class="col-12">
                                    <div class="card-header">
                                        <div class="card-title">{{ __('company.payment_settings') }}</div>
                                    </div>

                                    <div class="card-body">
                                        <div class="row g-2">

                                            <div class="col-12 col-md-6">
                                                <div class="form-group mb-2">
                                                    <label style="font-weight: 600;">{{ __('company.preferred_payment_method') }} <span class="text-danger">*</span></label>
                                                    
                                                    <div class="radio-group mb-2">
                                                        <input type="radio" id="payment_bank_transfer" name="Payment_Method" 
                                                            value="Bank_Transfer" {{ old('Payment_Method') == 'Bank_Transfer' ? 'checked' : '' }}>
                                                        <label for="payment_bank_transfer">{{ __('company.bank_transfer') }}</label>
                                                    </div>
                                                    
                                                    <div class="radio-group">
                                                        <input type="radio" id="payment_cash" name="Payment_Method" 
                                                            value="Cash" {{ old('Payment_Method') == 'Cash' ? 'checked' : '' }}>
                                                        <label for="payment_cash">{{ __('company.cash') }}</label>
                                                    </div>
                                                    
                                                    @error('Payment_Method')
                                                        <span class="text-danger small">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-6" id="bank_details_wrapper" style="display: none;">
                                                <div class="form-group mb-2">
                                                    <label style="font-weight: 600;">{{ __('company.bank_details_optional') }}</label>
                                                    
                                                    <div class="mb-2">
                                                        <label for="IBAN" class="form-label mb-1" style="font-size: 0.875rem;">
                                                            {{ __('company.iban') }} <small class="text-muted">({{ __('company.optional') }})</small>
                                                        </label>
                                                        <input type="text" id="IBAN" name="IBAN" 
                                                            class="form-control @error('IBAN') is-invalid @enderror" 
                                                            placeholder="{{ __('company.enter_iban_number') }}"
                                                            value="{{ old('IBAN') }}">
                                                        @error('IBAN')
                                                            <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>

                                                    <div>
                                                        <label for="Bank_Name" class="form-label mb-1" style="font-size: 0.875rem;">
                                                            {{ __('company.bank_name') }} <small class="text-muted">({{ __('company.optional') }})</small>
                                                        </label>
                                                        <input type="text" id="Bank_Name" name="Bank_Name" 
                                                            class="form-control @error('Bank_Name') is-invalid @enderror" 
                                                            placeholder="{{ __('company.enter_bank_name') }}"
                                                            value="{{ old('Bank_Name') }}">
                                                        @error('Bank_Name')
                                                            <span class="invalid-feedback">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ACTIONS --}}
                            <div class="row mt-3 mb-3">
                                <div class="col-12 text-end">
                                    <a href="{{ route('company.customers.index', $company) }}" class="btn btn-light">{{ __('company.cancel') }}</a>
                                    <button type="submit" class="btn btn-primary">{{ __('company.create_customer') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            
            $('input[name="Customer_Type"]').on('change', function() {
                const selectedType = $(this).val();
                $('#individual_field, #company_fields_row').hide();
                
                if (selectedType === 'Individual') {
                    $('#individual_field').fadeIn();
                } else if (selectedType === 'Company') {
                    $('#company_fields_row').fadeIn();
                }
            });
            
            $('#customerForm').on('submit', function() {
                const individualName = $('input[name="Legal_Name_Individual"]').val();
                const companyName = $('input[name="Legal_Name_Company"]').val();
                $('#Legal_Name_Company_Name').val(individualName || companyName);
            });

            $('#Tax_ID_Type').on('change', function() {
                const selectedType = $(this).val();
                
                if (selectedType !== '') {
                    const labels = {
                        'NIF': "{{ __('company.nif_number') }}",
                        'CIF': "{{ __('company.cif_number') }}",
                        'NIE': "{{ __('company.nie_number') }}",
                        'EU_VAT': "{{ __('company.eu_vat_number') }}"
                    };
                    
                    $('#tax_id_label').text(labels[selectedType] || "{{ __('company.tax_id_number') }}");
                    $('#tax_id_number_wrapper').fadeIn();
                } else {
                    $('#tax_id_number_wrapper').hide();
                }
            });

            $('input[name="Tax_Type"]').on('change', function() {
                const selectedTax = $(this).val();
                
                $('#vat_rate_wrapper, #irpf_rate_wrapper').hide();
                $('#Has_VAT, #Has_IRPF').val('0');
                
                if (selectedTax === 'VAT') {
                    $('#vat_rate_wrapper').fadeIn();
                    $('#Has_VAT').val('1');
                } else if (selectedTax === 'IRPF') {
                    $('#irpf_rate_wrapper').fadeIn();
                    $('#Has_IRPF').val('1');
                }
            });

            $('input[name="Payment_Method"]').on('change', function() {
                const selectedMethod = $(this).val();
                
                if (selectedMethod === 'Bank_Transfer') {
                    $('#bank_details_wrapper').fadeIn();
                } else {
                    $('#bank_details_wrapper').hide();
                }
            });

            // Initialize on load
            const selectedCustomerType = $('input[name="Customer_Type"]:checked').val();
            if (selectedCustomerType) {
                $('input[name="Customer_Type"]:checked').trigger('change');
            }
            
            const selectedTaxType = $('#Tax_ID_Type').val();
            if (selectedTaxType) {
                $('#Tax_ID_Type').trigger('change');
            }
            
            const selectedTax = $('input[name="Tax_Type"]:checked').val();
            if (selectedTax) {
                $('input[name="Tax_Type"]:checked').trigger('change');
            }
            
            const selectedPaymentMethod = $('input[name="Payment_Method"]:checked').val();
            if (selectedPaymentMethod) {
                $('input[name="Payment_Method"]:checked').trigger('change');
            }
        });
    </script>

    <style>
        .radio-group, .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .radio-group input[type="radio"],
        .checkbox-group input[type="checkbox"] {
            margin: 0;
            cursor: pointer;
        }
        
        .radio-group label,
        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            white-space: nowrap;
        }
    </style>
@endsection