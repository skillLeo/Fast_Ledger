@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header my-2 justify-content-between">
                            <div class="page-title">Complete Form </div>
                            <div class="prism-toggle">
                                <a href="{{ url()->previous() }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-arrow-left me-1"></i> Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="fileForm" method="POST" action="{{ route('clients.store') }}">
                                @csrf

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
                                        <input type="text"
                                            class="form-control @error('Contact_Name') is-invalid @enderror"
                                            name="Contact_Name" placeholder="Contact Name"
                                            value="{{ old('Contact_Name') }}" />
                                        @error('Contact_Name')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Row: Business Name and Company Reg No -->
                                <div class="row mb-3">
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
                                    <div class="col-md-6">
                                        <label class="form-label">Company Reg No</label>
                                        <input type="text" class="form-control" name="Company_Reg_No"
                                            placeholder="Company Registration Number" />
                                    </div>
                                </div>

                                <!-- Row: Business Name and Company Reg No -->
                                {{-- <div class="row mb-3">
    <!-- Business Type Dropdown -->
    <div class="col-md-6">
        <label class="form-label">Business Type *</label>
        <select class="form-control @error('Business_Type') is-invalid @enderror" name="Business_Type">
            <option value="">Select Business Type</option>
            <option value="Solicitor/Lawyer" {{ old('Business_Type') == 'Solicitor/Lawyer' ? 'selected' : '' }}>Solicitor/Lawyer</option>
            <option value="Limited Company" {{ old('Business_Type') == 'Limited Company' ? 'selected' : '' }}>Limited Company</option>
            <option value="Sole Trader" {{ old('Business_Type') == 'Sole Trader' ? 'selected' : '' }}>Sole Trader</option>
        </select>
        @error('Business_Type')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <!-- Business Category Dropdown -->
    <div class="col-md-6">
        <label class="form-label">Business Category *</label>
        <select class="form-control @error('Business_Category') is-invalid @enderror" name="Business_Category">
            <option value="">Select Business Category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ old('Business_Category') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('Business_Category')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
                                </div> --}}

                                <div class="row mb-3">
    <!-- Business Type Dropdown -->
    <div class="col-md-6">
        <label class="form-label">Business Type *</label>
        <select class="form-control select @error('Business_Type') is-invalid @enderror" name="Business_Type">
            <option value="">Select Business Type</option>
            <option value="Solicitor/Lawyer" {{ old('Business_Type') == 'Solicitor/Lawyer' ? 'selected' : '' }}>Solicitor/Lawyer</option>
            <option value="Limited Company" {{ old('Business_Type') == 'Limited Company' ? 'selected' : '' }}>Limited Company</option>
            <option value="Sole Trader" {{ old('Business_Type') == 'Sole Trader' ? 'selected' : '' }}>Sole Trader</option>
        </select>
        @error('Business_Type')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <!-- Business Category Dropdown (Static options) -->
    <div class="col-md-6">
        <label class="form-label">Business Category *</label>
        <select class="form-control @error('Business_Category') is-invalid @enderror" name="Business_Category">
            <option value="">Select Business Category</option>
            <option value="1" {{ old('Business_Category') == '1' ? 'selected' : '' }}>Retail</option>
            <option value="2" {{ old('Business_Category') == '2' ? 'selected' : '' }}>Services</option>
            <option value="3" {{ old('Business_Category') == '3' ? 'selected' : '' }}>Manufacturing</option>
            <option value="4" {{ old('Business_Category') == '4' ? 'selected' : '' }}>Technology</option>
            <option value="5" {{ old('Business_Category') == '5' ? 'selected' : '' }}>Finance</option>
        </select>
        @error('Business_Category')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
</div>


                                <!-- Address 1 -->
                                <div class="mb-3">
                                    <label class="form-label">Address Line 1 *</label>
                                    <input type="text" class="form-control @error('Address1') is-invalid @enderror"
                                        name="Address1" placeholder="Address Line 1" value="{{ old('Address1') }}" />
                                    @error('Address1')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <!-- Address 2 -->
                                <div class="mb-3">
                                    <label class="form-label">Address Line 2</label>
                                    <input type="text" class="form-control" name="Address2"
                                        placeholder="Address Line 2" />
                                </div>

                                <!-- Row: Town and Post Code -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Town *</label>
                                        <input type="text" class="form-control @error('Town') is-invalid @enderror"
                                            name="Town" placeholder="Town" value="{{ old('Town') }}" />
                                        @error('Town')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <!-- Post Code -->
                                    <div class="col-md-6">
                                        <label class="form-label">Post Code *</label>
                                        <input type="text" class="form-control @error('Post_Code') is-invalid @enderror"
                                            name="Post_Code" placeholder="Post Code" value="{{ old('Post_Code') }}" />
                                        @error('Post_Code')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>


                                <!-- Row: Country and Phone -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Country *</label>
                                        <select class="form-control @error('Country_ID') is-invalid @enderror"
                                            name="Country_ID">
                                            <option value="">Select Country</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->Country_ID }}"
                                                    {{ old('Country_ID') == $country->Country_ID ? 'selected' : '' }}>
                                                    {{ $country->Country_Name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('Country_ID')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <!-- Phone -->
                                    <div class="col-md-6">
                                        <label class="form-label">Phone *</label>
                                        <input type="text" class="form-control @error('Phone') is-invalid @enderror"
                                            name="Phone" placeholder="Phone Number" value="{{ old('Phone') }}" />
                                        @error('Phone')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Row: Mobile and Fax -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Mobile</label>
                                        <input type="text" class="form-control" name="Mobile"
                                            placeholder="Mobile Number" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Fax</label>
                                        <input type="text" class="form-control" name="Fax"
                                            placeholder="Fax Number" />
                                    </div>
                                </div>

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

                                <!-- Row: VAT Registration No and Fee Agreed -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">VAT Registration No</label>
                                        <input type="text" class="form-control" name="VAT_Registration_No"
                                            placeholder="VAT Registration Number" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Fee Agreed</label>
                                        <input type="number" step="0.01" class="form-control" name="Fee_Agreed"
                                            placeholder="Fee Agreed" />
                                    </div>
                                </div>

                                <!-- Row: Admin UserName and Password -->
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
                                </div>

                                <div class="row mb-3">
                                   <div class="col-md-6"> 
    <div class="form-check">
        <input class="form-check-input"
               type="checkbox" 
               name="snd_lgn_to_slctr" 
               value="true" 
               {{ old('snd_lgn_to_slctr') ? 'checked' : '' }} />
        <label class="form-check-label" for="sendDetails">
            Send login details to "Solicitors"?
        </label>
    </div>
    @error('snd_lgn_to_slctr')
        <small class="text-danger">{{ $message }}</small>
    @enderror
                                    </div>

                                    <div class="col-md-6">
                                        {{-- <label class="form-label"></label> --}}
                                        <input type="hidden"
       {{-- class="form-control"
       name="agnt_admin_id"
       value="{{ auth()->check() ? auth()->user()->id : '' }}" /> --}}
                                        {{-- @error('AdminPassword')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror --}}
                                    </div>

                                </div>

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
