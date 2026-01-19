@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="page-title">Batch Transactions</div>
                            <div class="prism-toggle">
                                <!--<button class="btn btn-sm btn-primary-light">Show Code<i-->
                                <!--        class="ri-code-line ms-2 d-inline-block align-middle"></i></button>-->
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('transactions.store-multiple') }}"
                                enctype="multipart/form-data">
                                @csrf
                                <div id="entryContainer">
                                    <div class="row d-flex flex-wrap align-items-end gx-2 entryRow">
                                        <div class="col-md-1">
                                            <label class="form-label">Date</label>
                                            <input type="date" class="form-control"
                                                name="transactions[0][Transaction_Date]" required />
                                        </div>

                                        <div class="col-md-1 position-relative">
                                            <label class="form-label">Ledger Ref</label>
                                            <input type="text" id="ledgerRefInput_0" class="form-control"
                                                name="transactions[0][Ledger_Ref]" autocomplete="off" required />
                                            <div id="ledgerRefDropdown_0"
                                                class="position-absolute w-100 bg-white shadow-sm rounded border"
                                                style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;">
                                                <!-- Suggestions will appear here -->
                                            </div>
                                        </div>

                                        <div class="col-md-1">
                                            <label class="form-label">Bank Account</label>
                                            <select id="BankAccountDropdown" name="transactions[0][Bank_Account_ID]"
                                                class="form-select" required>
                                                <option value="" selected disabled>Select Bank Account</option>
                                                @foreach ($bankAccounts as $bankAccount)
                                                    <option value="{{ $bankAccount->Bank_Account_ID }}"
                                                        data-bank-type="{{ $bankAccount->Bank_Type_ID }}">
                                                        {{ $bankAccount->Bank_Name }}
                                                        ({{ $bankAccount->bankAccountType->Bank_Type ?? 'N/A' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-1">
                                            <label class="form-label">Paid In/Out</label>
                                            <select id="PaidInOutDropdown" name="transactions[0][Paid_In_Out]"
                                                class="form-select" required>
                                                <option value="" selected disabled>Select Paid In/Out</option>
                                                <option value="1">Paid In</option>
                                                <option value="2">Paid Out</option>
                                            </select>
                                        </div>

                                        <div class="col-md-1">
                                            <label class="form-label">Payment Type</label>
                                            <select id="PaymentTypeDropdown" name="transactions[0][Payment_Type_ID]"
                                                class="form-select">
                                                <option value="" selected disabled>Select Payment Type</option>
                                            </select>
                                        </div>

                                        <div class="col-md-1">
                                            <label class="form-label">Account Ref</label>
                                            <select id="txtAccountRef" name="transactions[0][Account_Ref_ID]"
                                                class="form-select">
                                                <option value="" selected disabled>Select Account Ref</option>
                                            </select>
                                        </div>

                                        <div class="col-md-1">
                                            <label class="form-label">VAT Type</label>
                                            <select id="txtVatType" name="transactions[0][VAT_ID]" class="form-select">
                                                <option value="" selected disabled>Select VAT Type</option>
                                            </select>
                                        </div>

                                        <div class="col-md-1">
                                            <label class="form-label">Cheque</label>
                                            <input type="text" class="form-control" name="transactions[0][Cheque]"
                                                placeholder="Cheque" />
                                        </div>

                                        <div class="col-md-1">
                                            <label class="form-label">Amount</label>
                                            <input type="number" class="form-control" name="transactions[0][Amount]"
                                                required />
                                        </div>

                                        <div class="col-md-1">
                                            <label class="form-label">Description</label>
                                            <textarea class="form-control" name="transactions[0][Description]" rows="1" placeholder="Transaction Description"
                                                required></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2 row align-items-end gx-2">
                                    <div class="col-md-2">
                                        <button type="button" id="addEntry" class="btn addbutton w-100">Add
                                            Rows</button>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">Submit</button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive mt-5">
                                {!! $dataTable->table(['class' => 'table custom-datatable'], true) !!}
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
        document.addEventListener('DOMContentLoaded', () => {
            // Attach suggestion logic for a given row index
            function attachLedgerRefSuggestion(rowIndex) {
                const ledgerRefInput = document.getElementById(`ledgerRefInput_${rowIndex}`);
                const ledgerRefDropdown = document.getElementById(`ledgerRefDropdown_${rowIndex}`);

                if (!ledgerRefInput || !ledgerRefDropdown) return;

                let debounceTimer;
                ledgerRefInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const query = this.value;
                    if (!query) {
                        ledgerRefDropdown.style.display = 'none';
                        return;
                    }
                    debounceTimer = setTimeout(() => {
                        fetch(`/transactions/get-ledger-refs?query=${encodeURIComponent(query)}`)
                            .then(response => response.json())
                            .then(data => {
                                showSuggestions(data, ledgerRefInput, ledgerRefDropdown);
                            });
                    }, 300);
                });

                // Hide dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!ledgerRefDropdown.contains(e.target) && e.target !== ledgerRefInput) {
                        ledgerRefDropdown.style.display = 'none';
                    }
                });
            }

            // Show suggestions in dropdown
            function showSuggestions(suggestions, ledgerRefInput, ledgerRefDropdown) {
                if (suggestions.length === 0) {
                    ledgerRefDropdown.style.display = 'none';
                    return;
                }
                ledgerRefDropdown.innerHTML = '';
                suggestions.forEach(suggestion => {
                    const item = document.createElement('div');
                    item.className = 'p-2 cursor-pointer hover:bg-light';
                    item.textContent = suggestion.Ledger_Ref;
                    item.onclick = function() {
                        ledgerRefInput.value = suggestion.Ledger_Ref;
                        ledgerRefDropdown.style.display = 'none';
                    };
                    ledgerRefDropdown.appendChild(item);
                });
                ledgerRefDropdown.style.display = 'block';
            }

            // Attach suggestion to the first row
            attachLedgerRefSuggestion(0);

            const container = document.getElementById('entryContainer');

            // Event delegation for dynamically added dropdowns
            container.addEventListener('change', async (event) => {
                const target = event.target;

                // Handle BankAccountDropdown change
                if (target.matches('[id^="BankAccountDropdown"]')) {
                    const rowIndex = target.id.split('_')[1] || ''; // Extract row index
                    const paidInOutDropdown = document.getElementById(
                        `PaidInOutDropdown${rowIndex ? '_' + rowIndex : ''}`);
                    const paymentTypeDropdown = document.getElementById(
                        `PaymentTypeDropdown${rowIndex ? '_' + rowIndex : ''}`);
                    const accountRefDropdown = document.getElementById(
                        `txtAccountRef${rowIndex ? '_' + rowIndex : ''}`);

                    await fetchPaymentTypes(target, paidInOutDropdown, paymentTypeDropdown);
                    await fetchAccountRefs(target, paidInOutDropdown, accountRefDropdown);
                }

                // Handle PaidInOutDropdown change
                if (target.matches('[id^="PaidInOutDropdown"]')) {
                    const rowIndex = target.id.split('_')[1] || ''; // Extract row index
                    const bankAccountDropdown = document.getElementById(
                        `BankAccountDropdown${rowIndex ? '_' + rowIndex : ''}`);
                    const paymentTypeDropdown = document.getElementById(
                        `PaymentTypeDropdown${rowIndex ? '_' + rowIndex : ''}`);
                    const accountRefDropdown = document.getElementById(
                        `txtAccountRef${rowIndex ? '_' + rowIndex : ''}`);

                    await fetchPaymentTypes(bankAccountDropdown, target, paymentTypeDropdown);
                    await fetchAccountRefs(bankAccountDropdown, target, accountRefDropdown);
                }

                // Handle AccountRefDropdown change
                if (target.matches('[id^="txtAccountRef"]')) {
                    const rowIndex = target.id.split('_')[1] || ''; // Extract row index
                    const vatTypeDropdown = document.getElementById(
                        `txtVatType${rowIndex ? '_' + rowIndex : ''}`);
                    await fetchVatTypes(target, vatTypeDropdown);
                }
            });

            // Fetch Payment Types
            const fetchPaymentTypes = async (bankAccountDropdown, paidInOutDropdown, paymentTypeDropdown) => {
                const selectedBankType = bankAccountDropdown.options[bankAccountDropdown.selectedIndex]
                    ?.dataset.bankType;
                const selectedPaidInOut = paidInOutDropdown.value;

                if (!selectedBankType || !selectedPaidInOut) {
                    paymentTypeDropdown.innerHTML =
                        `<option value="" selected disabled>Select Payment Type</option>`;
                    return;
                }

                try {
                    const response = await fetch('/transactions/get-payment-types', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            bankAccountTypeId: selectedBankType,
                            paidInOut: selectedPaidInOut,
                        }),
                    });

                    if (!response.ok) throw new Error('Failed to fetch payment types');

                    const paymentTypes = await response.json();
                    paymentTypeDropdown.innerHTML =
                        `<option value="" selected disabled>Select Payment Type</option>`;
                    paymentTypes.forEach((paymentType) => {
                        paymentTypeDropdown.innerHTML += `
                        <option value="${paymentType.Payment_Type_ID}">
                            ${paymentType.Payment_Type_Name}
                        </option>`;
                    });
                } catch (error) {
                    console.error('Error fetching payment types:', error);
                    paymentTypeDropdown.innerHTML =
                        `<option value="" selected disabled>No Payment Types Found</option>`;
                }
            };

            // Fetch Account Refs
            const fetchAccountRefs = async (bankAccountDropdown, paidInOutDropdown, accountRefDropdown) => {
                const selectedBankAccount = bankAccountDropdown.value;
                const selectedPaidInOut = paidInOutDropdown.value;
                const selectedBankType = bankAccountDropdown.options[bankAccountDropdown.selectedIndex]
                    ?.dataset.bankType;

                if (!selectedBankAccount || !selectedPaidInOut || !selectedBankType) {
                    accountRefDropdown.innerHTML =
                        `<option value="" selected disabled>Select Account Ref</option>`;
                    return;
                }

                try {
                    const response = await fetch('/transactions/get-account-ref', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            bankAccountId: selectedBankAccount,
                            pinout: selectedPaidInOut,
                            bankTypeId: selectedBankType,
                        }),
                    });

                    if (!response.ok) throw new Error('Failed to fetch account refs');

                    const accountRefs = await response.json();
                    accountRefDropdown.innerHTML =
                        `<option value="" selected disabled>Select Account Ref</option>`;
                    accountRefs.forEach((accountRef) => {
                        accountRefDropdown.innerHTML += `
                        <option value="${accountRef.Account_Ref_ID}">
                            ${accountRef.Reference}
                        </option>`;
                    });
                } catch (error) {
                    console.error('Error fetching account refs:', error);
                    accountRefDropdown.innerHTML =
                        `<option value="" selected disabled>No Account Ref Found</option>`;
                }
            };

            // Fetch VAT Types
            const fetchVatTypes = async (accountRefDropdown, vatTypeDropdown) => {
                const selectedAccountRef = accountRefDropdown.value;

                if (!selectedAccountRef) {
                    vatTypeDropdown.innerHTML =
                        `<option value="" selected disabled>Select VAT Type</option>`;
                    return;
                }

                try {
                    const response = await fetch('/transactions/get-vat-types', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            Account_Ref_ID: selectedAccountRef,
                        }),
                    });

                    if (!response.ok) throw new Error('Failed to fetch VAT types');

                    const vatTypes = await response.json();
                    vatTypeDropdown.innerHTML =
                        `<option value="" selected disabled>Select VAT Type</option>`;
                    vatTypes.forEach((vatType) => {
                        vatTypeDropdown.innerHTML += `
                        <option value="${vatType.VAT_ID}">
                            ${vatType.VAT_Name}
                        </option>`;
                    });
                } catch (error) {
                    console.error('Error fetching VAT types:', error);
                    vatTypeDropdown.innerHTML =
                        `<option value="" selected disabled>No VAT Types Found</option>`;
                }
            };

            const addEntryButton = document.getElementById('addEntry');

            // Add a single new row on each click
            addEntryButton.addEventListener('click', () => {
                const newRowIndex = container.children.length; // Get the current number of rows
                const newRow = document.createElement('div');
                newRow.classList.add('row', 'd-flex', 'flex-wrap', 'align-items-end', 'gx-2', 'entryRow');

                newRow.innerHTML = `
                    <div class="col-md-1">
                        <input type="date" class="form-control" name="transactions[${newRowIndex}][Transaction_Date]" required />
                    </div>
                    <div class="col-md-1 position-relative">
                        <input type="text" id="ledgerRefInput_${newRowIndex}" class="form-control" name="transactions[${newRowIndex}][Ledger_Ref]" autocomplete="off" required />
                        <div id="ledgerRefDropdown_${newRowIndex}"
                            class="position-absolute w-100 bg-white shadow-sm rounded border"
                            style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;">
                            <!-- Suggestions will appear here -->
                        </div>
                    </div>
                    <div class="col-md-1">
                        <select id="BankAccountDropdown_${newRowIndex}" name="transactions[${newRowIndex}][Bank_Account_ID]" class="form-select" required>
                            <option value="" selected disabled>Select Bank Account</option>
                            @foreach ($bankAccounts as $bankAccount)
                                <option value="{{ $bankAccount->Bank_Account_ID }}" data-bank-type="{{ $bankAccount->Bank_Type_ID }}">
                                    {{ $bankAccount->Bank_Name }} ({{ $bankAccount->bankAccountType->Bank_Type ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <select id="PaidInOutDropdown_${newRowIndex}" name="transactions[${newRowIndex}][Paid_In_Out]" class="form-select" required>
                            <option value="" selected disabled>Select Paid In/Out</option>
                            <option value="1">Paid In</option>
                            <option value="2">Paid Out</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <select id="PaymentTypeDropdown_${newRowIndex}" name="transactions[${newRowIndex}][Payment_Type_ID]" class="form-select">
                            <option value="" selected disabled>Select Payment Type</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <select id="txtAccountRef_${newRowIndex}" name="transactions[${newRowIndex}][Account_Ref_ID]" class="form-select">
                            <option value="" selected disabled>Select Account Ref</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <select id="txtVatType_${newRowIndex}" name="transactions[${newRowIndex}][VAT_ID]" class="form-select">
                            <option value="" selected disabled>Select VAT Type</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <input type="text" class="form-control" name="transactions[${newRowIndex}][Cheque]" placeholder="Cheque" />
                    </div>
                    <div class="col-md-1">
                        <input type="number" class="form-control" name="transactions[${newRowIndex}][Amount]" required />
                    </div>
                    <div class="col-md-1">
                        <textarea class="form-control" name="transactions[${newRowIndex}][Description]" rows="1" placeholder="Transaction Description" required></textarea>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger removeEntry">Remove</button>
                    </div>
                `;

                container.appendChild(newRow);

                // Attach ledger ref suggestion to the newly added row
                attachLedgerRefSuggestion(newRowIndex);
            });

            // Remove row functionality
            container.addEventListener('click', (event) => {
                if (event.target.classList.contains('removeEntry')) {
                    event.target.closest('.entryRow').remove();
                }
            });
        });

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

                const fields = ['ledgerRef', 'bankAccount', 'paidInOut', 'accountRef', 'paymentType'];
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
                const fields = ['ledgerRef', 'bankAccount', 'paidInOut', 'accountRef', 'paymentType'];


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

        });
    </script>
@endsection
