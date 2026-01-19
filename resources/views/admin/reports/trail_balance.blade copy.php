@extends('admin.layout.app')






@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h4 class="page-title mb-6">Trial Balance</h4>

                            
                        </div>
                        <div class="card-body">
                            <!-- Filter Form -->
                           



                            <!-- Company Info Section -->
                            <div class="p-0 bg-light border-bottom">
                                <h4 class="mb-1">Trial Balance</h4>
                                <p class="mb-1 ">Energy Saviour Ltd</p>
                                <p class="mb-0 ">As at 31 March 2025</p>
                            </div>

                            <!-- Trial Balance Table -->

                            <table id="trial-balance-table overflow: auto;" 
                                class="table table-bordered table-hover mb-0 resizable-draggable-table">
                                <thead class="table-primary">
                                    <tr>
                                        <x-table-search-header column="code" label="Code" type="search"
                                            class="col-code" />
                                        <x-table-search-header column="ledger-ref" label="Ledger Ref" type="search"
                                            class="col-ledger-ref" />
                                        <x-table-search-header column="account-ref" label="Account Ref" type="search"
                                            class="col-account-ref" />
                                        <x-table-search-header column="account-type" label="Account Type" type="dropdown"
                                            class="col-account-type" :options="['P&L' => 'P&L', 'BS' => 'BS']" />
                                        <x-table-search-header column="ledger-balance" label="Ledger Balance"
                                            type="search" class="col-ledger-balance position-relative" />
                                        <x-table-search-header column="debit-ytd" label="Debit(YTD)" type="search"
                                            class="col-debit-ytd position-relative" />
                                        <x-table-search-header column="credit-ytd" label="Credit(YTD)" type="search"
                                            class="col-credit-ytd position-relative" />
                                    </tr>
                                </thead>

                                @php
                                    $fmt = fn($v) => $v < 0
                                        ? '(' . number_format(abs($v), 2) . ')'
                                        : number_format($v, 2);
                                @endphp

                                <tbody id="trial-balance-table-body">
                                    @foreach ($groupedAccounts as $ledgerRef => $accounts)
                                        @php $total = (float) ($groupTotals[$ledgerRef] ?? 0); @endphp

                                        {{-- 🔹 Ledger Section Header (always grey + total in Ledger Balance) --}}
                                        <tr class="ledger-header">
                                            <td class="col-code fw-bold"></td>
                                            <td class="col-ledger-ref fw-bold">{{ $ledgerRef }}</td>
                                            <td class="col-account-ref"></td>
                                            <td class="col-account-type"></td>
                                            <td class="col-ledger-balance text-end" data-column="ledger-balance">
                                                <span
                                                    class="{{ $total < 0 ? 'text-danger fw-semibold' : '' }}">{{ $fmt($total) }}</span>
                                            </td>
                                            <td class="col-debit-ytd text-end"></td>
                                            <td class="col-credit-ytd text-end"></td>
                                        </tr>

                                        {{-- 🔹 Detail rows --}}
                                        @foreach ($accounts as $acc)
                                            @php
                                                $lb = (float) ($acc->ledger_balance ?? 0);
                                                $normal = strtoupper($acc->normal_balance ?? 'DR');

                                                // Always produce numbers (0.00 if empty)
                                                $debitNum = 0.0;
                                                $creditNum = 0.0;

                                                if ($normal === 'DR') {
                                                    if ($lb >= 0) {
                                                        $debitNum = $lb;
                                                    } else {
                                                        $creditNum = abs($lb);
                                                    }
                                                } else {
                                                    // normal = CR
                                                    if ($lb >= 0) {
                                                        $creditNum = $lb;
                                                    } else {
                                                        $debitNum = abs($lb);
                                                    }
                                                }
                                            @endphp

                                            <tr class="account-detail" data-comparatives='@json($acc->comparatives)'>
                                                <td class="col-code" data-column="code">{{ $acc->id }}</td>
                                                {{-- yahan apna code field rakh sakte ho --}}
                                                <td class="col-ledger-ref" data-column="ledger-ref">
                                                    {{ $acc->ledger_ref }}</td>
                                                <td class="col-account-ref" data-column="account-ref">
                                                    {{ $acc->account_ref }}</td>
                                                <td class="col-account-type" data-column="account-type">
                                                    {{ $acc->pl_bs }}</td>

                                                {{-- Detail rows me Ledger Balance blank --}}
                                                <td class="col-ledger-balance" data-column="ledger-balance"></td>

                                                {{-- Debit / Credit hamesha 0.00 ya value --}}
                                                <td class="col-debit-ytd text-end" data-column="debit-ytd">
                                                    {{ number_format($debitNum, 2) }}</td>
                                                <td class="col-credit-ytd text-end" data-column="credit-ytd">
                                                    {{ number_format($creditNum, 2) }}</td>
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
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            // ---------- Helpers ----------
            function formatNumber(val) {
                const n = Number(val || 0);
                const abs = Math.abs(n).toFixed(2);
                return n < 0 ? '(' + Number(abs).toLocaleString() + ')' : Number(abs).toLocaleString();
            }

            function colorNegatives() {
                $('#trial-balance-table td').each(function() {
                    const txt = $(this).text().trim();
                    if (txt.startsWith('(') || txt.startsWith('-')) {
                        const $span = $(this).find('span');
                        if ($span.length) $span.addClass('text-danger fw-semibold');
                        else $(this).addClass('text-danger fw-semibold');
                    }
                });
            }

            function padZeros() {
                $('#trial-balance-table tbody tr.ledger-header').each(function() {
                    $(this).find('.col-debit-ytd').text('');
                    $(this).find('.col-credit-ytd').text('');
                });

                $('#trial-balance-table tbody tr.account-detail').each(function() {
                    const $dr = $(this).find('.col-debit-ytd');
                    const $cr = $(this).find('.col-credit-ytd');
                    if (!$dr.text().trim()) $dr.text('0.00');
                    if (!$cr.text().trim()) $cr.text('0.00');
                    $(this).find('.col-ledger-balance').text('');
                });
            }

            // ---------- ENHANCED: Column show/hide using column index ----------
            const columnMap = {
                'code': 0,
                'ledger-ref': 1,
                'account-ref': 2,
                'account-type': 3,
                'ledger-balance': 4,
                'debit-ytd': 5,
                'credit-ytd': 6
            };

            $('.column-toggle').on('change', function() {
                const columnName = $(this).data('column');
                const columnClass = 'col-' + columnName;
                const columnIndex = columnMap[columnName];
                
                if (columnIndex !== undefined) {
                    // Hide/show header th by index
                    $('#trial-balance-table thead tr th').eq(columnIndex).toggle(this.checked);
                }
                
                // Hide/show body td by class
                $('#trial-balance-table tbody td.' + columnClass).toggle(this.checked);
                
                updateColumnDropdownText();
            });

            // ---------- Select All Columns ----------
            $('#select-all-columns').on('change', function() {
                const isChecked = this.checked;
                $('.column-toggle').each(function() {
                    if ($(this).prop('checked') !== isChecked) {
                        $(this).prop('checked', isChecked).trigger('change');
                    }
                });
            });

            // Update select-all-columns based on individual checkboxes
            $('.column-toggle').on('change', function() {
                const totalColumns = $('.column-toggle').length;
                const checkedColumns = $('.column-toggle:checked').length;
                $('#select-all-columns').prop('checked', checkedColumns === totalColumns);
            });

            // ---------- Select All Years ----------
            $('#select-all-years').on('change', function() {
                const isChecked = this.checked;
                $('.year-toggle').each(function() {
                    if ($(this).prop('checked') !== isChecked) {
                        $(this).prop('checked', isChecked).trigger('change');
                    }
                });
            });

            // Update select-all-years based on individual checkboxes
            $('.year-toggle').on('change', function() {
                const totalYears = $('.year-toggle').length;
                const checkedYears = $('.year-toggle:checked').length;
                $('#select-all-years').prop('checked', checkedYears === totalYears);
            });

            // ---------- Years toggle (create/remove columns) ----------
            $('.year-toggle').on('change', function() {
                const year = $(this).data('year');
                const yearClass = 'year-' + year;

                if (this.checked) {
                    if ($('.' + yearClass).length === 0) addYearColumn(year);
                    $('.' + yearClass).show();
                } else {
                    $('#trial-balance-table thead th.' + yearClass).remove();
                    $('#trial-balance-table tbody td.' + yearClass).remove();
                }
                updateYearDropdownText();
                colorNegatives();
            });

            function addYearColumn(year) {
                const yearClass = 'year-' + year;
                const headerText = 'MAR/' + year;

                $('#trial-balance-table thead tr').append(
                    '<th class="year-col ' + yearClass + '">' + headerText + '</th>'
                );

                $('#trial-balance-table tbody tr').each(function() {
                    const $tr = $(this);
                    if ($tr.hasClass('account-detail')) {
                        const comp = $tr.data('comparatives') || {};
                        const val = comp[year] ?? 0;
                        $tr.append('<td class="year-col ' + yearClass + ' text-end">' + formatNumber(val) +
                            '</td>');
                    } else {
                        $tr.append('<td class="year-col ' + yearClass + '"></td>');
                    }
                });
            }

            function updateColumnDropdownText() {
                const selected = $('.column-toggle:checked').length;
                const total = $('.column-toggle').length;
                $('#columnsDisplayText').text(selected + '/' + total + ' Columns Selected');
            }

            function updateYearDropdownText() {
                const selected = $('.year-toggle:checked').length;
                const total = $('.year-toggle').length;
                $('#yearsDisplayText').text(selected + '/' + total + ' Years Selected');
            }

            // ---------- Init ----------
            updateColumnDropdownText();
            updateYearDropdownText();
            padZeros();
            colorNegatives();

            // Keep dropdown open on inner click
            $('.dropdown-menu').on('click', function(e) {
                e.stopPropagation();
            });

            // Apply filters
            $('#apply-filters').on('click', function() {
                $('#filter-form').trigger('submit');
            });

        });
    </script>

    <style>
        #trial-balance-table tbody tr.ledger-header td {
            background-color: #e9ecef !important;
            font-weight: 600;
            padding: 10px 8px !important;
        }

        #trial-balance-table tbody tr.account-detail:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .text-end {
            text-align: right !important;
        }
    </style>
@endsection
