<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="dark" data-toggled="close">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Company Setup - Fast Ledger</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('admin/assets/images/brand-logos/favicon.ico') }}" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('admin/assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Style CSS -->
    <link href="{{ asset('admin/assets/css/styles.css') }}" rel="stylesheet">

    <!-- Icons CSS -->
    <link href="{{ asset('admin/assets/css/icons.css') }}" rel="stylesheet">
</head>

<body style="background: #F2F2F2;">
    <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
        <div class="col-xxl-5 col-xl-6 col-lg-7 col-md-8 col-sm-10 col-12">
            
            {{-- Logo Section --}}
            <div class="my-5 d-flex justify-content-center">
                <a href="/">
                    <img src="{{ asset('admin/assets/images/brand-logos/desktop-dark.png') }}" alt="logo" class="desktop-dark">
                </a>
            </div>

            {{-- Choice Card --}}
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title text-center w-100">
                        🏢 Company Setup
                    </div>
                </div>
                <div class="card-body p-4">
                    <p class="text-center text-muted fs-14 mb-4">
                        Ready to set up your company profile?
                    </p>

                    <div class="row g-3">
                        {{-- Setup Now Option --}}
                        <div class="col-12">
                            <div class="card border border-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <span class="avatar avatar-md bg-primary-transparent rounded-circle">
                                                <i class="ri-building-line fs-18"></i>
                                            </span>
                                        </div>
                                        <div class="flex-fill">
                                            <h6 class="fw-semibold mb-2">Setup Now</h6>
                                            <ul class="list-unstyled text-muted fs-12 mb-3">
                                                <li class="mb-1">
                                                    <i class="ri-check-line text-success me-1"></i>
                                                    Takes only 2 minutes
                                                </li>
                                                <li class="mb-1">
                                                    <i class="ri-check-line text-success me-1"></i>
                                                    Start creating invoices immediately
                                                </li>
                                                <li class="mb-1">
                                                    <i class="ri-check-line text-success me-1"></i>
                                                    Complete company management
                                                </li>
                                            </ul>
                                            <a href="{{ route('company.setup.create') }}" 
                                               class="btn btn-primary w-100">
                                                <i class="ri-arrow-right-line me-1"></i>
                                                Setup Company Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Skip Option --}}
                        <div class="col-12">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <div class="me-3">
                                            <span class="avatar avatar-md bg-light rounded-circle">
                                                <i class="ri-time-line fs-18"></i>
                                            </span>
                                        </div>
                                        <div class="flex-fill">
                                            <h6 class="fw-semibold mb-2">Skip for Now</h6>
                                            <p class="text-muted fs-12 mb-3">
                                                You can set up your company later from the dashboard. 
                                                A reminder will be shown until you complete the setup.
                                            </p>
                                            <form action="{{ route('company.setup.skip') }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-light w-100">
                                                    <i class="ri-skip-forward-line me-1"></i>
                                                    I'll Do This Later
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Help Text --}}
                    <div class="text-center mt-3">
                        <p class="text-muted fs-12 mb-0">
                            Need help? <a href="#" class="text-primary">Contact Support</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="{{ asset('admin/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>