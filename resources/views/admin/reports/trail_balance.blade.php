@extends('admin.layout.app')

 


@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header mb-3">
                            <h4 class="page-title">Trial Balance</h4>
                        </div>
                        
                        <div class="card-body p-4">
                            <!-- Filters Form -->
                            <div class="w-100">
                                <form method="GET" id="filter-form">
                                    <div class="mb-4 d-flex justify-content-between align-items-end">
                                        <div class="d-flex gap-3">
                                            <div style="width: 100px;">
                                                <label for="from_date">From Date:</label>
                                                <input type="date" id="from_date" name="from_date" class="form-control"
                                                    value="{{ request('from_date', $fromDate) }}">
                                            </div>

                                            <div style="width: 100px;">
                                                <label for="to_date">To Date:</label>
                                                <input type="date" id="to_date" name="to_date" class="form-control"
                                                    value="{{ request('to_date', $toDate) }}">
                                            </div>

                                            <div style="width: 130px;">
                                                <label for="columns">Show Columns:</label>
                                                <div class="dropdown">
                                                    <button class="form-control text-start dropdown-toggle p-2"
                                                        type="button" id="columnsDropdown" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <span id="columnsDisplayText">7/7 Columns</span>
                                                    </button>
                                                    <ul class="dropdown-menu p-2" aria-labelledby="columnsDropdown">
                                                        <li>
                                                            <div class="form-check">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" id="col-code" data-column="code" checked>
                                                                <label class="form-check-label" for="col-code">Code</label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="form-check">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" id="col-ledger-ref"
                                                                    data-column="ledger-ref" checked>
                                                                <label class="form-check-label" for="col-ledger-ref">Ledger Ref</label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="form-check">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" id="col-account-ref"
                                                                    data-column="account-ref" checked>
                                                                <label class="form-check-label" for="col-account-ref">Account Ref</label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="form-check">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" id="col-account-type"
                                                                    data-column="account-type" checked>
                                                                <label class="form-check-label" for="col-account-type">Account Type</label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="form-check">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" id="col-ledger-balance"
                                                                    data-column="ledger-balance" checked>
                                                                <label class="form-check-label" for="col-ledger-balance">Ledger Balance</label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="form-check">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" id="col-debit-ytd"
                                                                    data-column="debit-ytd" checked>
                                                                <label class="form-check-label" for="col-debit-ytd">Debit(YTD)</label>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="form-check">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" id="col-credit-ytd"
                                                                    data-column="credit-ytd" checked>
                                                                <label class="form-check-label" for="col-credit-ytd">Credit(YTD)</label>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div style="width: 130px;">
                                                <label for="comparison-years">Compare Years:</label>
                                                <div class="dropdown">
                                                    <button class="form-control text-start dropdown-toggle p-2"
                                                        type="button" id="yearDropdown" data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <span id="yearsDisplayText">0/{{ count($availableYears) }} Years</span>
                                                    </button>
                                                    <ul class="dropdown-menu p-2" aria-labelledby="yearDropdown">
                                                        @foreach ($availableYears as $yr)
                                                            <li>
                                                                <div class="form-check">
                                                                    <input class="form-check-input year-toggle"
                                                                        type="checkbox" id="year-{{ $yr }}"
                                                                        data-year="{{ $yr }}">
                                                                    <label class="form-check-label"
                                                                        for="year-{{ $yr }}">MAR/{{ $yr }}</label>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-end gap-2 px-2">
                                            <div>
                                                <button type="button" id="apply-filters" class="btn teal-custom">
                                                    Apply Filters
                                                </button>
                                            </div>
                                            <x-download-dropdown pdf-id="print-pdf" csv-id="print-csv" />
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Scrollable Table Wrapper -->
                            <div class="table-sticky-wrapper">
                                <div style="max-height: calc(100vh - 280px);">
                                <table id="trial-balance-table" class="table table-truncate table-hover mb-0">
                                    <thead class="table-primary">
                                        <tr>
                                            <x-table-search-header column="code" label="Code" type="search" class="col-code" />
                                            <x-table-search-header column="ledger-ref" label="Ledger Ref" type="search" class="col-ledger-ref" />
                                            <x-table-search-header column="account-ref" label="Account Ref" type="search" class="col-account-ref" />
                                            <x-table-search-header column="account-type" label="Account Type" type="dropdown" class="col-account-type" :options="['P&L' => 'P&L', 'BS' => 'BS']" />
                                            <x-table-search-header column="ledger-balance" label="Ledger Balance" type="search" class="col-ledger-balance" />
                                            <x-table-search-header column="debit-ytd" label="Debit(YTD)" type="search" class="col-debit-ytd" />
                                            <x-table-search-header column="credit-ytd" label="Credit(YTD)" type="search" class="col-credit-ytd" />
                                        </tr>
                                    </thead>

                                    @php
                                        // PHP Helper function for formatting
                                        $fmt = fn($v) => $v < 0 ? '(' . number_format(abs($v), 2) . ')' : number_format($v, 2);
                                    @endphp

                                    <tbody id="trial-balance-table-body">
                                        @foreach ($groupedAccounts as $ledgerRef => $accounts)
                                            @php $total = (float) ($groupTotals[$ledgerRef] ?? 0); @endphp

                                            <tr class="ledger-header">
                                                <td class="col-code"></td>
                                                <td class="col-ledger-ref fw-bold">{{ $ledgerRef }}</td>
                                                <td class="col-account-ref"></td>
                                                <td class="col-account-type"></td>
                                                <td class="col-ledger-balance text-end">
                                                    <span class="{{ $total < 0 ? 'text-danger fw-semibold' : '' }}">
                                                        {{ $fmt($total) }}
                                                    </span>
                                                </td>
                                                <td class="col-debit-ytd"></td>
                                                <td class="col-credit-ytd"></td>
                                            </tr>

                                            @foreach ($accounts as $acc)
                                                @php
                                                    $lb = (float) ($acc->ledger_balance ?? 0);
                                                    $normal = strtoupper($acc->normal_balance ?? 'DR');
                                                    
                                                    if ($normal === 'DR') {
                                                        $debitNum = $lb >= 0 ? $lb : 0;
                                                        $creditNum = $lb < 0 ? abs($lb) : 0;
                                                    } else {
                                                        $creditNum = $lb >= 0 ? $lb : 0;
                                                        $debitNum = $lb < 0 ? abs($lb) : 0;
                                                    }
                                                @endphp

                                                <tr class="account-detail" data-comparatives='@json($acc->comparatives)'>
                                                    <td class="col-code">{{ $acc->id }}</td>
                                                    <td class="col-ledger-ref">{{ $acc->ledger_ref }}</td>
                                                    <td class="col-account-ref">{{ $acc->account_ref }}</td>
                                                    <td class="col-account-type">{{ $acc->pl_bs }}</td>
                                                    <td class="col-ledger-balance"></td>
                                                    <td class="col-debit-ytd text-end">{{ number_format($debitNum, 2) }}</td>
                                                    <td class="col-credit-ytd text-end">{{ number_format($creditNum, 2) }}</td>
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

    <script>
        $(document).ready(function() {
            // Initialize scrollable table
            ScrollableTable.init('.table-sticky-wrapper');
            
            // Apply negative number colors
            ScrollableTable.colorNegatives('#trial-balance-table');

            // Column toggle functionality
            const columnMap = {
                'code': 0, 'ledger-ref': 1, 'account-ref': 2,
                'account-type': 3, 'ledger-balance': 4,
                'debit-ytd': 5, 'credit-ytd': 6
            };

            $('.column-toggle').on('change', function() {
                const columnName = $(this).data('column');
                const columnIndex = columnMap[columnName];
                
                if (columnIndex !== undefined) {
                    $('#trial-balance-table thead tr th').eq(columnIndex).toggle(this.checked);
                }
                
                ScrollableTable.toggleColumn('col-' + columnName, this.checked);
                updateColumnDropdownText();
            });

            function updateColumnDropdownText() {
                const selected = $('.column-toggle:checked').length;
                const total = $('.column-toggle').length;
                $('#columnsDisplayText').text(selected + '/' + total + ' Columns');
            }

            // Year toggle functionality
            $('.year-toggle').on('change', function() {
                const year = $(this).data('year');
                const yearClass = 'year-' + year;

                if (this.checked) {
                    if ($('.' + yearClass).length === 0) {
                        addYearColumn(year);
                    }
                    $('.' + yearClass).show();
                } else {
                    $('.' + yearClass).remove();
                }
                
                updateYearDropdownText();
                ScrollableTable.colorNegatives('#trial-balance-table');
            });

            function addYearColumn(year) {
                const yearClass = 'year-' + year;
                const headerText = 'MAR/' + year;

                $('#trial-balance-table thead tr').append(
                    `<th class="year-col ${yearClass}">${headerText}</th>`
                );

                $('#trial-balance-table tbody tr').each(function() {
                    const $tr = $(this);
                    if ($tr.hasClass('account-detail')) {
                        const comp = $tr.data('comparatives') || {};
                        const val = comp[year] ?? 0;
                        $tr.append(
                            `<td class="year-col ${yearClass} text-end">${ScrollableTable.formatNumber(val)}</td>`
                        );
                    } else {
                        $tr.append(`<td class="year-col ${yearClass}"></td>`);
                    }
                });
            }

            function updateYearDropdownText() {
                const selected = $('.year-toggle:checked').length;
                const total = $('.year-toggle').length;
                $('#yearsDisplayText').text(selected + '/' + total + ' Years');
            }

            // Initialize
            updateColumnDropdownText();
            updateYearDropdownText();

            // Apply filters
            $('#apply-filters').on('click', function() {
                $('#filter-form').submit();
            });

            // Keep dropdown open on inner click
            $('.dropdown-menu').on('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>

    <style>
        .teal-custom {
            background-color: #13667d;
            color: white;
            border: none;
            padding: 5px 8px;
        }

        .teal-custom:hover {
            background-color: #0f5265;
            color: white;
        }
    </style>
@endsection