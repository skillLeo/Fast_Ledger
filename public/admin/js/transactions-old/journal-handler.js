/**
 * Journal Handler Module
 * Handles all journal-related functionality including rows, validation, and calculations
 */

class JournalHandler {
    constructor() {
        this.journalRowCounter = 0;
        this.elements = {
            journalRows: null,
            journalRowTemplate: null
        };
    }

    /**
     * Initialize journal elements
     */
    initializeElements() {
        this.elements.journalRows = document.getElementById('journalRows');
        this.elements.journalRowTemplate = document.getElementById('journalRowTemplate');
    }

    /**
     * Get current payment type
     */
    getCurrentPaymentType() {
        return window.formManager.getCurrentPaymentType();
    }

    /**
     * Render initial journal rows
     */
    renderJournalRows() {
        if (!this.elements.journalRows) {
            console.warn('Journal rows container not found');
            return;
        }

        const existingRows = this.elements.journalRows.querySelectorAll('tr:not([data-template-row])');
        existingRows.forEach(row => row.remove());

        this.addNewJournalRow();
        console.log('Initial journal row added');
    }

    /**
     * Add new journal row
     */
    addNewJournalRow() {
        if (!this.elements.journalRows || !this.elements.journalRowTemplate) {
            console.warn('Journal elements not available');
            return;
        }

        this.journalRowCounter++;
        const clone = this.elements.journalRowTemplate.cloneNode(true);
        clone.classList.remove('d-none');
        clone.removeAttribute('id');
        clone.removeAttribute('data-template-row');
        clone.dataset.journalRow = this.journalRowCounter;

        const currentPaymentType = this.getCurrentPaymentType();

        clone.innerHTML = `
            <td class="text-center align-middle">
                <i class="fas fa-grip-vertical drag-handle text-muted"></i>
            </td>
            <td>
                <input type="text" 
                    name="items[${this.journalRowCounter}][description]"
                    class=" border-0 bg-transparent shadow-none"
                    placeholder="Description" required>
            </td>
            <td>
                <select name="items[${this.journalRowCounter}][ledger_id]" 
                    class="form-select form-select-sm border-0 bg-transparent shadow-none journal-ledger-select" 
                    data-row="${this.journalRowCounter}" required>
                    <option value="">Select Ledger</option>
                </select>
            </td>
            <td>
                <select name="items[${this.journalRowCounter}][account_id]" 
                    class="form-select form-select-sm border-0 bg-transparent shadow-none journal-account-select" 
                    data-row="${this.journalRowCounter}" required>
                    <option value="">Select Account</option>
                </select>
            </td>
            <td>
                <select name="items[${this.journalRowCounter}][tax_rate]"
                    class="form-select form-select-sm border-0 bg-transparent shadow-none text-center journal-tax-select"
                    data-row="${this.journalRowCounter}">
                    <option value="0">Loading VAT rates...</option>
                </select>
            </td>
            <td>
                <input type="text" 
                    name="items[${this.journalRowCounter}][region]"
                    class=" border-0 bg-transparent shadow-none"
                    placeholder="Region" value="">
            </td>
            <td>
                <input type="number" 
                    name="items[${this.journalRowCounter}][debit_amount]"
                    class=" border-0 bg-transparent shadow-none text-end journal-debit"
                    step="0.01" placeholder="0.00" min="0">
            </td>
            <td>
                <input type="number" 
                    name="items[${this.journalRowCounter}][credit_amount]"
                    class=" border-0 bg-transparent shadow-none text-end journal-credit"
                    step="0.01" placeholder="0.00" min="0">
            </td>
            <td class="text-center">
                <button type="button"
                    class="btn btn-danger btn-sm d-inline-flex align-items-center justify-content-center p-0 rounded-1 remove-btn"
                    style="width: 20px; height: 20px;">×</button>
            </td>
        `;

        const hiddenInputs = document.createElement('div');
        hiddenInputs.style.display = 'none';
        hiddenInputs.innerHTML = `
            <input type="hidden" name="items[${this.journalRowCounter}][row_id]" value="${this.journalRowCounter}">
            <input type="hidden" name="items[${this.journalRowCounter}][chart_of_account_id]" value="" class="journal-account-id">
            <input type="hidden" name="items[${this.journalRowCounter}][vat_form_label_id]" value="" class="journal-vat-id">
        `;
        clone.appendChild(hiddenInputs);

        const ledgerSelect = clone.querySelector('.journal-ledger-select');
        const taxSelect = clone.querySelector('.journal-tax-select');

        this.populateJournalLedgerSelect(ledgerSelect);

        // Load VAT types
        window.vatManager.loadVatTypesByForm(currentPaymentType, (vatTypes) => {
            let vatOptions = '<option value="0" data-vat-id="">No Tax (0%)</option>';
            vatTypes.forEach(vatType => {
                vatOptions += `<option value="${vatType.percentage}" data-vat-id="${vatType.id}">
                    ${vatType.vat_name} (${vatType.percentage}%)
                </option>`;
            });

            taxSelect.innerHTML = vatOptions;

            const vatIdField = clone.querySelector('.journal-vat-id');
            const firstOption = taxSelect.options[0];
            if (vatIdField && firstOption && firstOption.dataset.vatId) {
                vatIdField.value = firstOption.dataset.vatId;
            }
        });

        // Tax rate change handler
        taxSelect.addEventListener('change', () => {
            const selectedOption = taxSelect.options[taxSelect.selectedIndex];
            const vatIdField = clone.querySelector('.journal-vat-id');
            if (vatIdField && selectedOption.dataset.vatId) {
                vatIdField.value = selectedOption.dataset.vatId;
            }
            this.updateJournalTotals();
            this.updateJournalAmountField();
        });

        // Ledger change handler
        ledgerSelect.addEventListener('change', () => {
            const selectedOption = ledgerSelect.options[ledgerSelect.selectedIndex];
            const selectedLedgerRef = selectedOption ? selectedOption.dataset.ledgerRef : '';
            const rowIndex = ledgerSelect.dataset.row;
            const accountSelect = clone.querySelector(`.journal-account-select[data-row="${rowIndex}"]`);
            const accountIdField = clone.querySelector('.journal-account-id');
            const taxSelect = clone.querySelector('.journal-tax-select');

            if (selectedLedgerRef) {
                accountSelect.innerHTML = '<option value="">Loading...</option>';
                accountSelect.disabled = true;

                window.dataLoader.getAccountRefsByLedger(selectedLedgerRef, (accountRefs) => {
                    accountSelect.innerHTML = '<option value="">Select Account</option>';
                    accountSelect.disabled = false;

                    if (accountRefs && accountRefs.length > 0) {
                        accountRefs.forEach(account => {
                            const option = document.createElement('option');
                            option.value = account.id || account.chart_of_account_id;
                            option.dataset.accountRef = account.account_ref;
                            option.dataset.vatId = account.vat_id || '';
                            option.textContent = account.account_ref;
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
                if (accountIdField) accountIdField.value = '';

                if (taxSelect) {
                    taxSelect.disabled = false;
                    taxSelect.style.backgroundColor = '';
                    taxSelect.style.cursor = '';
                    taxSelect.title = '';
                }
            }
        });

        // Account change handler
        const accountSelect = clone.querySelector('.journal-account-select');
        accountSelect.addEventListener('change', () => {
            const selectedOption = accountSelect.options[accountSelect.selectedIndex];
            const accountIdField = clone.querySelector('.journal-account-id');
            const vatIdFromCOA = selectedOption ? selectedOption.dataset.vatId : '';
            const taxSelect = clone.querySelector('.journal-tax-select');

            if (accountIdField) {
                accountIdField.value = selectedOption ? (selectedOption.value || '') : '';
            }

            if (!taxSelect) return;

            if (vatIdFromCOA === '5') {
                const noVatOption = Array.from(taxSelect.options).find(opt =>
                    opt.dataset.vatId === '5' || opt.value === '0'
                );

                if (noVatOption) {
                    taxSelect.value = noVatOption.value;
                    taxSelect.disabled = true;
                    taxSelect.style.backgroundColor = '#e9ecef';
                    taxSelect.style.cursor = 'not-allowed';
                    taxSelect.title = 'VAT rate is fixed for this account';

                    const vatIdField = clone.querySelector('.journal-vat-id');
                    if (vatIdField && noVatOption.dataset.vatId) {
                        vatIdField.value = noVatOption.dataset.vatId;
                    }
                }
            } else {
                taxSelect.disabled = false;
                taxSelect.style.backgroundColor = '';
                taxSelect.style.cursor = '';
                taxSelect.title = '';

                if (vatIdFromCOA) {
                    const matchingOption = Array.from(taxSelect.options).find(opt =>
                        opt.dataset.vatId === vatIdFromCOA
                    );

                    if (matchingOption) {
                        taxSelect.value = matchingOption.value;
                        const vatIdField = clone.querySelector('.journal-vat-id');
                        if (vatIdField && matchingOption.dataset.vatId) {
                            vatIdField.value = matchingOption.dataset.vatId;
                        }
                    }
                }
            }

            this.updateJournalTotals();
            this.updateJournalAmountField();
        });

        // Debit/Credit input handlers
        const debitInput = clone.querySelector('.journal-debit');
        const creditInput = clone.querySelector('.journal-credit');

        debitInput.addEventListener('input', () => {
            if (parseFloat(debitInput.value) > 0) {
                creditInput.value = '';
            }
            this.updateJournalTotals();
            this.updateJournalAmountField();
        });

        creditInput.addEventListener('input', () => {
            if (parseFloat(creditInput.value) > 0) {
                debitInput.value = '';
            }
            this.updateJournalTotals();
            this.updateJournalAmountField();
        });

        this.elements.journalRows.appendChild(clone);
        this.updateJournalTotals();
        this.updateJournalAmountField();

        // Reinitialize sortable
        setTimeout(() => {
            if (window.journalSortable) {
                window.journalSortable.destroy();
                window.journalSortable = null;
            }
            if (typeof window.initializeJournalSortable === 'function') {
                window.initializeJournalSortable();
            }
        }, 50);
    }

    /**
     * Populate journal ledger select
     */
    populateJournalLedgerSelect(selectElement) {
        selectElement.innerHTML = '<option value="">Select Ledger</option>';

        const ledgerData = window.dataLoader.getLedgerRefsData();
        if (ledgerData && ledgerData.length > 0) {
            ledgerData.forEach(ledger => {
                const option = document.createElement('option');
                option.value = ledger.id;
                option.dataset.ledgerRef = ledger.ledger_ref;
                option.textContent = ledger.ledger_ref;
                selectElement.appendChild(option);
            });
        }
    }

    /**
     * Update journal totals
     */
    updateJournalTotals() {
        let totalDebit = 0;
        let totalCredit = 0;
        let totalDebitVat = 0;
        let totalCreditVat = 0;

        const rows = document.querySelectorAll('#journalRows tr:not([data-template-row])');

        rows.forEach(row => {
            const debitInput = row.querySelector('input[name*="[debit_amount]"]');
            const creditInput = row.querySelector('input[name*="[credit_amount]"]');
            const taxSelect = row.querySelector('.journal-tax-select');

            const debitAmount = parseFloat(debitInput?.value || 0);
            const creditAmount = parseFloat(creditInput?.value || 0);
            const taxRate = parseFloat(taxSelect?.value || 0);

            const debitVat = (debitAmount * taxRate) / 100;
            const creditVat = (creditAmount * taxRate) / 100;

            totalDebit += debitAmount;
            totalCredit += creditAmount;
            totalDebitVat += debitVat;
            totalCreditVat += creditVat;
        });

        const grandTotalDebit = totalDebit + totalDebitVat;
        const grandTotalCredit = totalCredit + totalCreditVat;

        // Update UI
        const subtotalDebit = document.getElementById('journalSubtotalDebit');
        const subtotalCredit = document.getElementById('journalSubtotalCredit');
        if (subtotalDebit) subtotalDebit.textContent = totalDebit.toFixed(2);
        if (subtotalCredit) subtotalCredit.textContent = totalCredit.toFixed(2);

        const totalDebitVatElement = document.getElementById('journalTotalDebitVat');
        const totalCreditVatElement = document.getElementById('journalTotalCreditVat');
        if (totalDebitVatElement) totalDebitVatElement.textContent = totalDebitVat.toFixed(2);
        if (totalCreditVatElement) totalCreditVatElement.textContent = totalCreditVat.toFixed(2);

        const totalDebitElement = document.getElementById('journalTotalDebit');
        const totalCreditElement = document.getElementById('journalTotalCredit');
        if (totalDebitElement) totalDebitElement.textContent = grandTotalDebit.toFixed(2);
        if (totalCreditElement) totalCreditElement.textContent = grandTotalCredit.toFixed(2);

        const difference = grandTotalDebit - grandTotalCredit;
        const balanceDifferenceElement = document.getElementById('journalBalanceDifference');
        const balanceStatusElement = document.getElementById('balanceStatus');

        if (balanceDifferenceElement) {
            if (Math.abs(difference) < 0.01) {
                balanceDifferenceElement.className = 'text-success fw-bold';
                balanceDifferenceElement.textContent = '0.00';
            } else {
                balanceDifferenceElement.className = 'text-danger fw-bold';
                balanceDifferenceElement.textContent = (difference > 0 ? '' : '-') + Math.abs(difference).toFixed(2);
            }
        }

        if (balanceStatusElement) {
            if (Math.abs(difference) < 0.01) {
                balanceStatusElement.innerHTML =
                    '<i class="fas fa-check-circle me-1 text-success"></i><span class="text-success">Journal entries are balanced ✓</span>';
            } else {
                balanceStatusElement.innerHTML =
                    '<i class="fas fa-exclamation-triangle me-1 text-warning"></i><span class="text-warning">Journal entries must balance (Debit = Credit)</span>';
            }
        }
    }

    /**
     * Update journal amount field
     */
    updateJournalAmountField() {
        let totalDebit = 0;
        let totalCredit = 0;

        const rows = document.querySelectorAll('#journalRows tr:not([data-template-row])');

        rows.forEach(row => {
            const debitInput = row.querySelector('input[name*="[debit_amount]"]');
            const creditInput = row.querySelector('input[name*="[credit_amount]"]');
            const taxSelect = row.querySelector('.journal-tax-select');

            const debit = parseFloat(debitInput?.value || 0);
            const credit = parseFloat(creditInput?.value || 0);
            const taxRate = parseFloat(taxSelect?.value || 0);

            if (debit > 0) {
                const vatAmount = (debit * taxRate) / 100;
                totalDebit += debit + vatAmount;
            }

            if (credit > 0) {
                const vatAmount = (credit * taxRate) / 100;
                totalCredit += credit + vatAmount;
            }
        });

        const totalAmount = totalDebit;

        let amountField = document.getElementById('hiddenMainAmount');
        if (!amountField) {
            const salesForm = document.getElementById('salesInvoiceTransactionForm');
            if (salesForm) {
                amountField = document.createElement('input');
                amountField.type = 'hidden';
                amountField.name = 'Amount';
                amountField.id = 'hiddenMainAmount';
                amountField.value = totalAmount.toFixed(2);
                salesForm.appendChild(amountField);
            }
        } else {
            amountField.value = totalAmount.toFixed(2);
        }
    }

    /**
     * Validate journal table
     */
    validateJournalTable() {
        if (!this.elements.journalRows) return false;

        const rows = this.elements.journalRows.querySelectorAll('tr:not([data-template-row])');

        if (rows.length === 0) {
            alert('Please add at least one journal entry');
            return false;
        }

        let totalDebit = 0;
        let totalCredit = 0;
        let hasValidEntries = false;
        let errors = [];

        rows.forEach((row, index) => {
            const description = row.querySelector('input[name*="[description]"]');
            const ledgerRef = row.querySelector('select[name*="[ledger_id]"]');
            const accountRef = row.querySelector('select[name*="[account_id]"]');
            const debitInput = row.querySelector('input[name*="[debit_amount]"]');
            const creditInput = row.querySelector('input[name*="[credit_amount]"]');

            const debitValue = parseFloat(debitInput?.value) || 0;
            const creditValue = parseFloat(creditInput?.value) || 0;

            if (!description?.value.trim()) {
                errors.push(`Row ${index + 1}: Description is required`);
            }
            if (!ledgerRef?.value) {
                errors.push(`Row ${index + 1}: Ledger is required`);
            }
            if (!accountRef?.value) {
                errors.push(`Row ${index + 1}: Account is required`);
            }
            if (debitValue === 0 && creditValue === 0) {
                errors.push(`Row ${index + 1}: Either Debit or Credit amount must be greater than 0`);
            }
            if (debitValue > 0 && creditValue > 0) {
                errors.push(`Row ${index + 1}: Cannot have both Debit and Credit amounts`);
            }

            if (description?.value.trim() && ledgerRef?.value && accountRef?.value && (debitValue > 0 || creditValue > 0)) {
                hasValidEntries = true;
            }

            totalDebit += debitValue;
            totalCredit += creditValue;
        });

        if (errors.length > 0) {
            alert('Please fix the following errors:\n\n' + errors.join('\n'));
            return false;
        }

        if (!hasValidEntries) {
            alert('Please add at least one valid journal entry with description, ledger, account, and amount');
            return false;
        }

        if (Math.abs(totalDebit - totalCredit) > 0.01) {
            alert(
                `Journal entries must balance:\nTotal Debits: £${totalDebit.toFixed(2)}\nTotal Credits: £${totalCredit.toFixed(2)}\nDifference: £${Math.abs(totalDebit - totalCredit).toFixed(2)}\n\nPlease adjust your entries so that Debits = Credits.`
            );
            return false;
        }

        return true;
    }

    /**
     * Collect journal data
     */
    collectJournalData() {
        const journalData = [];
        const rows = document.querySelectorAll('#journalRows tr:not([data-template-row])');

        rows.forEach((row, index) => {
            const description = row.querySelector('input[name*="[description]"]')?.value || '';
            const ledgerRef = row.querySelector('select[name*="[ledger_ref]"]')?.value || '';
            const accountRef = row.querySelector('select[name*="[account_ref]"]')?.value || '';
            const taxRate = row.querySelector('select[name*="[tax_rate]"]')?.value || '0';
            const region = row.querySelector('input[name*="[region]"]')?.value || '';
            const debitAmount = parseFloat(row.querySelector('input[name*="[debit_amount]"]')?.value) || 0;
            const creditAmount = parseFloat(row.querySelector('input[name*="[credit_amount]"]')?.value) || 0;
            const chartOfAccountsId = row.querySelector('.journal-account-id')?.value || '';
            const vatTypeId = row.querySelector('.journal-vat-id')?.value || '';

            if (description || ledgerRef || accountRef || debitAmount > 0 || creditAmount > 0) {
                journalData.push({
                    description,
                    ledger_ref: ledgerRef,
                    account_ref: accountRef,
                    tax_rate: taxRate,
                    region,
                    debit_amount: debitAmount,
                    credit_amount: creditAmount,
                    chart_of_account_id: chartOfAccountsId,
                    vat_type_id: vatTypeId,
                    row_index: index + 1
                });
            }
        });

        return journalData;
    }
}

// Initialize and expose globally
window.JournalHandler = JournalHandler;
window.journalHandler = null;

document.addEventListener('DOMContentLoaded', () => {
    window.journalHandler = new JournalHandler();
    window.journalHandler.initializeElements();
});