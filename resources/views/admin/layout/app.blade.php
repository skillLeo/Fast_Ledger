
{{-- resources/views/admin/layout/app.blade.php --}}
<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="transparent"
    data-width="default" data-menu-styles="light" data-toggled="close">
    <meta http-equiv="Content-Security-Policy" content="
        default-src 'self';
        script-src 'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com https://cdnjs.cloudflare.com;
        style-src 'self' 'unsafe-inline';
        img-src 'self' data: https:;
        font-src 'self' data:;
        connect-src 'self' https://api.stripe.com;
        frame-src 'self' https://js.stripe.com;
    ">
    
<head>
    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Fast Ledger - @yield('title', 'Dashboard')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('admin/assets/images/brand-logos/favicon.ico') }}" type="image/x-icon">
    
    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Mona+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('admin/assets/libs/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">

    <!-- Style CSS -->
    <link href="{{ asset('admin/assets/css/styles.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/css/icons.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/banking.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/table-search.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/scrollable-table.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/resizable-table.css') }}" rel="stylesheet">

    <!-- Additional Libraries -->
    <link href="{{ asset('admin/assets/libs/node-waves/waves.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/libs/simplebar/simplebar.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/libs/@simonwep/pickr/themes/nano.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/assets/libs/choices.js/public/assets/styles/choices.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/assets/libs/@tarekraafat/autocomplete.js/css/autoComplete.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <style>
        body {
            font-family: 'Helvetica', sans-serif !important;
            font-weight: 400;
            background: #F2F2F2;
        }

        .card-title {
            font-family: 'Helvetica', sans-serif !important;
            font-weight: 700 !important;
            color: #1d687d !important;
        }

        .form-label, label {
            font-family: 'Helvetica', sans-serif !important;
            font-weight: 700 !important;
        }

        .btn {
            font-family: 'Helvetica', sans-serif !important;
            font-weight: 700 !important;
        }

        h1, h2, h3, h4, h5, h6, th {
            font-weight: 700 !important;
        }

        th {
            background-color: #bbddf2 !important;
        }

        td {
            font-weight: 500 !important;
        }

        /* ✅ Onboarding Layout Styles */
        .onboarding-layout {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .onboarding-content {
            width: 100%;
            max-width: 100%;
            padding: 0;
        }

        /* Hide elements during onboarding */
        .hide-during-onboarding {
            display: none !important;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://kit.fontawesome.com/5246715f93.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    @stack('styles')
</head>

<body>
    {{-- ✅ NEW LOGIC: Determine if user has PAID subscription for current month --}}
    @php
        $user = auth()->user();
        $isCompanyUser = $user && in_array(4, $user->getRoleIds());
        $isCompanyRoute = request()->is('company*') || request()->routeIs('company.*') || request()->routeIs('modules.*');
        
        // ✅ Check if user has ANY companies
        $hasAnyCompany = $user ? $user->companies()->exists() : false;
        
        // ✅ NEW: Check if user has ACTIVE subscription (trial OR paid)
        $hasActiveSubscription = false;
        if ($user) {
            $status = $user->subscription_status;
            
            // User has active subscription if:
            // 1. Status is 'trial' AND trial hasn't ended yet
            // 2. Status is 'active' AND next billing date is in the future
            if ($status === 'trial' && $user->trial_ends_at && now()->lt($user->trial_ends_at)) {
                $hasActiveSubscription = true;
            } elseif ($status === 'active' && $user->next_billing_date && now()->lt($user->next_billing_date)) {
                $hasActiveSubscription = true;
            }
        }
        
        // ✅ Check if on onboarding routes
        $isCompanySetupRoute = request()->routeIs('company.setup.*');
        $isPaymentRoute = request()->routeIs('company.payment.*');
        $isEmailVerificationRoute = request()->routeIs('verification.*');
        $isSubscriptionRoute = request()->routeIs('company.subscription.*');
        
        // ✅ CRITICAL: Show onboarding layout (no sidebar) if:
        // - User has NO companies, OR
        // - User has NO active subscription
        // - AND is in onboarding flow
        $isInOnboardingFlow = $isCompanySetupRoute || $isPaymentRoute || $isEmailVerificationRoute || $isSubscriptionRoute;
        
        $showOnboardingLayout = (!$hasAnyCompany || !$hasActiveSubscription) && $isInOnboardingFlow;
    @endphp

    {{-- Start Switcher (Hide during onboarding) --}}
    @if (!$showOnboardingLayout)
        @include('admin.partial.switcher')
    @endif
    {{-- End Switcher --}}

    {{-- Loader --}}
    <div id="loader">
        <img src="{{ asset('admin/assets/images/media/loader.svg') }}" alt="">
    </div>

    {{-- ✅ CONDITIONAL LAYOUT --}}
    @if ($showOnboardingLayout)
        {{-- ============================================ --}}
        {{-- ONBOARDING LAYOUT (No Sidebar/Header)       --}}
        {{-- Shown when: NO companies OR NO subscription --}}
        {{-- ============================================ --}}
        <div class="onboarding-content">
            @yield('content')
        </div>
    @else
        {{-- ============================================ --}}
        {{-- NORMAL LAYOUT (With Sidebar/Header)         --}}
        {{-- Shown when: Has companies AND has active    --}}
        {{-- subscription (trial or paid)                --}}
        {{-- ============================================ --}}
        
        {{-- Desktop Sidebar (Hidden on Mobile) --}}
        @if ($isCompanyUser && $isCompanyRoute)
            @include('admin.partial.company-sidebar')
        @else
            @include('admin.partial.sidebar')
        @endif

        {{-- Desktop Header (Hidden on Mobile) --}}
        @include('admin.partial.header')

        {{-- Mobile Header (Always Visible on Mobile) --}}
        @include('admin.partial.mobile-header')

        {{-- Main Content Area --}}
        <div class="mt-3" id="mainContent">
            @yield('content')
        </div>

        {{-- Footer --}}
        @include('admin.partial.footer')

        {{-- Modal for Search --}}
        <div class="modal fade" id="header-responsive-search" tabindex="-1" aria-labelledby="header-responsive-search"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="input-group">
                            <input type="text" class="form-control border-end-0" placeholder="Search Anything ...">
                            <button class="btn btn-primary" type="button"><i class="bi bi-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Scroll To Top --}}
        <div class="scrollToTop">
            <span class="arrow lh-1"><i class="ti ti-caret-up fs-20"></i></span>
        </div>
        <div id="responsive-overlay"></div>
    @endif

    <!-- jQuery & DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Popper & Bootstrap JS -->
    <script src="{{ asset('admin/assets/libs/@popperjs/core/umd/popper.min.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core Scripts -->
    <script src="{{ asset('admin/assets/js/defaultmenu.min.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/simplebar.js') }}"></script>
    <script src="{{ asset('admin/assets/js/table-search.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/@tarekraafat/autocomplete.js/autoComplete.min.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/@simonwep/pickr/pickr.es5.min.js') }}"></script>
    <script src="{{ asset('admin/assets/libs/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/custom.js') }}"></script>
    <script src="{{ asset('admin/assets/js/scrollable-table.js') }}"></script>
    <script src="{{ asset('admin/assets/js/resizable-table.js') }}"></script>
    <script src="{{ asset('admin/assets/js/custom-switcher.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/sticky.js') }}"></script>

    @stack('scripts')
    @yield('scripts')
</body>

</html>