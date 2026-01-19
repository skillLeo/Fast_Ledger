@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container mt-4">
            <div class="card shadow-sm rounded-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0" style="color: #1b598c;">Add/Edit Bank Account</h5>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('banks.store') }}">
                        @csrf

                        <!-- Client Name (readonly) -->
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">Client</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" value="{{ $user->Full_Name }}" readonly />
                                <input type="hidden" name="Client_ID" value="{{ $user->Client_ID }}" />
                            </div>
                        </div>

                        <!-- Bank Type -->
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">Bank Type</label>
                            <div class="col-sm-2">
                                <select name="Bank_Type_ID" class="form-select" required>
                                    <option value="">Select Type</option>
                                    @foreach ($bankTypes as $type)
                                        <option value="{{ $type->Bank_Type_ID }}">{{ $type->Bank_Type }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Bank Name -->
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">Bank Name</label>
                            <div class="col-sm-2">
                                <input type="text" name="Bank_Name" class="form-control" placeholder="Bank Name"
                                    required />
                            </div>
                        </div>

                        <!-- Account Name -->
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">Account Name</label>
                            <div class="col-sm-2">
                                <input type="text" name="Account_Name" class="form-control" placeholder="Account Name"
                                    required />
                            </div>
                        </div>

                        <!-- Account No -->
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">Account No</label>
                            <div class="col-sm-2">
                                <input type="text" name="Account_No" class="form-control" placeholder="Account Number"
                                    required />
                            </div>
                        </div>

                        <!-- Sort Code -->
                        <div class="mb-3 row">
                            <label class="col-sm-2 col-form-label">Sort Code</label>
                            <div class="col-sm-2">
                                <input type="text" name="Sort_Code" class="form-control" placeholder="Sort Code"
                                    required />
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="text-start">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('admin.users.banks', $user->User_ID) }}" class="btn"  style="background-color: #4bb6e0; color: #fff; border: none;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
