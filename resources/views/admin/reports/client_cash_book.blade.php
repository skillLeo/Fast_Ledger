@extends('admin.layout.app')
<style>
    #balance-display {
        font-size: 14px;
        margin-left: 0;
        text-align: left;
    }

    /* Fixed height for table headers to prevent expansion */
    .main-content .custom-card .custom-datatable thead th {
        padding: 5px 2px !important;
        height: 30px !important;
        /* Fixed height */
        white-space: nowrap;
        vertical-align: middle !important;
        overflow: visible !important;
        position: relative !important;
        /* Add this for absolute positioning of inputs */
    }

    /* Position inputs absolutely to prevent height expansion */
    .custom-datatable thead th input.form-control {
        position: absolute !important;
        top: -5px !important;
        left: 0 !important;
        width: 100% !important;
        padding: 4px 2px !important;
        font-size: 13px !important;
        height: 20px !important;
    }

    /* Keep title text in normal flow */
    .custom-datatable thead th .d-inline {
        display: inline-block !important;
        white-space: nowrap;
    }

    .dataTable th .filter-wrapper {
        flex: 1;
        min-width: 120px;
    }
</style>

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">

                        <h4 class="card-title fs-6">Client Cash Book</h4>

                        <div class="card-body mt-3">
                            <!-- Filter Form -->
                            <form method="GET" id="filter-form">
                                <div class="mb-4 row">
                                    <div class="col-md-2">
                                        <label for="from_date">From Date:</label>
                                        <input type="date" id="from_date" name="from_date" class="form-control"
                                            value="{{ request('from_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="to_date">To Date:</label>
                                        <input type="date" id="to_date" name="to_date" class="form-control"
                                            value="{{ request('to_date', \Carbon\Carbon::now()->format('Y-m-d')) }}">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="bank_account_id">Bank Name:</label>
                                        <select name="bank_account_id" id="bank_account_id" class="form-control p-2">
                                            <option value="">Select Bank Account</option>
                                            <option value="all_banks"
                                                {{ request('bank_account_id') == 'all_banks' ? 'selected' : '' }}>
                                                All Banks
                                            </option>
                                            @foreach ($banks as $bank)
                                                <option value="{{ $bank['Bank_Account_ID'] }}"
                                                    {{ request('bank_account_id') == $bank['Bank_Account_ID'] ? 'selected' : '' }}>
                                                    {{ $bank['Bank_Account_Name'] }}
                                                </option>
                                            @endforeach
                                            <option value="ledger_to_ledger"
                                                {{ request('bank_account_id') == 'ledger_to_ledger' ? 'selected' : '' }}>
                                                Ledger to Ledger
                                            </option>
                                        </select>
                                    </div>
                                    <div class=" col-md-2" style="align-self: end;">
                                        <button type="submit" id="filter-btn" class="btn teal-custom">View Report</button>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end justify-end" style="justify-content:end">
                                        <x-download-dropdown pdf-id="print-pdf" csv-id="print-csv" />

                                        {{-- <div class="ms-2">
                                            <button type="submit" id="print-pdf" class="btn downloadpdf">
                                                <i class="fas fa-file-pdf"></i>Print PDF Report
                                            </button>
                                        </div>
                                        <div class="ms-2">
                                            <button type="submit" id="print-csv" class="btn downloadcsv">
                                                <i class="fas fa-file-csv"></i> Excel Report
                                            </button>
                                        </div> --}}
                                    </div>
                                </div>
                            </form>

                            <!-- Render DataTable -->
                            <div class="table-sticky-wrapper">
                                <div style="max-height: calc(100vh - 280px);">
                                    {!! $dataTable->table(['class' => 'table custom-datatable resizable-draggable-table table-truncate'], true) !!}
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
    {!! $dataTable->scripts() !!}

    <script>
        // Global variable to track if ResizableDraggableTable is already initialized
        let resizableTableInstance = null;

        // Function to safely initialize ResizableDraggableTable
        function initializeResizableTable() {
            const tableElement = document.querySelector('.resizable-draggable-table');
            
            if (!tableElement) {
                console.warn('ResizableTable: Table element not found');
                return;
            }

            // Check if table has tbody (DataTables requirement)
            const tbody = tableElement.querySelector('tbody');
            if (!tbody || tbody.children.length === 0) {
                console.warn('ResizableTable: No tbody or empty tbody found, skipping initialization');
                return;
            }

            // Only initialize if ResizableDraggableTable class exists
            if (typeof ResizableDraggableTable !== 'undefined') {
                // Destroy previous instance if exists
                if (resizableTableInstance) {
                    console.log('ResizableTable: Reinitializing...');
                }
                
                resizableTableInstance = new ResizableDraggableTable(tableElement);
                console.log('ResizableTable: Initialized successfully');
            } else {
                console.error('ResizableTable: ResizableDraggableTable class not found');
            }
        }

        $(document).ready(function() {
            const dataTable = $('.custom-datatable').DataTable();

            // Initialize after DataTable is fully loaded
            dataTable.on('init.dt', function() {
                setTimeout(attachEventListeners, 100);
                
                // Initialize resizable table after DataTable init
                setTimeout(initializeResizableTable, 300);
            });

            // Also initialize on draw (when data changes)
            dataTable.on('draw.dt', function() {
                setTimeout(initializeResizableTable, 100);
            });

            setTimeout(attachEventListeners, 500);

            function attachEventListeners() {
                const fields = [
                    "date",
                    "transType",
                    "cheque",
                    "description",
                    "accountRef",
                    "ledgerRef",
                    "transactionCode",
                    "payments",
                    "receipts",
                    "balance",
                ];

                fields.forEach(field => {
                    attachTextFilterEvents(field);
                });
            }

            function attachTextFilterEvents(field) {
                const iconId = `#${field}Icon`;
                const inputId = `#${field}Filter`;
                const titleId = `#${field}Title`;

                $(iconId).off('click').on('click', function() {
                    const $icon = $(this);
                    const $input = $(inputId);
                    const $title = $(titleId);
                    const $th = $icon.closest("th");

                    const isHidden = $input.hasClass('d-none');

                    $input.toggleClass('d-none', !isHidden);
                    $title.toggleClass('d-none', isHidden);

                    if (isHidden) {
                        $input.focus();
                        $icon.removeClass('fa-search').addClass('fa-times');
                        $th.addClass("filter-active");
                    } else {
                        $input.val('');
                        $icon.removeClass('fa-times').addClass('fa-search');
                        $th.removeClass("filter-active");

                        dataTable.ajax.reload();
                    }
                });

                $(inputId).off('input').on('input', function() {
                    clearTimeout(window[`${field}Timeout`]);
                    window[`${field}Timeout`] = setTimeout(function() {
                        dataTable.ajax.reload();
                    }, 400);
                });
            }

            // CLEAR ALL FILTERS
            function clearAllFilters() {
                const fields = [
                    "date",
                    "transType",
                    "cheque",
                    "description",
                    "accountRef",
                    "ledgerRef",
                    "transactionCode",
                    "payments",
                    "receipts",
                    "balance",
                ];

                fields.forEach(field => {
                    const $input = $(`#${field}Filter`);
                    const $icon = $(`#${field}Icon`);
                    const $title = $(`#${field}Title`);

                    $input.val('').addClass('d-none');
                    $title.removeClass('d-none');
                    $icon.removeClass('fa-times').addClass('fa-search');
                });

                dataTable.ajax.reload();
            }
        });

        $(document).ready(function() {
            const balanceValue = "{{ number_format($initialBalance, 2) }}";
            const initialBalanceRaw = {{ $initialBalance }};
            const currentBankId = "{{ request('bank_account_id') }}";

            // Function to show/hide balance based on current selection
            function showInitialBalance() {
                // Remove any existing balance display
                $('#balance-display').remove();

                // If there's an actual bank selected (including "all_banks") and balance > 0, show it
                if ((currentBankId && currentBankId !== '' && currentBankId !== 'ledger_to_ledger') &&
                    initialBalanceRaw > 0) {
                    const balanceHtml = `
                        <div id="balance-display" class="ms-4 initial-balance text-end d-inline-block fw-bold" style="color: #000;">
                            Balance Brought Forward: <span class="balance-amount">${balanceValue}</span>
                        </div>
                    `;
                    $('.dataTables_filter').append(balanceHtml);
                } else if (!currentBankId || currentBankId === '' || initialBalanceRaw === 0) {
                    // Show message when no bank is selected or balance is 0
                    const messageHtml = `
                        <div id="balance-display" class="ms-4 initial-balance text-end d-inline-block fw-bold" style="color: #666;">
                            Select a bank to view balance
                        </div>
                    `;
                    $('.dataTables_filter').append(messageHtml);
                }
                // If ledger_to_ledger, show nothing (balance hidden)
            }

            // Show initial balance (once DataTable is initialized)
            setTimeout(() => {
                showInitialBalance();
            }, 500);

            // Handle bank account dropdown change to update balance immediately
            $('#bank_account_id').on('change', function() {
                const fromDate = $('#from_date').val();
                const toDate = $('#to_date').val();
                const bankAccountId = $(this).val();

                // Always remove existing balance display first
                $('#balance-display').remove();

                // If ledger to ledger selected, hide balance completely
                if (bankAccountId === 'ledger_to_ledger') {
                    return; // Don't show any balance
                }

                // If no bank selected, show default message
                if (!bankAccountId || bankAccountId === '') {
                    const messageHtml = `
                        <div id="balance-display" class="ms-4 initial-balance text-end d-inline-block fw-bold" style="color: #666;">
                            Select a bank to view balance
                        </div>
                    `;
                    $('.dataTables_filter').append(messageHtml);
                    return;
                }

                // If "All Banks" or specific bank selected and dates available, fetch balance via AJAX
                if ((bankAccountId === 'all_banks' || bankAccountId !== '') && fromDate && toDate) {
                    $.ajax({
                        url: '{{ route('client.cashbook.get_initial_balance') }}',
                        method: 'GET',
                        data: {
                            from_date: fromDate,
                            to_date: toDate,
                            bank_account_id: bankAccountId
                        },
                        success: function(response) {
                            $('#balance-display').remove();
                            const balanceHtml = `
                                <div id="balance-display" class="ms-4 initial-balance text-end d-inline-block fw-bold" style="color: #000;">
                                    Balance Brought Forward: <span class="balance-amount">${response.initial_balance}</span>
                                </div>
                            `;
                            $('.dataTables_filter').append(balanceHtml);
                        },
                        error: function(xhr) {
                            const errorHtml = `
                                <div id="balance-display" class="ms-4 initial-balance text-end d-inline-block fw-bold" style="color: #d32f2f;">
                                    Error loading balance
                                </div>
                            `;
                            $('.dataTables_filter').append(errorHtml);
                        }
                    });
                } else {
                    // Show loading message if dates not available
                    const loadingHtml = `
                        <div id="balance-display" class="ms-4 initial-balance text-end d-inline-block fw-bold" style="color: #666;">
                            Enter dates to view balance
                        </div>
                    `;
                    $('.dataTables_filter').append(loadingHtml);
                }
            });
        });

        $(document).ready(function() {
            const table = $('.dataTable').DataTable();
            const filterBtn = $('#filter-btn');

            // Handle filter form submission and update both the table and initial balance
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();

                const fromDate = $('#from_date').val();
                const toDate = $('#to_date').val();
                const bankAccountId = $('#bank_account_id').val();
                let isValid = true;

                $('.error-message').remove();

                // Validate from_date
                if (!fromDate) {
                    $('#from_date').after(
                        '<small class="text-danger error-message">From date is required.</small>');
                    isValid = false;
                }

                // Validate to_date
                if (!toDate) {
                    $('#to_date').after(
                        '<small class="text-danger error-message">To date is required.</small>');
                    isValid = false;
                }

                // Stop submission if validation fails
                if (!isValid) {
                    return;
                }

                // Disable the filter button to prevent duplicate clicks
                filterBtn.prop('disabled', true);

                const params = new URLSearchParams({
                    from_date: fromDate || '',
                    to_date: toDate || '',
                    bank_account_id: bankAccountId || ''
                });

                // Reload the DataTable with the new parameters
                table.ajax.url(`?${params.toString()}`).load(function() {
                    // Always remove existing balance first
                    $('#balance-display').remove();

                    // Handle balance display based on selection
                    if (bankAccountId === 'ledger_to_ledger') {
                        // Hide balance for Ledger to Ledger
                    } else if (bankAccountId === 'all_banks' || (bankAccountId && bankAccountId !==
                            '')) {
                        // Fetch balance for "All Banks" or specific bank
                        $.ajax({
                            url: '{{ route('client.cashbook.get_initial_balance') }}',
                            method: 'GET',
                            data: {
                                from_date: fromDate,
                                to_date: toDate,
                                bank_account_id: bankAccountId
                            },
                            success: function(data) {
                                const balanceHtml = `
                                    <div id="balance-display" class="ms-4 initial-balance text-end d-inline-block fw-bold" style="color: #000;">
                                        Balance Brought Forward: <span class="balance-amount">${data.initial_balance}</span>
                                    </div>
                                `;
                                $('.dataTables_filter').append(balanceHtml);
                            }
                        });
                    } else {
                        // Show message for no bank selected
                        const messageHtml = `
                            <div id="balance-display" class="ms-4 initial-balance text-end d-inline-block fw-bold" style="color: #666;">
                                Select a bank to view balance
                            </div>
                        `;
                        $('.dataTables_filter').append(messageHtml);
                    }

                    // Enable the filter button after reload
                    filterBtn.prop('disabled', false);

                    // ✅ Reinitialize resizable table after reload
                    setTimeout(initializeResizableTable, 200);
                });
            });

            $('#print-pdf').on('click', function(e) {
                e.preventDefault();

                const fromDate = $('#from_date').val();
                const toDate = $('#to_date').val();
                const bankAccountId = $('#bank_account_id').val();
                let isValid = true;

                $('.error-message').remove();

                // Validate from_date
                if (!fromDate) {
                    $('#from_date').after(
                        '<small class="text-danger error-message">From date is required.</small>');
                    isValid = false;
                }

                // Validate to_date
                if (!toDate) {
                    $('#to_date').after(
                        '<small class="text-danger error-message">To date is required.</small>');
                    isValid = false;
                }

                // Stop submission if validation fails
                if (!isValid) {
                    return;
                }

                // Construct the URL with query parameters
                const params = new URLSearchParams({
                    from_date: fromDate || '',
                    to_date: toDate || '',
                    bank_account_id: bankAccountId || ''
                });

                // Open the generated PDF in a new tab
                window.open(`{{ route('client.cashbook.export_pdf') }}?${params.toString()}`, '_blank');
            });
        });
    </script>
@endsection
