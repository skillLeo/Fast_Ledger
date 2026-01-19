@extends('admin.layout.app')

@section('content')
    @extends('admin.partial.errors')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">

                        <h4 class="page-title  mb-4">Client Bank Reconciliation Report</h4>
                        {{-- <div>
                            <a href="{{ route('transactions.create') }}" class="btn btn-primary rounded-pill btn-wave">New</a>
                        </div> --}}

                        <div class="card-body">
                            <!-- Report Filters -->
                            <div class="mb-3 d-flex justify-content-between align-items-center ">
                                <!-- Date Input on the left -->
                                <input type="date" id="filter-date" class="form-control  mx-2" style="width: 200px;"
                                    name="filter_date" placeholder="Select Date" value="{{ now()->format('Y-m-d') }}">

                                <button class="btn teal-custom" id="view-report-btn">View Report</button>
                                <!-- Button group on the right -->
                                <div class="d-flex gap-2 ms-auto">
                                    <x-download-dropdown pdf-id="pdfExportBtn" csv-id="downloadcsv" />
                                    {{-- 
                                    <button class="btn downloadpdf" id="pdfExportBtn">
                                        <i class="fas fa-file-pdf"></i> Print PDF Report
                                    </button>
                                    <button class="btn downloadcsv">
                                        <i class="fas fa-file-csv"></i> Print Excel Report
                                    </button> --}}
                                </div>
                            </div>


                            <!-- Reconciliation Table -->
                            <div class="table-sticky-wrapper">
                                <div style="max-height: calc(100vh - 500px);">
                                <table class="table text-center table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <x-table-search-header column="client_name" label="Client Name"
                                                type="search" />

                                            <x-table-search-header column="ledger_ref" label="Ledger Ref#" type="search" />

                                            <x-table-search-header column="client_ac" label="Client A/C (£)"
                                                type="search" />

                                            <x-table-search-header column="office_ac" label="Office A/C (£)"
                                                type="search" />
                                        </tr>
                                    </thead>
                                    <tbody id="reconciliation-table-body">
                                        <!-- Data will be dynamically added here -->
                                    </tbody>
                                    <tfoot id="reconciliation-table-footer">
                                        <!-- Totals will be dynamically added here -->
                                    </tfoot>
                                </table>
                            </div>
                                                                </div>


                            <div class="mt-4 row" id="bank-statement-section" style="display: none;">
                                <!-- Balance Reconciliation -->
                                <div class="mt-4 row">
                                    <!--<div class="col-md-6">-->
                                    <!--    <h5>Balance as per Bank Statement</h5>-->
                                    <!--    <label for="">Balance is on:</label>-->
                                    <!--    <input type="date" id="balance-date" class="mb-2 form-control w-50">-->

                                    <!--    <div class="p-3 border">-->
                                    <!--        <h6>Less (Interest Paid)</h6>-->
                                    <!--        <button class="btn btn-danger btn-sm" id="delete-interest-row-btn">Delete-->
                                    <!--            Row</button>-->
                                    <!--        <button class="btn addbutton btn-sm" id="add-interest-row-btn">Add to-->
                                    <!--            List</button>-->
                                    <!--        <table class="table mt-2" id="interest-table">-->
                                    <!--            <thead>-->
                                    <!--                <tr>-->
                                    <!--                    <th>check</th>-->
                                    <!--                    <th>Ref #</th>-->
                                    <!--                    <th>*Amount (£)</th>-->
                                    <!--                </tr>-->
                                    <!--            </thead>-->
                                    <!--            <tbody>-->
                                    <!-- New rows will be added here -->
                                    <!--            </tbody>-->
                                    <!--        </table>-->
                                    <!--    </div>-->
                                    <!--</div>-->

                                    <div class="col-md-12">
                                        <h5>Balance as per Bank Statement</h5>
                                        <label for="">*Balance:</label>
                                        <input type="number" id="input-balance" class="mb-2 form-control"
                                            style="width: 20%;">

                                        <div class="p-3 border">
                                            <h5>Less (Cheques in Transit)</h5>
                                            <button class="btn btn-danger btn-sm" id="delete-cheque-row-btn">Delete
                                                Row</button>
                                            <button class="btn addbutton btn-sm" id="add-cheque-row-btn">Add to
                                                List</button>
                                            <table class="table mt-2" id="cheque-table">
                                                <thead>
                                                    <tr>
                                                        <!--<th><input type="checkbox" class="cheque-checkbox"></th>-->
                                                        <th>Cheque No</th>
                                                        <th>Ref #</th>
                                                        <th>*Amount (£)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- New rows will be added here -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Final Balance -->
                                <div class="mt-3 text-end">
                                    <h5>*Balance: <span class="fw-bold" id="balance-display"></span></h5>
                                    <h5>Difference: <span class="fw-bold" id="difference-display"></span></h5>
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
    {{-- @push('scripts') --}}

    <script>
        $(document).ready(function() {

            // Function to update the balance and difference dynamically
            function updateBalanceAndDifference() {
                var totalClientBalance = parseFloat($('#reconciliation-table-footer td:nth-child(2) strong')
                    .text()) || 0;
                var enteredBalance = $('#input-balance').val().trim(); // Get input value

                // If no balance is entered, clear the difference field
                if (enteredBalance === "") {
                    $('#difference-display').text(""); // Empty the difference field
                    return;
                }

                enteredBalance = parseFloat(enteredBalance) || 0; // Convert to number if not empty

                // Get the total of interest and cheque amounts
                var totalInterest = 0;
                var totalCheque = 0;

                $('.interest-amount').each(function() {
                    var value = parseFloat($(this).val()) || 0;
                    totalInterest += value;
                });

                $('.cheque-amount').each(function() {
                    var value = parseFloat($(this).val()) || 0;
                    totalCheque += value;
                });

                // Calculate the new total balance (entered balance + total interest + total cheque)
                var newBalance = enteredBalance + totalInterest + totalCheque;

                // Update the balance display
                $('#balance-display').text(newBalance.toFixed(2));

                // Calculate the difference by subtracting the new balance from the total client balance
                var difference = totalClientBalance - newBalance;
                $('#difference-display').text(difference.toFixed(2));
            }

            // Attach event listener to input field (on input event)
            $('#input-balance').on('input', updateBalanceAndDifference);

            // Initially, keep the difference empty
            $('#difference-display').text("");
            updateBalanceAndDifference();

            // View Report Button Click
            $('#view-report-btn').click(function() {

                // Get the selected filter date
                var selectedDate = $('#filter-date').val();

                // Set the balance-date to be the same as the filter date
                $('#balance-date').val(selectedDate);

                if (!selectedDate) {
                    alert('Please select a date.');
                    return;
                }

                // Fetch data from the backend using AJAX
                $.ajax({
                    url: '{{ url('fetch-client-bank-reconciliation') }}/' +
                        selectedDate, // Ensure this URL is correct
                    method: 'GET',
                    success: function(response) {
                        console.log("Response received: ", response);

                        const reconciliation = response.reconciliation;
                        const interest = response.interest;
                        const cheques = response.cheques;

                        // Populate reconciliation table
                        $('#reconciliation-table-body').empty();
                        $('#reconciliation-table-footer').empty();

                        let totalClientBalance = 0;
                        let totalOfficeBalance = 0;

                        $.each(reconciliation, function(index, data) {
                            $('#reconciliation-table-body').append(`
                                    <tr>
                                        <td data-column="client_name">${data.Client_Name}</td>
                                        <td data-column="ledger_ref">${data.Ledger_Ref}</td>
                                        <td data-column="client_ac">${data['Client Balance']}</td>
                                        <td data-column="office_ac">${data.Office_Balance}</td>
                                    </tr>
                                `);
                            totalClientBalance += parseFloat(data['Client Balance']);
                            totalOfficeBalance += parseFloat(data.Office_Balance);
                        });

                        $('#reconciliation-table-footer').append(`
                            <tr class="table-info">
                                <td colspan="2"><strong>Total</strong></td>
                                <td><strong>${totalClientBalance.toFixed(2)}</strong></td>
                                <td><strong>${totalOfficeBalance.toFixed(2)}</strong></td>
                            </tr>
                        `);

                        // Populate Interest Table
                        $('#interest-table tbody').empty();
                        $.each(interest, function(index, item) {
                            $('#interest-table tbody').append(`
                <tr>
                    <td><input type="checkbox" class="interest-checkbox"></td>
                    <td><input type="text" class="form-control" value="${item.Ref}"></td>
                    <td><input type="number" class="form-control interest-amount" value="${item.Amount}"></td>
                </tr>
            `);
                        });

                        // Populate Cheque Table
                        $('#cheque-table tbody').empty();
                        $.each(cheques, function(index, item) {
                            $('#cheque-table tbody').append(`
                <tr>
                  
                    <td><input type="text" class="form-control" value="${item.Cheque}"></td>
                    <td><input type="text" class="form-control" value="${item.Ledger_Ref}"></td>
                    <td><input type="number" class="form-control cheque-amount" value="${item.Amount}"></td>
                </tr>
            `);
                        });

                        // Reattach listeners and update balance
                        attachInterestInputHandler();
                        attachChequeInputHandler();
                        updateBalanceAndDifference();
                        $('#bank-statement-section').fadeIn();
                    },
                    error: function(xhr, status, error) {
                        console.log("Error fetching data: ", error);
                    }
                });

            });

            // Add Interest Row
            $('#add-interest-row-btn').click(function() {
                var rowHtml = `
        <tr>
            <td><input type="checkbox" class="interest-checkbox"></td>
            <td><input type="text" class="form-control"></td>
            <td><input type="number" class="form-control interest-amount"></td>
        </tr>
    `;
                $('#interest-table tbody').append(rowHtml);
                attachInterestInputHandler(); // Re-attach input handler when new row is added
            });

            // Add Cheque Row
            $('#add-cheque-row-btn').click(function() {
                var rowHtml = `
        <tr>
            <td><input type="checkbox" class="cheque-checkbox"></td>
            <td><input type="text" class="form-control "></td>
            <td><input type="number" class="form-control cheque-amount"></td>
        </tr>
    `;
                $('#cheque-table tbody').append(rowHtml);
                attachChequeInputHandler(); // Re-attach input handler when new row is added
            });

            // Function to attach input handlers to the added rows
            function attachInterestInputHandler() {
                $('.interest-amount').off('input').on('input', function() {
                    updateBalanceAndDifference();
                });
            }

            function attachChequeInputHandler() {
                $('.cheque-amount').off('input').on('input', function() {
                    updateBalanceAndDifference();
                });
            }

            // Delete Selected Interest Row
            $('#delete-interest-row-btn').click(function() {
                var selectedRows = $('#interest-table tbody input[type="checkbox"]:checked');

                if (selectedRows.length === 0) {
                    alert('Please select a row to delete.');
                    return;
                }

                // Loop through each checked row and remove it
                selectedRows.each(function() {
                    $(this).closest('tr').remove();
                });

                updateBalanceAndDifference(); // Recalculate after deleting rows
            });

            // Delete Selected Cheque Row
            $('#delete-cheque-row-btn').click(function() {
                var selectedRows = $('#cheque-table tbody input[type="checkbox"]:checked');

                if (selectedRows.length === 0) {
                    alert('Please select a row to delete.');
                    return;
                }

                // Loop through each checked row and remove it
                selectedRows.each(function() {
                    $(this).closest('tr').remove();
                });

                updateBalanceAndDifference(); // Recalculate after deleting rows
            });

        });

        document.getElementById('pdfExportBtn').addEventListener('click', function() {
            let selectedDate = document.getElementById('filter-date').value; // Get date from input field
            if (!selectedDate) {
                alert("Please select a date first.");
                return;
            }
            window.location.href = `/client-bank-reconciliation/pdf/${selectedDate}`;
        });
    </script>
@endsection
