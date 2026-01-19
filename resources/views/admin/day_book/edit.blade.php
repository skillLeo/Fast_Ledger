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
    }

    .payment-type-btn {
        padding: 10px 16px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        color: #fff;
        background-color: #1b598c;
        font-size: 14px;
        font-weight: 500;
    }

    .payment-type-btn:hover {
        background-color: #72b3dc !important;
        color: #fff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .step-indicator {
        margin-bottom: 20px;
    }

    .step-indicator .step {
        display: inline-block;
        padding: 8px 20px;
        margin: 0 10px;
        border-radius: 25px;
        background-color: #e9ecef;
        color: #6c757d;
        font-size: 13px;
        font-weight: 600;
        position: relative;
    }

    .step-indicator .step.active {
        background-color: #007bff;
        color: white;
    }

    .step-indicator .step.completed {
        background-color: #28a745;
        color: white;
    }

    .step-indicator .step::after {
        content: '';
        position: absolute;
        top: 50%;
        right: -15px;
        transform: translateY(-50%);
        width: 0;
        height: 0;
        border-left: 5px solid #007bff;
        border-top: 5px solid transparent;
        border-bottom: 5px solid transparent;
        display: none;
    }

    .step-indicator .step.active::after {
        display: block;
    }

    .account-type-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    .payment-type-btn {
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        color: #fff;
        background-color: #1b598c;
    }

    .payment-type-btn:hover {
        background-color: #72b3dc !important;
        color: #fff !important;
    }

    .account-type-step {
        display: block;
    }

    .payment-type-step {
        display: none;
    }

    .step-indicator {
        margin-bottom: 20px;
    }

    .step-indicator .step {
        display: inline-block;
        padding: 5px 15px;
        margin: 0 5px;
        border-radius: 20px;
        background-color: #e9ecef;
        color: #6c757d;
        font-size: 12px;
        font-weight: bold;
    }

    .step-indicator .step.active {
        background-color: #007bff;
        color: white;
    }

    .step-indicator .step.completed {
        background-color: #28a745;
        color: white;
    }
</style>

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between ">
                            <h4 class="page-title">Day Book Entries</h4>
                            <div class="d-flex gap-2">
                                <!-- Changed from direct link to button that opens modal -->
                                <button type="button" class="btn addbutton btn-wave" data-bs-toggle="modal"
                                    data-bs-target="#accountTypeModal">
                                    <i class="fas fa-plus"></i>Add New
                                </button>
                                <button id="downloadPDF" class="btn downloadpdf "><i class="fas fa-file-pdf"></i>Download
                                    PDF</button>
                                <button id="download-csv" class="btn downloadcsv"> <i class="fas fa-file-csv"></i> Download
                                    CSV</button>
                                <button id="delete-selected" class="btn btn-danger"> <i class="fas fa-trash"></i>
                                    Delete</button>
                                <button id="import-selected" class="btn btn-success"> <i class="fas fa-file-import"></i>
                                    Import </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Render DataTable -->
                            <div class="table-responsive">
                                {!! $dataTable->table(['class' => 'table custom-datatable'], true) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Account Type Selection Modal -->
    <div class="modal fade" id="accountTypeModal" tabindex="-1" aria-labelledby="accountTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="accountTypeModalLabel">Create New Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Step Indicator -->
                    <div class="step-indicator text-center">
                        <span class="step active" id="step1">1. Account Type</span>
                        <span class="step" id="step2">2. Payment Type</span>
                    </div>

                    <!-- Step 1: Account Type Selection -->
                    <div class="account-type-step" id="accountTypeStep">
                        <p class="mb-4 text-center">Please select the type of account you want to create an entry for:</p>
                        <div class="d-grid gap-3">
                            <button type="button" class="btn btn-primary btn-lg account-type-btn" data-account-type="client">
                                <i class="fas fa-user me-2"></i>Client Account
                            </button>
                            <button type="button" class="btn btn-success btn-lg account-type-btn" data-account-type="office">
                                <i class="fas fa-building me-2"></i>Office Account
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Payment Type Selection (Only for Office Account) -->
                    <div class="payment-type-step" id="paymentTypeStep">
                        <div class="text-center mb-3">
                            <span class="badge bg-success fs-6">
                                <i class="fas fa-building me-1"></i>Office Account Selected
                            </span>
                        </div>
                        <p class="mb-4 text-center">Please select the payment type for your office account entry:</p>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <button type="button" class="payment-type-btn" data-payment-type="inter_bank_client">
                                Inter Bank Client
                            </button>
                            <button type="button" class="payment-type-btn" data-payment-type="inter_ledger">
                                Inter Ledger
                            </button>
                            <button type="button" class="payment-type-btn" data-payment-type="payment">
                                Payment
                            </button>
                            <button type="button" class="payment-type-btn" data-payment-type="receipt">
                                Receipt
                            </button>
                            <button type="button" class="payment-type-btn" data-payment-type="cheque">
                                Cheque
                            </button>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-secondary" id="backToAccountType">
                                <i class="fas fa-arrow-left me-1"></i>Back to Account Type
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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

            // ========== MODAL FUNCTIONALITY ==========
            
            // Account Type Selection
            $('.account-type-btn').on('click', function() {
                const accountType = $(this).data('account-type');
                
                if (accountType === 'client') {
                    // Direct redirect for client account
                    window.location.href = "{{ route('transactions.create', ['type' => 'client']) }}";
                } else if (accountType === 'office') {
                    // Show payment type selection for office account
                    showPaymentTypeStep();
                }
            });

            // Payment Type Selection
            $('.payment-type-btn').on('click', function() {
                const paymentType = $(this).data('payment-type');
                // Redirect to create page with office type and payment type
                window.location.href = `{{ route('transactions.create', ['type' => 'office']) }}&payment_type=${paymentType}`;
            });

            // Back to Account Type
            $('#backToAccountType').on('click', function() {
                showAccountTypeStep();
            });

            // Functions to show/hide steps
            function showPaymentTypeStep() {
                $('#accountTypeStep').removeClass('account-type-step').addClass('d-none');
                $('#paymentTypeStep').removeClass('payment-type-step').addClass('d-block');
                
                // Update step indicator
                $('#step1').removeClass('active').addClass('completed');
                $('#step2').addClass('active');
            }

            function showAccountTypeStep() {
                $('#paymentTypeStep').removeClass('d-block').addClass('payment-type-step');
                $('#accountTypeStep').removeClass('d-none').addClass('account-type-step');
                
                // Update step indicator
                $('#step1').removeClass('completed').addClass('active');
                $('#step2').removeClass('active');
            }

            // Reset modal when closed
            $('#accountTypeModal').on('hidden.bs.modal', function() {
                showAccountTypeStep();
            });

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
            function clearAllFilters() {
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
                        url: '{{ route('transactions.ledger-refs') }}',
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

            // ========== MODAL FUNCTIONALITY ==========
            
            // Account Type Selection
            $('.account-type-btn').on('click', function() {
                const accountType = $(this).data('account-type');
                
                if (accountType === 'client') {
                    // Direct redirect for client account
                    window.location.href = "{{ route('transactions.create', ['type' => 'client']) }}";
                } else if (accountType === 'office') {
                    // Show payment type selection for office account
                    showPaymentTypeStep();
                }
            });

            // Payment Type Selection
            $('.payment-type-btn').on('click', function() {
                const paymentType = $(this).data('payment-type');
                // Redirect to create page with office type and payment type
                window.location.href = "{{ route('transactions.create', ['type' => 'office']) }}&payment_type=" + paymentType;
            });

            // Back to Account Type
            $('#backToAccountType').on('click', function() {
                showAccountTypeStep();
            });

            // Functions to show/hide steps
            function showPaymentTypeStep() {
                $('#accountTypeStep').hide();
                $('#paymentTypeStep').show();
                
                // Update step indicator
                $('#step1').removeClass('active').addClass('completed');
                $('#step2').addClass('active');
            }

            function showAccountTypeStep() {
                $('#paymentTypeStep').hide();
                $('#accountTypeStep').show();
                
                // Update step indicator
                $('#step1').removeClass('completed').addClass('active');
                $('#step2').removeClass('active');
            }

            // Reset modal when closed
            $('#accountTypeModal').on('hidden.bs.modal', function() {
                showAccountTypeStep();
            });

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