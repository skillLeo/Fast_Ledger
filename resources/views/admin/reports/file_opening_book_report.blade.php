@extends('admin.layout.app')

@section('content')
    @extends('admin.partial.errors')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <h4 class="page-title">File Opening Book Report</h4>
                        
                        <div class="card-body mt-3">
                            <!-- Filter Form -->
                            <form method="GET" id="filter-form">
                                <div class="row mb-2">
                                    <div class="col-md-2">
                                        <label for="from_date">From Date:</label>
                                        <input 
                                            type="date" 
                                            id="from_date" 
                                            name="from_date"
                                            class="form-control datepicker" 
                                            placeholder="dd/mm/yyyy"
                                            value="{{ old('from_date', $fromDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}"
                                        >
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <label for="to_date">To Date:</label>
                                        <input 
                                            type="date" 
                                            id="to_date" 
                                            name="to_date" 
                                            class="form-control datepicker"
                                            placeholder="dd/mm/yyyy"
                                            value="{{ old('to_date', $toDate ?? \Carbon\Carbon::now()->format('Y-m-d')) }}"
                                        >
                                    </div>
                                    
                                    <div class="col-md-2 d-flex align-items-end">
                                        <div class="ms-2">
                                            <button type="button" id="filter-btn" class="btn teal-custom">
                                                View Report
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Table Section -->
                            <div id="table-section" class="mt-4" style="display: none;">
                                <!-- Report Header -->
                                <div class="d-flex justify-content-center align-items-center mb-3">
                                    <h5 style="margin-top: 10px; margin-right: 23px;">
                                        File Opening Book Report | 
                                        From Date: <span id="display-from-date"></span> | 
                                        To Date: <span id="display-to-date"></span>
                                    </h5>
                                    <x-download-dropdown pdf-id="download-pdf" csv-id="download-csv" />
                                </div>

                                <!-- Active Filters Display -->
                                <div id="active-filters" class="active-filters mb-3"></div>

                                <!-- Data Table -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped resizable-draggable-table">
                                        <thead>
                                            <tr style="background-color:#bbddf2 !important;">
                                                <th>S/No</th>
                                                <x-table-search-header 
                                                    column="file-open-date" 
                                                    label="File Open Date" 
                                                    type="search" 
                                                />
                                                <x-table-search-header 
                                                    column="ledger-ref" 
                                                    label="Ledger Ref" 
                                                    type="search" 
                                                />
                                                <x-table-search-header 
                                                    column="matter" 
                                                    label="Matter" 
                                                    type="search" 
                                                />
                                                <x-table-search-header 
                                                    column="client-name" 
                                                    label="Client Name" 
                                                    type="search" 
                                                />
                                                <x-table-search-header 
                                                    column="property-address" 
                                                    label="Property/Matter Address" 
                                                    type="search" 
                                                />
                                                <x-table-search-header 
                                                    column="fee-earner" 
                                                    label="Fee Earner" 
                                                    type="search" 
                                                />
                                                <x-table-search-header 
                                                    column="status" 
                                                    label="Status" 
                                                    type="dropdown"
                                                    :options="[
                                                        'Live' => 'Live',
                                                        'Close' => 'Close',
                                                        'Abortive' => 'Abortive',
                                                        'Close Abortive' => 'Close Abortive',
                                                    ]" 
                                                />
                                                <x-table-search-header 
                                                    column="close-date" 
                                                    label="Close Date" 
                                                    type="sort" 
                                                />
                                            </tr>
                                        </thead>
                                        <tbody id="file-table-body">
                                            <!-- Data will be appended here via AJAX -->
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
{{-- ============================================================
    RESIZABLE & DRAGGABLE TABLE INITIALIZATION
============================================================ --}}
<script>
    /**
     * Global variable to track ResizableDraggableTable instance
     * Prevents multiple initializations
     */
    let resizableTableInstance = null;

    /**
     * Initialize ResizableDraggableTable with proper checks
     * Handles width calculation and DOM readiness
     */
    function initializeResizableTable() {
        const tableElement = document.querySelector('.resizable-draggable-table');
        
        // Validation: Check if table exists
        if (!tableElement) {
            console.warn('ResizableTable: Table element not found');
            return;
        }

        // Validation: Check if table has content
        const tbody = tableElement.querySelector('tbody');
        if (!tbody || tbody.children.length === 0) {
            console.warn('ResizableTable: No tbody or empty tbody found, skipping initialization');
            return;
        }

        // Validation: Check if ResizableDraggableTable class is loaded
        if (typeof ResizableDraggableTable === 'undefined') {
            console.error('ResizableTable: ResizableDraggableTable class not found');
            return;
        }

        // Log reinitialization
        if (resizableTableInstance) {
            console.log('ResizableTable: Reinitializing...');
        }
        
        // Force table to calculate natural widths first
        // This ensures proper width calculation before fixing layout
        tableElement.style.width = 'auto';
        tableElement.style.tableLayout = 'auto';
        
        // Force browser reflow to get accurate measurements
        void tableElement.offsetWidth;
        
        // Apply fixed layout for resizing functionality
        tableElement.style.tableLayout = 'fixed';
        
        // Initialize the resizable/draggable functionality
        resizableTableInstance = new ResizableDraggableTable(tableElement);
        console.log('ResizableTable: Initialized successfully');
    }
</script>

{{-- ============================================================
    MAIN APPLICATION LOGIC
============================================================ --}}
<script>
$(document).ready(function() {
    
    // ========================================
    // UTILITY FUNCTIONS
    // ========================================
    
    /**
     * Format date from YYYY-MM-DD to DD/MM/YYYY
     * @param {string} date - Date string in YYYY-MM-DD format
     * @returns {string} Formatted date string
     */
    function formatDate(date) {
        if (!date) return '';
        
        const parts = date.split('-');
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    /**
     * Format date object to DD/MM/YYYY
     * @param {string} dateString - Date string
     * @returns {string} Formatted date or 'N/A'
     */
    function formatDates(dateString) {
        if (!dateString) return 'N/A';
        
        const dateObj = new Date(dateString);
        const day = String(dateObj.getDate()).padStart(2, '0');
        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
        const year = dateObj.getFullYear();
        
        return `${day}/${month}/${year}`;
    }

    /**
     * Map status code to readable status text
     * @param {string} statusCode - Single letter status code
     * @returns {string} Human-readable status
     */
    function getStatusLabel(statusCode) {
        const statusMap = {
            'L': 'Live',
            'C': 'Close',
            'A': 'Abortive',
            'I': 'Close Abortive'
        };
        
        return statusMap[statusCode] || 'Unknown';
    }

    /**
     * Validate date inputs
     * @returns {boolean} True if valid, false otherwise
     */
    function validateDates() {
        const fromDate = $('#from_date').val();
        const toDate = $('#to_date').val();

        if (!fromDate || !toDate) {
            alert('Please select From Date and To Date.');
            return false;
        }

        return true;
    }

    // ========================================
    // DATA FETCHING & TABLE POPULATION
    // ========================================
    
    /**
     * Fetch report data via AJAX and populate table
     * @param {number} page - Page number for pagination
     */
    function fetchReportData(page = 1) {
        // Validate dates before making request
        if (!validateDates()) {
            return;
        }

        const fromDate = $('#from_date').val();
        const toDate = $('#to_date').val();

        // Make AJAX request
        $.ajax({
            url: "{{ route('file.report.data') }}",
            type: "GET",
            data: {
                from_date: fromDate,
                to_date: toDate,
                page: page
            },
            beforeSend: function() {
                // Optional: Show loading indicator
                $('#filter-btn').prop('disabled', true).text('Loading...');
            },
            success: function(response) {
                handleSuccessResponse(response, fromDate, toDate);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('Something went wrong. Please try again.');
            },
            complete: function() {
                // Re-enable button
                $('#filter-btn').prop('disabled', false).text('View Report');
            }
        });
    }

    /**
     * Handle successful AJAX response
     * @param {object} response - AJAX response data
     * @param {string} fromDate - From date value
     * @param {string} toDate - To date value
     */
    function handleSuccessResponse(response, fromDate, toDate) {
        const tbody = $('#file-table-body');
        tbody.empty();

        if (response.data && response.data.length > 0) {
            populateTableWithData(response.data);
            updateReportHeader(fromDate, toDate);
            reapplyFilters();
            
            // Initialize resizable table after data is loaded
            setTimeout(initializeResizableTable, 200);
        } else {
            showNoRecordsMessage();
            
            // Initialize resizable table even for empty state
            setTimeout(initializeResizableTable, 200);
        }

        // Show the table section
        $('#table-section').show();
    }

    /**
     * Populate table with fetched data
     * @param {Array} data - Array of record objects
     */
    function populateTableWithData(data) {
        const tbody = $('#file-table-body');
        
        $.each(data, function(index, record) {
            const row = createTableRow(record, index + 1);
            tbody.append(row);
        });
    }

    /**
     * Create a table row HTML from record data
     * @param {object} record - Single record object
     * @param {number} serialNumber - Row serial number
     * @returns {string} HTML string for table row
     */
    function createTableRow(record, serialNumber) {
        const statusLabel = getStatusLabel(record.Status);
        const fullClientName = `${record.First_Name || ''} ${record.Last_Name || ''}`.trim();
        const fullAddress = [
            record.Address1,
            record.Address2,
            record.Town,
            record.Post_Code
        ].filter(Boolean).join(' ').trim();

        return `
            <tr>
                <td>${serialNumber}</td>
                <td data-column="file-open-date">${formatDates(record.File_Date)}</td>
                <td data-column="ledger-ref">
                    <a class="text-primary" href="/file/update/${record.File_ID || ''}">
                        ${record.Ledger_Ref || 'N/A'}
                    </a>
                </td>
                <td data-column="matter" class="truncate-cell" title="${record.Matter || 'N/A'}">
                    ${record.Matter || 'N/A'}
                </td>
                <td data-column="client-name" class="truncate-cell" title="${fullClientName}">
                    ${fullClientName || 'N/A'}
                </td>
                <td data-column="property-address" class="truncate-cell" title="${fullAddress}">
                    ${fullAddress || 'N/A'}
                </td>
                <td data-column="fee-earner" class="truncate-cell" title="${record.Fee_Earner || 'N/A'}">
                    ${record.Fee_Earner || 'N/A'}
                </td>
                <td data-column="status">${statusLabel}</td>
                <td data-column="close-date">${formatDates(record.File_Date)}</td>
            </tr>
        `;
    }

    /**
     * Show "No records found" message
     */
    function showNoRecordsMessage() {
        $('#file-table-body').html(
            '<tr><td colspan="9" class="text-center">No records found</td></tr>'
        );
    }

    /**
     * Update report header with date range
     * @param {string} fromDate - From date
     * @param {string} toDate - To date
     */
    function updateReportHeader(fromDate, toDate) {
        $('#display-from-date').text(formatDate(fromDate));
        $('#display-to-date').text(formatDate(toDate));
    }

    /**
     * Reapply active filters (if TableSearch component is available)
     */
    function reapplyFilters() {
        if (typeof TableSearch !== 'undefined' && TableSearch.filterTable) {
            TableSearch.filterTable();
        }
    }

    // ========================================
    // EVENT HANDLERS
    // ========================================
    
    /**
     * Handle filter button click
     */
    $('#filter-btn').on('click', function() {
        fetchReportData();
    });

    /**
     * Handle pagination link clicks
     */
    $(document).on('click', '.pagination a', function(event) {
        event.preventDefault();
        
        const page = $(this).attr('href').split('page=')[1];
        fetchReportData(page);
    });

    /**
     * Handle PDF download button click
     */
    $('#download-pdf').on('click', function() {
        if (!validateDates()) {
            return;
        }

        const fromDate = $('#from_date').val();
        const toDate = $('#to_date').val();
        const pdfUrl = "{{ route('file.report.pdf') }}" + 
                       "?from_date=" + encodeURIComponent(fromDate) + 
                       "&to_date=" + encodeURIComponent(toDate);
        
        window.location.href = pdfUrl;
    });

    /**
     * Handle CSV download button click
     */
    $('#download-csv').on('click', function() {
        if (!validateDates()) {
            return;
        }

        const fromDate = $('#from_date').val();
        const toDate = $('#to_date').val();
        const csvUrl = "{{ route('file.report.csv') }}" + 
                       "?from_date=" + fromDate + 
                       "&to_date=" + toDate;
        
        window.location.href = csvUrl;
    });

    // ========================================
    // INITIALIZATION ON PAGE LOAD
    // ========================================
    
    /**
     * Check if table has data on page load and initialize resizable table
     */
    setTimeout(function() {
        const tbody = document.querySelector('#file-table-body');
        
        if (tbody && tbody.children.length > 0) {
            initializeResizableTable();
        }
    }, 500);
});
</script>
@endsection