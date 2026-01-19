@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                   

                    <div class="card custom-card">
                        <div class="card-header mb-3">
                             <h4 class="page-title">Profit & Loss Statement</h4>
                        </div>
                        <div class="card-body">
                            <!-- Filter Form -->
                            <form method="GET" id="filter-form">
                                <div class="mb-4 row">
                                    <div class="col-md-2">
                                        <label for="from_date">From Date:</label>
                                        <input type="date" id="from_date" name="from_date" class="form-control"
                                            value="{{ request('from_date', $fromDate) }}">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="to_date">To Date:</label>
                                        <input type="date" id="to_date" name="to_date" class="form-control"
                                            value="{{ request('to_date', $toDate) }}">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="comparison-years">Compare Years:</label>
                                        <div class="dropdown w-100">
                                            <button class="form-control text-start dropdown-toggle" type="button"
                                                id="yearDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                Select Years
                                            </button>
                                            <ul class="dropdown-menu p-2 w-100" aria-labelledby="yearDropdown">
                                                @for ($year = $currentFinancialYear; $year >= $currentFinancialYear - 6; $year--)
                                                    <li>
                                                        <div class="form-check">
                                                            <input class="form-check-input year-toggle" type="checkbox"
                                                                id="year-{{ $year }}"
                                                                data-year="{{ $year }}"
                                                                {{ $year == $currentFinancialYear ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="year-{{ $year }}">FY{{ $year - 1 }}/{{ $year }}</label>
                                                        </div>
                                                    </li>
                                                @endfor
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Apply Filters Button -->
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="submit" id="apply-filters" class="btn teal-custom">
                                            Apply Filters
                                        </button>
                                    </div>

                                    <!-- Spacer column -->
                                    <div class="col-md-2"></div>

                                    <!-- PDF and Excel buttons -->
                                    <div class="col-md-2 d-flex align-items-end justify-content-end">
                                        <x-download-dropdown pdf-id="download-pdf" csv-id="customCSVId" />
                                    </div>
                                </div>
                            </form>

                            <!-- Company Info Section -->
                            <div class="mb-3">
                                <h4 class="mb-1">Profit & Loss Statement</h4>
                                <p class="mb-1">Energy Saviour Ltd</p>
                                <p class="mb-0" id="pl-period">
                                    For the period from {{ \Carbon\Carbon::parse($fromDate)->format('d F Y') }} to
                                    {{ \Carbon\Carbon::parse($toDate)->format('d F Y') }}
                                </p>
                            </div>

                            <!-- Scrollable Profit & Loss Table using ScrollableTable Component -->
                            <div class="table-sticky-wrapper" style="max-height: calc(100vh - 280px);">
                                <table id="profit-loss-table" class="table table-bordered table-hover mb-0">
                                    <thead class="table-primary">
                                        <tr>
                                            <x-table-search-header column="ledger_ref" label="Ledger Ref"
                                                type="search" />

                                            <x-table-search-header column="account_ref" label="Account Ref"
                                                type="search" />

                                            <th class="year-col year-{{ $currentFinancialYear }}">
                                                FY{{ $currentFinancialYear - 1 }}/{{ $currentFinancialYear }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="profit-loss-table-body">
                                        @php
                                            $totalIncome = 0;
                                            $totalExpenses = 0;
                                        @endphp

                                        @foreach ($groupedAccounts as $ledgerRef => $rows)
                                            @php
                                                $isIncome =
                                                    stripos($ledgerRef, 'income') !== false ||
                                                    stripos($ledgerRef, 'revenue') !== false ||
                                                    stripos($ledgerRef, 'sales') !== false;
                                                $subtotal = $rows->sum('balance');

                                                if ($isIncome) {
                                                    $totalIncome += $subtotal;
                                                } else {
                                                    $totalExpenses += $subtotal;
                                                }
                                            @endphp

                                            <!-- Ledger Reference Header Row -->
                                            <tr
                                                class="ledger-header {{ $isIncome ? 'income-header' : 'expense-header' }}">
                                                <td colspan="2" class="fw-bold bg-light text-start" data-column="ledger_ref">
                                                    {{ $ledgerRef }}
                                                </td>
                                                <td
                                                    class="year-col year-{{ $currentFinancialYear }} bg-light text-end fw-bold">
                                                    {{ number_format($subtotal, 2) }}
                                                </td>
                                            </tr>

                                            <!-- Account Detail Rows -->
                                            @foreach ($rows as $account)
                                                <tr class="account-detail">
                                                    <td class="ps-3" data-column="ledger_ref"></td>
                                                    <td class="ps-3 text-start" data-column="account_ref">
                                                        {{ $account->account_ref }}</td>
                                                    <td class="year-col year-{{ $currentFinancialYear }} text-end">
                                                        <span
                                                            class="{{ ($account->balance ?? 0) < 0 ? 'text-danger' : '' }}">
                                                            {{ number_format($account->balance ?? 0, 2) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-3">
                                {{ $accountsPaginator->appends(request()->query())->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            // Calculate correct P&L period based on selected dates
            function getPLPeriod() {
                const fromDate = $('#from_date').val();
                const toDate = $('#to_date').val();

                if (fromDate && toDate) {
                    const from = new Date(fromDate);
                    const to = new Date(toDate);

                    const fromFormatted = from.toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });
                    const toFormatted = to.toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });

                    return `For the period from ${fromFormatted} to ${toFormatted}`;
                }

                return 'For the current period';
            }

            // Update P&L period when dates change
            $('#from_date, #to_date').on('change', function() {
                $('#pl-period').text(getPLPeriod());
            });

            // Set initial P&L period
            $('#pl-period').text(getPLPeriod());

            // Handle year comparison show/hide functionality
            $('.year-toggle').on('change', function() {
                const year = $(this).data('year');
                const yearClass = 'year-' + year;
                const isChecked = $(this).is(':checked');

                if (isChecked) {
                    if ($('.' + yearClass).length === 0) {
                        addYearColumn(year);
                    } else {
                        $('.' + yearClass).show();
                    }
                } else {
                    $('.' + yearClass).hide();
                }

                updateYearDropdownText();
            });

            // Form submission handling
            $('#filter-form').on('submit', function(e) {
                const fromDate = $('#from_date').val();
                const toDate = $('#to_date').val();

                // Validate date range
                if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
                    e.preventDefault();
                    alert('From date cannot be greater than to date.');
                    return false;
                }

                // If it's an export request, allow form submission
                if ($(document.activeElement).attr('name') === 'export') {
                    return true;
                }

                // For regular filter application, submit the form
                return true;
            });

            // Apply filters button
            $('#apply-filters').on('click', function(e) {
                e.preventDefault();

                const fromDate = $('#from_date').val();
                const toDate = $('#to_date').val();

                // Validate date range
                if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
                    alert('From date cannot be greater than to date.');
                    return;
                }

                // Get selected years
                const selectedYears = [];
                $('.year-toggle:checked').each(function() {
                    selectedYears.push($(this).data('year'));
                });

                console.log('Applying filters...');
                console.log('From Date:', fromDate);
                console.log('To Date:', toDate);
                console.log('Selected Years:', selectedYears);

                // Submit the form
                $('#filter-form').submit();
            });

            // Add new year column to the table
            function addYearColumn(year) {
                const headerText = 'FY' + (year - 1) + '/' + year;
                const yearClass = 'year-' + year;

                // Add header
                $('#profit-loss-table thead tr').append('<th class="year-col ' + yearClass + '">' + headerText +
                    '</th>');

                // Add cells to header rows (ledger headers)
                $('#profit-loss-table tbody tr.ledger-header').each(function() {
                    $(this).append('<td class="year-col ' + yearClass + ' bg-light"></td>');
                });

                // Add cells to detail rows
                $('#profit-loss-table tbody tr.account-detail').each(function() {
                    $(this).append('<td class="year-col ' + yearClass + ' text-end">0.00</td>');
                });

                // Add cells to subtotal rows
                $('#profit-loss-table tbody tr.ledger-subtotal').each(function() {
                    $(this).append('<td class="year-col ' + yearClass +
                        ' text-end fw-bold border-top">0.00</td>');
                });

                // Add cells to total rows
                $('#profit-loss-table tbody tr.total-income, #profit-loss-table tbody tr.total-expenses, #profit-loss-table tbody tr.net-profit')
                    .each(function() {
                        $(this).append('<td class="year-col ' + yearClass + ' text-end fw-bold">0.00</td>');
                    });
            }

            // Update year dropdown display text
            function updateYearDropdownText() {
                const selectedYears = $('.year-toggle:checked').length;
                const totalYears = $('.year-toggle').length;
                $('#yearDropdown').text(selectedYears + '/' + totalYears + ' Years Selected');
            }

            // Prevent dropdown from closing when clicking inside
            $('.dropdown-menu').on('click', function(e) {
                e.stopPropagation();
            });

            // Initialize year dropdown text
            updateYearDropdownText();

            // Optional: Apply negative number styling
            ScrollableTable.colorNegatives('#profit-loss-table');
        });
    </script>

   <style>
    /* ===========================
   PROFIT & LOSS TABLE STYLES
   =========================== */

/* Table Wrapper */
.table-sticky-wrapper {
    position: relative;
    border: 1px solid #dee2e6;
    background: #fff;
    overflow: hidden; /* Hide overflow on wrapper */
}

/* Table Base */
#profit-loss-table {
    width: 100%;
    margin-bottom: 0;
    font-size: 12px;
    border-collapse: collapse;
}

/* Fixed Header */
#profit-loss-table thead {
    display: table;
    width: calc(100% - 0px); /* Account for scrollbar */
    table-layout: fixed;
}

#profit-loss-table thead th {
    background: #cce7ff !important;
    border: 1px solid #dee2e6 !important;
    padding: 8px;
    font-size: 11px;
    white-space: nowrap;
    text-align: center;
    font-weight: 500;
    position: sticky;
    top: 0;
    z-index: 10;
}

/* Scrollable Body */
#profit-loss-table tbody {
    display: block;
    width: 100%;
    max-height: calc(100vh - 415px);
    overflow-y: auto;
    overflow-x: hidden;
}

#profit-loss-table tbody tr {
    display: table;
    width: 100%;
    table-layout: fixed;
}

/* Column Widths - Adjust these as needed */
#profit-loss-table th:nth-child(1),
#profit-loss-table td:nth-child(1) {
    width: 20%; /* Ledger Ref column */
}

#profit-loss-table th:nth-child(2),
#profit-loss-table td:nth-child(2) {
    width: 20%; /* Account Ref column */
}

#profit-loss-table th:nth-child(3),
#profit-loss-table td:nth-child(3),
#profit-loss-table th.year-col,
#profit-loss-table td.year-col {
    width: 40%; /* Year columns */
}

/* Cell Styling */
#profit-loss-table td {
    padding: 6px 8px;
    font-size: 11px;
    border: 1px solid #dee2e6;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* Custom Scrollbar */
#profit-loss-table tbody::-webkit-scrollbar {
    width: 8px;
}

#profit-loss-table tbody::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

#profit-loss-table tbody::-webkit-scrollbar-thumb {
    background: #c0c1c2;
    border-radius: 4px;
}

#profit-loss-table tbody::-webkit-scrollbar-thumb:hover {
    background: #a8a9aa;
}

/* Ledger header styling */
.ledger-header {
    background-color: #e9ecef !important;
    font-weight: bold;
}

.ledger-header td {
    /* border-top: 2px solid #6c757d !important; */
    padding: 12px 8px !important;
    width: 20%  !important;
}   

/* Income/Expense specific headers */
.income-header {
    background-color: #d4edda !important;
}

.expense-header {
    background-color: #fff3cd !important;
}

/* Account detail styling */
.account-detail td:first-child {
    width: 5%;
}

.account-detail td:nth-child(2) {
    padding-left: 30px;
}

/* Zebra striping */
#profit-loss-table tbody tr.account-detail:nth-of-type(odd) {
    background-color: rgba(0, 0, 0, 0.02);
}

/* Hover effect */
#profit-loss-table tbody tr.account-detail:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

/* Text alignment */
.text-end {
    text-align: right !important;
}

.text-start {
    text-align: left !important;
}

.text-center {
    text-align: center !important;
}

/* Color utilities */
.text-danger {
    color: #dc3545 !important;
}

.text-success {
    color: #198754 !important;
}

.bg-light {
    background-color: #f8f9fa !important;
}

/* Dropdown styling */
.dropdown-menu {
    max-height: 300px;
    overflow-y: auto;
}

.form-check {
    margin-bottom: 8px;
}

.dropdown-toggle:focus {
    box-shadow: none;
    border-color: #ced4da;
}

/* Subtotal styling */
.ledger-subtotal {
    background-color: #f8f9fa !important;
}

.ledger-subtotal td {
    border-top: 1px solid #dee2e6 !important;
    font-weight: bold;
}

/* Total rows styling */
.total-income,
.total-expenses,
.net-profit {
    font-size: 1.1em;
}

.total-income td {
    border-top: 3px solid #198754 !important;
}

.total-expenses td {
    border-top: 3px solid #ffc107 !important;
}

.net-profit td {
    border-top: 4px double #0d6efd !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    #profit-loss-table tbody {
        max-height: calc(100vh - 280px);
    }
}
   </style>
@endsection