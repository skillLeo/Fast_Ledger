@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">

                        {{-- Page Header --}}
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title mb-0">
                                {{ __('company.edit_company') }}: {{ $company->Company_Name }}
                            </div>
                            <div class="ms-auto pageheader-btn">
                                <a href="{{ route('company.show', $company->id) }}" class="btn btn-light">
                                    <i class="ri-arrow-left-line me-1"></i> {{ __('company.back') }}
                                </a>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('company.update', $company->id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-xl-12">

                                    {{-- Basic Information --}}
                                    <div class="card custom-card mb-3">
                                        <div class="card-header">
                                            <div class="card-title">
                                                {{ __('company.basic_information') }}
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row gy-3">
                                                {{-- Company Name --}}
                                                <div class="col-xl-6">
                                                    <label for="Company_Name" class="form-label">
                                                        {{ __('company.company_name') }} <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('Company_Name') is-invalid @enderror"
                                                        id="Company_Name" name="Company_Name"
                                                        placeholder="{{ __('company.enter_company_name') }}" 
                                                        value="{{ old('Company_Name', $company->Company_Name) }}"
                                                        required>
                                                    @error('Company_Name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Trade Name --}}
                                                <div class="col-xl-6">
                                                    <label for="Trade_Name" class="form-label">{{ __('company.trade_name_optional') }}</label>
                                                    <input type="text"
                                                        class="form-control @error('Trade_Name') is-invalid @enderror"
                                                        id="Trade_Name" name="Trade_Name" placeholder="{{ __('company.enter_trade_name') }}"
                                                        value="{{ old('Trade_Name', $company->Trade_Name) }}">
                                                    @error('Trade_Name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Country (Read-only) --}}
                                                <div class="col-xl-6">
                                                    <label for="Country" class="form-label">
                                                        {{ __('company.country') }} <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control" value="{{ $company->Country }}" disabled>
                                                    <input type="hidden" name="Country" value="{{ $company->Country }}">
                                                    <div class="form-text">{{ __('company.country_cannot_change') }}</div>
                                                </div>

                                                {{-- Company Type (Read-only) --}}
                                                @if($company->Country === 'GB')
                                                    <div class="col-xl-6">
                                                        <label class="form-label">{{ __('company.company_type') }}</label>
                                                        <input type="text" class="form-control" value="{{ $company->Company_Type_UK }}" disabled>
                                                        <input type="hidden" name="Company_Type_UK" value="{{ $company->Company_Type_UK }}">
                                                        <div class="form-text">{{ __('company.company_type_cannot_change') }}</div>
                                                    </div>
                                                @endif

                                                @if($company->Country === 'ES')
                                                    <div class="col-xl-6">
                                                        <label class="form-label">{{ __('company.tipo_empresa') }}</label>
                                                        <input type="text" class="form-control" value="{{ $company->Company_Type_ES }}" disabled>
                                                        <input type="hidden" name="Company_Type_ES" value="{{ $company->Company_Type_ES }}">
                                                        <div class="form-text">{{ __('company.company_type_cannot_change') }}</div>
                                                    </div>
                                                @endif

                                                {{-- Tax ID (Read-only) --}}
                                                <div class="col-xl-6">
                                                    <label for="Tax_ID" class="form-label">
                                                        {{ __('company.tax_id_nif_cif_vat') }}
                                                    </label>
                                                    <input type="text" class="form-control" value="{{ $company->Tax_ID }}" disabled>
                                                    <input type="hidden" name="Tax_ID" value="{{ $company->Tax_ID }}">
                                                    <div class="form-text">{{ __('company.tax_id_cannot_change') }}</div>
                                                </div>

                                                {{-- Country of Tax Residence --}}
                                                <div class="col-xl-6">
                                                    <label for="Country_Tax_Residence" class="form-label">
                                                        {{ __('company.country_tax_residence') }} <span class="text-danger">*</span>
                                                    </label>
                                                    <select
                                                        class="form-select @error('Country_Tax_Residence') is-invalid @enderror"
                                                        id="Country_Tax_Residence" name="Country_Tax_Residence" required>
                                                        <option value="">{{ __('company.select_country') }}</option>
                                                        @foreach ($countries as $code => $name)
                                                            <option value="{{ $code }}"
                                                                {{ old('Country_Tax_Residence', $company->Country_Tax_Residence) == $code ? 'selected' : '' }}>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('Country_Tax_Residence')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Tax Regime (Spain only) --}}
                                                @if($company->Country === 'ES')
                                                    <div class="col-xl-6">
                                                        <label for="Tax_Regime" class="form-label">{{ __('company.regimen_fiscal') }}</label>
                                                        <select class="form-select @error('Tax_Regime') is-invalid @enderror"
                                                            id="Tax_Regime" name="Tax_Regime">
                                                            <option value="">{{ __('company.seleccionar_regimen') }}</option>
                                                            @foreach ($taxRegimes as $value => $label)
                                                                <option value="{{ $value }}"
                                                                    {{ old('Tax_Regime', $company->Tax_Regime) == $value ? 'selected' : '' }}>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('Tax_Regime')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Address Information --}}
                                    <div class="card custom-card mb-3">
                                        <div class="card-header">
                                            <div class="card-title">
                                                {{ __('company.address_information') }}
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row gy-3">
                                                {{-- Street Address --}}
                                                <div class="col-12">
                                                    <label for="Street_Address" class="form-label">
                                                        {{ __('company.street_address') }} <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('Street_Address') is-invalid @enderror"
                                                        id="Street_Address" name="Street_Address"
                                                        placeholder="{{ __('company.enter_street_address') }}"
                                                        value="{{ old('Street_Address', $company->Street_Address) }}" required>
                                                    @error('Street_Address')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- City --}}
                                                <div class="col-xl-4">
                                                    <label for="City" class="form-label">
                                                        {{ __('company.city') }} <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('City') is-invalid @enderror"
                                                        id="City" name="City" placeholder="{{ __('company.enter_city') }}"
                                                        value="{{ old('City', $company->City) }}" required>
                                                    @error('City')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- State/Province --}}
                                                <div class="col-xl-4">
                                                    <label for="State" class="form-label">{{ __('company.state_province_region') }}</label>
                                                    <input type="text"
                                                        class="form-control @error('State') is-invalid @enderror"
                                                        id="State" name="State" placeholder="{{ __('company.enter_state') }}"
                                                        value="{{ old('State', $company->State) }}">
                                                    @error('State')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Postal Code --}}
                                                <div class="col-xl-4">
                                                    <label for="Postal_Code" class="form-label">
                                                        {{ __('company.postal_zip_code') }} <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text"
                                                        class="form-control @error('Postal_Code') is-invalid @enderror"
                                                        id="Postal_Code" name="Postal_Code"
                                                        placeholder="{{ __('company.enter_postal_code') }}" 
                                                        value="{{ old('Postal_Code', $company->Postal_Code) }}"
                                                        required>
                                                    @error('Postal_Code')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Additional Details --}}
                                    <div class="card custom-card mb-3">
                                        <div class="card-header" data-bs-toggle="collapse"
                                            data-bs-target="#optionalDetails" style="cursor: pointer;">
                                            <div class="card-title d-flex justify-content-between align-items-center">
                                                <span>{{ __('company.additional_details_optional') }}</span>
                                                <i class="ri-arrow-down-s-line"></i>
                                            </div>
                                        </div>
                                        <div class="collapse show" id="optionalDetails">
                                            <div class="card-body">
                                                <div class="row gy-3">
                                                    {{-- Phone --}}
                                                    <div class="col-xl-6">
                                                        <label for="Phone_Number" class="form-label">{{ __('company.phone_number') }}</label>
                                                        <input type="text"
                                                            class="form-control @error('Phone_Number') is-invalid @enderror"
                                                            id="Phone_Number" name="Phone_Number"
                                                            placeholder="{{ __('company.enter_phone_number') }}"
                                                            value="{{ old('Phone_Number', $company->Phone_Number) }}">
                                                        @error('Phone_Number')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Email --}}
                                                    <div class="col-xl-6">
                                                        <label for="Email" class="form-label">{{ __('company.email_address') }}</label>
                                                        <input type="email"
                                                            class="form-control @error('Email') is-invalid @enderror"
                                                            id="Email" name="Email" placeholder="{{ __('company.enter_email_address') }}"
                                                            value="{{ old('Email', $company->Email) }}">
                                                        @error('Email')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Website --}}
                                                    <div class="col-xl-6">
                                                        <label for="Website" class="form-label">{{ __('company.website') }}</label>
                                                        <input type="url"
                                                            class="form-control @error('Website') is-invalid @enderror"
                                                            id="Website" name="Website"
                                                            placeholder="https://example.com"
                                                            value="{{ old('Website', $company->Website) }}">
                                                        @error('Website')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Company Logo --}}
                                                    <div class="col-xl-6">
                                                        <label for="logo" class="form-label">{{ __('company.company_logo') }}</label>
                                                        @if($company->Logo_Path)
                                                            <div class="mb-2">
                                                                <img src="{{ asset('storage/' . $company->Logo_Path) }}" 
                                                                     alt="{{ __('company.current_logo') }}" 
                                                                     style="max-height: 60px;">
                                                            </div>
                                                        @endif
                                                        <input type="file"
                                                            class="form-control @error('logo') is-invalid @enderror"
                                                            id="logo" name="logo" accept="image/*">
                                                        <div class="form-text">{{ __('company.logo_upload_help') }}</div>
                                                        @error('logo')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Invoice Prefix --}}
                                                    <div class="col-xl-6">
                                                        <label for="Invoice_Prefix" class="form-label">{{ __('company.invoice_prefix') }}</label>
                                                        <input type="text"
                                                            class="form-control @error('Invoice_Prefix') is-invalid @enderror"
                                                            id="Invoice_Prefix" name="Invoice_Prefix" placeholder="INV"
                                                            maxlength="10" 
                                                            value="{{ old('Invoice_Prefix', $company->Invoice_Prefix) }}">
                                                        <div class="form-text">{{ __('company.invoice_prefix_help') }}</div>
                                                        @error('Invoice_Prefix')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Submit Actions --}}
                                    <div class="card custom-card mb-4">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="{{ route('company.show', $company->id) }}" class="btn btn-light">
                                                    <i class="ri-close-line me-1"></i> {{ __('company.cancel') }}
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="ri-save-line me-1"></i> {{ __('company.update_company') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection