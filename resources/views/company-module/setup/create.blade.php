{{-- resources/views/company-module/setup/create.blade.php --}}
@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h4 class="page-title">Create Company</h4>
                        </div>

                        {{-- ✅ GLOBAL ERROR DISPLAY --}}
                         @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                                <strong>⚠️ Please fix the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('company.setup.store') }}" enctype="multipart/form-data">
                            @csrf

                            {{-- ================= ROW 1 : TWO CARDS ================= --}}
                            <div class="row g-3 mt-1">

                                {{-- ===== BASIC INFORMATION ===== --}}
                                <div class="col-12 col-xl-6">
                                    <div class="h-100">
                                        <div class="card-header">
                                            <div class="card-title">Basic Information</div>
                                        </div>

                                        <div class="card-body">
                                            <div class="row g-3">

                                                {{-- Company Name --}}
                                                <div class="col-12 col-md-6 col-xl-6">
                                                    <label class="form-label mb-1">Company Name *</label>
                                                    <input type="text" 
                                                           name="Company_Name" 
                                                           class="form-control @error('Company_Name') is-invalid @enderror" 
                                                           value="{{ old('Company_Name') }}" 
                                                           required>
                                                    @error('Company_Name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Trade Name --}}
                                                <div class="col-12 col-md-6 col-xl-6">
                                                    <label class="form-label mb-1">Trade Name</label>
                                                    <input type="text" 
                                                           name="Trade_Name" 
                                                           class="form-control @error('Trade_Name') is-invalid @enderror" 
                                                           value="{{ old('Trade_Name') }}">
                                                    @error('Trade_Name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Country --}}
                                                <div class="col-12 col-md-6 col-xl-6">
                                                    <label class="form-label mb-1">Country *</label>
                                                    <select name="Country" 
                                                            id="Country" 
                                                            class="form-select @error('Country') is-invalid @enderror"
                                                            onchange="handleCountryChange(this.value)" 
                                                            required>
                                                        <option value="">Select Country</option>
                                                        @foreach ($countries as $code => $name)
                                                            <option value="{{ $code }}" {{ old('Country') == $code ? 'selected' : '' }}>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('Country')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Company Type UK --}}
                                                <div class="col-12 col-md-6 col-xl-6" 
                                                     id="company_type_uk_container"
                                                     style="display:{{ old('Country') == 'GB' ? 'block' : 'none' }};">
                                                    <label class="form-label mb-1">Company Type *</label>
                                                    <select name="Company_Type_UK" 
                                                            id="Company_Type_UK" 
                                                            class="form-select @error('Company_Type_UK') is-invalid @enderror"
                                                            {{ old('Country') == 'GB' ? 'required' : '' }}>
                                                        <option value="">Select Type</option>
                                                        @foreach ($companyTypesUK as $v => $l)
                                                            <option value="{{ $v }}" {{ old('Company_Type_UK') == $v ? 'selected' : '' }}>
                                                                {{ $l }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('Company_Type_UK')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Company Type ES --}}
                                                <div class="col-12 col-md-6 col-xl-6" 
                                                     id="company_type_es_container"
                                                     style="display:{{ old('Country') == 'ES' ? 'block' : 'none' }};">
                                                    <label class="form-label mb-1">Tipo de Empresa *</label>
                                                    <select name="Company_Type_ES" 
                                                            id="Company_Type_ES" 
                                                            class="form-select @error('Company_Type_ES') is-invalid @enderror"
                                                            {{ old('Country') == 'ES' ? 'required' : '' }}>
                                                        <option value="">Seleccionar</option>
                                                        @foreach ($companyTypesES as $v => $l)
                                                            <option value="{{ $v }}" {{ old('Company_Type_ES') == $v ? 'selected' : '' }}>
                                                                {{ $l }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('Company_Type_ES')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Tax ID --}}
                                                <div class="col-12 col-md-6 col-xl-6">
                                                    <label class="form-label mb-1">Tax ID *</label>
                                                    <input type="text" 
                                                           name="Tax_ID" 
                                                           class="form-control @error('Tax_ID') is-invalid @enderror" 
                                                           value="{{ old('Tax_ID') }}" 
                                                           required>
                                                    @error('Tax_ID')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Tax Residence --}}
                                                <div class="col-12 col-md-6 col-xl-6">
                                                    <label class="form-label mb-1">Tax Residence *</label>
                                                    <select name="Country_Tax_Residence" 
                                                            class="form-select @error('Country_Tax_Residence') is-invalid @enderror" 
                                                            required>
                                                        <option value="">Select Country</option>
                                                        @foreach ($countries as $code => $name)
                                                            <option value="{{ $code }}" {{ old('Country_Tax_Residence') == $code ? 'selected' : '' }}>
                                                                {{ $name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('Country_Tax_Residence')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Tax Regime --}}
                                                <div class="col-12 col-md-6 col-xl-6" 
                                                     id="tax_regime_container"
                                                     style="display:{{ old('Country') == 'ES' ? 'block' : 'none' }};">
                                                    <label class="form-label mb-1">Tax Regime</label>
                                                    <select name="Tax_Regime" 
                                                            id="Tax_Regime" 
                                                            class="form-select @error('Tax_Regime') is-invalid @enderror">
                                                        <option value="">Seleccionar</option>
                                                        @foreach ($taxRegimes as $v => $l)
                                                            <option value="{{ $v }}" {{ old('Tax_Regime') == $v ? 'selected' : '' }}>
                                                                {{ $l }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('Tax_Regime')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ===== ADDRESS INFORMATION ===== --}}
                                <div class="col-12 col-xl-6">
                                    <div class="h-100">
                                        <div class="card-header">
                                            <div class="card-title">Address Information</div>
                                        </div>

                                        <div class="card-body">
                                            <div class="row g-3">

                                                {{-- Street Address --}}
                                                <div class="col-12">
                                                    <label class="form-label mb-1">Street Address *</label>
                                                    <input type="text" 
                                                           name="Street_Address" 
                                                           class="form-control @error('Street_Address') is-invalid @enderror"
                                                           value="{{ old('Street_Address') }}" 
                                                           required>
                                                    @error('Street_Address')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- City --}}
                                                <div class="col-12 col-md-4">
                                                    <label class="form-label mb-1">City *</label>
                                                    <input type="text" 
                                                           name="City" 
                                                           class="form-control @error('City') is-invalid @enderror" 
                                                           value="{{ old('City') }}" 
                                                           required>
                                                    @error('City')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- State --}}
                                                <div class="col-12 col-md-4">
                                                    <label class="form-label mb-1">State/Province</label>
                                                    <input type="text" 
                                                           name="State" 
                                                           class="form-control @error('State') is-invalid @enderror" 
                                                           value="{{ old('State') }}">
                                                    @error('State')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Postal Code --}}
                                                <div class="col-12 col-md-4">
                                                    <label class="form-label mb-1">Postal Code *</label>
                                                    <input type="text" 
                                                           name="Postal_Code" 
                                                           class="form-control @error('Postal_Code') is-invalid @enderror" 
                                                           value="{{ old('Postal_Code') }}" 
                                                           required>
                                                    @error('Postal_Code')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- ===== OPTIONAL DETAILS ===== --}}
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card-header" data-bs-toggle="collapse"
                                        data-bs-target="#optionalDetails" style="cursor:pointer;">
                                        <div class="card-title">Additional Details (Optional)</div>
                                    </div>

                                    <div class="collapse show" id="optionalDetails">
                                        <div class="card-body">
                                            <div class="row g-3">

                                                {{-- Phone --}}
                                                <div class="col-12 col-md-6 col-xl-3">
                                                    <label class="form-label mb-1">Phone</label>
                                                    <input type="text" 
                                                           name="Phone_Number" 
                                                           class="form-control @error('Phone_Number') is-invalid @enderror" 
                                                           value="{{ old('Phone_Number') }}">
                                                    @error('Phone_Number')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Email --}}
                                                <div class="col-12 col-md-6 col-xl-3">
                                                    <label class="form-label mb-1">Email</label>
                                                    <input type="email" 
                                                           name="Email" 
                                                           class="form-control @error('Email') is-invalid @enderror" 
                                                           value="{{ old('Email') }}">
                                                    @error('Email')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Website --}}
                                                <div class="col-12 col-md-6 col-xl-3">
                                                    <label class="form-label mb-1">Website</label>
                                                    <input type="url" 
                                                           name="Website" 
                                                           class="form-control @error('Website') is-invalid @enderror" 
                                                           value="{{ old('Website') }}">
                                                    @error('Website')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Logo --}}
                                                <div class="col-12 col-md-6 col-xl-3">
                                                    <label class="form-label mb-1">Company Logo</label>
                                                    <input type="file" 
                                                           name="logo" 
                                                           class="form-control @error('logo') is-invalid @enderror" 
                                                           accept="image/jpeg,image/png,image/jpg,image/gif">
                                                    @error('logo')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <small class="text-muted">Max 2MB. Formats: JPG, PNG, GIF</small>
                                                </div>

                                                {{-- Invoice Prefix --}}
                                                <div class="col-12 col-md-6 col-xl-3">
                                                    <label class="form-label mb-1">Invoice Prefix</label>
                                                    <input type="text" 
                                                           name="Invoice_Prefix" 
                                                           class="form-control @error('Invoice_Prefix') is-invalid @enderror" 
                                                           value="{{ old('Invoice_Prefix') }}" 
                                                           placeholder="e.g., INV-">
                                                    @error('Invoice_Prefix')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ===== ACTIONS ===== --}}
                            <div class="row mt-3 mb-3">
                                <div class="col-12 text-end">
                                    <a href="{{ route('company.setup.choice') }}" class="btn btn-light">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i> Create Company
                                    </button>
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
        function handleCountryChange(country) {
            const uk = document.getElementById('company_type_uk_container');
            const es = document.getElementById('company_type_es_container');
            const tax = document.getElementById('tax_regime_container');

            const ukSelect = document.getElementById('Company_Type_UK');
            const esSelect = document.getElementById('Company_Type_ES');

            uk.style.display = 'none';
            es.style.display = 'none';
            tax.style.display = 'none';

            ukSelect.removeAttribute('required');
            esSelect.removeAttribute('required');

            if (country === 'GB') {
                uk.style.display = 'block';
                ukSelect.setAttribute('required', 'required');
            }

            if (country === 'ES') {
                es.style.display = 'block';
                tax.style.display = 'block';
                esSelect.setAttribute('required', 'required');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const country = document.getElementById('Country').value;
            if (country) {
                handleCountryChange(country);
            }
        });
    </script>
@endsection