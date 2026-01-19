{{-- Split Screen Component - Only show on dashboard for mobile --}}
<style>
    /* ========================================
       SPLIT SCREEN - GREETING & CARDS
       ======================================== */
    .mobile-split-screen {
        display: none;
        min-height: calc(100vh - 70px);
        background: #f5f5f5;
    }

    .mobile-greeting-section {
        background: #fff;
        padding: 2rem 1.5rem;
        color: #000;
        text-align: center;
    }

    .mobile-greeting-section h1 {
        margin: 0 0 0.75rem 0;
        font-size: 24px;
        font-weight: 700;
        color: #000;
    }

    .mobile-greeting-section p {
        margin: 0;
        font-size: 15px;
        opacity: 0.95;
        font-weight: 400;
    }

    .mobile-cards-container {
        padding: 1.5rem 1rem;
        background: white;
    }

    .mobile-nav-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        max-width: 600px;
        margin: 0 auto;
    }

    .mobile-nav-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 2rem 1rem;
        text-align: center;
        text-decoration: none;
        color: #333;
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 130px;
    }

    .mobile-nav-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
        border-color: #01677d;
        text-decoration: none;
    }

    .mobile-nav-card:active {
        transform: translateY(-2px);
    }

    .mobile-nav-card i {
        font-size: 36px;
        color: #01677d;
        margin-bottom: 0.75rem;
        display: block;
    }

    .mobile-nav-card span {
        display: block;
        font-size: 15px;
        font-weight: 600;
        color: #1f2937;
    }

    /* ========================================
       RESPONSIVE BEHAVIOR
       ======================================== */
    @media (max-width: 991px) {
        .mobile-split-screen {
            display: block;
        }
    }

    @media (max-width: 576px) {
        .mobile-greeting-section {
            padding: 0.3rem 1rem;
        }

        .mobile-greeting-section h1 {
            font-size: 20px;
        }

        .mobile-greeting-section p {
            font-size: 14px;
        }

        .mobile-cards-container {
            padding: 1rem 0.75rem;
        }

        .mobile-nav-grid {
            gap: 0.75rem;
        }

        .mobile-nav-card {
            padding: 1.5rem 0.75rem;
            min-height: 120px;
        }

        .mobile-nav-card i {
            font-size: 32px;
        }

        .mobile-nav-card span {
            font-size: 14px;
        }
    }

    @media (max-width: 375px) {
        .mobile-nav-card {
            padding: 1.25rem 0.5rem;
            min-height: 110px;
        }

        .mobile-nav-card i {
            font-size: 28px;
        }

        .mobile-nav-card span {
            font-size: 13px;
        }
    }
</style>

{{-- Split Screen Content --}}
<div class="mobile-split-screen">
    {{-- Greeting Section --}}
    <div class="mobile-greeting-section">
        @php
            $hour = date('H');
            $greeting = 'Good Evening';
            if ($hour < 12) {
                $greeting = 'Good Morning';
            } elseif ($hour < 18) {
                $greeting = 'Good Afternoon';
            }
            $userName = auth()->user()->Full_Name ?? 'Company User';
            
            // Get current company ID from route parameter or session
            $companyId = request()->route('company') ?? session('current_company_id', 1);
        @endphp
        <h1>{{ $greeting }} {{ $userName }}</h1>
        <p>Create - Send - Get paid faster.</p>
    </div>

    {{-- Cards Section --}}
    <div class="mobile-cards-container">
        <div class="mobile-nav-grid">
            {{-- Dashboard Card --}}
            <a href="{{ route('company.dashboard', $companyId) }}" class="mobile-nav-card">
                <i class="ri-dashboard-line"></i>
                <span>Dashboard</span>
            </a>

            {{-- Customers Card --}}
            <a href="{{ route('company.customers.index', $companyId) }}" class="mobile-nav-card">
                <i class="ri-user-line"></i>
                <span>Customers</span>
            </a>

            {{-- Invoicing Card --}}
            <a href="{{ route('company.invoices.index', $companyId) }}" class="mobile-nav-card">
                <i class="ri-file-list-line"></i>
                <span>Invoicing</span>
            </a>

            {{-- Items Card --}}
            <a href="{{ route('company.products.index', $companyId) }}" class="mobile-nav-card">
                <i class="ri-shopping-bag-line"></i>
                <span>Items</span>
            </a>

            {{-- Log Audit Card --}}
            <a href="{{ route('company.dashboard', $companyId) }}" class="mobile-nav-card">
                <i class="ri-file-chart-line"></i>
                <span>Log Audit</span>
            </a>

            {{-- Invoice Design Card --}}
            <a href="{{ route('company.dashboard', $companyId) }}" class="mobile-nav-card">
                <i class="ri-layout-line"></i>
                <span>Invoice Design</span>
            </a>

            {{-- Settings Card --}}
            <a href="{{ route('company.edit', $companyId) }}" class="mobile-nav-card">
                <i class="ri-settings-line"></i>
                <span>Settings</span>
            </a>

            {{-- More Card --}}
            <a href="{{ route('modules.select') }}" class="mobile-nav-card">
                <i class="ri-more-line"></i>
                <span>More</span>
            </a>
        </div>
    </div>
</div>