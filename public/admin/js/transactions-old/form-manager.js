/**
 * ========================================================================
 * FORM MANAGER
 * ========================================================================
 * Manages form switching and label updates:
 * - Switching between invoice form and regular office form
 * - Dynamic label updates based on payment type
 * - Form visibility management
 */

class FormManager {
    constructor() {
        this.invoiceFormPaymentTypes = ['sales_invoice', 'sales_credit', 'purchase', 'purchase_credit', 'journal'];
        
        this.labels = {
            'sales_invoice': {
                customerLabel: 'Customer',
                invoiceLabel: 'Invoice No',
                dateLabel: 'Invoice Date',
                refLabel: 'Invoice Ref',
                formTitle: 'Invoicing'
            },
            'sales_credit': {
                customerLabel: 'Customer',
                invoiceLabel: 'Credit Note No',
                dateLabel: 'Credit Date',
                refLabel: 'Credit Ref',
                formTitle: 'Sales Credit Note'
            },
            'purchase': {
                customerLabel: 'Supplier',
                invoiceLabel: 'Purchase No',
                dateLabel: 'Purchase Date',
                refLabel: 'Purchase Ref',
                formTitle: 'Purchase Invoice'
            },
            'purchase_credit': {
                customerLabel: 'Supplier',
                invoiceLabel: 'Purchase Credit No',
                dateLabel: 'Credit Date',
                refLabel: 'Credit Ref',
                formTitle: 'Purchase Credit Note'
            },
            'journal': {
                customerLabel: 'Account',
                invoiceLabel: 'Journal No',
                dateLabel: 'Journal Date',
                refLabel: 'Journal Ref',
                formTitle: 'Journal'
            }
        };
    }

    /**
     * Check if payment type should use invoice form
     */
    shouldUseInvoiceForm(paymentType) {
        return this.invoiceFormPaymentTypes.includes(paymentType);
    }

    /**
     * Get form labels for a payment type
     */
    getFormLabels(paymentType) {
        return this.labels[paymentType] || this.labels['sales_invoice'];
    }

    /**
     * Update form labels based on payment type
     */
    updateFormLabels(paymentType) {
        const labels = this.getFormLabels(paymentType);
        const elementsToUpdate = {
            'customerFieldLabel': labels.customerLabel,
            'invoiceNoLabel': labels.invoiceLabel,
            'invoiceDateLabel': labels.dateLabel,
            'invoiceRefLabel': labels.refLabel
        };

        Object.entries(elementsToUpdate).forEach(([id, text]) => {
            const element = document.getElementById(id);
            if (element) element.textContent = text;
        });

        const customerDropdown = document.getElementById('customerDropdown');
        if (customerDropdown && customerDropdown.options[0]) {
            customerDropdown.options[0].textContent = `Select ${labels.customerLabel}`;
        }

        const formTitleElement = document.querySelector('.page-title h4');
        if (formTitleElement && this.shouldUseInvoiceForm(paymentType)) {
            formTitleElement.textContent = `${labels.formTitle}`;
        }
    }

    /**
     * Get current payment type from UI
     */
    getCurrentPaymentType() {
        const activeButton = document.querySelector('.btn-simple.active');
        if (activeButton) {
            return activeButton.dataset.paymentType;
        }
        return document.getElementById('salesInvoicePaymentType')?.value ||
               document.getElementById('currentPaymentType')?.value ||
               'sales_invoice';
    }

    /**
     * Show sales invoice form
     */
    showSalesInvoiceForm() {
        const regularOfficeForm = document.getElementById('regularOfficeForm');
        const salesInvoiceForm = document.getElementById('salesInvoiceForm');
        const saveDropdownContainer = document.getElementById('saveDropdownContainer');

        if (regularOfficeForm) {
            regularOfficeForm.classList.add('hidden');
            regularOfficeForm.style.display = 'none';
        }

        if (salesInvoiceForm) {
            salesInvoiceForm.classList.add('active');
            salesInvoiceForm.style.display = 'block';
        }

        if (saveDropdownContainer) {
            saveDropdownContainer.style.display = 'block';
        }

        this.updateInvoiceNumber();
        
        if (window.invoiceHandler) {
            window.invoiceHandler.createAmountField();
        }

        const journalSection = document.getElementById('journalItemsSection');
        const invoiceSection = document.getElementById('invoiceItemsSection');
        const currentPaymentType = this.getCurrentPaymentType();

        if (currentPaymentType === 'journal') {
            if (journalSection) journalSection.style.display = 'block';
            if (invoiceSection) invoiceSection.style.display = 'none';
        } else {
            if (journalSection) journalSection.style.display = 'none';
            if (invoiceSection) invoiceSection.style.display = 'block';
        }

        // Load data and populate dropdowns
        this.loadFormData(currentPaymentType);
    }

    /**
     * Load form data (dropdowns, VAT types)
     */
    loadFormData(currentPaymentType) {
        Promise.all([
            window.dataLoader.loadChartOfAccountsForDropdown(),
            window.dataLoader.loadLedgerRefsForDropdown()
        ]).then(() => {
            console.log('Data loaded successfully');
            window.dataLoader.populateCustomerDropdown();

            // Load VAT types for current payment type
            window.vatManager.loadVatTypesByForm(currentPaymentType, function(vatTypes) {
                console.log('VAT types loaded for form:', currentPaymentType, vatTypes);
            });

            if (currentPaymentType === 'journal') {
                console.log('Calling renderJournalRows');
                window.journalHandler.renderJournalRows();
            } else {
                const invoiceItemsTable = document.getElementById('invoiceItemsTable');
                if (invoiceItemsTable && invoiceItemsTable.children.length === 0) {
                    window.invoiceHandler.addNewInvoiceRow();
                }
            }

            setTimeout(() => {
                if (window.invoiceHandler) {
                    window.invoiceHandler.updateInvoiceSummary();
                }
            }, 100);
        }).catch(error => {
            console.error('Error loading data:', error);
            this.loadFormDataFallback(currentPaymentType);
        });

        // Initialize sortable functionality
        setTimeout(() => {
            if (currentPaymentType === 'journal') {
                if (typeof initializeJournalSortable === 'function') {
                    initializeJournalSortable();
                }
            } else {
                if (window.invoiceHandler) {
                    window.invoiceHandler.initializeSortable();
                }
            }
        }, 200);
    }

    /**
     * Fallback data loading if primary fails
     */
    loadFormDataFallback(currentPaymentType) {
        window.dataLoader.populateCustomerDropdown();

        window.vatManager.loadVatTypesByForm(currentPaymentType, function(vatTypes) {
            console.log('VAT types loaded (fallback):', vatTypes);
        });

        if (currentPaymentType === 'journal') {
            console.log('Calling renderJournalRows (fallback)');
            window.journalHandler.renderJournalRows();
        } else {
            const invoiceItemsTable = document.getElementById('invoiceItemsTable');
            if (invoiceItemsTable && invoiceItemsTable.children.length === 0) {
                window.invoiceHandler.addNewInvoiceRow();
            }
        }

        setTimeout(() => {
            if (window.invoiceHandler) {
                window.invoiceHandler.updateInvoiceSummary();
            }
        }, 100);
    }

    /**
     * Show regular office form
     */
    showRegularOfficeForm() {
        const regularOfficeForm = document.getElementById('regularOfficeForm');
        const salesInvoiceForm = document.getElementById('salesInvoiceForm');
        const saveDropdownContainer = document.getElementById('saveDropdownContainer');

        if (regularOfficeForm) {
            regularOfficeForm.classList.remove('hidden');
            regularOfficeForm.style.display = 'block';
        }

        if (salesInvoiceForm) {
            salesInvoiceForm.classList.remove('active');
            salesInvoiceForm.style.display = 'none';
        }

        if (saveDropdownContainer) {
            saveDropdownContainer.style.display = 'none';
        }

        const formTitleElement = document.querySelector('.page-title h4');
        if (formTitleElement) {
            formTitleElement.textContent = 'Add New Entry';
        }
    }

    /**
     * Update invoice number field
     */
    updateInvoiceNumber() {
        const entryRefInput = document.getElementById('entryRefInput');
        const hiddenTransactionCode = document.getElementById('hiddenTransactionCode');
        const invoiceNo = document.getElementById('invoiceNo');
        const invoiceTransactionCode = document.getElementById('invoiceTransactionCode');

        const transactionCode = entryRefInput?.value || hiddenTransactionCode?.value;

        if (invoiceNo && transactionCode) {
            invoiceNo.value = transactionCode;
        }
        if (invoiceTransactionCode && transactionCode) {
            invoiceTransactionCode.value = transactionCode;
        }
    }

    /**
     * Initialize payment type on page load
     */
    initializePaymentType() {
        const initialPaymentType = document.querySelector('.btn-simple.active')?.dataset.paymentType || 'payment';
        console.log('Initializing payment type:', initialPaymentType);

        const currentPaymentTypeInput = document.getElementById('currentPaymentType');
        let salesInvoicePaymentTypeInput = document.getElementById('salesInvoicePaymentType');

        if (currentPaymentTypeInput) {
            currentPaymentTypeInput.value = initialPaymentType;
        }

        if (salesInvoicePaymentTypeInput) {
            salesInvoicePaymentTypeInput.value = initialPaymentType;
        } else {
            const salesForm = document.getElementById('salesInvoiceTransactionForm');
            if (salesForm) {
                const newField = document.createElement('input');
                newField.type = 'hidden';
                newField.name = 'current_payment_type';
                newField.id = 'salesInvoicePaymentType';
                newField.value = initialPaymentType;
                salesForm.appendChild(newField);
            }
        }

        const paymentTypeButtons = document.querySelectorAll('.btn-simple');
        paymentTypeButtons.forEach(btn => {
            if (btn.dataset.paymentType === initialPaymentType) {
                btn.classList.add('active');
                btn.classList.remove('custom-hover');
            } else {
                btn.classList.remove('active');
                btn.classList.add('custom-hover');
            }
        });

        if (this.shouldUseInvoiceForm(initialPaymentType)) {
            this.showSalesInvoiceForm();
            this.updateFormLabels(initialPaymentType);
        } else {
            this.showRegularOfficeForm();
        }
    }
}

// Create global instance
window.formManager = new FormManager();