@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="page-title">Add Fee Earner</div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('feeearner.store') }}">
                              @csrf
                                  <div class="row">
                                    
                                    <!-- Full Name -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control @error('Full_Name') is-invalid @enderror"
                                            name="Full_Name" placeholder="Full name" value=" " />
                                        @error('Full_Name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- User Name -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">User Name</label>
                                        <input type="text" class="form-control @error('User_Name') is-invalid @enderror"
                                            name="User_Name" placeholder="User name" value=" " />
                                        @error('User_Name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            name="email" placeholder="Email" value=" " />
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Status -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-control @error('Is_Active') is-invalid @enderror" name="Is_Active">
                                            <option value="0">Active</option>
                                            <option value="1" >Inactive</option>
                                        </select>
                                        @error('Is_Active')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Password -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            name="password" placeholder="Enter password  " />
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Confirm Password -->
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                                            name="password_confirmation" placeholder="Confirm password" />
                                        @error('password_confirmation')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="col-md-12 mt-3">
                                        <button type="submit" class="btn btn-primary">Add</button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
