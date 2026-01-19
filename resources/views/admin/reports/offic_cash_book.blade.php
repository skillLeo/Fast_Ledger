@extends('admin.layout.app')
<style>
    /* Fixed height for table headers to prevent expansion */
    .main-content .custom-card .custom-datatable thead th {
        height: 30px !important;
        /* Fixed height */
        white-space: nowrap;
        vertical-align: middle !important;
        overflow: visible !important;
        position: relative !important;
        /* Required for absolute positioning of inputs */
    }



    /* Keep title text in normal flow */
    .custom-datatable thead th .d-inline {
        display: inline-block !important;
        white-space: nowrap;
    }
</style>
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">

                        <h4 class="page-title">Office Cash Book</h4>

                        <div class="card-body mt-3">
                            <!-- Filter Form -->
                            <form method="GET" id="filter-form">
                                <div class="mb-2 row">
                                    <div class="col-md-2">
                                        <label for="from_date">From Date:</label>
                                        <input type="date" id="from_date" name="from_date" class="form-control"
                                            {{-- value="{{ request('from_date') }}"> --}}
                                            value="{{ request('from_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}">

                                    </div>
                                    <div class="col-md-2">
                                        <label for="to_date">To Date:</label>
                                        <input type="date" id="to_date" name="to_date" class="form-control"
                                            {{-- value="{{ request('to_date') }}"> --}}
                                            value="{{ request('to_date', \Carbon\Carbon::now()->format('Y-m-d')) }}">

                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <div class="col-md-4">
                                            <label for="bank_account_id">Bank Name:</label>
                                            <select name="bank_account_id" id="bank_account_id" class="form-control p-2">
                                                <option value="">Select Bank</option>
                                                @foreach ($banks as $key => $bank)
                                                    <option value="{{ $bank['Bank_Account_ID'] }}"
                                                        {{ request('bank_account_id') == $bank['Bank_Account_ID'] || ($key === 0 && !request('bank_account_id')) ? 'selected' : '' }}>

                                                        {{ $bank['Bank_Account_Name'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="ms-3">
                                            <button type="submit" id="filter-btn" class="btn teal-custom">View
                                                Report</button>
                                        </div>

                                    </div>
                                    <div class="col-md-4 d-flex align-items-end justify-end" style="justify-content:end">
                                        <x-download-dropdown pdf-id="print-pdf" csv-id="print-csv" />

                                        {{-- <div class="ms-3">
                                            <button type="submit" id="print-pdf" class="btn downloadpdf"><i class="fas fa-file-pdf"></i>Print PDF Report</button>
                                        </div>
                                        <div class="ms-3">
                                            <button type="submit" id="print-csv" class="btn downloadcsv"><i class="fas fa-file-csv"></i>Print Excel Report</button>
                                        </div> --}}
                                    </div>
                                </div>
                            </form>

                            <!-- Display Initial Balance -->
                            <div class="initial-balance text-end">
                                <p><strong>Balance Brought Forward:</strong> {{ number_format($initialBalance, 2) }}</p>
                            </div>

                            <!-- Render DataTable -->
                            <div class="table-sticky-wrapper">
                                <div style="max-height: calc(100vh - 280px);">
                                    {!! $dataTable->table(['class' => 'table custom-datatable table-truncate resizable-draggable-table'], true) !!}
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
    {{-- ============ RESIZABLE TABLE INITIALIZATION ============ --}}
    <script>
        // Global variable to track ResizableDraggableTable instance
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

            if (typeof ResizableDraggableTable !== 'undefined') {
                if (resizableTableInstance) {
                    console.log('ResizableTable: Reinitializing...');
                }

                // Force table to calculate natural widths first
                tableElement.style.width = 'auto';
                tableElement.style.tableLayout = 'auto';
                void tableElement.offsetWidth; // Force reflow
                tableElement.style.tableLayout = 'fixed';

                resizableTableInstance = new ResizableDraggableTable(tableElement);
                console.log('ResizableTable: Initialized successfully');
            } else {
                console.error('ResizableTable: ResizableDraggableTable class not found');
            }
        }
    </script>
    <script>
        $(document).ready(function() {
            const dataTable = $('.custom-datatable').DataTable();

            dataTable.on('init.dt', function() {
                setTimeout(attachEventListeners, 100);
                // ✅ Initialize resizable table after DataTable init
                setTimeout(initializeResizableTable, 300);
            });

            // ✅ Reinitialize on every draw (when data changes)
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
                    const $th = $icon.closest("th"); // get current header th

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

            // Append next to search bar (once DataTable is initialized)
            setTimeout(() => {
                const balanceHtml = `
            <div id="balance-display" class="ms-4 d-inline-block fw-bold" style="color: #000;">
                Balance Brought Forward: <span style="">${balanceValue}</span>
            </div>
        `;

                // Place next to the default DataTable search bar
                $('.dataTables_filter').append(balanceHtml);
            }, 500);
        });




        $(document).ready(function() {
            const table = $('.dataTable').DataTable();
            const filterBtn = $('#filter-btn');
            const initialBalanceElem = $('.initial-balance p');

            // Fetch initial balance on page load (if any filters are present in the URL)
            const urlParams = new URLSearchParams(window.location.search);
            const fromDate = urlParams.get('from_date');
            const toDate = urlParams.get('to_date');
            const bankAccountId = urlParams.get('bank_account_id');

            if (fromDate || toDate || bankAccountId) {
                $.ajax({
                    url: '{{ route('office.cashbook.get_initial_balance') }}', // Correct route
                    method: 'GET',
                    data: {
                        from_date: fromDate,
                        to_date: toDate,
                        bank_account_id: bankAccountId
                    },
                    success: function(data) {
                        // Update the initial balance display
                        initialBalanceElem.html(
                            `<strong>Initial Balance:</strong> ${data.initial_balance}`);
                    }
                });
            }

            // Handle filter form submission and update both the table and initial balance
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();

                const fromDate = $('#from_date').val();
                const toDate = $('#to_date').val();
                const bankAccountId = $('#bank_account_id').val();

                // Disable the filter button to prevent duplicate clicks
                filterBtn.prop('disabled', true);

                const params = new URLSearchParams({
                    from_date: fromDate || '',
                    to_date: toDate || '',
                    bank_account_id: bankAccountId || ''
                });

                // Update the URL without reloading the page
                history.replaceState(null, '', window.location
                    .pathname); // Reset to current URL without query params

                // Reload the DataTable with the new parameters
                table.ajax.url(`?${params.toString()}`).load(function() {
                    // Fetch and update the initial balance after the table reload
                    $.ajax({
                        url: '{{ route('office.cashbook.get_initial_balance') }}',
                        method: 'GET',
                        data: params.toString(),
                        success: function(data) {
                            initialBalanceElem.html(
                                `<strong>Initial Balance:</strong> ${data.initial_balance}`
                            );
                        }
                    });

                    // Enable the filter button after reload
                    filterBtn.prop('disabled', false);

                    // ✅ Reinitialize resizable table after reload
                    setTimeout(initializeResizableTable, 200);
                });
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

            console.log(params);

            // Open the generated PDF in a new tab
            window.open(`{{ route('office.cashbook.export_pdf') }}?${params.toString()}`, '_blank');
        });

        {{-- ============ INITIALIZE ON PAGE LOAD ============ --}}
        $(document).ready(function() {
            // Initialize resizable table after page fully loads
            setTimeout(initializeResizableTable, 500);
        });
    </script>
@endsection
