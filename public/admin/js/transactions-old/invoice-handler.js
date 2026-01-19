/**
 * ========================================================================
 * INVOICE HANDLER
 * ========================================================================
 * Manages invoice items functionality:
 * - Adding/removing invoice rows
 * - VAT calculations
 * - Subtotal/total calculations
 * - Product integration
 * - Invoice validation
 */

class InvoiceHandler {
    constructor() {
        this.itemCounter = 0;
        this.selectedRowIndex = -1;
        this.invoiceSortable = null;

        // Will be initialized later
        this.elements = {
            invoiceItemsTable: null
        };
    }

    /**
     * Initialize DOM element references
     */
    initializeElements() {
        this.elements.invoiceItemsTable = document.getElementById('invoiceItemsTable');

        if (!this.elements.invoiceItemsTable) {
            console.warn('Invoice items table not found');
        } else {
            console.log('Invoice elements initialized');
        }
    }

    /**
     * Get current payment type
     */
    getCurrentPaymentType() {
        return window.formManager.getCurrentPaymentType();
    }

    /**
     * Update invoice summary (totals)
     */
    updateInvoiceSummary() {
        let totalNet = 0;
        let totalVAT = 0;
        let totalAmount = 0;

        if (this.elements.invoiceItemsTable) {
            const rows = this.elements.invoiceItemsTable.querySelectorAll('tr');
            rows.forEach(row => {
                const unitAmount = parseFloat(row.querySelector('.unit-amount')?.value || 0);
                const vatAmount = parseFloat(row.querySelector('.vat-amount')?.value || 0);
                const netAmount = parseFloat(row.querySelector('.net-amount')?.value || 0);

                totalNet += unitAmount;
                totalVAT += vatAmount;
                totalAmount += netAmount;
            });
        }

        const summaryNetAmount = document.getElementById('summaryNetAmount');
        const summaryTotalVAT = document.getElementById('summaryTotalVAT');
        const summaryTotalAmount = document.getElementById('summaryTotalAmount');
        const hiddenInvoiceNetAmount = document.getElementById('hiddenInvoiceNetAmount');
        const hiddenInvoiceVATAmount = document.getElementById('hiddenInvoiceVATAmount');
        const hiddenInvoiceTotalAmount = document.getElementById('hiddenInvoiceTotalAmount');

        if (summaryNetAmount) summaryNetAmount.textContent = `£${totalNet.toFixed(2)}`;
        if (summaryTotalVAT) summaryTotalVAT.textContent = `£${totalVAT.toFixed(2)}`;
        if (summaryTotalAmount) summaryTotalAmount.textContent = `£${totalAmount.toFixed(2)}`;

        if (hiddenInvoiceNetAmount) hiddenInvoiceNetAmount.value = totalNet.toFixed(2);
        if (hiddenInvoiceVATAmount) hiddenInvoiceVATAmount.value = totalVAT.toFixed(2);
        if (hiddenInvoiceTotalAmount) hiddenInvoiceTotalAmount.value = totalAmount.toFixed(2);

        let amountField = document.getElementById('hiddenMainAmount');
        if (!amountField) {
            amountField = this.createAmountField();
        }

        if (amountField) {
            amountField.value = totalNet.toFixed(2);
        }
    }

    /**
     * Create hidden amount field for form submission
     */
    createAmountField() {
        const existingAmountFields = document.querySelectorAll('input[name="Amount"]');
        existingAmountFields.forEach(field => field.remove());

        const salesForm = document.getElementById('salesInvoiceTransactionForm') ||
            document.querySelector('#salesInvoiceForm form');

        if (salesForm) {
            const amountField = document.createElement('input');
            amountField.type = 'hidden';
            amountField.name = 'Amount';
            amountField.id = 'hiddenMainAmount';
            amountField.value = document.getElementById('hiddenInvoiceNetAmount')?.value || '0';
            salesForm.appendChild(amountField);
            return amountField;
        }
        return null;
    }

    /**
     * Add new invoice row
     */
    addNewInvoiceRow() {
        if (!this.elements.invoiceItemsTable) {
            console.error('Invoice items table not found');
            return;
        }

        this.itemCounter++;
        const row = document.createElement('tr');
        row.dataset.itemId = this.itemCounter;

        const currentPaymentType = this.getCurrentPaymentType();

        let ledgerOptions = '<option value="">Select Ledger</option>';

        if (Array.isArray(window.dataLoader.getLedgerRefsData()) && window.dataLoader.getLedgerRefsData().length > 0) {
            window.dataLoader.getLedgerRefsData().forEach(ledger => {
                ledgerOptions += `<option value="${ledger.id}"
                                        data-ledger-id="${ledger.id}"
                                        data-ledger-ref="${ledger.ledger_ref}">
                                        ${ledger.ledger_ref}
                                    </option>`;
            });
        } else {
            ledgerOptions += `
                    <option value="LR001">LR001</option>
                    <option value="LR002">LR002</option>
                    <option value="LR003">LR003</option>`;
        }

        row.innerHTML = `
                    <td class="text-center align-middle">
                        <i class="fas fa-grip-vertical drag-handle text-muted"></i>
                    </td>
                    <td>
                        <input type="text" 
                            name="items[${this.itemCounter}][item_code]" 
                            class="border-0 bg-transparent shadow-none item-code-input" 
                            placeholder="Item code"
                            data-row="${this.itemCounter}"
                            autocomplete="off">
                    </td>
                    <td><input type="text" name="items[${this.itemCounter}][description]" class="border-0 bg-transparent shadow-none" placeholder="Description" required></td>
                    <td>
                        <select name="items[${this.itemCounter}][ledger_id]" class="form-select form-select-sm border-0 bg-transparent shadow-none ledger-select" data-row="${this.itemCounter}">
                            ${ledgerOptions}
                        </select>
                    </td>
                    <td>
                        <select name="items[${this.itemCounter}][account_ref]" class="form-select form-select-sm border-0 bg-transparent shadow-none account-select" data-row="${this.itemCounter}">
                            <option value="">Select Account</option>
                        </select>
                    </td>
                    <td><input type="number" name="items[${this.itemCounter}][unit_amount]" step="0.01" placeholder="0.00" class="border-0 bg-transparent shadow-none unit-amount" required></td>
                    <td>
                        <select name="items[${this.itemCounter}][vat_rate]" class="form-select form-select-sm border-0 bg-transparent shadow-none vat-rate" data-row="${this.itemCounter}">
                            <option value="0">Loading VAT rates...</option>
                        </select>
                    </td>
                    <td><input type="number" name="items[${this.itemCounter}][vat_amount]" step="0.01" placeholder="0.00" class="border-0 bg-transparent shadow-none vat-amount" readonly></td>
                    <td><input type="number" name="items[${this.itemCounter}][net_amount]" step="0.01" placeholder="0.00" class="border-0 bg-transparent shadow-none net-amount" readonly></td>
                    <td class="text-center align-middle">
                        <div class="item-image-preview" data-row="${this.itemCounter}">
                            <div class="no-image-placeholder">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                        </div>
                    </td>
                    
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm d-inline-flex align-items-center justify-content-center p-0 rounded-1 remove-btn" style="width: 20px; height: 20px;">×</button>
                    </td>
                `;

        // Add hidden field for VAT type ID
        const hiddenVatInput = document.createElement('input');
        hiddenVatInput.type = 'hidden';
        hiddenVatInput.name = `items[${this.itemCounter}][vat_form_label_id]`;
        hiddenVatInput.className = 'item-vat-id';
        row.appendChild(hiddenVatInput);

        const hiddenImageInput = document.createElement('input');
        hiddenImageInput.type = 'hidden';
        hiddenImageInput.name = `items[${this.itemCounter}][product_image]`;
        hiddenImageInput.className = 'item-image-url';
        row.appendChild(hiddenImageInput);

        const unitAmountInput = row.querySelector('.unit-amount');
        const vatRateSelect = row.querySelector('.vat-rate');
        const vatAmountInput = row.querySelector('.vat-amount');
        const netAmountInput = row.querySelector('.net-amount');
        const ledgerSelect = row.querySelector('.ledger-select');

        // Load VAT types for this form
        window.vatManager.loadVatTypesByForm(currentPaymentType, function (vatTypes) {
            vatRateSelect.innerHTML = window.vatManager.createVatDropdownOptions(vatTypes);

            const firstOption = vatRateSelect.options[0];
            const vatIdField = row.querySelector('.item-vat-id');
            if (vatIdField && firstOption && firstOption.dataset.vatId) {
                vatIdField.value = firstOption.dataset.vatId;
            }
        });

        // VAT rate change handler
        vatRateSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const vatIdField = row.querySelector('.item-vat-id');
            if (vatIdField && selectedOption.dataset.vatId) {
                vatIdField.value = selectedOption.dataset.vatId;
            }
            calculateRowAmounts();
        });

        // Ledger selection handler
        const self = this;
        ledgerSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const selectedLedgerId = this.value;
            const selectedLedgerRef = opt ? opt.dataset.ledgerRef : '';
            const rowIndex = this.dataset.row;
            const accountSelect = row.querySelector(`.account-select[data-row="${rowIndex}"]`);
            const vatRateSelect = row.querySelector('.vat-rate');

            if (selectedLedgerRef) {
                accountSelect.innerHTML = '<option value="">Loading...</option>';
                accountSelect.disabled = true;

                window.dataLoader.getAccountRefsByLedger(selectedLedgerRef, function (accountRefs) {
                    accountSelect.innerHTML = '<option value="">Select Account</option>';
                    accountSelect.disabled = false;

                    if (accountRefs && accountRefs.length > 0) {
                        accountRefs.forEach(account => {
                            const option = document.createElement('option');
                            option.value = account.account_ref;
                            option.textContent = account.account_ref;
                            option.dataset.vatId = account.vat_id || '';

                            if (account.description) {
                                option.textContent += ` (${account.description})`;
                            }
                            accountSelect.appendChild(option);
                        });
                    } else {
                        accountSelect.innerHTML = '<option value="">No accounts available</option>';
                    }
                });
            } else {
                accountSelect.innerHTML = '<option value="">Select Account</option>';
                accountSelect.disabled = false;

                if (vatRateSelect) {
                    vatRateSelect.disabled = false;
                    vatRateSelect.style.backgroundColor = '';
                }
            }
        });

        // Account Ref change handler - Auto-set VAT based on Chart of Accounts
        const accountSelect = row.querySelector('.account-select');
        accountSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const vatIdFromCOA = selectedOption ? selectedOption.dataset.vatId : '';
            const vatRateSelect = row.querySelector('.vat-rate');

            if (!vatRateSelect) {
                console.warn('⚠️ VAT dropdown not found');
                return;
            }

            if (vatIdFromCOA === '5') {
                const noVatOption = Array.from(vatRateSelect.options).find(opt => {
                    const vatId = opt.dataset.vatId;
                    return vatId === '5' || opt.value === '0';
                });

                if (noVatOption) {
                    vatRateSelect.value = noVatOption.value;
                    vatRateSelect.disabled = true;
                    vatRateSelect.style.backgroundColor = '#e9ecef';
                    vatRateSelect.style.cursor = 'not-allowed';
                    vatRateSelect.title = 'VAT rate is fixed for this account';

                    const vatIdField = row.querySelector('.item-vat-id');
                    if (vatIdField && noVatOption.dataset.vatId) {
                        vatIdField.value = noVatOption.dataset.vatId;
                    }

                    calculateRowAmounts();
                }
            } else {
                vatRateSelect.disabled = false;
                vatRateSelect.style.backgroundColor = '';
                vatRateSelect.style.cursor = '';
                vatRateSelect.title = '';

                if (vatIdFromCOA) {
                    const matchingOption = Array.from(vatRateSelect.options).find(opt =>
                        opt.dataset.vatId === vatIdFromCOA
                    );

                    if (matchingOption) {
                        vatRateSelect.value = matchingOption.value;

                        const vatIdField = row.querySelector('.item-vat-id');
                        if (vatIdField) {
                            vatIdField.value = matchingOption.dataset.vatId;
                        }
                    }
                }

                calculateRowAmounts();
            }
        });

        // Calculation function
        const calculateRowAmounts = () => {
            const unitAmount = parseFloat(unitAmountInput.value) || 0;
            const vatRate = parseFloat(vatRateSelect.value) || 0;

            const vatAmount = (unitAmount * vatRate) / 100;
            const netAmount = unitAmount + vatAmount;

            vatAmountInput.value = vatAmount.toFixed(2);
            netAmountInput.value = netAmount.toFixed(2);

            self.updateInvoiceSummary();

            setTimeout(() => {
                const amountField = document.getElementById('hiddenMainAmount');
                if (!amountField) {
                    self.createAmountField();
                    self.updateInvoiceSummary();
                }
            }, 10);
        };

        unitAmountInput.addEventListener('input', calculateRowAmounts);

        row.addEventListener('click', () => {
            document.querySelectorAll('#invoiceItemsTable tr').forEach(r => {
                r.classList.remove('table-row-selected');
            });
            row.classList.add('table-row-selected');
            this.selectedRowIndex = Array.from(this.elements.invoiceItemsTable.children).indexOf(row);
        });

        this.elements.invoiceItemsTable.appendChild(row);
        this.updateInvoiceSummary();

        // Reinitialize sortable
        setTimeout(() => {
            if (this.invoiceSortable) {
                this.invoiceSortable.destroy();
                this.invoiceSortable = null;
            }
            this.initializeSortable();
        }, 50);
    }

    /**
     * Initialize sortable functionality
     */
    initializeSortable() {
        if (this.elements.invoiceItemsTable && !this.invoiceSortable) {
            this.invoiceSortable = Sortable.create(this.elements.invoiceItemsTable, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onStart: function (evt) {
                    evt.item.style.opacity = '0.5';
                },
                onEnd: (evt) => {
                    evt.item.style.opacity = '1';
                    this.updateInvoiceSummary();
                    this.updateItemIndices();
                    console.log('Invoice item moved from index', evt.oldIndex, 'to', evt.newIndex);
                }
            });
            console.log('Invoice sortable initialized');
        }
    }

    /**
     * Destroy sortable instance
     */
    destroySortable() {
        if (this.invoiceSortable) {
            this.invoiceSortable.destroy();
            this.invoiceSortable = null;
            console.log('Invoice sortable destroyed');
        }
    }

    /**
     * Update item indices after reordering
     */
    updateItemIndices() {
        const rows = this.elements.invoiceItemsTable.querySelectorAll('tr');
        rows.forEach((row, index) => {
            const inputs = row.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.name && input.name.includes('items[')) {
                    const newName = input.name.replace(/items\[\d+\]/, `items[${index}]`);
                    input.name = newName;
                }
            });

            row.dataset.itemId = index;

            const selects = row.querySelectorAll('select[data-row]');
            selects.forEach(select => {
                select.dataset.row = index;
            });
        });
    }

    /**
     * Validate invoice form
     */
    validateInvoiceForm() {
        const currentPaymentType = this.getCurrentPaymentType();

        const labels = this.getFormLabels(currentPaymentType);

        const customerDropdown = document.getElementById('customerDropdown');
        if (!customerDropdown) {
            alert('Error: Customer dropdown not found. Please refresh the page.');
            return false;
        }

        if (!customerDropdown.value || customerDropdown.value === '') {
            alert(`Please select a ${labels.customerLabel.toLowerCase()}`);
            customerDropdown.focus();
            return false;
        }

        if (!this.elements.invoiceItemsTable) {
            alert('Error: Invoice items table not found. Please refresh the page.');
            return false;
        }

        if (this.elements.invoiceItemsTable.children.length === 0) {
            alert(`Please add at least one item to the ${labels.formTitle.toLowerCase()}`);
            const addButton = document.getElementById('addItemBtn');
            if (addButton) addButton.focus();
            return false;
        }

        const rows = this.elements.invoiceItemsTable.querySelectorAll('tr');
        let hasValidItems = false;

        rows.forEach((row) => {
            const description = row.querySelector('input[name*="[description]"]');
            const unitAmount = row.querySelector('input[name*="[unit_amount]"]');

            if (description && unitAmount &&
                description.value.trim() &&
                parseFloat(unitAmount.value) > 0) {
                hasValidItems = true;
            }
        });

        if (!hasValidItems) {
            alert('Please ensure at least one item has a description and unit amount greater than 0');
            return false;
        }

        const codeVal = document.getElementById('invoiceTransactionCode')?.value ||
            document.getElementById('hiddenTransactionCode')?.value ||
            (typeof getFullCode === 'function' ? getFullCode() : '');

        if (!codeVal || !/^[A-Z]+[0-9]{6}$/.test(codeVal)) {
            alert('Transaction code is required');
            return false;
        }

        return true;
    }

    /**
     * Get form labels based on payment type
     */
    getFormLabels(paymentType) {
        return window.formManager.getFormLabels(paymentType);
    }
}

// Create global instance
window.invoiceHandler = new InvoiceHandler();