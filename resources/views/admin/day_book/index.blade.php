@extends('admin.layout.app')
<style>
    .filter-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 34px;
        position: relative;

    }

    .filter-wrapper .form-control {
        padding-right: 30px;
    }

    .filter-icon {
        z-index: 1;
        padding: 5px 6px;
        background-color: #2c477d;
        color: white;
        /* border-radius: 4px; */
    }
</style>

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.partial.errors')
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <span class="page-title">Banking</span>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('bulk-transactions.dashboard') }}" class="nav-link-btn">
                                    Upload Transactions
                                </a>
                                <a href="#" class="nav-link-btn active" data-section="matters">Manual Entry</a>

                                {{-- <a href="#" class="nav-link-btn" data-section="employees">Employees</a> --}}
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <!-- Button opens modal -->
                                <button type="button" class="btn addbutton btn-wave" data-bs-toggle="modal"
                                    data-bs-target="#accountTypeModal">
                                    <i class="fas fa-plus me-1"></i> Add New
                                </button>

                                {{-- Optional buttons --}}
                                {{-- 
                                <button id="downloadPDF" class="btn downloadpdf">
                                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                                </button>
                                <button id="download-csv" class="btn downloadcsv">
                                    <i class="fas fa-file-csv me-1"></i> Download CSV
                                </button>
                                --}}

                                <button id="delete-selected" class="btn btn-danger">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>

                                <button id="import-selected" class="btn addbutton">
                                    <i class="fas fa-file-import me-1"></i> Import
                                </button>

                                <x-download-dropdown pdf-id="downloadPDF" csv-id="downloadCSV" />
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Render DataTable -->
                            <div class="table-responsive">
                                {!! $dataTable->table(['class' => 'table custom-datatable resizable-draggable-table'], true) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Type Selection Modal -->
    <div class="modal fade" id="accountTypeModal" tabindex="-1" aria-labelledby="accountTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered justify-content-center">
            <div class="modal-content" style="width: 55%">
                <div class="modal-header">
                    <h5 class="modal-title" id="accountTypeModalLabel">Select Account Type</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">

                    <div class="d-grid gap-3">
                        <a href="{{ route('transactions.create', ['type' => 'client']) }}" class="btn teal-custom btn-lg">
                            <i class="fas fa-user me-2"></i>Client Account
                        </a>
                        <a href="{{ route('transactions.create', ['type' => 'office']) }}" class="btn teal-custom btn-lg">
                            <i class="fas fa-building me-2"></i>Office Account
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {!! $dataTable->scripts() !!}

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            // let dataTable = $('.custom-datatable').DataTable();
            const dataTable = $('.custom-datatable').DataTable();

            // Wait for DataTable to fully initialize before adding event listeners
            dataTable.on('init.dt', function() {
                setTimeout(attachEventListeners, 100);
            });

            // Also attach listeners immediately in case the table is already initialized
            setTimeout(attachEventListeners, 500);



            function attachEventListeners() {

                const fields = ['ledgerRef', 'reference', 'bankAccount', 'paidInOut', 'accountRef', 'paymentType'];
                const dropdownFields = ['bankAccount', 'paidInOut', 'accountRef', 'paymentType'];


                fields.forEach(field => attachTextFilterEvents(field));
                dropdownFields.forEach(field => attachDropdownToggleEvents(field));


            }

            function attachTextFilterEvents(field) {
                const iconId = `#${field}Icon`;
                const inputId = `#${field}Filter`;
                const titleId = `#${field}Title`;

                $(iconId).off('click').on('click', function() {
                    const $icon = $(this);
                    const $input = $(inputId);
                    const $title = $(titleId);

                    const isHidden = $input.hasClass('d-none');

                    $input.toggleClass('d-none', !isHidden);
                    $title.toggleClass('d-none', isHidden);

                    if (isHidden) {
                        $input.focus();
                        $icon.removeClass('fa-search').addClass('fa-times');
                    } else {
                        $input.val('');
                        $icon.removeClass('fa-times').addClass('fa-search');
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

                $icon.off('click').on('click', function() {
                    const isHidden = $dropdown.hasClass('d-none');

                    $dropdown.toggleClass('d-none', !isHidden);
                    $title.toggleClass('d-none', isHidden);

                    if (isHidden) {
                        $icon.removeClass('fa-chevron-down').addClass('fa-times');
                    } else {
                        $dropdown.val('');
                        $icon.removeClass('fa-times').addClass('fa-chevron-down');
                        $('.custom-datatable').DataTable().ajax.reload();
                    }
                });

                $dropdown.off('change').on('change', function() {
                    $('.custom-datatable').DataTable().ajax.reload();
                });
            }



            // ========== HELPER FUNCTIONS ==========
            function updateFilterIndicator($th, selectedText, className) {
                $th.find('span.' + className).remove();
                if (selectedText && selectedText !== 'All') {
                    $th.append('<span class="' + className + ' text-primary ml-2">(' + selectedText + ')</span>');
                }
            }

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
                    $suggestion.css({
                        'padding': '8px 12px',
                        'cursor': 'pointer',
                        'border-bottom': '1px solid #eee'
                    });

                    $suggestion.text(item[field]);

                    $suggestion.on('mouseenter', function() {
                        $(this).css('background-color', '#f5f5f5');
                    });

                    $suggestion.on('mouseleave', function() {
                        $(this).css('background-color', 'white');
                    });

                    $suggestion.on('click', function() {
                        $input.val(item[field]);
                        hideAutocompleteSuggestions();
                        dataTable.ajax.reload();
                    });

                    $suggestions.append($suggestion);
                });

                $('body').append($suggestions);

                $(document).on('click.autocomplete', function(e) {
                    if (!$(e.target).closest(
                            '.autocomplete-suggestions, #ledgerRefFilter, #referenceFilter').length) {
                        hideAutocompleteSuggestions();
                    }
                });
            }

            function hideAutocompleteSuggestions() {
                $('.autocomplete-suggestions').remove();
                $(document).off('click.autocomplete');
            }

            // Keyboard navigation for autocomplete
            $('#ledgerRefFilter, #referenceFilter').on('keydown', function(e) {
                const $suggestions = $('.autocomplete-suggestions');
                const $activeSuggestion = $suggestions.find('.autocomplete-suggestion.active');

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if ($activeSuggestion.length === 0) {
                        $suggestions.find('.autocomplete-suggestion:first').addClass('active').css(
                            'background-color', '#007bff').css('color', 'white');
                    } else {
                        $activeSuggestion.removeClass('active').css('background-color', 'white').css(
                            'color', 'black');
                        const $next = $activeSuggestion.next();
                        if ($next.length) {
                            $next.addClass('active').css('background-color', '#007bff').css('color',
                                'white');
                        } else {
                            $suggestions.find('.autocomplete-suggestion:first').addClass('active').css(
                                'background-color', '#007bff').css('color', 'white');
                        }
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if ($activeSuggestion.length === 0) {
                        $suggestions.find('.autocomplete-suggestion:last').addClass('active').css(
                            'background-color', '#007bff').css('color', 'white');
                    } else {
                        $activeSuggestion.removeClass('active').css('background-color', 'white').css(
                            'color', 'black');
                        const $prev = $activeSuggestion.prev();
                        if ($prev.length) {
                            $prev.addClass('active').css('background-color', '#007bff').css('color',
                                'white');
                        } else {
                            $suggestions.find('.autocomplete-suggestion:last').addClass('active').css(
                                'background-color', '#007bff').css('color', 'white');
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

            // ========== CLEAR ALL FILTERS ==========
            // Update the clear all filters function to include account ref
            function clearAllFilters() {
                // const fields = ['ledgerRef', 'matter', 'name', 'address', 'postCode'];
                const fields = ['ledgerRef', 'reference', 'bankAccount', 'paidInOut', 'accountRef', 'paymentType'];


                fields.forEach(field => {
                    const $input = $(`#${field}Filter`);
                    const $icon = $(`#${field}Icon`);
                    const $title = $(`#${field}Title`);

                    $input.val('').addClass('d-none');
                    $title.removeClass('d-none');
                    $icon.removeClass('fa-times').addClass('fa-search');
                });

                $('#from_date').val('');
                $('#to_date').val('');
                $('#bankAccountDropdown').val('');
                $('#paidInOutDropdown').val('');
                $('#bankAccountFilterDiv').addClass('d-none');
                $('#paidInOutFilterDiv').addClass('d-none');

                $('#accountRefDropdown').val('');
                $('#accountRefFilterDiv').addClass('d-none');

                $('#paymentTypeDropdown').val('');
                $('#paymentTypeFilterDiv').addClass('d-none');

                $('.autocomplete-suggestions').hide();

                $('.custom-datatable').DataTable().ajax.url("{{ route('files.index') }}").load();
            }
            // Add clear filters button
            if ($('#clear-filters').length === 0) {
                const $clearButton = $(
                    '<button id="clear-filters" class="btn btn-primary btn-sm ml-2">Clear All Filters</button>'
                );
                $('.card-header .d-flex').prepend($clearButton);

                $clearButton.on('click', function() {
                    clearAllFilters();
                });
            }

            function setupAutocomplete(inputId, fieldName) {
                const $input = $('#' + inputId);

                $input.on('input', function() {
                    const query = $input.val();
                    if (!query) return;

                    $.ajax({
                        url: '{{ route('transactions.ledger-refs') }}', // Update this if needed
                        method: 'GET',
                        data: {
                            query
                        },
                        success: function(data) {
                            showAutocompleteSuggestions($input, data, fieldName);
                        },
                        error: function() {
                            console.error('Autocomplete fetch failed.');
                        }
                    });
                });
            }


            setupAutocomplete('ledgerRefFilter', 'ledger_ref');
            setupAutocomplete('bankAccountFilter', 'bankAccount');
            setupAutocomplete('paidInOutFilter', 'paidInOut');
            setupAutocomplete('accountRefFilter', 'accountRef');
            setupAutocomplete('paymentTypeFilter', 'paymentType');
            setupAutocomplete('referenceFilter', 'reference');

            // ========== EXISTING FUNCTIONALITY ==========
            // Toggle all checkboxes
            $('#select-all').on('click', function() {
                $('.transaction-checkbox').prop('checked', this.checked);
            });

            // Bulk delete
            $('#delete-selected').on('click', function() {
                var selectedIds = $('.transaction-checkbox:checked').map(function() {
                    return this.value;
                }).get();

                if (selectedIds.length === 0) {
                    alert('Please select at least one record.');
                    return;
                }

                if (!confirm('Are you sure you want to delete selected transactions?')) {
                    return;
                }

                $.ajax({
                    url: '{{ route('transactions.destroy') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        ids: selectedIds
                    },
                    success: function(response) {
                        dataTable.ajax.reload(null, false);
                    },
                    error: function() {
                        alert('An error occurred.');
                    }
                });
            });

            // Import selected
            $('#import-selected').on('click', function() {
                const selectedIds = [];
                $('.transaction-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    alert('Please select at least one transaction.');
                    return;
                }

                $.ajax({
                    url: "{{ route('transactions.importeda') }}",
                    type: 'POST',
                    data: {
                        selected_ids: selectedIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.redirect_url;
                        } else {
                            alert(response.message || 'An error occurred.');
                        }
                    },
                    error: function(xhr) {
                        alert('An unexpected error occurred.');
                    }
                });
            });

            // PDF Download
            $('#downloadPDF').click(function() {
                $.ajax({
                    url: "{{ route('transactions.daybook.download.pdf') }}",
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
        });
    </script>
@endsection
