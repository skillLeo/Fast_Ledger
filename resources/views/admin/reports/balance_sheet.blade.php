    @extends('admin.layout.app')

    @section('content')
        <div class="main-content app-content">
            <div class="container-fluid">
                <!-- Page Header -->
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card custom-card">
                            <div class="card-header mb-3">
                                <h4 class="page-title">Balance Sheet</h4>
                            </div>

                            <div class="card-body">
                                <!-- Filter Form -->
                                <form method="GET" id="filter-form">
                                    <div class="mb-4 row">
                                        <div class="col-md-2">
                                            <label for="from_date">From Date:</label>
                                            <input type="date" id="from_date" name="from_date" class="form-control"
                                                value="{{ request('from_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}">
                                        </div>

                                        <div class="col-md-2">
                                            <label for="comparison-years">Compare Years:</label>
                                            <div class="dropdown w-100">
                                                <button class="form-control text-start dropdown-toggle p-2" type="button"
                                                    id="yearDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Select Years
                                                </button>
                                                <ul class="dropdown-menu p-2 w-100" aria-labelledby="yearDropdown">
                                                    @foreach ([2026, 2025, 2024, 2023, 2022, 2021, 2020] as $year)
                                                        <li>
                                                            <div class="form-check">
                                                                <input class="form-check-input year-toggle" type="checkbox"
                                                                    id="year-{{ $year }}"
                                                                    data-year="{{ $year }}"
                                                                    {{ $year == 2026 ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="year-{{ $year }}">
                                                                    MAR/{{ $year }}
                                                                </label>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" id="apply-filters" class="btn teal-custom">
                                                Apply Filters
                                            </button>
                                        </div>

                                        <div class="col-md-2"></div>

                                        <div class="col-md-4 d-flex align-items-end justify-content-end">
                                            <x-download-dropdown pdf-id="download-pdf" csv-id="customCSVId" />
                                        </div>
                                    </div>
                                </form>


                                <!-- Company Info Section -->

                                <h4 class="mb-1">Balance Sheet</h4>
                                <p class="mb-1">Energy Saviour Ltd</p>
                                <p class="mb-0" id="balance-sheet-date">As at 31 March 2026</p>


                                <!-- Balance Sheet Table -->
                                <div class="table-sticky-wrapper">
                                    <div class="table-responsive" style="max-height: calc(100vh - 280px); overflow-x: auto;">
                                        <table id="balance-sheet-table"
                                            class="table table-bordered table-hover mb-0 resizable-draggable-table">
                                            <thead class="table-primary">
                                                <tr id="table-header-row">
                                                    <x-table-search-header column="ledger-ref" label="Ledger Ref" type="search"
                                                        class="col-ledger-ref" />
                                                    <x-table-search-header column="account-ref" label="Account Ref"
                                                        type="search" class="col-account-ref" />

                                                    {{-- Only show MAR/2026 by default --}}
                                                    <x-table-search-header column="year-2026" label="MAR/2026" type="search"
                                                        class="year-col year-2026" />
                                                </tr>
                                            </thead>
                                            <tbody id="balance-sheet-table-body">
                                                @foreach ($groupedAccounts as $ledgerRef => $accounts)
                                                    @php $subtotal = $accounts->sum('balance'); @endphp

                                                    <tr class="ledger-header">
                                                        <td colspan="2" class="fw-bold bg-light ledger-name-cell"
                                                            data-column="ledger-ref">
                                                            {{ $ledgerRef }}
                                                        </td>
                                                        <td class="year-col year-2026 bg-light text-end fw-bold"
                                                            data-column="year-2026">
                                                            @if ($subtotal != 0)
                                                                <span class="{{ $subtotal < 0 ? 'text-danger' : '' }}">
                                                                    {{ $subtotal < 0 ? '(' . number_format(abs($subtotal), 2) . ')' : number_format($subtotal, 2) }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>

                                                    @foreach ($accounts as $account)
                                                        <tr class="account-detail">
                                                            <td class="ps-3" data-column="ledger-ref"></td>
                                                            <td class="ps-3" data-column="account-ref">
                                                                {{ $account->account_ref }}
                                                            </td>
                                                            <td class="year-col year-2026 text-end" data-column="year-2026">
                                                                @php $val = $account->balance ?? 0; @endphp
                                                                <span class="{{ $val < 0 ? 'text-danger fw-semibold' : '' }}">
                                                                    {{ $val < 0 ? '(' . number_format(abs($val), 2) . ')' : number_format($val, 2) }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('scripts')
        {{-- ============================================================
        RESIZABLE & DRAGGABLE TABLE INITIALIZATION
    ============================================================ --}}
        <script>
            /**
             * Global variable to track ResizableDraggableTable instance
             * Prevents multiple initializations
             */
            let resizableTableInstance = null;

            /**
             * Initialize ResizableDraggableTable with proper checks
             * Handles width calculation and DOM readiness
             */
            function initializeResizableTable() {
                const tableElement = document.querySelector('#balance-sheet-table');

                // Validation: Check if table exists
                if (!tableElement) {
                    console.warn('ResizableTable: Table element not found');
                    return;
                }

                // Validation: Check if table has content
                const tbody = tableElement.querySelector('tbody');
                if (!tbody || tbody.children.length === 0) {
                    console.warn('ResizableTable: No tbody or empty tbody found, skipping initialization');
                    return;
                }

                // Validation: Check if ResizableDraggableTable class is loaded
                if (typeof ResizableDraggableTable === 'undefined') {
                    console.error('ResizableTable: ResizableDraggableTable class not found');
                    return;
                }

                // Log reinitialization
                if (resizableTableInstance) {
                    console.log('ResizableTable: Reinitializing...');
                }

                // Set table to full width
                tableElement.style.width = '100%';
                tableElement.style.tableLayout = 'fixed';

                // Force browser reflow
                void tableElement.offsetWidth;

                // Initialize the resizable/draggable functionality
                resizableTableInstance = new ResizableDraggableTable(tableElement);
                console.log('ResizableTable: Initialized successfully');
            }
        </script>

        {{-- ============================================================
        MAIN APPLICATION LOGIC
    ============================================================ --}}
        <script>
            $(document).ready(function() {

                // ========================================
                // INITIALIZATION & CLEANUP
                // ========================================

                /**
                 * Clean up any stored widths from previous sessions
                 * But preserve ledger header alignment
                 */
                function cleanupStoredData() {
                    // Remove styles from regular cells, but not from ledger headers with colspan
                    $('#balance-sheet-table td:not(.ledger-header td[colspan]), #balance-sheet-table th').removeAttr('style');
                    localStorage.removeItem('balance-sheet-table-widths');
                    sessionStorage.removeItem('balance-sheet-table-widths');
                }

                // Run cleanup on page load
                cleanupStoredData();

                // ========================================
                // UTILITY FUNCTIONS
                // ========================================

                /**
                 * Apply red color styling to negative values in year columns
                 */
                function colorNegatives() {
                    $('#balance-sheet-table td.year-col').each(function() {
                        const text = $(this).text().trim();

                        if (text.startsWith('(') || text.startsWith('-')) {
                            $(this).addClass('text-danger fw-semibold');
                        } else {
                            $(this).removeClass('text-danger fw-semibold');
                        }
                    });
                }

                /**
                 * Calculate balance sheet date based on current date
                 * @returns {object} Object with displayDate and defaultYear
                 */
                function getBalanceSheetDate() {
                    const currentDate = new Date();
                    const currentYear = currentDate.getFullYear();
                    const marchEnd = new Date(currentYear, 2, 31); // March 31st

                    let balanceSheetYear;

                    // If current date is after March 31, use next year
                    if (currentDate > marchEnd) {
                        balanceSheetYear = currentYear + 1;
                    } else {
                        balanceSheetYear = currentYear;
                    }

                    return {
                        displayDate: `As at 31 March ${balanceSheetYear}`,
                        defaultYear: balanceSheetYear
                    };
                }

                /**
                 * Update the year dropdown button text with selection count
                 */
                function updateYearDropdownText() {
                    const selectedYears = $('.year-toggle:checked').length;
                    const totalYears = $('.year-toggle').length;

                    $('#yearDropdown').text(`${selectedYears}/${totalYears} Years Selected`);
                }

                // ========================================
                // DATE INITIALIZATION
                // ========================================

                /**
                 * Set balance sheet date based on current date
                 */
                const balanceSheetInfo = getBalanceSheetDate();
                $('#balance-sheet-date').text(balanceSheetInfo.displayDate);

                // ========================================
                // YEAR COLUMN MANAGEMENT
                // ========================================

                /**
                 * Check if a year column already exists in the table
                 * @param {number} year - The year to check
                 * @returns {boolean} True if column exists
                 */
                function yearColumnExists(year) {
                    return $(`.year-${year}`).length > 0;
                }

                /**
                 * Add a new year column to the table dynamically
                 * @param {number} year - The year to add
                 */
                function addYearColumn(year) {
                    const headerText = `MAR/${year}`;
                    const yearClass = `year-${year}`;
                    const columnName = `year-${year}`;

                    // Add header column
                    const headerHtml = `
                        <th class="year-col ${yearClass}" data-column="${columnName}">
                            <div class="d-flex">
                                <div class="filter-wrapper position-relative">
                                    <span class="d-inline">${headerText}</span>
                                    <input type="text" class="form-control form-control-sm d-none" 
                                        placeholder="Search ${headerText}" />
                                </div>
                                <div>
                                    <i class="fas fa-search pointer table-header-icon filter-icon position-absolute top-0 end-0 d-flex"></i>
                                </div>
                            </div>
                        </th>
                    `;
                    $('#table-header-row').append(headerHtml);

                    // Add cells to ledger header rows
                    $('#balance-sheet-table tbody tr.ledger-header').each(function() {
                        $(this).append(
                            `<td class="year-col ${yearClass} bg-light text-end fw-bold" data-column="${columnName}">0.00</td>`
                        );
                    });

                    // Add cells to account detail rows
                    $('#balance-sheet-table tbody tr.account-detail').each(function() {
                        $(this).append(
                            `<td class="year-col ${yearClass} text-end" data-column="${columnName}">0.00</td>`
                        );
                    });

                    console.log(`Added column for year ${year}`);
                }

                /**
                 * Remove a year column from the table
                 * @param {number} year - The year to remove
                 */
                function removeYearColumn(year) {
                    const yearClass = `.year-${year}`;
                    $(yearClass).remove();
                    console.log(`Removed column for year ${year}`);
                }

                // ========================================
                // EVENT HANDLERS
                // ========================================

                /**
                 * Handle year toggle checkbox changes
                 */
                $('.year-toggle').on('change', function() {
                    const year = $(this).data('year');
                    const isChecked = $(this).is(':checked');

                    if (isChecked) {
                        // User checked the box - add column if it doesn't exist
                        if (!yearColumnExists(year)) {
                            addYearColumn(year);
                            colorNegatives();

                            // Reinitialize resizable table after adding column
                            setTimeout(initializeResizableTable, 200);
                        }
                    } else {
                        // User unchecked the box - remove column if it exists
                        if (yearColumnExists(year)) {
                            removeYearColumn(year);

                            // Reinitialize resizable table after removing column
                            setTimeout(initializeResizableTable, 200);
                        }
                    }

                    updateYearDropdownText();
                });

                /**
                 * Handle apply filters button click
                 */
                $('#apply-filters').on('click', function() {
                    const fromDate = $('#from_date').val();
                    const selectedYears = [];

                    // Collect all selected years
                    $('.year-toggle:checked').each(function() {
                        selectedYears.push($(this).data('year'));
                    });

                    console.log('From Date:', fromDate);
                    console.log('Selected Years:', selectedYears);

                    // TODO: Implement AJAX call when backend route is ready
                    alert('Filters Applied!\nFrom Date: ' + fromDate + '\nSelected Years: ' + selectedYears
                        .join(', '));
                });
                
                /**
                 * Prevent dropdown from closing when clicking inside
                 */
                $('.dropdown-menu').on('click', function(e) {
                    e.stopPropagation();
                });

                // ========================================
                // INITIAL CONFIGURATION
                // ========================================

                /**
                 * Set initial state - only 2026 checked, update dropdown text
                 */
                updateYearDropdownText();
                colorNegatives();

                /**
                 * Initialize resizable table after page fully loads
                 */
                setTimeout(initializeResizableTable, 500);
            });
        </script>

        {{-- ============================================================
        CUSTOM STYLES
    ============================================================ --}}
        <style>
            /* ========================================
            TABLE CONTAINER
            ======================================== */
            .table-sticky-wrapper {
                width: 100%;
                overflow: visible;
            }

            .table-responsive {
                width: 100%;
            }

            /* ========================================
            TABLE LAYOUT - FULL WIDTH
            ======================================== */
            #balance-sheet-table {
                width: 100% !important;
                table-layout: fixed !important;
                border-collapse: collapse;
            }

            /* ========================================
            COLUMN WIDTHS - Percentage Based for Full Width
            ======================================== */
            
            /* First Column - Ledger Ref (30%) */
            #balance-sheet-table thead th:nth-child(1),
            #balance-sheet-table tbody td:nth-child(1) {
                width: 30% !important;
            }

            /* Second Column - Account Ref (45%) */
            #balance-sheet-table thead th:nth-child(2),
            #balance-sheet-table tbody td:nth-child(2) {
                width: 45% !important;
            }

            /* Year Columns (25% divided by number of year columns) */
            .year-col {
                width: 25% !important;
            }

            /* When multiple year columns are present, adjust dynamically via JS if needed */
            
            /* ========================================
            LEDGER HEADER SPECIFIC STYLING
            ======================================== */
            
            /* Ledger header cell with colspan */
            .ledger-header td.ledger-name-cell[colspan="2"] {
                text-align: left !important;
                width: 75% !important; /* 30% + 45% */
            }

            .ledger-header {
                background-color: #e9ecef;
                font-weight: bold;
            }

            .ledger-header td {
                border-top: 2px solid #6c757d !important;
                padding: 12px 8px !important;
            }

            /* ========================================
            TEXT ALIGNMENT
            ======================================== */
            .text-end {
                text-align: right !important;
            }

            /* ========================================
            BACKGROUND COLORS
            ======================================== */
            .bg-light {
                background-color: #f8f9fa !important;
            }

            .table-primary {
                background-color: #cce7ff !important;
            }

            .table-primary th {
                position: sticky;
                top: 0;
                z-index: 10;
            }

            /* ========================================
            DROPDOWN STYLING
            ======================================== */
            .dropdown-menu {
                max-height: 300px;
                overflow-y: auto;
            }

            .form-check {
                margin-bottom: 8px;
            }

            /* ========================================
            ADDITIONAL TABLE STYLING
            ======================================== */
            
            /* Account detail rows padding */
            .account-detail td {
                padding: 8px !important;
            }

            /* Ensure negative values are visible */
            .text-danger {
                color: #dc3545 !important;
            }

            .fw-semibold {
                font-weight: 600 !important;
            }

            .fw-bold {
                font-weight: 700 !important;
            }

            /* Table borders */
            #balance-sheet-table td,
            #balance-sheet-table th {
                border: 1px solid #dee2e6;
            }

            /* Ensure no horizontal scrollbar on container */
            .card-body {
                overflow-x: hidden;
            }
        </style>
    @endsection