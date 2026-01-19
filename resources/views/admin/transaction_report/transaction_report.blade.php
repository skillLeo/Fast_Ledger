@extends('admin.layout.app')
<style>
    /* Fixed height for table headers to prevent expansion */
    .main-content .custom-card #transaction-table.table thead th {
        padding: 5px 2px !important;
        height: 30px !important;
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
        z-index: 10 !important;
        padding: 4px 2px !important;
        font-size: 13px !important;
        height: 20px !important;
    }

    /* Keep title text in normal flow */
    #transaction-table thead th .d-inline {
        display: inline-block !important;
        white-space: nowrap;
    }

    /* Prevent table layout from auto-adjusting */
    .resizable-draggable-table {
        table-layout: fixed;
        width: 100%;
    }

    /* Resize handle styling */
    .resizable-draggable-table th {
        position: relative;
    }

    .resizable-draggable-table .resize-handle {
        position: absolute;
        right: 0;
        top: 0;
        width: 5px;
        height: 100%;
        cursor: col-resize;
        background: transparent;
        z-index: 15;
    }

    .resizable-draggable-table .resize-handle:hover {
        background: rgba(0, 123, 255, 0.3);
    }

    .resizable-draggable-table .resize-handle:active {
        background: rgba(0, 123, 255, 0.5);
    }

    .resizable-draggable-table th.resizing {
        background-color: rgba(0, 123, 255, 0.1);
    }

    .resizable-draggable-table th,
    .resizable-draggable-table td {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>

@section('content')
    @extends('admin.partial.errors')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <h4 class="card-title" style="font-size: 18px">Transactions</h4>

                        <div class="col-xl-12 mt-4">
                            <div class="card-header d-flex justify-content-between">
                                <form method="GET" id="filter-form">
                                    <div class="d-flex">
                                        <div class="input-group" style="width: 200px;">
                                            <span class="input-group-text">From Date:</span>
                                            <input type="date" id="from_date" name="from_date" class="form-control"
                                                value="{{ request('from_date') }}">
                                        </div>
                                        <div class="input-group" style="width: 188px;">
                                            <span class="input-group-text">To Date:</span>
                                            <input type="date" id="to_date" name="to_date" class="form-control"
                                                value="{{ request('to_date') }}">
                                        </div>
                                    </div>
                                </form>

                                <div class="d-flex gap-2">
                                    <x-download-dropdown pdf-id="downloadPDF" csv-id="download-csv" />
                                    @if (session()->has('impersonator_id'))
                                        <button id="delete-selected" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                {!! $dataTable->table(['class' => 'table custom-datatable resizable-draggable-table'], true) !!}
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
        let dataTable;

        $(document).ready(function() {
            // PDF Download Handler
            $('#downloadPDF').on('click', function() {
                $.ajax({
                    url: "{{ route('transaction.download.pdf') }}",
                    type: "GET",
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(data) {
                        var blob = new Blob([data], {
                            type: 'application/pdf'
                        });
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "daybook_report.pdf";
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    },
                    error: function() {
                        alert('Failed to download PDF');
                    }
                });
            });

            // Wait for DataTable initialization
            setTimeout(initializeTableFeatures, 500);
        });

        // Global function called from DataTable initComplete
        function initializeTableFeatures() {
            try {
                dataTable = $('.custom-datatable').DataTable();

                if (dataTable) {
                    console.log('DataTable initialized');

                    setupFilters();
                    setupAutocomplete();
                    setupBulkOperations();
                    setupClearFilters();
                    
                    // Initialize resizable-draggable functionality
                    initializeResizableDraggable();

                    // Handle table draw events (pagination, filtering, etc.)
                    dataTable.on('draw.dt', function() {
                        // Re-setup event handlers after table redraw
                        setupBulkOperations();
                        initializeResizableDraggable(); // Re-initialize resize handles
                    });
                }
            } catch (error) {
                console.error('Error initializing table features:', error);
            }
        }

        function initializeResizableDraggable() {
            // Remove any existing resize handles to prevent duplicates
            $('.resizable-draggable-table .resize-handle').remove();
            
            const table = $('.resizable-draggable-table');
            
            // Check if using a plugin
            if (typeof table.resizableColumns === 'function') {
                try {
                    table.resizableColumns('destroy');
                } catch (e) {
                    // Ignore if not initialized
                }
                table.resizableColumns();
            } else {
                // Manual initialization for resizable-draggable-table
                table.find('thead th').each(function(index) {
                    const $th = $(this);
                    
                    // Add resize handle
                    const $handle = $('<div class="resize-handle"></div>');
                    $th.append($handle);
                    
                    let startX, startWidth;
                    
                    $handle.on('mousedown', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        startX = e.pageX;
                        startWidth = $th.width();
                        
                        // Add visual feedback
                        $th.addClass('resizing');
                        
                        $(document).on('mousemove.resize', function(e) {
                            const diff = e.pageX - startX;
                            const newWidth = startWidth + diff;
                            
                            if (newWidth > 50) { // Minimum width
                                // Set width on both header and corresponding body cells
                                $th.css('width', newWidth + 'px');
                                table.find('tbody td:nth-child(' + (index + 1) + ')').css('width', newWidth + 'px');
                            }
                        });
                        
                        $(document).on('mouseup.resize', function() {
                            $(document).off('mousemove.resize mouseup.resize');
                            $th.removeClass('resizing');
                        });
                    });
                });
            }
        }

        function setupFilters() {
            try {
                // Text fields with search icon
                const textFields = ['ledgerRef', 'reference'];
                textFields.forEach(field => {
                    attachTextFilterEvents(field);
                });

                // Dropdown fields
                const dropdownFields = ['bankAccount', 'paidInOut', 'paymentType'];
                dropdownFields.forEach(field => {
                    attachDropdownToggleEvents(field);
                });

                // Date filter
                attachDateFilterEvents();

                // Amount fields (Net Amount, VAT Amount, Total Amount)
                const amountFields = ['netAmount', 'vatAmount', 'totalAmount'];
                amountFields.forEach(field => {
                    attachAmountFilterEvents(field);
                });

            } catch (error) {
                console.error('Error setting up filters:', error);
            }
        }

        function attachDateFilterEvents() {
            const $icon = $('#dateIcon');
            const $input = $('#dateFilter');
            const $title = $('#dateTitle');
            const $th = $icon.closest("th");

            $icon.off('click').on('click', function() {
                const isHidden = $input.hasClass('d-none');
                $input.toggleClass('d-none', !isHidden);
                $title.toggleClass('d-none', isHidden);

                if (isHidden) {
                    $input.focus();
                    $icon.removeClass('fa-calendar-alt').addClass('fa-times');
                    $th.addClass("filter-active");
                } else {
                    $input.val('');
                    $icon.removeClass('fa-times').addClass('fa-calendar-alt');
                    $th.removeClass("filter-active");

                    if (dataTable) dataTable.ajax.reload();
                }
            });

            $input.off('change').on('change', function() {
                if (dataTable) dataTable.ajax.reload();
            });
        }

        function attachAmountFilterEvents(field) {
            const iconId = `#${field}Icon`;
            const inputId = `#${field}Filter`;
            const titleId = `#${field}Title`;

            $(iconId).off('click').on('click', function() {
                const $icon = $(this);
                const $input = $(inputId);
                const $title = $(titleId);
                const isHidden = $input.hasClass('d-none');
                const $th = $icon.closest("th");

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

                    if (dataTable) dataTable.ajax.reload();
                }
            });

            $(inputId).off('input').on('input', function() {
                clearTimeout(window[`${field}Timeout`]);
                window[`${field}Timeout`] = setTimeout(function() {
                    if (dataTable) dataTable.ajax.reload();
                }, 400);
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

                    $('.custom-datatable').DataTable().ajax.reload();
                }
            });

            $(inputId).off('input').on('input', function() {
                clearTimeout(window[`${field}Timeout`]);
                window[`${field}Timeout`] = setTimeout(function() {
                    $('.custom-datatable').DataTable().ajax.reload();
                }, 400);
            });
        }

        function attachDropdownToggleEvents(field) {
            const $icon = $(`#${field}Icon`);
            const $dropdown = $(`#${field}Filter`);
            const $title = $(`#${field}Title`);
            const $th = $icon.closest("th");

            $icon.off('click').on('click', function() {
                const isHidden = $dropdown.hasClass('d-none');

                $dropdown.toggleClass('d-none', !isHidden);
                $title.toggleClass('d-none', isHidden);

                if (isHidden) {
                    $icon.removeClass('fa-chevron-down').addClass('fa-times');
                    $th.addClass("filter-active");
                } else {
                    $dropdown.val('');
                    $icon.removeClass('fa-times').addClass('fa-chevron-down');
                    $th.removeClass("filter-active");

                    $('.custom-datatable').DataTable().ajax.reload();
                }
            });

            $dropdown.off('change').on('change', function() {
                $('.custom-datatable').DataTable().ajax.reload();
            });
        }

        // Autocomplete Functions
        function showAutocompleteSuggestions($input, data, field) {
            hideAutocompleteSuggestions();
            if (data.length === 0) return;

            const $suggestions = $('<div class="autocomplete-suggestions"></div>');
            $suggestions.css({
                'position': 'absolute',
                'top': $input.offset().top + $input.outerHeight(),
                'left': $input.offset().left,
                'width': $input.outerWidth(),
                'background': 'white',
                'border': '1px solid #ccc',
                'border-radius': '4px',
                'box-shadow': '0 2px 10px rgba(0,0,0,0.1)',
                'z-index': 9999,
                'max-height': '200px',
                'overflow-y': 'auto'
            });

            data.forEach(function(item) {
                const $suggestion = $('<div class="autocomplete-suggestion"></div>');
                $suggestion.text(item[field]);
                $suggestion.on('click', function() {
                    $input.val(item[field]);
                    hideAutocompleteSuggestions();
                    if (dataTable) dataTable.ajax.reload();
                });
                $suggestions.append($suggestion);
            });

            $('body').append($suggestions);
            $(document).on('click.autocomplete', function(e) {
                if (!$(e.target).closest('.autocomplete-suggestions, #ledgerRefFilter, #referenceFilter').length) {
                    hideAutocompleteSuggestions();
                }
            });
        }

        function hideAutocompleteSuggestions() {
            $('.autocomplete-suggestions').remove();
            $(document).off('click.autocomplete');
        }

        function setupAutocomplete() {
            setupSingleAutocomplete('ledgerRefFilter', 'ledger_ref', '{{ route('transactions.ledger-ref') }}');
            setupSingleAutocomplete('referenceFilter', 'reference', '{{ route('transactions.references') }}');

            // Keyboard navigation for autocomplete
            $('#ledgerRefFilter, #referenceFilter').off('keydown.autocomplete').on('keydown.autocomplete', function(e) {
                const $suggestions = $('.autocomplete-suggestions');
                const $activeSuggestion = $suggestions.find('.autocomplete-suggestion.active');

                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    $suggestions.find('.autocomplete-suggestion').removeClass('active');

                    if (e.key === 'ArrowDown') {
                        if ($activeSuggestion.length === 0) {
                            $suggestions.find('.autocomplete-suggestion:first').addClass('active');
                        } else {
                            const $next = $activeSuggestion.next('.autocomplete-suggestion');
                            if ($next.length) {
                                $next.addClass('active');
                            } else {
                                $suggestions.find('.autocomplete-suggestion:first').addClass('active');
                            }
                        }
                    } else {
                        if ($activeSuggestion.length === 0) {
                            $suggestions.find('.autocomplete-suggestion:last').addClass('active');
                        } else {
                            const $prev = $activeSuggestion.prev('.autocomplete-suggestion');
                            if ($prev.length) {
                                $prev.addClass('active');
                            } else {
                                $suggestions.find('.autocomplete-suggestion:last').addClass('active');
                            }
                        }
                    }
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if ($activeSuggestion.length) {
                        $activeSuggestion.click();
                    }
                } else if (e.key === 'Escape') {
                    hideAutocompleteSuggestions();
                }
            });
        }

        function setupSingleAutocomplete(inputId, fieldName, route) {
            const $input = $('#' + inputId);
            $input.off('input.autocomplete').on('input.autocomplete', function() {
                const query = $input.val();
                if (!query || query.length < 2) {
                    hideAutocompleteSuggestions();
                    return;
                }

                $.ajax({
                    url: route,
                    method: 'GET',
                    data: {
                        query
                    },
                    success: function(data) {
                        showAutocompleteSuggestions($input, data, fieldName);
                    },
                    error: function() {
                        console.error('Autocomplete fetch failed for ' + fieldName);
                    }
                });
            });
        }

        function setupBulkOperations() {
            // Toggle all checkboxes
            $(document).off('click', '#select-all').on('click', '#select-all', function() {
                $('.transaction-checkbox').prop('checked', this.checked);
            });

            // Individual checkbox handling
            $(document).off('click', '.transaction-checkbox').on('click', '.transaction-checkbox', function() {
                const totalCheckboxes = $('.transaction-checkbox').length;
                const checkedCheckboxes = $('.transaction-checkbox:checked').length;
                $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
            });

            // Bulk delete
            $('#delete-selected').off('click').on('click', function() {
                const selectedIds = $('.transaction-checkbox:checked').map(function() {
                    return this.value;
                }).get();

                if (selectedIds.length === 0) {
                    alert('Please select at least one record.');
                    return;
                }

                if (!confirm('Are you sure you want to delete ' + selectedIds.length +
                        ' selected transaction(s)?')) {
                    return;
                }

                // Disable button during operation
                const $button = $(this);
                const originalText = $button.html();
                $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');

                $.ajax({
                    url: '{{ route('transactions.bulk-delete') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: selectedIds
                    },
                    success: function(response) {
                        if (dataTable) dataTable.ajax.reload(null, false);
                        $('#select-all').prop('checked', false);
                        alert('Selected transactions have been deleted successfully.');
                    },
                    error: function() {
                        alert('An error occurred while deleting transactions.');
                    },
                    complete: function() {
                        $button.prop('disabled', false).html(originalText);
                    }
                });
            });
        }

        function setupClearFilters() {
            if ($('#clear-filters').length === 0) {
                const $clearButton = $(`
            <button id="clear-filters" type="button" class="btn btn-primary btn-clear-filters">
                <i class="fas fa-sliders-h me-1"></i> Clear Filters
            </button>
        `);
                $('.card-header .d-flex.gap-2').prepend($clearButton);
            }

            $('#clear-filters').off('click').on('click', function() {
                // All filter fields
                const fields = ['ledgerRef', 'bankAccount', 'paidInOut', 'paymentType', 'reference', 'netAmount',
                    'vatAmount', 'totalAmount'
                ];

                fields.forEach(field => {
                    const $input = $(`#${field}Filter`);
                    const $icon = $(`#${field}Icon`);
                    const $title = $(`#${field}Title`);

                    if ($input.length && $icon.length && $title.length) {
                        $input.val('').addClass('d-none');
                        $title.removeClass('d-none');

                        // Reset icon based on field type
                        if (field === 'ledgerRef' || field === 'reference' || field === 'netAmount' ||
                            field === 'vatAmount' || field === 'totalAmount') {
                            $icon.removeClass('fa-times').addClass('fa-search');
                        } else {
                            $icon.removeClass('fa-times').addClass('fa-chevron-down');
                        }
                    }
                });

                // Clear date filter
                const $dateInput = $('#dateFilter');
                const $dateIcon = $('#dateIcon');
                const $dateTitle = $('#dateTitle');
                if ($dateInput.length) {
                    $dateInput.val('').addClass('d-none');
                    $dateTitle.removeClass('d-none');
                    $dateIcon.removeClass('fa-times').addClass('fa-calendar-alt');
                }

                // Hide autocomplete suggestions
                hideAutocompleteSuggestions();

                // Reload table data
                if (dataTable) {
                    dataTable.ajax.reload();
                }
            });
        }

        // Reusable event listener for all icons (fallback)
        $(document).on("click", ".active-icon", function() {
            const th = $(this).closest("th");
            const filter = th.find("input, select");
            filter.toggleClass("d-none");
            if (!filter.hasClass("d-none")) {
                th.addClass("filter-active");
            } else {
                th.removeClass("filter-active");
            }
        });

        // Handle date filter changes
        $('#from_date, #to_date').on('change', function() {
            if (dataTable) {
                dataTable.ajax.reload();
            }
        });

        // Prevent form submission on Enter in date filters
        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            if (dataTable) {
                dataTable.ajax.reload();
            }
        });
    </script>
@endsection