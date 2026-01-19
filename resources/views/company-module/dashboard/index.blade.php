{{-- resources/views/company-module/dashboard/index.blade.php --}}
@extends('admin.layout.app')

@section('content')
    {{-- Desktop Dashboard Content (Shows on desktop only) --}}
    <div class="main-content app-content desktop-dashboard-content">
        <div class="container-fluid">
            
            {{-- Page Header --}}
            <div class="d-md-flex d-block align-items-center justify-content-between mb-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">Company Dashboard</h4>
                    <p class="text-muted fs-13 mb-0">Welcome back! Here's what's happening with your companies.</p>
                </div>
                <div class="ms-auto pageheader-btn">
                    <a href="{{ route('company.setup.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Add New Company
                    </a>
                </div>
            </div>

            {{-- Incomplete Profile Alert --}}
            @if($hasIncompleteProfiles)
                <div class="row">
                    <div class="col-xl-12">
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-start">
                                <div class="me-2">
                                    <i class="ri-information-line fs-18"></i>
                                </div>
                                <div class="flex-fill">
                                    <strong>Complete Your Profile!</strong>
                                    <p class="mb-2">You have {{ $incompleteCompanies->count() }} company profile(s) that need completion to unlock all features.</p>
                                    @foreach($incompleteCompanies as $incomplete)
                                        <a href="{{ route('company.edit', $incomplete->id) }}" class="btn btn-sm btn-warning me-2 mb-1">
                                            <i class="ri-edit-line me-1"></i> Complete {{ $incomplete->Company_Name }}
                                        </a>
                                    @endforeach
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Statistics Cards --}}
            <div class="row">
                <div class="col-xxl-4 col-lg-4 col-md-4">
                    <div class="card custom-card overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex align-items-top justify-content-between">
                                <div>
                                    <span class="avatar avatar-md avatar-rounded bg-primary-transparent">
                                        <i class="ri-building-line fs-20"></i>
                                    </span>
                                </div>
                                <div class="flex-fill ms-3">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                        <div>
                                            <p class="text-muted mb-0">Total Companies</p>
                                            <h4 class="fw-semibold mt-1">{{ $stats['total_companies'] }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-4 col-md-4">
                    <div class="card custom-card overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex align-items-top justify-content-between">
                                <div>
                                    <span class="avatar avatar-md avatar-rounded bg-success-transparent">
                                        <i class="ri-check-line fs-20"></i>
                                    </span>
                                </div>
                                <div class="flex-fill ms-3">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                        <div>
                                            <p class="text-muted mb-0">Active Companies</p>
                                            <h4 class="fw-semibold mt-1">{{ $stats['active_companies'] }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-4 col-md-4">
                    <div class="card custom-card overflow-hidden">
                        <div class="card-body">
                            <div class="d-flex align-items-top justify-content-between">
                                <div>
                                    <span class="avatar avatar-md avatar-rounded bg-warning-transparent">
                                        <i class="ri-alert-line fs-20"></i>
                                    </span>
                                </div>
                                <div class="flex-fill ms-3">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                        <div>
                                            <p class="text-muted mb-0">Incomplete Profiles</p>
                                            <h4 class="fw-semibold mt-1">{{ $stats['incomplete_profiles'] }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Companies List --}}
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="card-title">
                                Your Companies
                            </div>
                            <a href="{{ route('company.index') }}" class="btn btn-sm btn-primary-light">
                                View All <i class="ri-arrow-right-line ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body">
                            @if($companies->isEmpty())
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="ri-building-line fs-50 text-muted op-5"></i>
                                    </div>
                                    <h5 class="fw-semibold mb-2">No Companies Yet</h5>
                                    <p class="text-muted mb-4">Get started by creating your first company profile.</p>
                                    <a href="{{ route('company.create') }}" class="btn btn-primary">
                                        <i class="ri-add-line me-1"></i> Create Your First Company
                                    </a>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table text-nowrap table-hover">
                                        <thead>
                                            <tr>
                                                <th>Company Name</th>
                                                <th>Country</th>
                                                <th>Tax ID</th>
                                                <th>Profile Completion</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($companies as $company)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($company->Logo_Path)
                                                                <span class="avatar avatar-sm me-2">
                                                                    <img src="{{ asset('storage/' . $company->Logo_Path) }}" alt="logo">
                                                                </span>
                                                            @else
                                                                <span class="avatar avatar-sm bg-primary-transparent me-2">
                                                                    <i class="ri-building-line"></i>
                                                                </span>
                                                            @endif
                                                            <div>
                                                                <span class="fw-semibold">{{ $company->Company_Name }}</span>
                                                                @if($company->Trade_Name)
                                                                    <br><small class="text-muted">{{ $company->Trade_Name }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">{{ $company->Country }}</span>
                                                    </td>
                                                    <td>{{ $company->Tax_ID }}</td>
                                                    <td>
                                                        <div class="progress progress-sm">
                                                            <div class="progress-bar bg-{{ $company->Profile_Completion_Percentage >= 80 ? 'success' : ($company->Profile_Completion_Percentage >= 50 ? 'warning' : 'danger') }}" 
                                                                 role="progressbar" 
                                                                 style="width: {{ $company->Profile_Completion_Percentage }}%">
                                                            </div>
                                                        </div>
                                                        <small class="text-muted">{{ $company->Profile_Completion_Percentage }}%</small>
                                                    </td>
                                                    <td>
                                                        @if($company->Is_Active)
                                                            <span class="badge bg-success-transparent">Active</span>
                                                        @else
                                                            <span class="badge bg-danger-transparent">Inactive</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('company.show', $company->id) }}" 
                                                           class="btn btn-sm btn-primary-light"
                                                           data-bs-toggle="tooltip" 
                                                           title="View Details">
                                                            <i class="ri-eye-line"></i>
                                                        </a>
                                                        <a href="{{ route('company.edit', $company->id) }}" 
                                                           class="btn btn-sm btn-info-light"
                                                           data-bs-toggle="tooltip" 
                                                           title="Edit">
                                                            <i class="ri-edit-line"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Mobile Split Screen (Shows on mobile ONLY on dashboard) --}}
    @include('admin.partial.mobile-split-screen')

    <style>
        /* ============================================
           CONDITIONAL DISPLAY LOGIC
           ============================================ */
        
        /* Desktop View (> 991px) */
        @media (min-width: 992px) {
            /* Show desktop content */
            .desktop-dashboard-content {
                display: block !important;
            }
            
            /* Hide mobile split screen */
            .mobile-split-screen {
                display: none !important;
            }
        }

        /* Mobile/Tablet View (≤ 991px) */
        @media (max-width: 991px) {
            /* Hide desktop content */
            .desktop-dashboard-content {
                display: none !important;
            }
            
            /* Show mobile split screen */
            .mobile-split-screen {
                display: block !important;
            }
        }
    </style>
@endsection