@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.partial.errors')

            {{-- If user has NO companies --}}
            @if($companies->isEmpty())
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card custom-card">
                            <span class="page-title">Welcome to Company Module</span>
                            <div class="card-body">
                                
                                {{-- Welcome Message --}}
                                <div class="text-center my-5">
                                    <div class="mb-4">
                                        <span class="avatar avatar-xxl bg-primary-transparent rounded-circle">
                                            <i class="ri-building-line fs-1 text-primary"></i>
                                        </span>
                                    </div>
                                    <h3 class="fw-semibold mb-2">Welcome to Company Module! 🎉</h3>
                                    <p class="text-muted fs-15">
                                        Get started by creating your first company profile.
                                    </p>
                                </div>

                                {{-- Info Card --}}
                                <div class="row justify-content-center">
                                    <div class="col-xl-8">
                                        <div class="text-center py-4">
                                            <h5 class="fw-semibold mb-3">You Don't Have Any Companies Yet</h5>
                                            <p class="text-muted mb-4">
                                                Create your company profile to start managing invoices, customers, and quotations.
                                                It only takes 2 minutes!
                                            </p>
                                            
                                            <div class="row g-3 justify-content-center mb-4">
                                                <div class="col-md-4">
                                                    <div class="p-3">
                                                        <i class="ri-file-list-3-line fs-30 text-primary mb-2"></i>
                                                        <h6 class="fw-semibold">Manage Invoices</h6>
                                                        <small class="text-muted">Create and track invoices</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3">
                                                        <i class="ri-team-line fs-30 text-success mb-2"></i>
                                                        <h6 class="fw-semibold">Track Customers</h6>
                                                        <small class="text-muted">Manage customer database</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="p-3">
                                                        <i class="ri-file-chart-line fs-30 text-warning mb-2"></i>
                                                        <h6 class="fw-semibold">Generate Reports</h6>
                                                        <small class="text-muted">Financial reports & analytics</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <a href="{{ route('company.setup.choice') }}" class="btn btn-primary btn-lg px-5">
                                                <i class="ri-add-line me-2"></i> Create Your First Company
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                {{-- Help Section --}}
                                <div class="text-center mt-4">
                                    <p class="text-muted fs-13 mb-0">
                                        Need help getting started? 
                                        <a href="#" class="text-primary">Contact Support</a>
                                    </p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            
            {{-- If user HAS companies --}}
            @else
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card custom-card">
                             {{-- Page Header --}}<span class="page-title">Select Company</span>
                                <div class="d-md-flex d-block align-items-center justify-content-between mb-1">

                                    <div>
                                        <p class="text-muted fs-13 mb-0">Choose which company you want to work in</p>
                                    </div>
                                    <div class="ms-auto">
                                        <a href="{{ route('company.create') }}" class="btn btn-primary">
                                            <i class="ri-add-line me-1"></i> Create New Company
                                        </a>
                                    </div>
                                </div>
                            
                            <div class="card-body">

                                <div class="row">
                                    @foreach($companies as $company)
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                            <div class="card custom-card company-selection-card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-start mb-3">
                                                        @if($company->Logo_Path)
                                                            <span class="avatar avatar-lg me-3">
                                                                <img src="{{ asset('storage/' . $company->Logo_Path) }}" alt="logo">
                                                            </span>
                                                        @else
                                                            <span class="avatar avatar-lg bg-primary-transparent me-3">
                                                                <i class="ri-building-line fs-20"></i>
                                                            </span>
                                                        @endif
                                                        <div class="flex-fill">
                                                            <h5 class="fw-semibold mb-1">{{ $company->Company_Name }}</h5>
                                                            @if($company->Trade_Name)
                                                                <p class="text-muted fs-12 mb-1">{{ $company->Trade_Name }}</p>
                                                            @endif
                                                            <span class="badge bg-light text-dark">
                                                                {{ $company->Country }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <small class="text-muted d-block mb-1">
                                                            <i class="ri-fingerprint-line me-1"></i>
                                                            Tax ID: {{ $company->Tax_ID }}
                                                        </small>
                                                        <small class="text-muted d-block mb-1">
                                                            <i class="ri-shield-user-line me-1"></i>
                                                            Your Role: 
                                                            <strong class="text-capitalize">{{ $company->pivot->Role }}</strong>
                                                        </small>
                                                        @if($company->Email)
                                                            <small class="text-muted d-block">
                                                                <i class="ri-mail-line me-1"></i>
                                                                {{ $company->Email }}
                                                            </small>
                                                        @endif
                                                    </div>

                                                    <div class="mb-3">
                                                        <div class="d-flex justify-content-between mb-1">
                                                            <small>Profile Completion</small>
                                                            <small>{{ $company->Profile_Completion_Percentage }}%</small>
                                                        </div>
                                                        <div class="progress progress-sm">
                                                            <div class="progress-bar bg-{{ $company->Profile_Completion_Percentage >= 80 ? 'success' : ($company->Profile_Completion_Percentage >= 50 ? 'warning' : 'danger') }}" 
                                                                 role="progressbar" 
                                                                 style="width: {{ $company->Profile_Completion_Percentage }}%">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <form action="{{ route('company.set-current', $company->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary w-100">
                                                            <i class="ri-login-circle-line me-1"></i>
                                                            Work in This Company
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
<style>
.company-selection-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.company-selection-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>
@endsection