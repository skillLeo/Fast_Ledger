@extends('admin.layout.app')
{{-- <style>
    /* Main container for all three tables */
    .tables-container {
        display: flex;
        gap: 0px;
        margin-top: 20px;
        flex-wrap: wrap;
        /* Allow wrapping on smaller screens */
    }

    /* Each table container should have equal width */
    .table-container {
        flex: 1;
        min-width: 300px;
        /* Minimum width to prevent tables from being too narrow */
        display: flex;
        flex-direction: column;
    }

    /* Table header styling */
    .table-header {
        background-color: #f8f9fa;
        padding: 8px;
        text-align: center;
        border: 1px solid #000;
        border-bottom: none;
        color: #000 !important;
        font-weight: bold;
        margin: 0;
    }

    /* Different colored headers for each table */
    .details-header {
        background-color: #dff3f9;
    }

    .office-account-header {
        background-color: #ebebec;
    }

    .client-account-header {
        background-color: #dddddd;
    }

    /* Table styling */
    .account-table {
        border: 1px solid #dee2e6;
        margin-bottom: 0;
        width: 100%;
        table-layout: fixed;
        /* Ensures consistent column widths */
        flex-grow: 1;
    }

    .account-table th,
    .account-table td {
        border: 1px solid #000 !important;
        padding: 8px;
        text-align: center;
        font-size: 12px;
        word-wrap: break-word;
    }

    .account-table th {
        background-color: #f8f9fa;
        font-weight: bold;
    }

    /* Specific table header colors - targeting actual HTML structure */
    .details-table th {
        background-color: #dff3f9 !important;
    }

    /* Target the office account table (second table container) */
    .tables-container .table-container:nth-child(2) .account-table th {
        background-color: #ebebec !important;
    }

    /* Target the client account table (third table container) */
    .tables-container .table-container:nth-child(3) .account-table th {
        background-color: #dddddd !important;
    }

    /* Responsive design for smaller screens */
    @media (max-width: 1200px) {
        .tables-container {
            flex-direction: column;
        }

        .table-container {
            min-width: auto;
            margin-bottom: 20px;
        }

        .table-container:last-child {
            margin-bottom: 0;
        }
    }

    @media (max-width: 768px) {
        .tables-container {
            gap: 0px;
        }

        .account-table th,
        .account-table td {
            padding: 6px;
            font-size: 11px;
        }

        .table-header {
            padding: 6px;
            font-size: 12px;
        }
    }

    /* For PDF version - ensure proper layout */
    #pdf-table-section .tables-container {
        display: flex;
        flex-direction: row;
        gap: 0px;
    }

    #pdf-table-section .table-container {
        flex: 1;
    }

    /* PDF table headers - same targeting approach */
    #pdf-table-section .tables-container .table-container:nth-child(2) .account-table th {
        background-color: #ebebec !important;
    }

    #pdf-table-section .tables-container .table-container:nth-child(3) .account-table th {
        background-color: #dddddd !important;
    }
</style> --}}

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <h4 class="page-title">Client Ledger Report</h4>

                        <div class="card-body mt-3">
                            <div class="col-lg-12">
                                <form method="GET" id="filter-form">
                                    <div class="row mb-4">
                                        <!-- Left Section: Ledger Ref and View Report Button -->
                                        <div class="col-md-2">
                                            <label for="ledger_ref">Ledger Ref:</label>
                                            <input type="text" id="ledger_ref" name="ledger_ref"
                                                value="{{ $Ledger_Ref ?? '' }}" class="form-control"
                                                placeholder="Ledger Ref">
                                            <input type="hidden" id="File_id" name="File_id" class="form-control"
                                                value="{{ $File_id ?? '' }}">
                                            <div id="results-dropdown" class="dropdown-menu ledger_dorpdown"
                                                aria-labelledby="ledger_ref"></div>
                                        </div>

                                        <div class="col-md-4 d-flex align-items-end">
                                            <button type="button" id="filter-btn" class="btn teal-custom">View
                                                Report</button>
                                        </div>

                                        <!-- Right Section: Download Buttons -->
                                        <div style="display: none !important"
                                            class="col-md-4 d-flex justify-content-end align-items-end doc_buttons">
                                            <x-download-dropdown pdf-id="downloadPDF" csv-id="download-csv" />
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div id="table-section" class="mt-4" style="display: none">
                                <div class="d-flex justify-content-center align-items-center mb-3">
                                    <h6 style="margin-top: 10px;margin-right: 23px;">
                                        <span id="Client_Ref"></span> | Client Name: <span id="Client_name"></span> | Ledger
                                        Ref: <span id="ledger_Ref"></span> | Address: <span id="Address"></span>
                                    </h6>


                                </div>
                                <!-- Client Information Section -->
                                {{-- <div class="client-info-section">
                                    <div class="client-info-row">
                                        <div class="client-info-item">
                                            <span class="client-info-label">Name:</span>
                                            <span class="client-info-value" id="Client_name"></span>
                                        </div>
                                        <div class="client-info-item">
                                            <span class="client-info-label">Ledger Ref:</span>
                                            <span class="client-info-value" id="ledger_Ref"></span>
                                        </div>
                                        <div class="client-info-item">
                                            <span class="client-info-label">Matter:</span>
                                            <span class="client-info-value" id="Matter">Personal Injury</span>
                                        </div>
                                    </div>
                                    <div class="client-info-row">
                                        <div class="client-info-item">
                                            <span class="client-info-label">Fee Earner:</span>
                                            <span class="client-info-value" id="Fee_Earner">-</span>
                                        </div>
                                        <div class="client-info-item">
                                            <span class="client-info-label">Status:</span>
                                            <span class="client-info-value status-live">Live</span>
                                        </div>
                                        <div class="client-info-item">
                                            <span class="client-info-label">Address:</span>
                                            <span class="client-info-value" id="Address"></span>
                                        </div>
                                    </div>
                                </div> --}}

                                <!-- Three Separate Tables Container -->
                                <div class="tables-container">
                                    <!-- Details Table -->
                                    <div class="table-container">
                                        <div class="table-header details-header">Details</div>
                                        <table class="table account-table table-truncate details-table resizable-draggable-table">
                                            <thead>
                                                <tr>
                                                    <x-table-search-header column="date" label="Date" type="search"
                                                        class="position-relative" />
                                                    <x-table-search-header column="description" label="Description"
                                                        type="search" />
                                                </tr>
                                            </thead>
                                            <tbody id="details-table-body">
                                                <!-- Rows will be populated here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Office Account Table -->
                                    <div class="table-container">
                                        <div class="table-header office-account-header">Office Account</div>
                                        <table class="table account-table resizable-draggable-table">
                                            <thead>
                                                <tr>
                                                    <x-table-search-header column="office-debit" label="Debit"
                                                        type="search" class="position-relative" />
                                                    <x-table-search-header column="office-credit" label="Credit"
                                                        type="search" class="position-relative" />
                                                    <x-table-search-header column="office-balance" label="Balance"
                                                        type="search" class="position-relative" />
                                                </tr>
                                            </thead>
                                            <tbody id="office-table-body">
                                                <!-- Rows will be populated here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Client Account Table -->
                                    <div class="table-container">
                                        <div class="table-header client-account-header">Client Account</div>
                                        <table class="table account-table resizable-draggable-table resizable-draggable-table">
                                            <thead>
                                                <tr>
                                                    <x-table-search-header column="client-debit" label="Debit"
                                                        type="search" class="position-relative" />
                                                    <x-table-search-header column="client-credit" label="Credit"
                                                        type="search" class="position-relative" />
                                                    <x-table-search-header column="client-balance" label="Balance"
                                                        type="search" class="position-relative" />
                                                </tr>
                                            </thead>
                                            <tbody id="client-table-body">
                                                <!-- Rows will be populated here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- PDF Table Section (Hidden, for PDF generation) -->
                            <div id="pdf-table-section" class="mt-4" style="display: none">
                                <div class="client-info-section">
                                    <div class="client-info-row">
                                        <div class="client-info-item">
                                            <span class="client-info-label">Name:</span>
                                            <span class="client-info-value" id="pdf-Client_name"></span>
                                        </div>
                                        <div class="client-info-item">
                                            <span class="client-info-label">Ledger Ref:</span>
                                            <span class="client-info-value" id="pdf-ledger_Ref"></span>
                                        </div>
                                        <div class="client-info-item">
                                            <span class="client-info-label">Matter:</span>
                                            <span class="client-info-value">Personal Injury</span>
                                        </div>
                                    </div>
                                    <div class="client-info-row">
                                        <div class="client-info-item">
                                            <span class="client-info-label">Fee Earner:</span>
                                            <span class="client-info-value">-</span>
                                        </div>
                                        <div class="client-info-item">
                                            <span class="client-info-label">Status:</span>
                                            <span class="client-info-value status-live">Live</span>
                                        </div>
                                        <div class="client-info-item">
                                            <span class="client-info-label">Address:</span>
                                            <span class="client-info-value" id="pdf-Address"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="tables-container col-md-12">
                                    <div class="table-container">
                                        <div class="table-header details-header">Details</div>
                                        <table class="table account-table details-table resizable-draggable-table">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Description</th>
                                                </tr>
                                            </thead>
                                            <tbody id="pdf-details-table-body">
                                                <!-- Rows for PDF -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="table-container">
                                        <div class="table-header office-account-header">Office Account</div>
                                        <table class="table account-table resizable-draggable-table">
                                            <thead>
                                                <tr>
                                                    <th>Debit</th>
                                                    <th>Credit</th>
                                                    <th>Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody id="pdf-office-table-body">
                                                <!-- Rows for PDF -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="table-container">
                                        <div class="table-header client-account-header">Client Account</div>
                                        <table class="table account-table resizable-draggable-table">
                                            <thead>
                                                <tr>
                                                    <th>Debit</th>
                                                    <th>Credit</th>
                                                    <th>Balance</th>
                                                </tr>
                                            </thead>
                                            <tbody id="pdf-client-table-body">
                                                <!-- Rows for PDF -->
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
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        // Ledger search functionality
        document.getElementById('ledger_ref').addEventListener('input', function() {
            const query = this.value;

            if (query.length < 2) {
                document.getElementById('results-dropdown').innerHTML = '';
                document.getElementById('results-dropdown').classList.remove('show');
                return;
            }

            fetch(`/search-ledger?query=${query}`)
                .then(response => response.json())
                .then(data => {
                    const dropdown = document.getElementById('results-dropdown');
                    dropdown.innerHTML = '';
                    dropdown.style.width = '15.3%';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const option = document.createElement('a');
                            option.classList.add('dropdown-item');
                            option.href = '#';
                            option.textContent = item.Ledger_Ref;
                            option.addEventListener('click', function() {
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

        $(document).ready(function() {
            // Function to format currency values
            function formatCurrency(value) {
                if (!value || value === '0' || value === '0.00') {
                    return '0.00';
                }
                return parseFloat(value).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Function to format date
            // Updated function to handle DD/MM/YYYY format
            function formatDate(dateString) {
                if (!dateString || dateString === 'Invalid Date') return 'Invalid Date';

                // Check if it's already in DD/MM/YYYY format
                if (dateString.includes('/')) {
                    const parts = dateString.split('/');
                    if (parts.length === 3) {
                        // Already formatted as DD/MM/YYYY, return as is
                        return dateString;
                    }
                }

                // Otherwise try to parse as ISO date
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return dateString; // Return original if can't parse

                return date.toLocaleDateString('en-GB'); // DD/MM/YYYY format
            }

            // Function to populate three separate tables
            function populateTables(transactions, isForPdf = false) {
                const detailsBodyId = isForPdf ? 'pdf-details-table-body' : 'details-table-body';
                const officeBodyId = isForPdf ? 'pdf-office-table-body' : 'office-table-body';
                const clientBodyId = isForPdf ? 'pdf-client-table-body' : 'client-table-body';

                // Clear previous data
                $(`#${detailsBodyId}`).empty();
                $(`#${officeBodyId}`).empty();
                $(`#${clientBodyId}`).empty();

                if (transactions.length > 0) {
                    $.each(transactions, function(index, record) {
                        // Details table row - ADD data-column attributes
                        const detailsRow = `
                <tr>
                    <td data-column="date">${record.TransactionDate || 'N/A'}</td>
                    <td data-column="description">${record.Description || ''}</td>
                </tr>
            `;
                        $(`#${detailsBodyId}`).append(detailsRow);

                        // Office Account table row - ADD data-column attributes
                 const officeRow = `
                            <tr>
                                <td class="amount-column" data-column="office-debit">${formatCurrency(record.Office_Debit)}</td>
                                <td class="amount-column" data-column="office-credit">${formatCurrency(record.Office_Credit)}</td>
                                <td class="amount-column" data-column="office-balance">${formatCurrency(record.Office_Balance)}</td>
                            </tr>
                        `;
                        $(`#${officeBodyId}`).append(officeRow);

                        // Client Account table row - ADD data-column attributes
                       const clientRow = `
                            <tr>
                                <td class="amount-column" data-column="client-debit">${formatCurrency(record.Client_Debit)}</td>
                                <td class="amount-column" data-column="client-credit">${formatCurrency(record.Client_Credit)}</td>
                                <td class="amount-column" data-column="client-balance">${formatCurrency(record.Client_Balance)}</td>
                            </tr>
                        `;
                        $(`#${clientBodyId}`).append(clientRow);
                    });
                } else {
                    // No data message
                    const noDataRow =
                        '<tr><td colspan="2" class="text-center" style="padding: 20px; color: #6c757d;">No transactions found</td></tr>';
                    const noDataRowAccount =
                        '<tr><td colspan="3" class="text-center" style="padding: 20px; color: #6c757d;">No transactions found</td></tr>';

                    $(`#${detailsBodyId}`).html(noDataRow);
                    $(`#${officeBodyId}`).html(noDataRowAccount);
                    $(`#${clientBodyId}`).html(noDataRowAccount);
                }
            }

            // Function to update client information
            function updateClientInfo(response, isForPdf = false) {
                const clientName = response.file_data ?
                    `${response.file_data.First_Name || ''} ${response.file_data.Last_Name || ''}`.trim() : 'N/A';
                const address = response.file_data ?
                    `${response.file_data.Address1 || ''} ${response.file_data.Address2 || ''} ${response.file_data.Town || ''}`
                    .trim() : 'N/A';
                const ledgerRef = response.file_data ? response.file_data.Ledger_Ref : 'N/A';

                if (isForPdf) {
                    $('#pdf-Client_name').text(clientName);
                    $('#pdf-Address').text(address);
                    $('#pdf-ledger_Ref').text(ledgerRef);
                } else {
                    $('#Client_name').text(clientName);
                    $('#Address').text(address);
                    $('#ledger_Ref').text(ledgerRef);
                }
            }

            // Main filter button click event
            $('#filter-btn').click(function() {
                const File_id = $('#File_id').val();
                const ledger_ref = $('#ledger_ref').val();

                if (!File_id || !ledger_ref) {
                    alert('Please ensure both File ID and Ledger Ref are selected.');
                    return;
                }

                // Show loading state
                $('#details-table-body').html(
                    '<tr><td colspan="2" class="text-center" style="padding: 20px;"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...</td></tr>'
                );
                $('#office-table-body').html(
                    '<tr><td colspan="3" class="text-center" style="padding: 20px;"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...</td></tr>'
                );
                $('#client-table-body').html(
                    '<tr><td colspan="3" class="text-center" style="padding: 20px;"><div class="spinner-border spinner-border-sm me-2" role="status"></div>Loading...</td></tr>'
                );
                $('#table-section').show();

                $.ajax({
                    url: "{{ route('client.ledger.data') }}",
                    type: "GET",
                    data: {
                        File_id: File_id,
                        ledger_ref: ledger_ref
                    },
                    success: function(response) {
                        console.log('Response received:', response);

                        // Update client information for both display and PDF
                        updateClientInfo(response, false);
                        updateClientInfo(response, true);

                        // Populate both sets of tables
                        populateTables(response.transactions || [], false);
                        populateTables(response.transactions || [], true);

                        // Show/hide download buttons
                        if (response.transactions && response.transactions.length > 0) {
                            $('.doc_buttons').css('display', 'flex');
                        } else {
                            $('.doc_buttons').hide();
                        }

                        $('#table-section').show();
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', {
                            xhr,
                            status,
                            error
                        });

                        const errorRow =
                            '<tr><td colspan="2" class="text-center" style="padding: 20px; color: #dc3545;"><i class="fas fa-exclamation-triangle me-2"></i>Error loading data</td></tr>';
                        const errorRowAccount =
                            '<tr><td colspan="3" class="text-center" style="padding: 20px; color: #dc3545;"><i class="fas fa-exclamation-triangle me-2"></i>Error loading data</td></tr>';

                        $('#details-table-body').html(errorRow);
                        $('#office-table-body').html(errorRowAccount);
                        $('#client-table-body').html(errorRowAccount);

                        alert('Something went wrong. Please try again.');
                        $('.doc_buttons').hide();
                    }
                });
            });

            // PDF Download functionality
            $("#downloadPDF").click(function(event) {
                event.preventDefault();
                generatePDF();
            });

            function generatePDF() {
                const {
                    jsPDF
                } = window.jspdf;
                const doc = new jsPDF('l', 'mm', 'a4');

                $('#pdf-table-section').show();

                let pdfElement = document.getElementById('pdf-table-section');

                if (!pdfElement || pdfElement.offsetWidth === 0 || pdfElement.offsetHeight === 0) {
                    alert("No data available for PDF generation!");
                    $('#pdf-table-section').hide();
                    return;
                }

                html2canvas(pdfElement, {
                    scale: 1.5,
                    useCORS: true,
                    allowTaint: true
                }).then(canvas => {
                    let imgData = canvas.toDataURL('image/png');
                    let imgWidth = 280;
                    let imgHeight = (canvas.height * imgWidth) / canvas.width;

                    doc.addImage(imgData, 'PNG', 10, 10, imgWidth, imgHeight);
                    doc.save(`Ledger_Report_${$('#ledger_ref').val()}.pdf`);

                    $('#pdf-table-section').hide();
                }).catch(error => {
                    console.error("Error generating PDF:", error);
                    alert("Error generating PDF. Please try again.");
                    $('#pdf-table-section').hide();
                });
            }

            // Auto-load from URL parameters
            function getQueryParam(param) {
                let urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(param);
            }

            let urlFileId = getQueryParam('File_id');
            let urlLedgerRef = getQueryParam('ledger_ref');

            if (urlFileId && urlLedgerRef) {
                $('#File_id').val(urlFileId);
                $('#ledger_ref').val(urlLedgerRef);
                $('#filter-btn').click();
            }

            // Auto-load if values are already present
            let existingFileId = $('#File_id').val();
            let existingLedgerRef = $('#ledger_ref').val();

            if (existingFileId && existingLedgerRef) {
                $('#filter-btn').click();
            }
        });
    </script>
@endsection
