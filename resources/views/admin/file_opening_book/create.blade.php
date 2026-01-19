@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="page-title">Complete Form</div>
                            <div class="prism-toggle">
                                {{-- <button class="btn btn-sm btn-primary-light">Show Code<i
                                        class="ri-code-line ms-2 d-inline-block align-middle"></i></button> --}}
                            </div>
                        </div>
                        <div class="card-body">
                            <x-form method="POST" action="/files">
                                {{-- <input type="hidden" name="File_ID" value="" />
                                <input type="hidden" name="Client_ID" value="" /> --}}

                                <div class="row">
                                    <!-- Name Fields -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" value="{{ old('First_Name') }}" class="form-control @error('First_Name') is-invalid @enderror"
                                            name="First_Name" placeholder="First name" />
                                        @error('First_Name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" value="{{ old('Last_Name') }}" class="form-control @error('Last_Name') is-invalid @enderror"
                                            name="Last_Name" placeholder="Last name" />

                                        @error('Last_Name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Date and Contact Details -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">File Date (dd/mm/yyyy)</label>
                                        <input type="date" value="{{ old('File_Date') }}" class="form-control @error('File_Date') is-invalid @enderror"
                                            name="File_Date" placeholder="dd/mm/yyyy" />

                                        @error('File_Date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text"  value="{{ old('Phone') }}" class="form-control" name="Phone"
                                            placeholder="Phone number" />

                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Mobile</label>
                                        <input type="text" class="form-control" value="{{ old('Mobile') }}" name="Mobile"
                                            placeholder="Mobile number" />
                                    </div>

                                    <!-- Address Fields -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Address 1</label>
                                        <input type="text" class="form-control @error('Address1') is-invalid @enderror" value="{{ old('Address1') }}" name="Address1"
                                            placeholder="Address 1" />

                                            @error('Address1')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Address 2</label>
                                        <input type="text" class="form-control" value="{{ old('Address2') }}" name="Address2"
                                            placeholder="Address 2" />
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Town</label>
                                        <input type="text" class="form-control @error('Town') is-invalid @enderror" value="{{ old('Town') }}" name="Town" placeholder="Town" />

                                        @error('Town')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Post Code</label>
                                        <input type="text" class="form-control @error('Post_Code') is-invalid @enderror" value="{{ old('Post_Code') }}" name="Post_Code"
                                            placeholder="Post Code" />

                                            @error('Post_Code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Country</label>
                                        <select name="Country_ID" class="form-select @error('Country_ID') is-invalid @enderror">
                                            <option selected disabled>Select Country</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->Country_ID }}" {{ old('Country_ID') == $country->Country_ID ? 'selected' : '' }}>
                                                    {{ $country->Country_Name }}
                                                </option>
                                            @endforeach

                                        </select>

                                        @error('Country_ID')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>


                                    <!-- Additional Information -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Ledger Ref#</label>
                                        <input type="text" class="form-control @error('Ledger_Ref') is-invalid @enderror" value="{{ old('Ledger_Ref') }}" name="Ledger_Ref"
                                            placeholder="Ledger Reference" />

                                            @error('Ledger_Ref')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!-- Matter Dropdown -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Matter</label>
                                        <select name="Matter" id="matter" class="form-select @error('Matter') is-invalid @enderror">
                                            <option selected disabled>Select Matter</option>
                                            @foreach ($matters as $matter)
                                                <option value="{{ $matter->matter }}" data-id="{{ $matter->id }}">{{ $matter->matter }}</option>

                                            @endforeach
                                        </select>

                                        @error('Matter')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Sub Matter Dropdown -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Sub Matter</label>
                                        <select name="Sub_Matter" id="submatter" class="form-select">
                                            <option selected disabled>Select Sub Matter</option>
                                        </select>
                                    </div>
                                    <!-- Additional Contact Info -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" value="{{ old('Email') }}" name="Email" placeholder="Email" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Referral Name</label>
                                        <input type="text" class="form-control" value="{{ old('Referral_Name') }}" name="Referral_Name"
                                            placeholder="Referral Name" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Referral Fee</label>
                                        <input type="number" value="{{ old('Referral_Fee') }}" class="form-control @error('Referral_Fee') is-invalid @enderror" name="Referral_Fee"
                                            placeholder="Referral Fee" />

                                            @error('Referral_Fee')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Fee and Status Fields -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Fee Agreed</label>
                                        <input type="number" value="{{ old('Fee_Agreed') }}" class="form-control @error('Fee_Agreed') is-invalid @enderror" name="Fee_Agreed"
                                            placeholder="Fee Agreed" />

                                            @error('Fee_Agreed')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status</label>
                                        <select name="Status" class="form-select @error('Status') is-invalid @enderror">
                                            <option value="">Select Status</option>
                                            <option value="L" {{ old('Status') == 'L' ? 'selected' : '' }}>Live</option>
                                            <option value="C" {{ old('Status') == 'C' ? 'selected' : '' }}>Close</option>
                                            <option value="A" {{ old('Status') == 'A' ? 'selected' : '' }}>Abortive</option>
                                            <option value="I" {{ old('Status') == 'I' ? 'selected' : '' }}>Close Abortive</option>

                                        </select>

                                        @error('Status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Missing Fields -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">NIC No</label>
                                        <input type="text" class="form-control" value="{{ old('NIC_No') }}" name="NIC_No"
                                            placeholder="NIC No" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" value="{{ old('Date_Of_Birth') }}" name="Date_Of_Birth"
                                            placeholder="Date of Birth" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Key Date</label>
                                        <input type="date" class="form-control" value="{{ old('Key_Date') }}" name="Key_Date"
                                            placeholder="Key Date" />
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Special Note</label>
                                        <textarea class="form-control" name="Special_Note" placeholder="Special Note">{{ old('Special_Note') }}</textarea>
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </div>
                            </x-form>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#matter').on('change', function() {
            // var matterId = $(this).val();
            var matterId = $('#matter option:selected').data('id');


            if (matterId) {
                $.ajax({
                    url: '/matters/' + matterId + '/submatters',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#submatter').empty().append(
                            '<option selected disabled>Select Sub Matter</option>');
                        $.each(data, function(index, submatter) {
                            $('#submatter').append('<option value="' + submatter
                                .submatter + '">' + submatter.submatter + '</option>');
                        });
                    }
                });
            } else {
                $('#submatter').empty().append('<option selected disabled>Select Sub Matter</option>');
            }
        });
    });
</script>
@endsection