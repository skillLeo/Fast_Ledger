<header class="app-header sticky" id="header">
    <div class="main-header-container container-fluid" style="background-color: #01677d;">
        <style>
            /* Company switcher upgrades */
            .company-switcher-enhanced .btn.dropdown-toggle {
                background: transparent;
                border: 1px solid rgba(255, 255, 255, 0.06);
                border-radius: 6px;
                padding: 0.35rem 0.6rem;
                color: #fff;
                min-width: 220px;
            }

            .company-switcher-enhanced .dropdown-menu {
                width: 360px;
                border-radius: 10px;
            }

            .dropdown-header-card {
                background: #fff;
            }

            .company-list .company-item {
                padding: 0.65rem 0.9rem;
                gap: .5rem;
                display: flex;
                align-items: center;
                border: none;
                background: #fff;
            }

            .company-list .company-item:hover {
                background-color: #f8f9fb;
            }

            .company-list .company-item.active {
                background: linear-gradient(90deg, rgba(231, 245, 255, 1), #ffffff);
                border-left: 3px solid #0d6efd;
            }

            .muted-label {
                color: rgba(0, 0, 0, 0.55);
                font-size: 11px;
            }

            .company-name {
                font-size: 13px;
            }

            .avatar.avatar-xs {
                width: 32px;
                height: 32px;
                object-fit: cover;
            }

            .company-list::-webkit-scrollbar {
                width: 8px;
            }

            .company-list::-webkit-scrollbar-thumb {
                background: rgba(0, 0, 0, 0.08);
                border-radius: 4px;
            }

            .fl-quick .header-link i,
            .fl-chevron i {
                color: #fff;
                line-height: 1
            }

            .fl-quick .header-link:hover i,
            .fl-chevron:hover i {
                opacity: .9
            }

            .badge.bg-dark {
                background-color: rgba(0, 0, 0, .35) !important;
                padding: .4rem .9rem;
            }

            .btn-custom {
                padding: 0.2rem 1rem !important;
                background: #014351;
                font-family: 'Mona Sans', sans-serif;
                font-weight: 100 !important;
                font-size: 12px;
            }

            /* ✅ Language Switcher Styles */
            #languageSwitcher {
                transition: all 0.3s ease;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 6px;
            }

            #languageSwitcher:hover {
                background: rgba(255, 255, 255, 0.2) !important;
            }

            .dropdown-menu .dropdown-item {
                transition: background-color 0.2s;
            }

            .dropdown-menu .dropdown-item:hover {
                background-color: #f8f9fa;
            }

            .dropdown-menu .dropdown-item.active {
                background-color: #e7f5ff;
                color: #0c63e4;
                font-weight: 500;
            }

            .dropdown-menu .dropdown-item form {
                width: 100%;
            }

            .dropdown-menu .dropdown-item button {
                background: none;
                border: none;
                width: 100%;
                text-align: left;
                cursor: pointer;
                padding: 0;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .company-badge {
                display: inline-block;
                padding: 0.25rem 0.5rem;
                font-size: 10px;
                font-weight: 600;
                border-radius: 4px;
                background: #01677d;
                color: #fff;
            }
        </style>

        <!-- Start::header-content-left -->
        <div class="header-content-left">
            <!-- Start::header-element -->
            <div class="header-element">
                <div class="horizontal-logo">
                    <a href="{{ route('dashboard') }}" class="header-logo">
                        <img src="{{ asset('admin/assets/images/brand-logos/logo.png') }}" alt="logo"
                            class="desktop-logo">
                        <img src="{{ asset('admin/assets/images/brand-logos/toggle-logo.png') }}" alt="logo"
                            class="toggle-logo">
                    </a>
                </div>
            </div>
            <!-- End::header-element -->

            {{-- Company Switcher (enhanced) - Only show in company module --}}
            @if (request()->is('company*') && session()->has('current_company_id'))
                <div class="fl-utility-left d-none d-md-flex align-items-center ms-4 company-switcher-enhanced">
                    <div class="dropdown">
                        <button class="btn dropdown-toggle d-flex align-items-center text-white px-2"
                            id="companySwitcherBtn" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true"
                            type="button">
                            @if (session()->has('current_company_logo'))
                                <img src="{{ asset('storage/' . session('current_company_logo')) }}"
                                    class="avatar avatar-xs me-2 rounded-circle" alt="logo">
                            @else
                                <i class="ri-building-line fs-18 me-2"></i>
                            @endif
                            <div class="d-flex flex-column text-start me-2">
                                <small class="muted-label">{{ __('company.working_in') }}</small>
                                <strong class="company-name text-truncate"
                                    style="max-width:170px;">{{ session('current_company_name') }}</strong>
                            </div>
                            <i class="ri-arrow-down-s-line ms-1"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-start p-0 shadow-lg border-0"
                            aria-labelledby="companySwitcherBtn">
                            <div class="p-3 border-bottom dropdown-header-card">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-bold">{{ __('company.switch_company') }}</div>
                                        <small class="text-muted">{{ __('company.select_company') }}</small>
                                    </div>
                                    <button class="btn btn-sm btn-outline-secondary" id="companySwitcherClose"
                                        aria-label="Close dropdown">&times;</button>
                                </div>

                                <div class="mt-2">
                                    <input id="companySwitcherSearch" class="form-control form-control-sm"
                                        type="search" placeholder="{{ __('company.search_companies') }}"
                                        aria-label="{{ __('company.search_companies') }}">
                                </div>
                            </div>

                            <div class="list-group list-group-flush company-list"
                                style="max-height:320px; overflow-y:auto;">
                                @foreach (auth()->user()->companies as $company)
                                    <form action="{{ route('company.set-current', $company->id) }}" method="POST"
                                        class="d-block company-item-form">
                                        @csrf
                                        <button type="submit"
                                            class="list-group-item list-group-item-action d-flex align-items-center company-item {{ session('current_company_id') == $company->id ? 'active' : '' }}"
                                            data-company-name="{{ strtolower($company->Company_Name) }}">
                                            @if ($company->Logo_Path)
                                                <img src="{{ asset('storage/' . $company->Logo_Path) }}"
                                                    class="avatar avatar-xs me-3 rounded-circle" alt="logo">
                                            @else
                                                <span
                                                    class="avatar avatar-xs bg-light text-primary me-3 rounded-circle d-inline-flex align-items-center justify-content-center"
                                                    style="width:32px;height:32px;">
                                                    <i class="ri-building-line fs-14"></i>
                                                </span>
                                            @endif

                                            <div class="flex-fill text-start">
                                                <div class="fw-semibold text-truncate" style="max-width:220px;">
                                                    {{ $company->Company_Name }}</div>
                                                <small class="text-muted">{{ __('company.company_id') }}
                                                    {{ $company->id }}</small>
                                            </div>

                                            @if (session('current_company_id') == $company->id)
                                                <i class="ri-check-line text-success ms-2"
                                                    title="{{ __('company.current') }}"></i>
                                            @endif
                                        </button>
                                    </form>
                                @endforeach
                            </div>

                            <div class="p-2 border-top d-flex gap-2">
                                <a class="btn btn-sm btn-light w-100" href="{{ route('company.select') }}">
                                    <i class="ri-apps-line me-1"></i> {{ __('company.view_all') }}
                                </a>
                                <a class="btn btn-sm btn-primary w-100" href="{{ route('company.create') }}">
                                    <i class="ri-add-line me-1"></i> {{ __('company.create') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- Simple version for non-company pages --}}
                <div class="fl-utility-left d-none d-md-flex align-items-center ms-4">
                    <div class="dropdown">
                        <button class="btn btn-custom text-white d-flex align-items-center" id="companySwitcher"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="text-truncate" style="max-width:220px;">
                                {{ auth()->user()->client->Business_Name ?? 'No Business' }}
                            </span>
                            <i class="ri-arrow-down-s-line ms-2"></i>
                        </button>
                        {{-- <ul class="dropdown-menu dropdown-menu-start shadow" aria-labelledby="companySwitcher">
                            <li><a class="dropdown-item" href="#">Energy Saviour Ltd</a></li>
                            <li><a class="dropdown-item" href="#">Bright Solar Co.</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Add company…</a></li>
                        </ul> --}}
                    </div>
                </div>
            @endif
        </div>
        <!-- End::header-content-left -->

        <!-- Start::header-content-right -->
        <ul class="header-content-right">
            <!-- Quick icons -->
            <li class="header-element d-none d-md-flex align-items-center me-3 fl-quick">
                <a href="#" class="header-link text-white px-2" title="{{ __('company.new') }}">
                    <i class="fa-thin fa-plus fa-xl"></i>
                </a>
                <a href="#" class="header-link text-white px-2" title="{{ __('company.search') }}">
                    <i class="fa-thin fa-magnifying-glass fa-xl"></i>
                </a>
                <a href="#" class="header-link text-white px-2" title="{{ __('company.help') }}">
                    <i class="fa-thin fa-question fa-xl"></i>
                </a>
                <a href="#" class="header-link position-relative text-white px-2"
                    title="{{ __('company.notifications') }}">
                    <i class="fa-thin fa-bell fa-xl"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                        style="font-size:.55rem;">3</span>
                </a>
            </li>

            {{-- ✅ LANGUAGE SWITCHER - Only in Company Module --}}
            @if (request()->routeIs('company.*'))
                {{-- ✅ LANGUAGE SWITCHER - GLOBAL (Available everywhere) --}}
                <li class="header-element d-none d-md-flex align-items-center me-3">
                    <div class="dropdown">
                        <button class="btn-custom text-white d-flex align-items-center border-0" id="languageSwitcher"
                            data-bs-toggle="dropdown" aria-expanded="false" style="padding: 0.4rem 0.8rem;">
                            @if (app()->getLocale() === 'es')
                                <i class="ri-global-line me-2 fs-16"></i>
                                <span>Español</span>
                            @else
                                <i class="ri-global-line me-2 fs-16"></i>
                                <span>English</span>
                            @endif
                            <i class="ri-arrow-down-s-line ms-2"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow border-0"
                            style="min-width: 200px; border-radius: 8px;" aria-labelledby="languageSwitcher">
                            <li class="dropdown-header border-bottom">
                                <i class="ri-translate-2 me-2"></i>Select Language
                            </li>
                            <li>
                                <form action="{{ route('language.switch') }}" method="POST"
                                    class="dropdown-item p-0">
                                    @csrf
                                    <input type="hidden" name="locale" value="en">
                                    <button type="submit"
                                        class="dropdown-item d-flex align-items-center {{ app()->getLocale() === 'en' ? 'active' : '' }}"
                                        style="padding: 0.65rem 1rem; width: 100%;">
                                        <img src="https://flagcdn.com/w20/gb.png" alt="UK" class="me-2"
                                            style="width: 20px; height: 15px;">
                                        <span class="flex-fill">English (UK)</span>
                                        @if (app()->getLocale() === 'en')
                                            <i class="ri-check-line text-success ms-2"></i>
                                        @endif
                                    </button>
                                </form>
                            </li>
                            <li>
                                <form action="{{ route('language.switch') }}" method="POST"
                                    class="dropdown-item p-0">
                                    @csrf
                                    <input type="hidden" name="locale" value="es">
                                    <button type="submit"
                                        class="dropdown-item d-flex align-items-center {{ app()->getLocale() === 'es' ? 'active' : '' }}"
                                        style="padding: 0.65rem 1rem; width: 100%;">
                                        <img src="https://flagcdn.com/w20/es.png" alt="Spain" class="me-2"
                                            style="width: 20px; height: 15px;">
                                        <span class="flex-fill">Español</span>
                                        @if (app()->getLocale() === 'es')
                                            <i class="ri-check-line text-success ms-2"></i>
                                        @endif
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            <!-- Username -->
            <li class="header-element d-none d-md-flex align-items-center">
                <span class="btn-custom text-white rounded-0">
                    <span class="text-capitalize">{{ auth()->user()->client->Contact_Name ?? 'No Name' }}</span>
                </span>
            </li>

            <!-- Profile dropdown -->
            <li class="header-element dropdown">
                <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <div class="d-flex align-items-center">
                        <div class="me-xl-2 me-0">
                            <img src="{{ asset('admin/assets/images/faces/14.jpg') }}" alt="img"
                                class="avatar avatar-sm avatar-rounded">
                        </div>
                    </div>
                </a>
                <ul class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end"
                    aria-labelledby="mainHeaderProfile">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <i class="ti ti-user me-2 fs-18 text-primary"></i>{{ __('company.profile') }}
                        </a>
                    </li>
                    <li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                            @csrf
                        </form>
                        <a class="dropdown-item d-flex align-items-center" href="#"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="ti ti-logout me-2 fs-18 text-warning"></i>{{ __('company.logout') }}
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
        <!-- End::header-content-right -->
    </div>
</header>

{{-- Company Switcher Script --}}
<script>
    (function() {
        const search = document.getElementById('companySwitcherSearch');
        if (!search) return;
        const list = document.querySelectorAll('.company-list .company-item');

        search.addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            list.forEach(item => {
                const name = item.getAttribute('data-company-name') || '';
                item.style.display = name.includes(q) ? '' : 'none';
            });
        });

        const closeBtn = document.getElementById('companySwitcherClose');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const btn = document.getElementById('companySwitcherBtn');
                btn && btn.dispatchEvent(new MouseEvent('click', {
                    bubbles: true
                }));
            });
        }
    })();
</script>
