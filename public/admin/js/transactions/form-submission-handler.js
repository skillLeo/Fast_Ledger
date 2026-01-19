/**
 * Form Submission Handler
 * Handles form submission logic for sales invoices and journal entries
 */
class FormSubmissionHandler {
    constructor() {
        // Will be initialized in initialize() method
    }

    initialize() {
        console.log('✅ FormSubmissionHandler initialized');
    }

    /**
     * ✅ FIXED: Detect if we're in company module context
     */
    isCompanyModule() {
        // Method 1: Check meta tag (most reliable)
        const metaContext = document.querySelector('meta[name="app-context"]')?.content;
        if (metaContext === 'company_module') {
            return true;
        }

        // Method 2: Check form action URL
        const form = this.getSalesInvoiceForm();
        if (form) {
            const action = form.getAttribute('action');
            if (action && action.includes('/company/invoices')) {
                return true;
            }
        }

        // Method 3: Check current URL
        if (window.location.pathname.startsWith('/company/')) {
            return true;
        }

        // Method 4: Check session storage (if set)
        if (sessionStorage.getItem('app_context') === 'company_module') {
            return true;
        }

        return false;
    }

    /**
     * ✅ NEW: Get the sales invoice form
     */
    getSalesInvoiceForm() {
        return document.getElementById('salesInvoiceTransactionForm') ||
            document.querySelector('#salesInvoiceForm form') ||
            document.querySelector('.sales-invoice-form form') ||
            document.querySelector('form[action*="transactions.store"]') ||
            document.querySelector('form[action*="company.invoices.store"]');
    }

    /**
     * Submit sales invoice form with specified action
     * @param {string} action - save, save_and_email, save_and_add_new, save_as_draft, preview
     */
    submitSalesInvoiceForm(action) {
        const currentPaymentType = window.formManager.getCurrentPaymentType();

        // Validate journal entries if journal type
        if (currentPaymentType === 'journal' && !window.journalHandler.validateJournalTable()) {
            return false;
        }

        let salesForm = this.getSalesInvoiceForm();

        if (!salesForm) {
            alert('Error: Could not find the invoice form. Please refresh the page and try again.');
            return false;
        }

        try {
            // Ensure payment type is set correctly
            let paymentTypeField = document.getElementById('salesInvoicePaymentType');
            if (!paymentTypeField) {
                paymentTypeField = document.createElement('input');
                paymentTypeField.type = 'hidden';
                paymentTypeField.name = 'current_payment_type';
                paymentTypeField.id = 'salesInvoicePaymentType';
                salesForm.appendChild(paymentTypeField);
            }
            paymentTypeField.value = currentPaymentType;

            // Set action field
            const existingActionFields = salesForm.querySelectorAll('input[name="action"]');
            existingActionFields.forEach(field => field.remove());

            const actionField = document.createElement('input');
            actionField.type = 'hidden';
            actionField.name = 'action';
            actionField.value = action;
            salesForm.appendChild(actionField);

            // For journal entries, ensure amount field is updated and add summary data
            if (currentPaymentType === 'journal') {
                window.journalHandler.updateJournalAmountField();

                // Add comprehensive journal summary data for server-side validation
                const existingSummaryFields = salesForm.querySelectorAll('input[name="journal_summary"]');
                existingSummaryFields.forEach(field => field.remove());

                const totalDebit = parseFloat(document.getElementById('journalTotalDebit')?.textContent || 0);
                const totalCredit = parseFloat(document.getElementById('journalTotalCredit')?.textContent || 0);

                const journalSummary = document.createElement('input');
                journalSummary.type = 'hidden';
                journalSummary.name = 'journal_summary';
                journalSummary.value = JSON.stringify({
                    total_debit: totalDebit,
                    total_credit: totalCredit,
                    difference: totalDebit - totalCredit,
                    entry_count: document.querySelectorAll('#journalRows tr:not([data-template-row])').length,
                    is_balanced: Math.abs(totalDebit - totalCredit) < 0.01
                });
                salesForm.appendChild(journalSummary);

                console.log('Journal data being submitted:');
                console.log('- Summary:', {
                    total_debit: totalDebit,
                    total_credit: totalCredit,
                    difference: totalDebit - totalCredit,
                    is_balanced: Math.abs(totalDebit - totalCredit) < 0.01
                });
                console.log('- Entries:', window.journalHandler.collectJournalData());
            } else {
                // For other payment types, handle invoice summary
                window.invoiceHandler.updateInvoiceSummary();
            }

            // Update transaction codes
            if (window.codeManager) window.codeManager.syncTransactionCode();

            // Debug: Log all form data being submitted
            const formData = new FormData(salesForm);
            console.log('All form data being submitted:');
            for (let [key, value] of formData.entries()) {
                if (key.includes('journal_items') || key === 'journal_summary' || key === 'Amount' || key === 'current_payment_type') {
                    console.log(key + ': ' + value);
                }
            }

            // Ensure Amount field exists
            let hasAmount = false;
            for (let [key, value] of formData.entries()) {
                if (key === 'Amount') {
                    hasAmount = true;
                    break;
                }
            }

            if (!hasAmount) {
                alert('Error: Amount field is missing. Please refresh the page and try again.');
                return false;
            }

            // Show success message before submission
            if (currentPaymentType === 'journal') {
                console.log('✅ Journal entries validated and ready for submission!');
            }

            // ✅ FIXED: HANDLE PREVIEW ACTION WITH PROPER CONTEXT DETECTION
            if (action === 'preview') {
                const originalAction = salesForm.getAttribute('action');
                const isCompany = this.isCompanyModule();

                console.log('🔍 Preview Context Detection:', {
                    isCompanyModule: isCompany,
                    currentUrl: window.location.pathname,
                    formAction: originalAction
                });

                // ✅ Set correct preview route based on context
                if (isCompany) {
                    console.log('📍 Company module detected - using company preview route');
                    salesForm.setAttribute('action', '/company/invoices/templates/preview');
                } else {
                    console.log('📍 Main app detected - using main preview route');
                    salesForm.setAttribute('action', '/invoicetemplates/preview');
                }

                // Submit form
                salesForm.submit();

                // Restore original action (in case of back button)
                setTimeout(() => {
                    salesForm.setAttribute('action', originalAction);
                }, 100);
                
                return;
            }

            // Submit the form for other actions
            salesForm.submit();

        } catch (error) {
            console.error('Error submitting form:', error);
            alert('Error submitting form: ' + error.message);
        }
    }
}

// Export to window for global access
if (typeof window !== 'undefined') {
    window.FormSubmissionHandler = FormSubmissionHandler;
}
