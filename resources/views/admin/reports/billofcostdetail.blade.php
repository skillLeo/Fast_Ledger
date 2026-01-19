@extends('admin.layout.app')
<style>
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
                        <div class="card-header">
                            <h4 class="page-title">Bill Of Cost Report</h4>
                        </div>
                        <div class="card-body">
                            <div class="col-lg-12">
                                <form method="GET" id="filter-form1">
                                    <div class="row mb-4">


                                        <div style="display:none"
                                            class="col-md-4 d-flex justify-content-end align-items-end  doc_button">
                                            <button id="downloadPDF" class="btn downloadpdf me-2"><i
                                                    class="fas fa-file-pdf"></i>Download PDF</button>
                                            <button id="download-csv" class="btn downloadcsv"> <i
                                                    class="fas fa-file-excel"></i>Download CSV</button>
                                            <a href="{{ url()->previous() }}" class="btn btn-secondary me-2">
                                                <i class="fas fa-arrow-left"></i> Back
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Table Section (Initially Hidden) -->
                            <div id="table-section" class="mt-4" style="display: none">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>




<script>
    @push('scripts')
        <
        script src = "https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" >
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
@section('scripts')
    {!! $dataTable->scripts() !!}


    document.getElementById('ledger_ref').addEventListener('input', function () {
    const query = this.value;

    if (query.length < 2) { document.getElementById('results-dropdown').innerHTML='' ;
        document.getElementById('results-dropdown').classList.remove('show'); return; }
        fetch(`/search-ledger?query=${query}`) .then(response=> response.json())
        .then(data => {
        const dropdown = document.getElementById('results-dropdown');
        dropdown.innerHTML = '';

        if (data.length > 0) {
        data.forEach(item => {
        const option = document.createElement('a');
        option.classList.add('dropdown-item');
        option.href = '#';
        option.textContent = item.Ledger_Ref;
        option.addEventListener('click', function () {
        document.getElementById('ledger_ref').value = item.Ledger_Ref;
        dropdown.classList.remove('show');
        });
        option.addEventListener('click', function () {
        document.getElementById('ledger_ref').value = item.Ledger_Ref;

        document.getElementById('File_id').value = item.file_id;

        dropdown.classList.remove('show');
        });
        dropdown.appendChild(option);
        });
        dropdown.classList.add('show');
        } else {
        dropdown.classList.remove('show');
        }
        })
        .catch(error => console.error('Error:', error));
        });

        $(document).ready(function () {
        $("#downloadPDF").click(function (event) {
        event.preventDefault(); // Prevent form submission
        generatePDF();
        });

        function generatePDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');

        let pdfElement = document.getElementById('table-section');

        if (!pdfElement || pdfElement.offsetWidth === 0 || pdfElement.offsetHeight === 0) {
        alert("The section is hidden or empty! Ensure it has content.");
        return;
        }

        html2canvas(pdfElement, { scale: 2 }).then(canvas => {
        let imgData = canvas.toDataURL('image/png');
        let imgWidth = 210; // A4 width in mm
        let imgHeight = (canvas.height * imgWidth) / canvas.width;

        doc.addImage(imgData, 'PNG', 0, 10, imgWidth, imgHeight);
        doc.save('Ledger_Report.pdf');
        }).catch(error => {
        console.error("Error capturing PDF:", error);
        });
        }
        });

        $(document).ready(function () {
        $('#download-pdf').click(function () {
        var fromDate = $('#from_date').val();
        var toDate = $('#to_date').val();

        if (!fromDate || !toDate) {
        alert('Please select both From Date and To Date before downloading.');
        return;
        }

        window.location.href = "{{ route('file.report.pdf') }}?from_date=" + fromDate +
        "&to_date=" + toDate;
            });

            $('#download-csv').click(function() {
                var fromDate = $('#from_date').val();
                var toDate = $('#to_date').val();

                if (!fromDate || !toDate) {
                    alert('Please select both From Date and To Date before downloading.');
                    return;
                }

                window.location.href = "{{ route('file.report.csv') }}?from_date="
        + fromDate +
        "&to_date=" + toDate;
            });
        });

        $(document).ready(function() {
            $('#file-table').DataTable({
                "pagingType":
        "simple_numbers", // Use smaller pagination
        "lengthMenu": [10, 25, 50, 100], // Control page size
        "language": {
        "paginate": {
        "previous": "<", // Use smaller text "next" : ">" } } }); }); $(document).on('click', '.description' , function () {
            var transactionID=$(this).data('transaction-id'); // Get Transaction_ID console.log('Updating Transaction ID:',
            transactionID); // Perform AJAX request or open a modal for update }); </script>
        @endsection
