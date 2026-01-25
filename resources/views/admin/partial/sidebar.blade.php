@php
    $hideSidebar = request()->routeIs('clients.index')
        && DB::table('userrole')
            ->where('User_ID', auth()->user()->User_ID)
            ->whereIn('Role_ID', [1,3])
            ->exists();
@endphp

<aside class="app-sidebar sticky" id="sidebar">
    <!-- Sidebar Header -->
    <div class="main-sidebar-header">
        <a href="/" class="header-logo">
            <img src="{{ asset('admin/assets/images/brand-logos/logo.png') }}" width="130" height="30" alt="logo"
                class="desktop-logo">
            <img src="{{ asset('admin/assets/images/brand-logos/toggle-dark.png') }}" alt="logo"
                class="toggle-dark">
            <img src="{{ asset('admin/assets/images/brand-logos/desktop-dark.png') }}" alt="logo"
                class="desktop-dark">
            <img src="{{ asset('admin/assets/images/brand-logos/toggle-logo.png') }}" alt="logo"
                class="toggle-logo">
        </a>

        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="ri-menu-line"></i>
        </button>

        <div class="collapsed-logo" id="collapsedLogo">
            <img src="{{ asset('admin/assets/images/brand-logos/toggle-logo-sidebar.png') }}" alt="logo"
                class="collapsed-logo-img">
        </div>
    </div>

    <div class="main-sidebar {{ $hideSidebar ? 'd-none' : '' }}" id="sidebar-scroll">
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <ul class="main-menu">
                <!-- Fast Books Group -->
                <li class="slide has-sub fast-group">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i
                            class="fa-light fa-caret-large-down fa-flip-horizontal side-menu__angle_left fa-xs side-menu__icon"></i>
                        <span class="side-menu__label">Fast Books</span>
                    </a>

                    <ul class="slide-menu">
                        <li class="slide">
                            <a href="/"
                                class="side-menu__item {{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}">
                                <i class="fa-regular fa-house side-menu__icon"></i>
                                <span class="side-menu__label">Dashboard</span>
                            </a>
                        </li>

                        <li class="slide">
                            <a href="{{ route('files.index') }}"
                                class="side-menu__item {{ Route::currentRouteName() == 'files.index' ? 'active' : '' }}">
                                <i class="fa-light fa-address-book side-menu__icon"></i>
                                <span class="side-menu__label">Client</span>
                            </a>
                        </li>

                        <li class="slide">
                            <a href="{{ route('bulk-transactions.dashboard') }}"
                                class="side-menu__item 
                                    {{ request()->routeIs('bulk-transactions.dashboard') ||
                                    (request()->routeIs('transactions.index') && request()->get('view') == 'day_book') ||
                                    (request()->routeIs('transactions.create') &&
                                        in_array(request('type'), ['client', 'office']) &&
                                        !request()->has('payment_type'))
                                        ? 'active'
                                        : '' }}">
                                <i class="fa-light fa-building-columns side-menu__icon"></i>
                                <span class="side-menu__label">Banking</span>
                            </a>
                        </li>

                        <li class="slide">
                            <a href="{{ route('invoices.index', ['tab' => 'issued']) }}"
                                class="side-menu__item
                                    {{ request()->routeIs('invoices.index') && request('tab') === 'issued' ? 'active' : '' }}">
                                <i class="fa-light fa-receipt side-menu__icon"></i>
                                <span class="side-menu__label">Sales</span>
                            </a>
                        </li>
                        
                        <li class="slide">
                            <a href="{{ route('purchases.index', ['tab' => 'issued']) }}"
                                class="side-menu__item
                                    {{ request()->routeIs('purchases.index') && request('tab') === 'issued' ? 'active' : '' }}">
                                <i class="fa-light fa-receipt side-menu__icon"></i>
                                <span class="side-menu__label">Purchases</span>
                            </a>
                        </li>

                        <li class="slide">
                            <a href="{{ route('transactions.create', ['type' => 'office', 'payment_type' => 'journal']) }}"
                                class="side-menu__item
                                    {{ request()->routeIs('transactions.create') &&
                                    request('type') === 'office' &&
                                    request('payment_type') === 'journal'
                                        ? 'active'
                                        : '' }}">
                                <i class="fa-light fa-calculator-simple side-menu__icon"></i>
                                <span class="side-menu__label">Journals</span>
                            </a>
                        </li>

                        <li class="slide">
                            <a href="{{ route('transactions.imported') }}"
                                class="side-menu__item {{ Route::currentRouteName() == 'transactions.imported' ? 'active' : '' }}">
                                <i class="fa-light fa-arrow-up-arrow-down side-menu__icon"></i>
                                <span class="side-menu__label">Transactions</span>
                            </a>
                        </li>

                        <li class="slide">
                            <a href="javascript:void(0);" class="side-menu__item" id="reportsBtn">
                                <i class="fa-light fa-chart-mixed side-menu__icon"></i>
                                <span class="side-menu__label">Reports</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Fast Manager Group -->
                <li class="slide has-sub fast-group">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="fa-light fa-caret-large-down side-menu__angle_left side-menu__icon"></i>
                        <span class="side-menu__label">Fast Manager</span>
                    </a>

                    <ul class="slide-menu">
                        <li class="slide">
                            <a href="#" class="side-menu__item">
                                <i class="fa-light fa-list-check side-menu__icon"></i>
                                <span class="side-menu__label">Tasks</span>
                            </a>
                        </li>

                        <li class="slide">
                            <a href="#" class="side-menu__item">
                                <i class="fa-light fa-messages side-menu__icon"></i>
                                <span class="side-menu__label">Communications</span>
                            </a>
                        </li>

                        <li class="slide">
                            <a href="#" class="side-menu__item">
                                <i class="fa-light fa-folders side-menu__icon"></i>
                                <span class="side-menu__label">Sharing Folders</span>
                            </a>
                        </li>

                        <li class="slide">
                            <a href="#" class="side-menu__item">
                                <i class="fa-light fa-file-contract side-menu__icon"></i>
                                <span class="side-menu__label">Documents</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Fast Payroll Group -->
                <li class="slide has-sub fast-group">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="fa-light fa-caret-large-down side-menu__angle_left side-menu__icon"></i>
                        <span class="side-menu__label">Fast Payroll</span>
                    </a>
                    <ul class="slide-menu child1"></ul>
                </li>

                <!-- Fast Accounts Group -->
                <li class="slide has-sub fast-group">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="fa-light fa-caret-large-down side-menu__angle_left side-menu__icon"></i>
                        <span class="side-menu__label">Fast Accounts</span>
                    </a>
                    <ul class="slide-menu child1"></ul>
                </li>

                <!-- Fast Taxations Group -->
                <li class="slide has-sub fast-group">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="fa-light fa-caret-large-down side-menu__angle_left side-menu__icon"></i>
                        <span class="side-menu__label">Fast Taxations</span>
                    </a>
                    <ul class="slide-menu child1"></ul>
                </li>
                
                 <!-- HMRC -->
                <li class="slide has-sub fast-group">
                    <a href="javascript:void(0);" class="side-menu__item" style="border-bottom: 1px white solid;">
                        <i class="fa-light fa-caret-large-down side-menu__angle_left side-menu__icon"></i>
                        <span class="side-menu__label">HMRC</span>
                    </a>
                    <ul class="slide-menu">
                        <!-- Connection & Setup -->
                        <li class="slide">
                            <a href="{{ route('hmrc.auth.index') }}"
                                class="side-menu__item {{ Route::currentRouteName() == 'hmrc.auth.index' || str_contains(Route::currentRouteName(), 'hmrc.auth') ? 'active' : '' }}">
                                <i class="fa-light fa-plug side-menu__icon"></i>
                                <span class="side-menu__label">Connect to HMRC</span>
                            </a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('hmrc.businesses.index') }}"
                                class="side-menu__item {{ Route::currentRouteName() == 'hmrc.businesses.index' || str_contains(Route::currentRouteName(), 'hmrc.businesses') ? 'active' : '' }}">
                                <i class="fa-light fa-buildings side-menu__icon"></i>
                                <span class="side-menu__label">Businesses</span>
                            </a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('hmrc.obligations.index') }}"
                                class="side-menu__item {{ Route::currentRouteName() == 'hmrc.obligations.index' || str_contains(Route::currentRouteName(), 'hmrc.obligations') ? 'active' : '' }}">
                                <i class="fa-light fa-list-check side-menu__icon"></i>
                                <span class="side-menu__label">Obligations</span>
                            </a>
                        </li>

                        <!-- Self Employment Business -->
                        <li class="slide">
                            <a href="{{ route('hmrc.submissions.index') }}"
                                class="side-menu__item {{ Route::currentRouteName() == 'hmrc.submissions.index' || str_contains(Route::currentRouteName(), 'hmrc.submissions') ? 'active' : '' }}">
                                <i class="fa-light fa-file-lines side-menu__icon"></i>
                                <span class="side-menu__label">SE - PERIODIC</span>
                            </a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('hmrc.annual-submissions.index') }}"
                                class="side-menu__item {{ Route::currentRouteName() == 'hmrc.annual-submissions.index' || str_contains(Route::currentRouteName(), 'hmrc.annual-submissions') ? 'active' : '' }}">
                                <i class="fa-light fa-calendar-days side-menu__icon"></i>
                                <span class="side-menu__label">SE - ANNUAL</span>
                            </a>
                        </li>

                        <!-- UK Property Business -->
                        <li class="slide">
                            <a href="{{ route('hmrc.uk-property-period-summaries.index') }}"
                                class="side-menu__item {{ str_contains(Route::currentRouteName(), 'hmrc.uk-property-period-summaries') ? 'active' : '' }}">
                                <i class="fa-light fa-file-lines side-menu__icon"></i>
                                <span class="side-menu__label">PROPERTY - PERIODIC</span>
                            </a>
                        </li>
                        <li class="slide">
                            <a href="{{ route('hmrc.uk-property-annual-submissions.index') }}"
                                class="side-menu__item {{ str_contains(Route::currentRouteName(), 'hmrc.uk-property-annual-submissions') ? 'active' : '' }}">
                                <i class="fa-light fa-calendar-days side-menu__icon"></i>
                                <span class="side-menu__label">PROPERTY - ANNUAL</span>
                            </a>
                        </li>

                        <!-- Tax Calculations -->
                        <li class="slide">
                            <a href="{{ route('hmrc.calculations.index') }}"
                                class="side-menu__item {{ Route::currentRouteName() == 'hmrc.calculations.index' || str_contains(Route::currentRouteName(), 'hmrc.calculations') ? 'active' : '' }}">
                                <i class="fa-light fa-calculator-simple side-menu__icon"></i>
                                <span class="side-menu__label">Tax Calculations</span>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Other Links -->
                <li class="slide">
                    <a href="#" class="side-menu__item">
                        <i class="fa-light fa-link-horizontal side-menu__icon"></i>
                        <span class="side-menu__label">Useful Links</span>
                    </a>
                </li>

                <li class="slide">
                    <a href="#" class="side-menu__item">
                        <i class="fa-light fa-circle-info side-menu__icon"></i>
                        <span class="side-menu__label">Support</span>
                    </a>
                </li>

                <!-- Settings (Admin Only) -->
                @if (auth()->check() && auth()->user()->User_Role != 1)
                    <li class="slide has-sub keep-closed">
                        <a href="javascript:void(0);" class="side-menu__item">
                            <i class="fa-light fa-gear-complex side-menu__icon"></i>
                            <span class="side-menu__label">Settings</span>
                            <i class="ri-arrow-down-s-line side-menu__angle"></i>
                        </a>

                        <ul class="slide-menu">
                            <li class="slide">
                                <a href="{{ route('admin.users.banks', ['user' => auth()->id()]) }}"
                                    class="side-menu__item {{ Route::currentRouteName() == 'admin.users.banks' ? 'active' : '' }}">
                                    <i class="fa-light fa-building-columns side-menu__icon"></i>
                                    <span class="side-menu__label">User Bank Accounts</span>
                                </a>
                            </li>

                            <li class="slide">
                                <a href="{{ route('check.active') }}"
                                    class="side-menu__item {{ Route::currentRouteName() == 'check.active' ? 'active' : '' }}">
                                    <i class="fa-light fa-user side-menu__icon"></i>
                                    <span class="side-menu__label">Fee Earners</span>
                                </a>
                            </li>

                            <li class="slide">
                                <a href="{{ route('finexer.settings', ['user' => auth()->id()]) }}"
                                    class="side-menu__item {{ Route::currentRouteName() == 'finexer.settings' ? 'active' : '' }}">
                                    <i class="fa-light fa-building-columns side-menu__icon"></i>
                                    <span class="side-menu__label">Banks</span>
                                </a>
                            </li>

                            <li class="slide">
                                <a href="{{ route('products.index') }}"
                                    class="side-menu__item {{ Route::currentRouteName() == 'products.index' ? 'active' : '' }}">
                                    <i class="fa-light fa-box side-menu__icon"></i>
                                    <span class="side-menu__label">Products Item</span>
                                </a>
                            </li>
                            
                            <li class="slide">
                                <a href="{{ route('invoices.activity_logs.index') }}"
                                    class="side-menu__item {{ Route::currentRouteName() == 'invoices.activity_logs.index' ? 'active' : '' }}">
                                    <i class="fa-light fa-history side-menu__icon"></i>
                                    <span class="side-menu__label">All Log History</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>

<!-- Horizontal Submenu (Reports) -->
<div class="horizontal-submenu" id="horizontalSubmenu">
    <div class="submenu-sections">
        <!-- Client Reports Section -->
        <div class="submenu-section">
            <div class="section-title" data-section="client-reports">
                <span>Client Reports</span>
                <i class="ri-arrow-right-s-line section-arrow"></i>
            </div>
            <ul class="section-items collapsed" id="client-reports">
                <li class="section-item">
                    <a href="{{ route('file.report') }}" class="section-link">Matters Book</a>
                </li>
                <li class="section-item">
                    <a href="{{ route('client.ledger') }}" class="section-link">Client Ledgers</a>
                </li>
                <li class="section-item">
                    <a href="{{ route('client.cashbook') }}" class="section-link">Client Cash Book</a>
                </li>
                <li class="section-item">
                    <a href="{{ route('client.bank_bank_reconciliation') }}" class="section-link">Client Bank
                        Reconciliation</a>
                </li>
                <li class="section-item">
                    <a href="{{ route('client.passed.check') }}" class="section-link">Clients Funds > 14 Days</a>
                </li>
            </ul>
        </div>

        <!-- Financials Reports Section -->
        <div class="submenu-section">
            <div class="section-title" data-section="financial-reports">
                <span>Financials Reports</span>
                <i class="ri-arrow-right-s-line section-arrow"></i>
            </div>
            <ul class="section-items collapsed" id="financial-reports">
                <li class="section-item">
                    <a href="{{ route('office.cashbook') }}" class="section-link">Office Cash Book</a>
                </li>
                <li class="section-item">
                    <a href="{{ route('office.bank_reconciliation') }}" class="section-link">Office Bank
                        Reconciliation</a>
                </li>
                <li class="section-item">
                    <a href="{{ route('bill.of.cost') }}" class="section-link">BOC (Consolidated)</a>
                </li>
                <li class="section-item">
                    <a href="{{ route('hmrc.vat.dashboard') }}" class="section-link">VAT Return</a>
                </li>
                <!--<li class="section-item">-->
                <!--    <a href="{{ route('vat.report') }}" class="section-link">VAT Report</a>-->
                <!--</li>-->
                <li class="section-item">
                    <a href="{{ route('profit.and.loos') }}" class="section-link">Profit & Loss</a>
                </li>
                <li class="section-item">
                    <a href="{{ route('profit-loss') }}" class="section-link">Profit And Loss</a>
                </li>
                <li class="section-item">
                    <a href="{{ route('trail_balances.index') }}" class="section-link">Trial Balance</a>
                </li>
                <li class="section-item">
                    <a href="{{ route('balance-sheet') }}" class="section-link">Balance Sheet</a>
                </li>
                <li class="section-item">
                    <a href="{{ route('invoices.reporting') }}" class="section-link">Invoice Reporting</a>
                </li>
                <li class="section-item">
                    <a href="{{ route('transactions.index', ['view' => 'batch_invoicing']) }}"
                        class="section-link">Batch Invoicing</a>
                </li>
                <li class="section-item">
                    <a href="{{ route('transactions.cheque') }}" class="section-link">Cheque Records</a>
                </li>
                <li class="section-item">
                    <a href="{{ route('charts.of.accounts.index') }}" class="section-link">Charts Of Accounts</a>
                </li>
                <li class="section-item">
                    <a href="{{ route('suppliers.index') }}" class="section-link">Suppliers</a>
                </li>
            </ul>
        </div>

        <!-- Management Reports Section -->
        <div class="submenu-section">
            <div class="section-title" data-section="management-reports">
                <span>Management Reports</span>
                <i class="ri-arrow-right-s-line section-arrow"></i>
            </div>
            <ul class="section-items collapsed" id="management-reports">
                <li class="section-item">
                    <a href="#" class="section-link">Aged Debtors</a>
                </li>
                <li class="section-item">
                    <a href="#" class="section-link">Aged Creditors</a>
                </li>
                <li class="section-item">
                    <a href="#" class="section-link">Fixed Assets Register</a>
                </li>
            </ul>
        </div>
    </div>
</div>

<script>
    /**
     * NavigationManager Class
     * Manages the sidebar navigation, collapsible menus, and reports submenu
     */
    class NavigationManager {
        /**
         * Constructor - Initialize the navigation manager
         */
        constructor() {
            this.elements = this.initializeElements();
            this.state = {
                isSubmenuOpen: false,
                isSidebarCollapsed: false
            };

            if (this.elements.isValid) {
                this.init();
            }
        }

        /**
         * Initialize and cache DOM elements
         * @returns {Object} Object containing all DOM elements
         */
        initializeElements() {
            const elements = {
                sidebarToggle: document.getElementById('sidebarToggle'),
                sidebar: document.getElementById('sidebar'),
                mainContent: document.querySelector('.main-content.app-content'),
                header: document.querySelector('.app-header'),
                horizontalSubmenu: document.getElementById('horizontalSubmenu'),
                reportsBtn: document.getElementById('reportsBtn'),
                sectionTitles: document.querySelectorAll('.section-title'),
                otherMenuItems: document.querySelectorAll('.side-menu__item'),
                collapsedLogo: document.getElementById('collapsedLogo')
            };

            elements.isValid = !!(elements.sidebar && elements.horizontalSubmenu && elements.reportsBtn);
            return elements;
        }

        /**
         * Initialize the navigation manager
         */
        init() {
            this.bindEvents();
            this.initializeSectionDropdowns();
            this.autoExpandAllSidebarDropdowns();
            this.setupIndividualDropdownToggles();
            this.checkActiveSubmenuLinks();
            this.checkActiveSidebarLinks();
            this.autoExpandAllSections();
        }

        /**
         * Bind all event listeners
         */
        bindEvents() {
            // Sidebar toggle
            if (this.elements.sidebarToggle) {
                this.elements.sidebarToggle.addEventListener('click', () => this.handleSidebarToggle());
            }

            // Reports button toggle
            this.elements.reportsBtn.addEventListener('click', (e) => this.handleReportsToggle(e));

            // Click outside to close submenu
            document.addEventListener('click', (e) => this.handleOutsideClick(e));

            // Other menu items click
            this.elements.otherMenuItems.forEach(item => {
                if (item !== this.elements.reportsBtn) {
                    item.addEventListener('click', () => this.handleOtherMenuClick());
                }
            });

            // Submenu links click
            const submenuLinks = this.elements.horizontalSubmenu.querySelectorAll('.section-link');
            submenuLinks.forEach(link => {
                link.addEventListener('click', () => this.handleSubmenuLinkClick(link));
            });
        }

        /**
         * Initialize section dropdown toggles
         */
        initializeSectionDropdowns() {
            this.elements.sectionTitles.forEach(title => {
                title.addEventListener('click', () => this.handleSectionToggle(title));
            });
        }

        /**
         * Auto-expand all sidebar dropdowns on load (except keep-closed)
         */
        autoExpandAllSidebarDropdowns() {
            const allDropdowns = document.querySelectorAll('.has-sub');

            allDropdowns.forEach(dropdown => {
                if (dropdown.classList.contains('keep-closed')) {
                    return;
                }

                dropdown.classList.add('open');

                const slideMenu = dropdown.querySelector('.slide-menu');
                if (slideMenu) {
                    slideMenu.style.display = 'block';
                }

                const arrow = dropdown.querySelector('.side-menu__angle, .side-menu__angle_left');
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            });
        }

        /**
         * Setup individual dropdown toggle functionality
         */
        setupIndividualDropdownToggles() {
            const allDropdowns = document.querySelectorAll('.has-sub');

            allDropdowns.forEach(dropdown => {
                const parentToggle = dropdown.querySelector(':scope > .side-menu__item');
                if (!parentToggle) return;

                // Clone to remove existing listeners
                const newParentToggle = parentToggle.cloneNode(true);
                parentToggle.parentNode.replaceChild(newParentToggle, parentToggle);

                newParentToggle.addEventListener('click', (e) => {
                    const target = e.target;
                    const clickedInsideSlideMenu = target.closest('.slide-menu');

                    // Don't toggle if clicking on child links
                    if (clickedInsideSlideMenu) {
                        return;
                    }

                    e.preventDefault();
                    e.stopPropagation();

                    this.toggleDropdown(dropdown);
                });
            });
        }

        /**
         * Toggle a dropdown open/closed
         * @param {HTMLElement} dropdown - The dropdown element to toggle
         */
        toggleDropdown(dropdown) {
            const isOpen = dropdown.classList.contains('open');
            const slideMenu = dropdown.querySelector(':scope > .slide-menu');
            const arrow = dropdown.querySelector(
                ':scope > .side-menu__item .side-menu__angle, :scope > .side-menu__item .side-menu__angle_left');

            if (isOpen) {
                dropdown.classList.remove('open');
                if (slideMenu) slideMenu.style.display = 'none';
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            } else {
                dropdown.classList.add('open');
                if (slideMenu) slideMenu.style.display = 'block';
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            }
        }

        /**
         * Handle sidebar collapse/expand toggle
         */
        handleSidebarToggle() {
            this.state.isSidebarCollapsed = !this.state.isSidebarCollapsed;

            this.elements.sidebar.classList.toggle('collapsed');
            this.updateMainContentLayout();
            this.updateHeaderLayout();
            this.updateSidebarIcon();

            if (this.state.isSidebarCollapsed && this.state.isSubmenuOpen && !this.hasActiveSubmenuLink()) {
                this.closeSubmenu();
            }

            setTimeout(() => {
                if (this.state.isSubmenuOpen) {
                    this.updateSubmenuLayout();
                }
            }, 100);
        }

        /**
         * Handle reports button toggle
         * @param {Event} e - Click event
         */
        handleReportsToggle(e) {
            e.preventDefault();
            e.stopPropagation();

            this.state.isSubmenuOpen = !this.state.isSubmenuOpen;

            if (this.state.isSubmenuOpen) {
                this.openSubmenu();
                this.navigateToFirstReport();
            } else {
                this.closeSubmenu();
            }
        }

        /**
         * Navigate to the first report in the submenu
         */
        navigateToFirstReport() {
            const firstReportLink = this.elements.horizontalSubmenu.querySelector('.section-link');

            if (firstReportLink) {
                const href = firstReportLink.getAttribute('href');

                // Mark as active
                const allLinks = this.elements.horizontalSubmenu.querySelectorAll('.section-link');
                allLinks.forEach(link => link.classList.remove('active'));
                firstReportLink.classList.add('active');

                // Expand parent section
                const parentSectionId = this.getParentSectionId(firstReportLink);
                if (parentSectionId) {
                    this.autoExpandSection(parentSectionId);
                }

                // Navigate
                if (href && href !== '#') {
                    window.location.href = href;
                }
            }
        }

        /**
         * Handle section title toggle
         * @param {HTMLElement} title - Section title element
         */
        handleSectionToggle(title) {
            const sectionId = title.getAttribute('data-section');
            const sectionItems = document.getElementById(sectionId);
            const arrow = title.querySelector('.section-arrow');

            if (!sectionItems || !arrow) return;

            const isCollapsed = sectionItems.classList.contains('collapsed');

            if (isCollapsed) {
                this.expandSection(sectionItems, arrow, title);
            } else {
                this.collapseSection(sectionItems, arrow, title);
            }
        }

        /**
         * Handle clicks outside sidebar and submenu
         * @param {Event} e - Click event
         */
        handleOutsideClick(e) {
            if (!this.state.isSubmenuOpen) return;

            const isOutsideClick = !this.elements.sidebar.contains(e.target) &&
                !this.elements.horizontalSubmenu.contains(e.target);

            if (isOutsideClick && !this.hasActiveSubmenuLink()) {
                this.closeSubmenu();
            }
        }

        /**
         * Handle other menu item clicks
         */
        handleOtherMenuClick() {
            if (this.state.isSubmenuOpen && !this.hasActiveSubmenuLink()) {
                this.closeSubmenu();
            }
        }

        /**
         * Handle submenu link click
         * @param {HTMLElement} clickedLink - The clicked link
         */
        handleSubmenuLinkClick(clickedLink) {
            const submenuLinks = this.elements.horizontalSubmenu.querySelectorAll('.section-link');
            submenuLinks.forEach(link => link.classList.remove('active'));
            clickedLink.classList.add('active');
        }

        /**
         * Expand a section
         * @param {HTMLElement} sectionItems - Section items container
         * @param {HTMLElement} arrow - Arrow icon element
         * @param {HTMLElement} title - Section title element
         */
        expandSection(sectionItems, arrow, title) {
            sectionItems.classList.remove('collapsed');
            arrow.className = 'ri-arrow-down-s-line section-arrow';
            title.classList.remove('collapsed');
        }

        /**
         * Collapse a section
         * @param {HTMLElement} sectionItems - Section items container
         * @param {HTMLElement} arrow - Arrow icon element
         * @param {HTMLElement} title - Section title element
         */
        collapseSection(sectionItems, arrow, title) {
            sectionItems.classList.add('collapsed');
            arrow.className = 'ri-arrow-right-s-line section-arrow';
            title.classList.add('collapsed');
        }

        /**
         * Open the reports submenu
         */
        openSubmenu() {
            this.elements.horizontalSubmenu.classList.add('active');
            this.elements.reportsBtn.classList.add('active');
            this.updateSubmenuLayout();
            this.updateReportsArrow('down');

            setTimeout(() => {
                this.autoExpandAllSections();
            }, 100);
        }

        /**
         * Close the reports submenu
         */
        closeSubmenu() {
            this.state.isSubmenuOpen = false;
            this.elements.horizontalSubmenu.classList.remove('active');
            this.elements.reportsBtn.classList.remove('active');

            if (this.elements.mainContent) {
                this.elements.mainContent.classList.remove('submenu-open', 'sidebar-collapsed');
            }

            this.updateReportsArrow('right');
        }

        /**
         * Update submenu layout based on sidebar state
         */
        updateSubmenuLayout() {
            if (!this.elements.mainContent) return;

            this.elements.mainContent.classList.add('submenu-open');

            if (this.state.isSidebarCollapsed) {
                this.elements.mainContent.classList.add('sidebar-collapsed');
            } else {
                this.elements.mainContent.classList.remove('sidebar-collapsed');
            }
        }

        /**
         * Update main content layout
         */
        updateMainContentLayout() {
            if (this.elements.mainContent) {
                this.elements.mainContent.classList.toggle('expanded');
            }
        }

        /**
         * Update header layout based on sidebar state
         */
        updateHeaderLayout() {
            if (!this.elements.header) return;

            const marginLeft = this.state.isSidebarCollapsed ? '50px' : '170px';
            const maxWidth = this.state.isSidebarCollapsed ? 'calc(100% - 50px)' : 'calc(100% - 170px)';

            this.elements.header.style.marginLeft = marginLeft;
            this.elements.header.style.maxWidth = maxWidth;
        }

        /**
         * Update sidebar toggle icon and collapsed logo
         */
        updateSidebarIcon() {
            const icon = this.elements.sidebarToggle?.querySelector('i');

            if (icon) {
                icon.className = this.state.isSidebarCollapsed ? 'ri-menu-unfold-line' : 'ri-menu-line';
            }

            if (this.elements.collapsedLogo) {
                if (this.state.isSidebarCollapsed) {
                    this.elements.collapsedLogo.style.display = 'block';
                    this.elements.collapsedLogo.onclick = () => this.handleSidebarToggle();
                    setTimeout(() => {
                        this.elements.collapsedLogo.style.opacity = '1';
                    }, 100);
                } else {
                    this.elements.collapsedLogo.style.opacity = '0';
                    this.elements.collapsedLogo.onclick = null;
                    setTimeout(() => {
                        this.elements.collapsedLogo.style.display = 'none';
                    }, 300);
                }
            }
        }

        /**
         * Update reports arrow direction
         * @param {string} direction - 'up', 'down', 'left', or 'right'
         */
        updateReportsArrow(direction) {
            const arrow = this.elements.reportsBtn.querySelector('.side-menu__angle');
            if (arrow) {
                arrow.className = `ri-arrow-${direction}-s-line side-menu__angle`;
            }
        }

        /**
         * Check if there's an active submenu link
         * @returns {boolean} True if active link exists
         */
        hasActiveSubmenuLink() {
            return !!this.elements.horizontalSubmenu.querySelector('.section-link.active');
        }

        /**
         * Check and mark active submenu links based on current URL
         */
        checkActiveSubmenuLinks() {
            const submenuLinks = this.elements.horizontalSubmenu.querySelectorAll('.section-link');
            const currentLocation = this.getCurrentLocation();

            let hasActiveLink = false;
            let activeSectionId = null;

            submenuLinks.forEach(link => {
                const isActive = this.isLinkActive(link, currentLocation);

                if (isActive) {
                    link.classList.add('active');
                    hasActiveLink = true;
                    activeSectionId = this.getParentSectionId(link);
                } else {
                    link.classList.remove('active');
                }
            });

            if (activeSectionId) {
                this.autoExpandSection(activeSectionId);
            }

            if (hasActiveLink && !this.state.isSubmenuOpen) {
                this.state.isSubmenuOpen = true;
                this.openSubmenu();
            }

            return hasActiveLink;
        }

        /**
         * Check and expand sidebar dropdowns with active links
         */
        checkActiveSidebarLinks() {
            const allDropdowns = document.querySelectorAll('.has-sub');

            allDropdowns.forEach(dropdown => {
                const childLinks = dropdown.querySelectorAll(
                    '.slide-menu .side-menu__item, .slide-menu .section-link');
                let hasActiveChild = false;

                childLinks.forEach(link => {
                    if (link.classList.contains('active')) {
                        hasActiveChild = true;
                    }
                });

                if (hasActiveChild) {
                    dropdown.classList.add('open');

                    const slideMenu = dropdown.querySelector('.slide-menu');
                    if (slideMenu) {
                        slideMenu.style.display = 'block';
                    }

                    const arrow = dropdown.querySelector('.side-menu__angle');
                    if (arrow) {
                        arrow.style.transform = 'rotate(180deg)';
                    }
                }
            });
        }

        /**
         * Get current location object
         * @returns {Object} Current location data
         */
        getCurrentLocation() {
            return {
                pathname: window.location.pathname,
                search: window.location.search,
                fullUrl: window.location.pathname + window.location.search
            };
        }

        /**
         * Check if a link is active based on current URL
         * @param {HTMLElement} link - Link element to check
         * @param {Object} location - Current location object
         * @returns {boolean} True if link is active
         */
        isLinkActive(link, location) {
            const href = link.getAttribute('href');
            const linkText = link.textContent.trim();

            if (!href || href === '#') return false;

            // Extract pathname and query from href
            let hrefPath, hrefQuery = '';

            if (href.startsWith('http://') || href.startsWith('https://')) {
                try {
                    const url = new URL(href);
                    hrefPath = url.pathname;
                    hrefQuery = url.search;
                } catch (e) {
                    hrefPath = href.split('?')[0];
                    hrefQuery = href.includes('?') ? '?' + href.split('?')[1] : '';
                }
            } else {
                hrefPath = href.split('?')[0].split('#')[0];
                hrefQuery = href.includes('?') ? '?' + href.split('?')[1].split('#')[0] : '';
            }

            // Normalize paths (remove trailing slashes)
            const normalizedHref = hrefPath.replace(/\/$/, '');
            const normalizedPath = location.pathname.replace(/\/$/, '');

            // 1. EXACT PATH MATCH
            if (normalizedHref === normalizedPath) {
                // If there's a query string in href, check it matches too
                if (hrefQuery) {
                    return hrefQuery === location.search;
                }
                return true;
            }

            // 2. BATCH INVOICING SPECIAL CASE
            if (location.search.includes('batch_invoicing') &&
                linkText.includes('Batch Invoicing') &&
                normalizedPath === '/transactions') {
                return true;
            }

            return false;
        }

        /**
         * Get parent section ID for a link
         * @param {HTMLElement} link - Link element
         * @returns {string|null} Parent section ID
         */
        getParentSectionId(link) {
            const parentSection = link.closest('.section-items');
            return parentSection ? parentSection.id : null;
        }

        /**
         * Auto-expand a specific section
         * @param {string} sectionId - Section ID to expand
         */
        autoExpandSection(sectionId) {
            const section = document.getElementById(sectionId);
            const sectionToggle = document.querySelector(`[data-section="${sectionId}"]`);

            if (section && sectionToggle) {
                section.classList.remove('collapsed');
                sectionToggle.classList.remove('collapsed');

                const arrow = sectionToggle.querySelector('.section-arrow');
                if (arrow) {
                    arrow.className = 'ri-arrow-down-s-line section-arrow';
                }
            }
        }

        /**
         * Auto-expand all report sections
         */
        autoExpandAllSections() {
            const sectionIds = ['client-reports', 'financial-reports', 'management-reports'];

            sectionIds.forEach(sectionId => {
                this.autoExpandSection(sectionId);
            });
        }
    }

    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', () => {
        new NavigationManager();
    });
</script>

<style>
    /* ============================================
       SIDEBAR STYLES
       ============================================ */

    /* Keep closed dropdowns properly hidden */
    .has-sub.keep-closed:not(.open)>.slide-menu {
        display: none !important;
    }

    .has-sub.keep-closed:not(.open)>.side-menu__item .side-menu__angle {
        transform: rotate(0deg);
    }

    /* Open dropdowns */
    .has-sub.open>.slide-menu {
        display: block !important;
        max-height: none;
        opacity: 1;
    }

    .has-sub.open>.side-menu__item .side-menu__angle {
        transform: rotate(-90deg);
        transition: transform 0.3s ease;
    }

    /* Child links visibility */
    .has-sub.open .slide-menu .slide {
        display: block;
    }

    /* ============================================
       SIDEBAR HEADER
       ============================================ */

    .main-sidebar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }

    /* Collapsed logo - hidden by default */
    .collapsed-logo {
        display: none;
        text-align: center;
        padding: 0;
        opacity: 0;
        transition: opacity 0.3s ease;
        cursor: pointer;
    }

    .collapsed-logo-img {
        width: 51px;
        height: 30px;
        object-fit: contain;
    }

    /* Collapsed sidebar state */
    .app-sidebar.collapsed .main-sidebar-header {
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 8px 0;
        gap: 8px;
    }

    .app-sidebar.collapsed .header-logo {
        display: none;
    }

    .app-sidebar.collapsed .collapsed-logo {
        display: block;
        opacity: 1;

    }

    .app-sidebar.collapsed .sidebar-toggle {
        display: none;
        /* Hide the toggle button when collapsed */
    }

    /* ============================================
       SIDEBAR COLLAPSE
       ============================================ */

    .app-sidebar.collapsed {
        width: 50px;
    }

    .app-sidebar.collapsed .side-menu__label,
    .app-sidebar.collapsed .side-menu__angle {
        opacity: 0;
        width: 0;
        overflow: hidden;
    }

    .app-sidebar.collapsed .slide-menu {
        display: none;
    }

    /* ============================================
       SIDEBAR TOGGLE BUTTON
       ============================================ */

    .sidebar-toggle {
        background: none;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
        padding: 8px;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .sidebar-toggle:hover {
        background-color: #01677d;
    }

    /* ============================================
       MENU STYLING
       ============================================ */

    /* Main menu borders */
    .main-sidebar .main-menu>li {
        border-bottom: 1px solid #fff;
    }

    .main-sidebar .main-menu>li:last-child {
        border-bottom: none;
    }

    /* Submenu borders */
    .main-sidebar .slide-menu>li {
        border-bottom: 1px solid #fff !important;
    }

    /* Parent menu items with open dropdown */
    .has-sub.open>.side-menu__item {
        border-bottom: 1px solid #fff;
        margin-bottom: 0;
    }

    .main-sidebar .main-menu>li.has-sub.open {
        border-bottom: none !important;
    }

    /* Hover effects */
    .main-sidebar .side-menu__item:hover,
    .main-sidebar .side-menu__item:hover .side-menu__label,
    .main-sidebar .side-menu__item:hover .side-menu__icon {
        color: #fff !important;
    }

    .main-sidebar .has-sub>.side-menu__item:hover,
    .main-sidebar .has-sub>.side-menu__item:hover .side-menu__label,
    .main-sidebar .has-sub>.side-menu__item:hover i.side-menu__icon {
        color: #fff !important;
    }

    /* ============================================
       HORIZONTAL SUBMENU (REPORTS)
       ============================================ */

    .horizontal-submenu {
        position: fixed;
        top: 52px;
        left: 11rem;
        margin-left: 31px;
        width: 180px;
        height: calc(100vh - 52px);
        transform: translateX(-100%);
        transition: all 0.3s ease;
        overflow-y: auto;
        visibility: hidden;
        z-index: 999;
    }

    .horizontal-submenu.active {
        transform: translateX(0);
        visibility: visible;
    }

    /* Adjust position when sidebar is collapsed */
    .app-sidebar.collapsed~.horizontal-submenu {
        left: 50px;
    }

    /* ============================================
       SUBMENU SECTIONS
       ============================================ */

    .submenu-section {
        margin-top: 10px;
        border: #615f5f 1px solid;
        box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1), 2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .section-title {
        background: #F2F2F2;
        color: #000;
        padding: 3px 2px;
        font-weight: bold;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s ease;
        user-select: none;
    }

    .section-title:hover {
        background: #138496;
    }

    .section-title.collapsed {
        background: #17a2b8;
    }

    .section-arrow {
        font-size: 14px;
        transition: transform 0.3s ease;
    }

    /* Section items */
    .section-items {
        list-style: none;
        margin: 0;
        padding: 0;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .section-items.collapsed {
        max-height: 0;
        opacity: 0;
    }

    .section-item {
        border-bottom: 1px solid #f1f3f4;
    }

    .section-item:last-child {
        border-bottom: none;
    }

    /* Section links */
    .section-link {
        display: block;
        padding: 3.5px 2px;
        color: #495057;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 12px;
        border-left: 3px solid transparent;
    }

    .section-link:hover {
        background-color: #13667d;
        color: #fff !important;
        padding-left: 20px;
        border-left-color: #e4f2fa;
    }

    .section-link.active {
        color: #000;
        font-weight: 600;
        border-left-color: #000;
    }

    /* Reports button active state */
    #reportsBtn.active {
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    #reportsBtn.active .side-menu__icon,
    #reportsBtn.active .side-menu__label {
        color: #fff !important;
    }

    /* ============================================
       MAIN CONTENT ADJUSTMENTS
       ============================================ */

    .main-content {
        transition: all 0.3s ease;
    }

    .main-content.expanded {
        margin-left: 50px;
    }

    .main-content.submenu-open {
        margin-left: calc(11rem + 231px);
    }

    .main-content.submenu-open.sidebar-collapsed {
        margin-left: calc(2rem + 235px);
    }
</style>
