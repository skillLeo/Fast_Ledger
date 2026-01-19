@extends('admin.layout.app')
@section('content')
    <style>
        .tabletop-style {
            color: black !important;
            background-color: rgba(10, 10, 10, 0.13);
            font-family: Arial, sans-serif;
        }
    </style>
    @extends('admin.partial.errors')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="page-title mb-2">Office Bank Reconciliation Report</h4>
                        </div>
                        <div class="card-body mt-3">
                            <!-- Report Filters -->
                            <div class="mb-3 d-flex align-items-end justify-content-between flex-wrap">
                                <!-- Left group: Bank Name, From/To Date, View Report -->
                                <div class="d-flex flex-wrap gap-3 align-items-end">
                                    <!-- Bank Name -->
                                    <div>
                                        <label for="bank_account_id">Bank Name:</label>
                                        <select name="bank_account_id" id="bank_account_id" class="form-control p-2">
                                            <option value="">Select Bank</option>
                                            @foreach ($banks as $key => $bank)
                                                <option value="{{ $bank['Bank_Account_ID'] }}"
                                                    {{ request('bank_account_id') == $bank['Bank_Account_ID'] || ($key === 0 && !request('bank_account_id')) ? 'selected' : '' }}>
                                                    {{ $bank['Bank_Name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- From Date -->
                                    <div>
                                        <label for="from-date">From Date:</label>
                                        <input type="date" id="from-date" class="form-control"
                                            value="{{ now()->format('Y-m-d') }}">
                                    </div>

                                    <!-- To Date -->
                                    <div>
                                        <label for="to-date">To Date:</label>
                                        <input type="date" id="to-date" class="form-control"
                                            value="{{ now()->format('Y-m-d') }}">
                                    </div>

                                    <!-- View Report Button -->
                                    <div>
                                        <button class="btn teal-custom mt-2" id="view-report-btn">View Report</button>
                                    </div>
                                </div>

                                <!-- Right group: Download Buttons -->
                                <div class="d-flex gap-2 mt-2 me-2">
                                       <x-download-dropdown pdf-id="printPdf" csv-id="downloadcsv"/>
                                    {{-- <button class="btn downloadpdf" id="printPdf">
                                        <i class="fas fa-file-pdf"></i> Print PDF Report
                                    </button> --}}
                                    <form id="pdfForm" action="{{ route('generate.pdf') }}" method="POST"
                                        style="display: none;">
                                        @csrf
                                        <input type="hidden" name="bank_account_id" id="pdf_bank_account_id">
                                        <input type="hidden" name="from_date" id="pdf_from_date">
                                        <input type="hidden" name="to_date" id="pdf_to_date">
                                        <input type="hidden" name="bank_balance" id="pdf_bank_balance">
                                        <input type="hidden" name="final_difference" id="pdf_final_difference">
                                        <input type="hidden" name="interest_rows" id="pdf_interest_rows">
                                        <input type="hidden" name="cheque_rows" id="pdf_cheque_rows">
                                    </form>

                                    {{-- <button class="btn downloadcsv">
                                        <i class="fas fa-file-excel"></i> Print Excel Report
                                    </button> --}}
                                </div>
                            </div>


                            <!-- Reconciliation Table -->
                            <div class="table-responsive">
                                <div id="tabletop"
                                    class="tabletop-style mb-2 p-2 t-size-20px fs-4 text-white font-weight-bold">
                                    Office Bank Reconciliation Report</div>

                                <table class="table table-bordered table-striped resizable-draggable-table">
                                    <tbody>
                                        <!-- Static Table Headers -->
                                        <tr style="height: 28px;">
                                            <th class="border border-dark p-2"><b>Lloyds TSB Sort Code</b></th>
                                            <th class="border border-dark p-2" width="15%"><b>Sort Code</b></th>
                                            <th class="border border-dark p-2" width="10%"><b>30-98-91</b></th>
                                            <th class="border border-dark p-2" width="10%">&nbsp;</th>
                                            <th class="border border-dark p-2" width="10%"><b>Account</b></th>
                                            <th class="border border-dark p-2" width="10%"><b>30324060</b></th>
                                        </tr>
                                        <tr>
                                            <th class="border border-dark p-2">&nbsp;</th>
                                            <th class="border border-dark p-2" width="15%"><b>Ref</b></th>
                                            <th class="border border-dark p-2" width="10%"><b>Ref</b></th>
                                            <th class="border border-dark p-2 text-center" width="10%"><b>&pound;</b>
                                            </th>
                                            <th class="border border-dark p-2 text-center" width="10%"><b>&pound;</b>
                                            </th>
                                            <th class="border border-dark p-2 text-center" width="10%"><b>&pound;</b>
                                            </th>
                                        </tr>

                                        <!-- Static Table Rows -->
                                        <tr>
                                            <td colspan="6" class="border border-dark p-2"><b>Balance as per Cash
                                                    Book</b></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="border border-dark p-2">
                                                <table class="table table-sm table-borderless">

                                                    <tr>
                                                        <td>Balance as on:</td>
                                                        {{-- <td>February 2025</td> --}}
                                                        <td align="right">
                                                            <font style="color:#FF0000 ">*</font>Balance:
                                                        </td>
                                                        <td colspan="2" align="right" id="initial-balance"></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    <tfoot id="flow-balance">
                                    </tfoot>

                                    <!-- Receipts as per Ledger Book -->
                                    <tr>
                                        <td colspan="6" class="border border-dark p-2"><b>Receipts as per Ledger
                                                Book</b></td>
                                    </tr>
                                    <tbody id="bookledger-data">
                                        <!-- Dynamic Rows will be injected here -->
                                    </tbody>
                                    <tfoot id="bookledger-total">
                                    </tfoot>

                                    <!-- Disbursements as per Ledger Book -->
                                    <tr>
                                        <td colspan="6" class="border border-dark p-2"><b>Disbursements as per Ledger
                                                Book</b></td>
                                    </tr>
                                    <tbody id="disbursements-data">
                                        <!-- Dynamic Rows will be injected here -->
                                    </tbody>
                                    <tfoot id="disbursements-total">
                                    </tfoot>

                                    <!-- Receipts as per Sales Book -->
                                    <tr>
                                        <td colspan="6" class="border border-dark p-2"><b>Receipts as per Sales
                                                Book</b></td>
                                    </tr>
                                    <tbody id="salesBook-data">
                                        <!-- Dynamic Rows will be injected here -->
                                    </tbody>
                                    <tfoot id="salesBook-total">
                                    </tfoot>

                                    <!-- Payments and Refunds -->
                                    <tr>
                                        <td colspan="6" class="border border-dark p-2"><b>Payments and Refunds (Net) as
                                                per Purchase Day Book</b></td>
                                    </tr>
                                    <tbody id="payment-refund-data">
                                        <!-- Dynamic Rows will be injected here -->
                                    </tbody>
                                    <tfoot id="payment-refund-total">
                                    </tfoot>
                                    <!-- Receipts, Payments and Transfers for Capital, Drawings and Loan Account -->
                                    <tr>
                                        <td colspan="6" class="border border-dark p-2"><b>Receipts, Payments and
                                                Transfers as per Capital, Drawings and Loan Account</b></td>
                                    </tr>
                                    <tbody id="payment-transfer-data">
                                        <!-- Dynamic Rows will be injected here -->
                                    </tbody>
                                    <tfoot id="payment-transfer-total">
                                    </tfoot>

                                    <!-- Miscellaneous Account -->
                                    <tr>
                                        <td colspan="6" class="border border-dark p-2"><b>Receipts, Payments and
                                                Transfers as per Miscellaneous Account</b></td>
                                    </tr>
                                    <tbody id="miscellaneous-data">
                                        <!-- Dynamic Rows will be injected here -->
                                    </tbody>
                                    <tfoot id="miscellaneous-total">
                                    </tfoot>


                                    </tbody>
                                    <tbody id="grand-total">

                                    </tbody>

                                </table>

                            </div>

                            <div class="mt-4 row" id="bank-statement-section" style="display: none;">
                                <!-- Balance Reconciliation -->
                                <div class="mt-4 row">
                                    <div class="col-md-6">
                                        <h5>Balance as per Bank Statement</h5>
                                        <label for="">Balance is on:</label>
                                        <input type="date" id="balance-date" class="mb-2 form-control w-50">

                                        <div class="p-3 border">
                                            <h6>Less (Interest Paid)</h6>
                                            <button class="btn btn-danger btn-sm" id="delete-interest-row-btn">Delete
                                                Row</button>
                                            <button class="btn addbutton btn-sm" id="add-interest-row-btn">Add to
                                                List</button>
                                            <table class="table mt-2" id="interest-table">
                                                <thead>
                                                    <tr>
                                                        <th><input type="checkbox" class="cheque-checkbox"></th>

                                                        <th>check</th>
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

                                    <div class="col-md-6">
                                        <h5>Balance as per Bank Statement</h5>
                                        <label for="">*Balance:</label>
                                        {{-- <input type="number" class="mb-2 form-control w-50"> --}}
                                        <input type="number" id="input-balance" class="mb-2 form-control w-50">

                                        <div class="p-3 border">
                                            <h5>Less (Cheques in Transit)</h5>
                                            <button class="btn btn-danger btn-sm" id="delete-cheque-row-btn">Delete
                                                Row</button>
                                            <button class="btn addbutton btn-sm" id="add-cheque-row-btn">Add to
                                                List</button>
                                            <table class="table mt-2" id="cheque-table">
                                                <thead>
                                                    <tr>
                                                        <th><input type="checkbox" class="cheque-checkbox"></th>
                                                        <th>Cheque</th>
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
    <script>
        $(document).ready(function() {
            let initialBalance1 = 0;

            function updateBalanceAndDifference() {
                var totalClientBalance = initialBalance1;
                console.log('yes', totalClientBalance);

                var enteredBalance = $('#input-balance').val().trim();
                console.log('totals::=>', totalClientBalance);

                if (enteredBalance === "") {
                    $('#difference-display').text("");
                    return;
                }

                enteredBalance = parseFloat(enteredBalance) || 0;

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

                var newBalance = enteredBalance + totalInterest + totalCheque;
                $('#balance-display').text(newBalance.toFixed(2));

                var difference = totalClientBalance - newBalance;
                $('#difference-display').text(difference.toFixed(2));
            }

            $('#input-balance').on('input', updateBalanceAndDifference);
            $('#difference-display').text("");
            updateBalanceAndDifference();

            $('#view-report-btn').on('click', function() {
                var bankAccountId = $('#bank_account_id').val();
                var fromDate = $('#from-date').val();
                var toDate = $('#to-date').val();
                $('#balance-date').val(fromDate);

                if (!bankAccountId || !fromDate || !toDate) {
                    alert('Please select a bank and date range.');
                    return;
                }

                $.ajax({
                    url: '{{ route('Office.bank_reconciliation.data') }}',
                    method: 'GET',
                    data: {
                        bank_account_id: bankAccountId,
                        from_date: fromDate,
                        to_date: toDate
                    },
                    success: function(data) {
                        $('#disbursements-data, #bookledger-data, #payment-refund-data, #salesBook-data, #payment-transfer-data, #miscellaneous-data')
                            .empty();
                        $('#disbursements-total, #bookledger-total, #payment-refund-total, #salesBook-total, #payment-transfer-total, #miscellaneous-total, #grand-total, #flow-balance')
                            .empty();

                        if (data.error) {
                            alert(data.error);
                            return;
                        }

                        function formatCurrency(amount) {
                            return '£' + parseFloat(amount).toFixed(2);
                        }

                        let grandTotal = 0;
                        let initialBalance = 0;

                        $.ajax({
                            url: '{{ route('Office.bank_reconciliation.initial_balance') }}',
                            method: 'GET',
                            data: {
                                bank_account_id: bankAccountId,
                                from_date: fromDate
                            },
                            success: function(initialData) {
                                if (initialData.initial_balance) {
                                    initialBalance = parseFloat(initialData
                                        .initial_balance);
                                    console.log('Initial balance:', initialBalance);

                                    if (!isNaN(initialBalance)) {
                                        let formattedInitialBalance =
                                            initialBalance < 0 ?
                                            `-£${Math.abs(initialBalance).toFixed(2)}` :
                                            `£${initialBalance.toFixed(2)}`;

                                        $('#initial-balance').html(
                                            formattedInitialBalance);
                                    } else {
                                        console.error(
                                            "Initial balance is not a valid number"
                                        );
                                    }
                                }
                            }
                        }).then(() => {
                            let flowBalance = grandTotal + initialBalance;
                            initialBalance1 = flowBalance;

                            let formattedFlowBalance = `
                            <tr>
                                <td colspan="3" class="border border-dark p-2 text-right"><b>Total Balance:</b></td>
                                <td colspan="3" class="border border-dark p-2 text-right"><b>${flowBalance < 0 ? `-£${Math.abs(flowBalance).toFixed(2)}` : `£${flowBalance.toFixed(2)}`}</b></td>
                            </tr>
                        `;

                            $('#flow-balance').html(formattedFlowBalance);
                        });

                        function calculateTotal(data, sectionId) {
                            let total = 0;
                            data.forEach(function(transaction) {
                                let amount = parseFloat(transaction.Amount);
                                total += transaction.Paid_In_Out == 2 ? -Math.abs(
                                    amount) : amount;
                            });

                            grandTotal += total;

                            let formattedTotal = total < 0 ? `-£${Math.abs(total).toFixed(2)}` :
                                `£${total.toFixed(2)}`;

                            let totalRow = `
                            <tr>
                                <td colspan="3" class="border border-dark p-2 text-right"><b>Total:</b></td>
                                <td class="border border-dark p-2 text-right"><b>${formattedTotal}</b></td>
                                <td class="border border-dark p-2"></td>
                                <td class="border border-dark p-2"></td>
                            </tr>
                        `;

                            $('#' + sectionId + '-total').html(totalRow);
                        }

                        function appendDataToTable(data, sectionId) {
                            data.forEach(function(transaction) {
                                var amount = parseFloat(transaction.Amount);
                                var textColor = amount < 0 ? 'red' : 'black';

                                let row = `
                                <tr>
                                    <td class="border border-dark p-2">${transaction.AccountRefDescription}</td>
                                    <td class="border border-dark p-2">${transaction.Cheque}</td>
                                    <td class="border border-dark p-2">${transaction.Ledger_Ref}</td>
                                    <td class="border border-dark p-2 text-right" style="color:${textColor};">${formatCurrency(amount)}</td>
                                    <td class="border border-dark p-2 text-right"></td>
                                    <td class="border border-dark p-2"></td>
                                </tr>
                            `;
                                $('#' + sectionId + '-data').append(row);
                            });

                            calculateTotal(data, sectionId);
                        }

                        if (data.book_ledger?.length) appendDataToTable(data.book_ledger,
                            'bookledger');
                        if (data.disbursments?.length) appendDataToTable(data.disbursments,
                            'disbursements');
                        if (data.sales_book?.length) appendDataToTable(data.sales_book,
                            'salesBook');
                        if (data.payment_refund?.length) appendDataToTable(data.payment_refund,
                            'payment-refund');
                        if (data.payment_transfer?.length) appendDataToTable(data
                            .payment_transfer, 'payment-transfer');
                        if (data.miscellaneous?.length) appendDataToTable(data.miscellaneous,
                            'miscellaneous');

                        let formattedGrandTotal = grandTotal < 0 ?
                            `-£${Math.abs(grandTotal).toFixed(2)}` :
                            `£${grandTotal.toFixed(2)}`;
                        let grandTotalRow = `
                        <tr>
                            <td colspan="3" class="border border-dark p-2 text-right"><strong>Net Cash (Inflow/Outflow):</strong></td>
                            <td colspan="3" class="border border-dark p-2 text-right"><strong>${formattedGrandTotal}</strong></td>
                        </tr>
                    `;

                        $('#grand-total').html(grandTotalRow);

                        updateBalanceAndDifference();
                        $('#bank-statement-section').fadeIn();
                    }
                });
            });

            // Add new interest row
            $('#add-interest-row-btn').on('click', function() {
                const newRow = `
                <tr>
                    <td><input type="checkbox" class="form-check-input interest-check"></td>
                    <td><input type="text" name="cheque[]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="ref[]" class="form-control form-control-sm"></td>
                    <td><input type="number" name="amount[]" class="form-control form-control-sm interest-amount" step="0.01"></td>
                </tr>`;
                        $('#interest-table tbody').append(newRow);
                        attachInterestInputHandler(); // ✅ Attach input event
                    });

                    // Add new cheque row
                 $('#add-cheque-row-btn').on('click', function() {
                        const newRow = `
                    <tr>
                        <td><input type="checkbox" class="form-check-input interest-check"></td>
                            <td><input type="text" name="cheque[]" class="form-control form-control-sm"></td>
                            <td><input type="text" name="ref[]" class="form-control form-control-sm"></td>
                        <td><input type="number" name="amount[]" class="form-control form-control-sm cheque-amount" step="0.01"></td>
                    </tr>`;
                    $('#cheque-table tbody').append(newRow);
                    attachChequeInputHandler(); // ✅ Attach input event
            });


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

            document.getElementById('printPdf').addEventListener('click', function(e) {
                e.preventDefault();

                $('#pdf_bank_account_id').val($('#bank_account_id').val());
                $('#pdf_from_date').val($('#from-date').val());
                $('#pdf_to_date').val($('#to-date').val());
                $('#pdf_bank_balance').val($('#input-balance').val());
                $('#pdf_final_difference').val($('#difference-display').text());

                // Serialize interest and cheque tables to JSON
                const interestRows = [];
                $('#interest-table tbody tr').each(function() {
                    const ref = $(this).find('input[name="ref[]"]').val() || '';
                    const amount = $(this).find('input.interest-amount').val() || 0;
                    interestRows.push({
                        ref,
                        amount
                    });
                });

                const chequeRows = [];
                $('#cheque-table tbody tr').each(function() {
                    const ref = $(this).find('input[name="ref[]"]').val() || '';
                    const cheque = $(this).find('input[name="cheque[]"]').val() || '';
                    const amount = $(this).find('input.cheque-amount').val() || 0;
                    chequeRows.push({
                        cheque,
                        ref,
                        amount
                    });
                });

                $('#pdf_interest_rows').val(JSON.stringify(interestRows));
                $('#pdf_cheque_rows').val(JSON.stringify(chequeRows));

                $('#pdfForm').submit();
            });

            function attachInterestInputHandler() {
                $('.interest-amount').off('input').on('input', updateBalanceAndDifference);
            }

            function attachChequeInputHandler() {
                $('.cheque-amount').off('input').on('input', updateBalanceAndDifference);
            }

        });
    </script>
@endsection
