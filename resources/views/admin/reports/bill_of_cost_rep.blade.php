@extends('admin.layout.app')
<style>
    /* Fixed height for table headers to prevent expansion */
    .main-content .custom-card #transaction-table.table thead th {
        padding: 5px 2px !important;
        height: 30px !important;
        /* Fixed height */
        vertical-align: middle !important;
        overflow: visible !important;
    }

    /* Position inputs/selects absolutely to prevent height expansion */
    #transaction-table thead th input.form-control,
    #transaction-table thead th select.form-control {
        position: absolute !important;
        top: -5px !important;
        left: 0 !important;
        width: 100% !important;
        padding: 4px 2px !important;
        font-size: 13px !important;
        height: 20px !important;
    }

    /* Keep title text in normal flow */
    #transaction-table thead th .d-inline {
        display: inline-block !important;
        white-space: nowrap;
        /* z-index: 5 !important; */
    }

    .desc_width {
        width: 22% !important;
    }

    .ledger_dorpdown {
        width: 31% !important;
    }
</style>
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header mb-3">
                            <h4 class="page-title">Bill Of Cost Report</h4>

                        </div>
                        <div class="card-body">
                            <div class="col-lg-12">
                                <form method="GET" id="filter-form1">
                                    <div class="row justify-content-end">
                                        <div class="col-md-6 d-flex justify-content-end align-items-end gap-2 doc_button"
                                            style="display: none !important">
                                            <button class="btn teal-custom" id="back-to-table">
                                                <i class="fas fa-arrow-left"></i> Back to Report
                                            </button>
                                            <x-download-dropdown pdf-id="downloadPDF" csv-id="downloadcsv" />

                                            {{-- <button id="downloadPDF" class="btn downloadpdf">
                                                <i class="fas fa-file-pdf"></i> Download PDF
                                            </button> --}}

                                        </div>
                                    </div>
                                </form>
                            </div>


                            <!-- Render DataTable -->
                            <div class="table-sticky-wrapper">
                                <div style="max-height: calc(100vh - 280px);">
                                    {!! $dataTable->table(['class' => 'table custom-datatable resizable-draggable-table'], true) !!}
                                </div>
                            </div>
                        </div>
                        <div id="table-section" style="display: none">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped resizable-draggable-table">
                                    <tbody>
                                        <tr>
                                            <td>Client Name:</td>
                                            <td class="client_name" colspan="5"></td>
                                        </tr>
                                        <tr>
                                            <td>Client Address:</td>
                                            <td class="client_addres" colspan="5"></td>
                                        </tr>
                                        <tr>
                                            <td>Matter</td>
                                            <td class="matter_name"></td>
                                            <td>Bill Date:</td>
                                            <td class="bill_date"></td>
                                            <td colspan="2"></td>
                                        </tr>
                                        <tr>
                                            <td>Ledger Ref</td>
                                            <td class="ledger_refs">
                                                <a class="ledger-link text-primary" href="javascript:void(0);"></a>
                                            </td>

                                            <td>Bill Ref:</td>
                                            <td class="bill_ref"></td>
                                            <td colspan="2"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6"><strong>Particulars</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">On the above information, we expect our fees and other
                                                charges to be:</td>
                                            <td align="center"><strong>(£)</strong></td>
                                            <td align="center"><strong>(£)</strong></td>
                                            <td align="center"><strong>(£)</strong></td>
                                            <td align="center"><strong>(£)</strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">Our Costs</td>
                                            <td align="center"><strong>Net</strong></td>
                                            <td align="center"><strong>VAT</strong></td>
                                            <td align="center"><strong>Total</strong></td>
                                            <td></td>
                                        </tr>
                                    <tbody id="table-body"></tbody>
                                    <tr>
                                        <td align="right"><strong>Our Costs total</strong></td>
                                        <td colspan="3"></td>
                                        <td class="cost_total" align="right"></td>
                                        <td></td>

                                    </tr>
                                    <tr>
                                        <td colspan="6"><strong>Disbursements</strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="1" align="right"><strong>Disbursements Total</strong></td>
                                        <td colspan="4"></td>
                                        <td class="disbursments_total" align="right">0.00</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td align="right"><strong>Bill Total</strong></td>
                                        <td class="bill_total" align="right"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td align="right"><strong>Payment Received</strong></td>
                                        <td class="payment_received" align="right"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td align="right"><strong>Outstanding Balance</strong></td>
                                        <td class="outstanding_balance" align="right"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6">
                                            <ul class="list-unstyled">
                                                <li>Payments can be made by cheque payable to Z Dummy LLP or by Bank
                                                    transfer to the following account.</li>
                                                <li>Bank: Barclays</li>
                                                <li>Account No: 98765432</li>
                                                <li>Sort code: 456789</li>
                                                <li>If you have any queries, please contact Our Office on 02020207812.
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
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
    {!! $dataTable->scripts() !!}

    {{-- ============ RESIZABLE TABLE INITIALIZATION ============ --}}
    <script>
        // Global variable to track ResizableDraggableTable instance
        let resizableTableInstance = null;

        // Function to safely initialize ResizableDraggableTable
        function initializeResizableTable() {
            const tableElement = document.querySelector('#transaction-table');

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
    </script>
    {{-- ============ END RESIZABLE TABLE INITIALIZATION ============ --}}

    {{-- ============ ADD THIS FUNCTION BEFORE ANY OTHER SCRIPTS ============ --}}
    <script>
        function attachEventListeners() {
            const table = $('#transaction-table').DataTable();

            // ✅ Initialize resizable table on draw event
            table.on('draw.dt', function() {
                setTimeout(initializeResizableTable, 100);
            });

            // Date filter toggle
            $('#dateIcon').on('click', function() {
                $('#dateTitle').toggleClass('d-none');
                $('#dateFilter').toggleClass('d-none');
                if (!$('#dateFilter').hasClass('d-none')) {
                    $('#dateFilter').focus();
                }
            });

            $('#dateFilter').on('change', function() {
                table.ajax.reload();
            });

            // Text filters
            const textFilters = [
                'ledgerRef', 'bankAccount', 'paidInOut', 'reference',
                'paymentType', 'netAmount', 'vatAmount', 'totalAmount'
            ];

            textFilters.forEach(filter => {
                $(`#${filter}Icon`).on('click', function() {
                    $(`#${filter}Title`).toggleClass('d-none');
                    $(`#${filter}Filter`).toggleClass('d-none');
                    if (!$(`#${filter}Filter`).hasClass('d-none')) {
                        $(`#${filter}Filter`).focus();
                    }
                });

                $(`#${filter}Filter`).on('keyup', function() {
                    clearTimeout(window.filterTimeout);
                    window.filterTimeout = setTimeout(() => {
                        table.ajax.reload();
                    }, 500);
                });
            });

            // ✅ Initialize resizable table after filters are attached
            setTimeout(initializeResizableTable, 300);
        }
    </script>
    {{-- ============ END OF attachEventListeners FUNCTION ============ --}}

    <script>
        $(document).on('click', '.ref-link', function() {
            var fileId = $(this).data('file-id');
            var ledgerRef = $(this).data('ledger-ref');

            if (fileId && ledgerRef) {
                $.ajax({
                    url: "{{ route('bill.of.cost.data') }}",
                    type: "GET",
                    data: {
                        File_id: fileId,
                        ledger_ref: ledgerRef
                    },
                    success: function(response) {
                        $('#table-body').empty();

                        if (response.file_data) {
                            $('.client_name').text(response.file_data.First_Name + " " + response
                                .file_data.Last_Name);
                            $('.client_address').text(response.file_data.Address1);
                            $('.matter_name').text(response.file_data.Matter);
                            $('.ledger_refs a')
                                .text(response.file_data.Ledger_Ref)
                                .attr('href', "{{ route('client.ledgers') }}?File_id=" + response
                                    .file_data.File_ID + "&ledger_ref=" + response.file_data.Ledger_Ref
                                );
                        }

                        var totalNetAmount = 0;
                        var totalVatAmount = 0;
                        var totalAmount = 0;
                        var chequeList = [];

                        response.transactions.forEach(function(transaction) {
                            var netAmount = parseFloat(transaction.total_amount) || 0;
                            var vatAmount = parseFloat(transaction.vat_amount) || 0;
                            var totalAmountTransaction = parseFloat(transaction.Amount) || 0;

                            if (transaction.Cheque) {
                                chequeList.push(transaction.Cheque);
                            }

                            totalNetAmount += netAmount;
                            totalVatAmount += vatAmount;
                            totalAmount += totalAmountTransaction;

                            var row = `
                            <tr>
                                <td class="description" colspan="2">${transaction.description}</td>
                                <td align="center">${netAmount.toFixed(2)}</td>
                                <td align="center">${vatAmount.toFixed(2)}</td>
                                <td align="center">${totalAmountTransaction.toFixed(2)}</td>
                                <td></td>
                            </tr>
                        `;
                            $('#table-body').append(row);
                        });

                        $('.bill_date').text(response.last_transaction_date);
                        $('.bill_ref').text(chequeList.join(', '));
                        $('.cost_total').text(totalNetAmount.toFixed(2));
                        $('.bill_total').text(totalNetAmount.toFixed(2));
                        $('.payment_received').text(totalNetAmount.toFixed(2));
                        $('.outstanding_balance').text('0.00');

                        $('.dataTables_wrapper').hide();
                        $('#table-section').show();
                        $('.doc_button').show();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('Something went wrong. Please try again.');
                    }
                });
            }
        });
    </script>

    {{-- REST OF YOUR SCRIPTS REMAIN THE SAME --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        $(document).ready(function() {
            $("#downloadPDF").click(function(event) {
                event.preventDefault();
                generatePDF();
            });

            function generatePDF() {
                const {
                    jsPDF
                } = window.jspdf;
                const doc = new jsPDF('p', 'mm', 'a4');

                let pdfElement = document.getElementById('table-section');

                if (!pdfElement || pdfElement.offsetWidth === 0 || pdfElement.offsetHeight === 0) {
                    alert("The section is hidden or empty! Ensure it has content.");
                    return;
                }

                html2canvas(pdfElement, {
                    scale: 2
                }).then(canvas => {
                    let imgData = canvas.toDataURL('image/png');
                    let imgWidth = 210;
                    let imgHeight = (canvas.height * imgWidth) / canvas.width;

                    doc.addImage(imgData, 'PNG', 0, 10, imgWidth, imgHeight);
                    doc.save('Ledger_Report.pdf');
                }).catch(error => {
                    console.error("Error capturing PDF:", error);
                });
            }
        });
        $('#back-to-table').on('click', function() {
            $('#table-section').hide();
            $('.dataTables_wrapper').show();
            $('.doc_button').hide();

            // ✅ Reinitialize resizable table when returning to main table
            setTimeout(initializeResizableTable, 200);
        });
    </script>

    {{-- ============ INITIALIZE ON PAGE LOAD ============ --}}
    <script>
        $(document).ready(function() {
            // Initialize resizable table after DataTable is ready
            setTimeout(initializeResizableTable, 500);
        });
    </script>
@endsection

