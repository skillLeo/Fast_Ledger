{{-- Mobile Header - Always Visible --}}
<style>
    /* ========================================
       MOBILE HEADER (ALWAYS VISIBLE)
       ======================================== */
    .mobile-fixed-header {
        display: none;
        background-color: #01677d;
        padding: 1rem;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .mobile-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 100%;
    }

    .mobile-logo img {
        height: 30px;
    }

    .mobile-company-select {
        flex: 1;
        margin: 0 1rem;
        max-width: 300px;
    }

    .mobile-company-btn {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        width: 100%;
        text-align: left;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 1rem;
        font-size: 14px;
        border-radius: 6px;
        white-space: nowrap;
        overflow: hidden;
    }

    .mobile-company-btn span {
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .mobile-company-btn i {
        margin-left: 0.5rem;
        flex-shrink: 0;
    }

    .mobile-hamburger-btn {
        background: transparent;
        border: none;
        color: white;
        font-size: 28px;
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        line-height: 1;
    }

    /* ========================================
       HAMBURGER MENU OVERLAY & SIDEBAR
       ======================================== */
    .mobile-menu-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1500;
    }

    .mobile-menu-overlay.active {
        display: block;
    }

    .mobile-menu-sidebar {
        position: fixed;
        top: 0;
        right: -300px;
        width: 280px;
        height: 100%;
        background: white;
        z-index: 1600;
        transition: right 0.3s ease;
        box-shadow: -2px 0 15px rgba(0, 0, 0, 0.2);
        overflow-y: auto;
    }

    .mobile-menu-sidebar.active {
        right: 0;
    }

    .mobile-menu-user-section {
        background: #f8f9fa;
        padding: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .mobile-user-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .mobile-user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #01677d;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: bold;
        flex-shrink: 0;
    }

    .mobile-user-details h6 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }

    .mobile-user-details p {
        margin: 0;
        font-size: 12px;
        color: #6c757d;
    }

    .mobile-menu-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .mobile-menu-list li {
        border-bottom: 1px solid #e9ecef;
    }

    .mobile-menu-list li a,
    .mobile-menu-list li button {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.5rem;
        color: #333;
        text-decoration: none;
        background: none;
        border: none;
        width: 100%;
        text-align: left;
        transition: background 0.2s;
        cursor: pointer;
        font-size: 15px;
    }

    .mobile-menu-list li a:hover,
    .mobile-menu-list li button:hover {
        background: #f8f9fa;
    }

    .mobile-menu-list i {
        font-size: 18px;
        width: 24px;
        text-align: center;
        color: #01677d;
    }

    /* ========================================
       RESPONSIVE BEHAVIOR
       ======================================== */
    @media (max-width: 991px) {
        /* Hide desktop header and sidebar */
        .app-header {
            display: none !important;
        }

        .app-sidebar {
            display: none !important;
        }

        /* Show mobile header */
        .mobile-fixed-header {
            display: block;
        }

        /* Adjust main content */
        #mainContent {
            margin-left: 0 !important;
            margin-top: 70px !important;
            padding: 0 !important;
        }

        body {
            padding-top: 0 !important;
        }
    }

    @media (max-width: 576px) {
        .mobile-company-select {
            max-width: 200px;
        }

        .mobile-company-btn {
            font-size: 13px;
            padding: 0.4rem 0.8rem;
        }
    }
</style>

{{-- Mobile Header Structure --}}
<div class="mobile-fixed-header">
    <div class="mobile-header-row">
        {{-- Logo --}}
        <div class="mobile-logo">
            <a href="{{ route('company.dashboard', session('current_company_id', 1)) }}">
                <img src="{{ asset('admin/assets/images/brand-logos/logo.png') }}" alt="Fast Ledger">
            </a>
        </div>

        {{-- Company Dropdown --}}
        <div class="mobile-company-select">
            <button class="mobile-company-btn dropdown-toggle" type="button" 
                    data-bs-toggle="dropdown" aria-expanded="false">
                <span>{{ session('current_company_name', 'Wynn Hanson Co') }}</span>
                <i class="ri-arrow-down-s-line"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                @if(auth()->check() && auth()->user()->companies)
                    @foreach(auth()->user()->companies as $company)
                        <li>
                            <form action="{{ route('company.set-current', $company->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    {{ $company->Company_Name }}
                                </button>
                            </form>
                        </li>
                    @endforeach
                    <li><hr class="dropdown-divider"></li>
                @endif
                <li>
                    <a class="dropdown-item" href="{{ route('company.create') }}">
                        <i class="ri-add-line me-2"></i> Add Company
                    </a>
                </li>
            </ul>
        </div>

        {{-- Hamburger Menu --}}
        <button class="mobile-hamburger-btn" id="mobileHamburger">
            <i class="ri-menu-line"></i>
        </button>
    </div>
</div>

{{-- Menu Overlay --}}
<div class="mobile-menu-overlay" id="mobileOverlay"></div>

{{-- Hamburger Sidebar Menu --}}
<div class="mobile-menu-sidebar" id="mobileSidebar">
    <div class="mobile-menu-user-section">
        <div class="mobile-user-info">
            <div class="mobile-user-avatar">
                {{ strtoupper(substr(auth()->user()->Full_Name ?? 'U', 0, 1)) }}
            </div>
            <div class="mobile-user-details">
                <h6>{{ auth()->user()->Full_Name ?? 'Username' }}</h6>
                <p>{{ auth()->user()->email ?? 'user@example.com' }}</p>
            </div>
        </div>
    </div>

    <ul class="mobile-menu-list">
        <li>
            <a href="{{ route('company.dashboard', session('current_company_id', 1)) }}">
                <i class="ri-user-line"></i>
                <span>Profile</span>
            </a>
        </li>
        <li>
            <a href="{{ route('company.invoices.create') }}">
                <i class="ri-add-circle-line"></i>
                <span>Add New Invoice</span>
            </a>
        </li>
        <li>
            <a href="#">
                <i class="ri-notification-line"></i>
                <span>Notifications</span>
            </a>
        </li>
        <li>
            <a href="#">
                <i class="ri-question-line"></i>
                <span>Help</span>
            </a>
        </li>
        <li>
            <form id="mobile-logout-form" action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
            </form>
            <button onclick="event.preventDefault(); document.getElementById('mobile-logout-form').submit();">
                <i class="ri-logout-box-line"></i>
                <span>Logout</span>
            </button>
        </li>
    </ul>
</div>

{{-- Mobile Menu JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.getElementById('mobileHamburger');
    const overlay = document.getElementById('mobileOverlay');
    const sidebar = document.getElementById('mobileSidebar');

    function openMenu() {
        overlay.classList.add('active');
        sidebar.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        overlay.classList.remove('active');
        sidebar.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (hamburger) {
        hamburger.addEventListener('click', function() {
            if (sidebar.classList.contains('active')) {
                closeMenu();
            } else {
                openMenu();
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeMenu);
    }
});
</script>