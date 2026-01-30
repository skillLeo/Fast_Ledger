@extends('admin.layout.app')

@section('content')


{{-- @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the following:</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif --}}



    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class=" custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center my-2 justify-content-between">
                            <div class="page-title">Complete Form </div>
                            <div class="prism-toggle">
                                <a href="{{ url()->previous() }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>
                        <div class="">
                            <form id="fileForm" method="POST" action="{{ route('clients.store') }}">
                                @csrf

                                

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="card-title h5">BASIC INFORMATION</div>
                                            </div>
                                            <div class="card-body">
                                                {{-- ===== BASIC INFORMATION ===== --}}
                                                <div class="row g-3">

                                                    {{-- Company Name --}}
                                                    <div class="col-12 col-md-6 col-xl-6">
                                                        <label class="form-label mb-1">Company Name *</label>
                                                        <input type="text" name="Company_Name"
                                                            class="form-control @error('Company_Name') is-invalid @enderror"
                                                            value="{{ old('Company_Name') }}" required>
                                                        @error('Company_Name')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Trade Name --}}
                                                    <div class="col-12 col-md-6 col-xl-6">
                                                        <label class="form-label mb-1">Trade Name</label>
                                                        <input type="text" name="Trade_Name"
                                                            class="form-control @error('Trade_Name') is-invalid @enderror"
                                                            value="{{ old('Trade_Name') }}">
                                                        @error('Trade_Name')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Country --}}
                                                    <div class="col-12 col-md-6 col-xl-6">
                                                        <label class="form-label mb-1">Country *</label>
                                                        <select name="Country" id="Country"
                                                            class="form-select @error('Country') is-invalid @enderror"
                                                            onchange="handleCountryChange(this.value)" required>
                                                            <option value="">Select Country</option>
                                                            @foreach ($countries as $code => $name)
                                                                                                                <option value="{{ $code }}" {{ old('Country') == $code ? 'selected'
                                                                : '' }}>
                                                                                                                    {{ $name }}
                                                                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('Country')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Company Type UK --}}
                                                    <div class="col-12 col-md-6 col-xl-6" id="company_type_uk_container"
                                                        style="display:{{ old('Country') == 'GB' ? 'block' : 'none' }};">
                                                        <label class="form-label mb-1">Company Type *</label>
                                                        <select name="Company_Type_UK" id="Company_Type_UK"
                                                            class="form-select @error('Company_Type_UK') is-invalid @enderror"
                                                            {{ old('Country') == 'GB' ? 'required' : '' }}>
                                                            <option value="">Select Type</option>
                                                            @foreach ($companyTypesUK as $v => $l)
                                                                                                                <option value="{{ $v }}" {{ old('Company_Type_UK') == $v ? 'selected'
                                                                : '' }}>
                                                                                                                    {{ $l }}
                                                                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('Company_Type_UK')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Company Type ES --}}
                                                    <div class="col-12 col-md-6 col-xl-6" id="company_type_es_container"
                                                        style="display:{{ old('Country') == 'ES' ? 'block' : 'none' }};">
                                                        <label class="form-label mb-1">Tipo de Empresa *</label>
                                                        <select name="Company_Type_ES" id="Company_Type_ES"
                                                            class="form-select @error('Company_Type_ES') is-invalid @enderror"
                                                            {{ old('Country') == 'ES' ? 'required' : '' }}>
                                                            <option value="">Seleccionar</option>
                                                            @foreach ($companyTypesES as $v => $l)
                                                                                                                <option value="{{ $v }}" {{ old('Company_Type_ES') == $v ? 'selected'
                                                                : '' }}>
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
                                                        <input type="text" name="Tax_ID"
                                                            class="form-control @error('Tax_ID') is-invalid @enderror"
                                                            value="{{ old('Tax_ID') }}" required>
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
                                                                                                                <option value="{{ $code }}" {{ old('Country_Tax_Residence') == $code
                                                                ? 'selected' : '' }}>
                                                                                                                    {{ $name }}
                                                                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('Country_Tax_Residence')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Tax Regime --}}
                                                    <div class="col-12 col-md-6 col-xl-6" id="tax_regime_container"
                                                        style="display:{{ old('Country') == 'ES' ? 'block' : 'none' }};">
                                                        <label class="form-label mb-1">Tax Regime</label>
                                                        <select name="Tax_Regime" id="Tax_Regime"
                                                            class="form-select @error('Tax_Regime') is-invalid @enderror">
                                                            <option value="">Seleccionar</option>
                                                            @foreach ($taxRegimes as $v => $l)
                                                                <option value="{{ $v }}" {{ old('Tax_Regime') == $v ? 'selected' : ''
                                                                        }}>
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

                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <div class="card-title h5">Address Information</div>
                                            </div>

                                            {{-- ===== ADDRESS INFORMATION ===== --}}

                                            <div class="card-body">
                                                <div class="row g-3">

                                                    {{-- Street Address --}}
                                                    <div class="col-12">
                                                        <label class="form-label mb-1">Street Address *</label>
                                                        <input type="text" name="Street_Address"
                                                            class="form-control @error('Street_Address') is-invalid @enderror"
                                                            value="{{ old('Street_Address') }}" required>
                                                        @error('Street_Address')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- City --}}
                                                    <div class="col-md-6">
                                                        <label class="form-label mb-1">City *</label>
                                                        <input type="text" name="City"
                                                            class="form-control @error('City') is-invalid @enderror"
                                                            value="{{ old('City') }}" required>
                                                        @error('City')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- State --}}
                                                    <div class="col-md-6">
                                                        <label class="form-label mb-1">State/Province</label>
                                                        <input type="text" name="State"
                                                            class="form-control @error('State') is-invalid @enderror"
                                                            value="{{ old('State') }}">
                                                        @error('State')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Postal Code --}}
                                                    <div class="col-md-6">
                                                        <label class="form-label mb-1">Postal Code *</label>
                                                        <input type="text" name="Postal_Code"
                                                            class="form-control @error('Postal_Code') is-invalid @enderror"
                                                            value="{{ old('Postal_Code') }}" required>
                                                        @error('Postal_Code')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                </div>




                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title h5">Business Details</div>
                                    </div>
                                    <div class="card-body">

                                        <!-- Row: Client Ref# and Contact Name -->
                                <div class="row mb-3">
                                    <!-- Client Ref# -->
                                    <div class="col-md-6">
                                        <label class="form-label">Client Ref# *</label>
                                        <input type="text" class="form-control @error('Client_Ref') is-invalid @enderror"
                                            name="Client_Ref" placeholder="Client Reference Number"
                                            value="{{ old('Client_Ref') }}" />
                                        @error('Client_Ref')
                                        <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <!-- Contact Name -->
                                    <div class="col-md-6">
                                        <label class="form-label">Contact Name *</label>
                                        <input type="text" class="form-control @error('Contact_Name') is-invalid @enderror"
                                            name="Contact_Name" placeholder="Contact Name"
                                            value="{{ old('Contact_Name') }}" />
                                        @error('Contact_Name')
                                        <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    
                                </div> 


                                        <!-- Row: Business Name and Company Reg No -->
                                        <div class="row mb-3">



                                            <div class="col-md-6">
                                                <label class="form-label mb-1">Owner’s Full Legal Name*</label>
                                                <input type="text" name="owner_Name" placeholder="Owner’s Full Legal Name"
                                                    class="form-control @error('owner_Name') is-invalid @enderror"
                                                    value="{{ old('owner_Name') }}">
                                                @error('owner_Name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>


                                            <!-- Business Name -->
                                            <div class="col-md-6">
                                                <label class="form-label">Business Name *</label>
                                                <input type="text"
                                                    class="form-control @error('Business_Name') is-invalid @enderror"
                                                    name="Business_Name" placeholder="Business Name"
                                                    value="{{ old('Business_Name') }}" />
                                                @error('Business_Name')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>


                                            {{-- Company Type UK --}}
                                            <div class="col-md-6 col-xl-6" id="company_type_uk_container"
                                                style="display:{{ old('Country') == 'GB' ? 'block' : 'none' }};">
                                                <label class="form-label mb-1">Company Type *</label>
                                                <select name="Company_Type_UK" id="Company_Type_UK"
                                                    class="form-select @error('Company_Type_UK') is-invalid @enderror" {{
        old('Country') == 'GB' ? 'required' : '' }}>
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
                                        </div>



                                        <div class="row mb-3">
                                            <!-- Business Type Dropdown -->
                                            <div class="col-md-6">
                                                <label class="form-label">Business Type *</label>
                                                <select
                                                    class="form-control select @error('Business_Type') is-invalid @enderror"
                                                    name="Business_Type">
                                                    <option value="">Select Business Type</option>
                                                    <option value="Solicitor/Lawyer" {{ old('Business_Type') == 'Solicitor/Lawyer'
        ? 'selected' : '' }}>
                                                        Solicitor/Lawyer</option>
                                                    <option value="Limited Company" {{ old('Business_Type') == 'Limited Company'
        ? 'selected' : '' }}>Limited Company</option>
                                                    <option value="Sole Trader" {{ old('Business_Type') == 'Sole Trader' ? 'selected'
        : '' }}>Sole Trader</option>
                                                </select>
                                                @error('Business_Type')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <!-- Business Category Dropdown (Static options) -->
                                            <div class="col-md-6">
                                                <label class="form-label">Business Category *</label>
                                                <select
                                                    class="form-control @error('Business_Category') is-invalid @enderror"
                                                    name="Business_Category">
                                                    <option value="">Select Business Category</option>
                                                    <option value="1" {{ old('Business_Category') == '1' ? 'selected' : '' }}>
                                                        Retail
                                                    </option>
                                                    <option value="2" {{ old('Business_Category') == '2' ? 'selected' : '' }}>
                                                        Services
                                                    </option>
                                                    <option value="3" {{ old('Business_Category') == '3' ? 'selected' : '' }}>
                                                        Manufacturing</option>
                                                    <option value="4" {{ old('Business_Category') == '4' ? 'selected' : '' }}>
                                                        Technology</option>
                                                    <option value="5" {{ old('Business_Category') == '5' ? 'selected' : '' }}>
                                                        Finance
                                                    </option>
                                                </select>
                                                @error('Business_Category')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>



                                        <!-- Address 1 -->
                                        <div class="mb-3">
                                            <label class="form-label">Address Line 1*</label>
                                            <input type="text" name="Address1"
                                                class="form-control @error('Address1') is-invalid @enderror"
                                                value="{{ old('Address1') }}" required>
                                            @error('Address1')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Address Line 2*</label>
                                            <input type="text" name="Address2"
                                                class="form-control @error('Address2') is-invalid @enderror"
                                                value="{{ old('Address2') }}" required>
                                            @error('Address2')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>


                                        <div class="row mb-3">
                                            {{-- City --}}


                                            <div class="col-md-6">
                                                <label class="form-label">Town *</label>
                                                <input type="text" class="form-control @error('Town') is-invalid @enderror"
                                                    name="Town" placeholder="Town" value="{{ old('Town') }}" />
                                                @error('Town')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 ">
                                                <label class="form-label">Country *</label>
                                                <select class="form-control @error('Country_ID') is-invalid @enderror"
                                                    name="Country_ID">
                                                    <option value="">Select Country</option>
                                                    @foreach ($countries as $country)
                                                                                                <option value="{{ $country->Country_ID }}" {{ old('Country_ID') == $country->
                                                        Country_ID ? 'selected' : '' }}>
                                                                                                    {{ $country->Country_Name }}
                                                                                                </option>
                                                    @endforeach
                                                </select>
                                                @error('Country_ID')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>


                                        </div>
                                        <!-- Row: Town and Post Code -->
                                        <div class="row mb-3">

                                            {{-- optional ap setup --}}

                                            {{-- <div class="col-md-6">
                                                <label class="form-label">UTR (Unique Taxpayer Reference) *</label>
                                                <input type="text" class="form-control @error('Town') is-invalid @enderror"
                                                    name="Town" placeholder="Town" value="{{ old('Town') }}" />
                                                @error('Town')
                                                <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div> --}}



                                            <!-- Post Code -->
                                            <div class="col-md-6">
                                                <label class="form-label">Post Code *</label>
                                                <input type="text"
                                                    class="form-control @error('Post_Code') is-invalid @enderror"
                                                    name="Post_Code" placeholder="Post Code"
                                                    value="{{ old('Post_Code') }}" />
                                                @error('Post_Code')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Business Phone Number *</label>
                                                <input type="text" class="form-control @error('Phone') is-invalid @enderror"
                                                    name="Phone" placeholder="Phone Number" value="{{ old('Phone') }}" />
                                                @error('Phone')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>


                                        </div>
                                        <!-- Row: VAT Registration No and Fee Agreed -->
                                        <div class="row mb-3">

                                            <div class="col-md-6">
                                                <label class="form-label">Are you VAT Registered? * </label>

                                                <select name="you_vat_reg" class="form-control select" id="">
                                                    <option value="Not Registered">Not Registered</option>
                                                    <option value="Registration Applied For">Registration Applied For
                                                    </option>
                                                    <option value="Registered">Registered</option>
                                                </select>
                                                @error('you_vat_reg')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">VAT Registration No</label>
                                                <input type="text" class="form-control" name="VAT_Registration_No" value="{{ old('VAT_Registration_No') }}" />
                                                    @error('VAT_Registration_No')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>


                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">

                                                <label class="form-label">VAT Scheme * </label>
                                                <select name="vat_scheme" class="form-control select" id="">
                                                    <option value="VAT Scheme">VAT Scheme</option>
                                                    <option value="Flat Rate Scheme">Flat Rate Scheme</option>
                                                    <option value="Cash Accounting Scheme">Cash Accounting Scheme</option>
                                                    <option value="Margin Scheme">Margin Scheme</option>
                                                    <option value="Reverse Charge">Reverse Charge</option>
                                                </select>

                                                 @error('vat_scheme')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror

                                            </div>
                                        </div>
                                    </div>
                                </div>





                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title h5">Deadlines</div>
                                    </div>
                                    <div class="card-body">
                                        <!-- Row: Dead-lines -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">When did you officially start trading? *</label>
                                                <input type="date" class="form-control" name="officially_start" value="{{ old('officially_start') }}" />
                                                    @error('officially_start')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">What date do you want your books from?*</label>
                                                <input type="date" class="form-control" name="date_want_your_books"
                                                    placeholder="" />
                                                    @error('date_want_your_books')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">When is your Self Assessment tax return due?
                                                    *</label>
                                                <input type="date" class="form-control" name="date_self_assessment_tax_ret"
                                                    placeholder="" />
                                                    @error('date_self_assessment_tax_ret')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">When is your VAT return due?</label>
                                                <input type="date" class="form-control" name="vat_return_due"
                                                    placeholder="" />
                                                    @error('vat_return_due')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <!-- Row: Admin UserName and Password -->
                                <div class="card">
                                    <div class="card-header">
                                        <div class="card-title h5">Details</div>
                                    </div>
                                    <div class="card-body">
                                        <!-- Row: Email and Contact No -->
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" name="Email"
                                                    placeholder="Email Address" />
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Contact No</label>
                                                <input type="text" class="form-control" name="Contact_No"
                                                    placeholder="Contact Number" />
                                            </div>
                                        </div>
                                        <div class="row mb-3">

                                            <div class="col-md-6">
                                                <label class="form-label">Admin User Name *</label>
                                                <input type="text"
                                                    class="form-control @error('AdminUserName') is-invalid @enderror"
                                                    name="AdminUserName" placeholder="Admin User Name"
                                                    value="{{ old('AdminUserName') }}" />
                                                @error('AdminUserName')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                            <!-- Admin Password -->
                                            <div class="col-md-6">
                                                <label class="form-label">Admin Password *</label>
                                                <input type="password"
                                                    class="form-control @error('AdminPassword') is-invalid @enderror"
                                                    name="AdminPassword" placeholder="Admin Password" />
                                                @error('AdminPassword')
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>

                                              <div class="col-md-6">
                                        <div class="form-check mt-3">
                                            <input class="form-check-input" type="checkbox" name="snd_lgn_to_slctr"
       value="1" {{ old('snd_lgn_to_slctr') ? 'checked' : '' }} />
                                            <label class="form-check-label" for="sendDetails">
                                                Send login details to "Solicitors"?
                                            </label>
                                        </div>
                                        @error('snd_lgn_to_slctr')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                  


                                    <!-- Submit Button -->
                                    <div class="mb-3 text-end">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                            </form>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection