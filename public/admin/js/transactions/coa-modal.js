/**
 * ========================================================================
 * CHART OF ACCOUNTS MODAL MANAGER
 * ========================================================================
 * Manages the Chart of Accounts selection modal:
 * - Opening and closing modal
 * - Loading COA data via AJAX
 * - Filtering ledgers and accounts
 * - Selecting and applying accounts to form
 */

class CoaModalManager {
    constructor() {
        this.coaModalData = [];
        this.selectedLedgerRef = null;
        this.selectedAccountData = null;
        
        this.elements = {
            coaModalTrigger: null,
            coaModal: null,
            coaModalClose: null,
            selectAccountBtn: null,
            ledgerSearchInput: null,
            accountSearchInput: null,
            ledgerRefList: null,
            accountRefList: null,
            selectedAccountDisplay: null
        };
    }

    /**
     * Initialize modal and bind events
     */
    initialize() {
        this.initializeElements();
        this.bindEvents();
    }

    /**
     * Cache DOM element references
     */
    initializeElements() {
        this.elements.coaModalTrigger = document.getElementById('coaModalTrigger');
        this.elements.coaModal = document.getElementById('coaModal');
        this.elements.coaModalClose = document.getElementById('coaModalClose');
        this.elements.selectAccountBtn = document.getElementById('selectAccountBtn');
        this.elements.ledgerSearchInput = document.getElementById('ledgerSearchInput');
        this.elements.accountSearchInput = document.getElementById('accountSearchInput');
        this.elements.ledgerRefList = document.getElementById('ledgerRefList');
        this.elements.accountRefList = document.getElementById('accountRefList');
        this.elements.selectedAccountDisplay = document.getElementById('selectedAccountDisplay');

        if (!this.elements.coaModalTrigger || !this.elements.coaModal) {
            console.warn('Chart of Accounts modal elements not found');
            return false;
        }

        console.log('COA Modal elements initialized');
        return true;
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        if (this.elements.coaModalTrigger) {
            this.elements.coaModalTrigger.addEventListener('click', () => {
                this.openModal();
            });
        }

        if (this.elements.coaModalClose) {
            this.elements.coaModalClose.addEventListener('click', () => {
                this.closeModal();
            });
        }

        if (this.elements.coaModal) {
            this.elements.coaModal.addEventListener('click', (e) => {
                if (e.target === this.elements.coaModal) {
                    this.closeModal();
                }
            });
        }

        if (this.elements.selectAccountBtn) {
            this.elements.selectAccountBtn.addEventListener('click', () => {
                this.selectAccountToForm();
            });
        }

        if (this.elements.ledgerSearchInput) {
            this.elements.ledgerSearchInput.addEventListener('input', (e) => {
                this.filterLedgers(e.target.value);
            });
        }

        if (this.elements.accountSearchInput) {
            this.elements.accountSearchInput.addEventListener('input', (e) => {
                this.filterAccounts(e.target.value);
            });
        }

        // ESC key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.elements.coaModal && this.elements.coaModal.classList.contains('show')) {
                this.closeModal();
            }
        });
    }

    /**
     * Open the modal
     */
    openModal() {
        if (!this.elements.coaModal) return;

        this.elements.coaModal.classList.add('show');
        document.body.classList.add('modal-open');

        // Reset search inputs
        if (this.elements.ledgerSearchInput) this.elements.ledgerSearchInput.value = '';
        if (this.elements.accountSearchInput) this.elements.accountSearchInput.value = '';

        if (this.coaModalData.length === 0) {
            this.loadModalData();
        } else {
            this.renderLedgers(this.coaModalData);
        }

        this.resetSelection();
    }

    /**
     * Close the modal
     */
    closeModal() {
        if (!this.elements.coaModal) return;

        this.elements.coaModal.classList.remove('show');
        document.body.classList.remove('modal-open');
        this.resetSelection();
    }

    /**
     * Load COA data from server
     */
    loadModalData() {
        if (!this.elements.ledgerRefList) return;

        this.elements.ledgerRefList.innerHTML = '<div class="coa-empty">Loading...</div>';

        const csrfToken = document.querySelector('meta[name="csrf-token"]');

        fetch('/charts-of-accounts/modal', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
            }
        })
        .then(response => response.json())
        .then(data => {
            this.coaModalData = Array.isArray(data) ? data : [];
            this.renderLedgers(this.coaModalData);
        })
        .catch(error => {
            console.error('Error loading Chart of Accounts:', error);
            this.elements.ledgerRefList.innerHTML = '<div class="coa-empty">Error loading data</div>';
        });
    }

    /**
     * Render ledger list
     */
    renderLedgers(data, searchTerm = '') {
        if (!this.elements.ledgerRefList) return;

        if (!data || data.length === 0) {
            this.elements.ledgerRefList.innerHTML = '<div class="coa-empty">No ledgers found</div>';
            return;
        }

        // Filter data based on search term
        const filteredData = searchTerm ?
            data.filter(item =>
                item.ledger_ref && item.ledger_ref.toLowerCase().includes(searchTerm.toLowerCase())
            ) :
            data;

        if (filteredData.length === 0) {
            this.elements.ledgerRefList.innerHTML = '<div class="coa-empty">No ledgers match your search</div>';
            return;
        }

        const ledgerHtml = filteredData.map(ledger => `
            <div class="coa-item" data-ledger-ref="${ledger.ledger_ref}" onclick="window.coaModal.selectLedger('${ledger.ledger_ref}')">
                <span class="coa-item-text">${ledger.ledger_ref}</span>
                <span class="coa-balance">£${parseFloat(ledger.balance || 0).toFixed(2)}</span>
            </div>
        `).join('');

        this.elements.ledgerRefList.innerHTML = ledgerHtml;
    }

    /**
     * Select a ledger and show its accounts
     */
    selectLedger(ledgerRef) {
        this.selectedLedgerRef = ledgerRef;

        const ledgerItems = document.querySelectorAll('#ledgerRefList .coa-item');
        ledgerItems.forEach(item => {
            item.classList.toggle('selected', item.dataset.ledgerRef === ledgerRef);
        });

        const ledgerData = this.coaModalData.find(item => item.ledger_ref === ledgerRef);
        if (ledgerData && ledgerData.accounts) {
            // Get current account search term
            const searchTerm = this.elements.accountSearchInput ? this.elements.accountSearchInput.value : '';
            this.renderAccounts(ledgerData.accounts, searchTerm);
        }

        this.selectedAccountData = null;
        this.updateSelectedAccountDisplay();
    }

    /**
     * Render account list
     */
    renderAccounts(accounts, searchTerm = '') {
        if (!this.elements.accountRefList) return;

        if (!accounts || accounts.length === 0) {
            this.elements.accountRefList.innerHTML = '<div class="coa-empty">No accounts found</div>';
            return;
        }

        // Filter accounts based on search term
        const filteredAccounts = searchTerm ?
            accounts.filter(account =>
                (account.account_ref && account.account_ref.toLowerCase().includes(searchTerm.toLowerCase())) ||
                (account.description && account.description.toLowerCase().includes(searchTerm.toLowerCase()))
            ) :
            accounts;

        if (filteredAccounts.length === 0) {
            this.elements.accountRefList.innerHTML = '<div class="coa-empty">No accounts match your search</div>';
            return;
        }

        const accountHtml = filteredAccounts.map(account => `
            <div class="coa-item" data-account-id="${account.id}" onclick="window.coaModal.selectAccountData(${account.id}, '${account.account_ref}', '${account.description || ''}', ${account.balance || 0})">
                <div class="coa-item-main">
                    <span class="coa-item-text">${account.account_ref}</span>
                    ${account.description ? `<div style="font-size: 11px; color: #666; margin-top: 2px;">${account.description}</div>` : ''}
                </div>
                <span class="coa-balance">£${parseFloat(account.balance || 0).toFixed(2)}</span>
            </div>
        `).join('');

        this.elements.accountRefList.innerHTML = accountHtml;
    }

    /**
     * Filter ledgers based on search term
     */
    filterLedgers(searchTerm) {
        this.renderLedgers(this.coaModalData, searchTerm);

        // Reset account list when searching ledgers
        if (this.elements.accountRefList) {
            this.elements.accountRefList.innerHTML = '<div class="coa-empty">Select a Ledger Ref to view accounts</div>';
        }

        // Clear account search
        if (this.elements.accountSearchInput) {
            this.elements.accountSearchInput.value = '';
        }

        this.selectedLedgerRef = null;
        this.selectedAccountData = null;
        this.updateSelectedAccountDisplay();
    }

    /**
     * Filter accounts based on search term
     */
    filterAccounts(searchTerm) {
        if (!this.selectedLedgerRef) return;

        const ledgerData = this.coaModalData.find(item => item.ledger_ref === this.selectedLedgerRef);
        if (ledgerData && ledgerData.accounts) {
            this.renderAccounts(ledgerData.accounts, searchTerm);
        }

        this.selectedAccountData = null;
        this.updateSelectedAccountDisplay();
    }

    /**
     * Select an account
     */
    selectAccountData(accountId, accountRef, description, balance) {
        this.selectedAccountData = {
            id: accountId,
            account_ref: accountRef,
            description: description,
            balance: balance,
            ledger_ref: this.selectedLedgerRef
        };

        const accountItems = document.querySelectorAll('#accountRefList .coa-item');
        accountItems.forEach(item => {
            item.classList.toggle('selected', item.dataset.accountId == accountId);
        });

        this.updateSelectedAccountDisplay();
    }

    /**
     * Update selected account display
     */
    updateSelectedAccountDisplay() {
        if (!this.elements.selectedAccountDisplay || !this.elements.selectAccountBtn) return;

        if (this.selectedAccountData) {
            this.elements.selectedAccountDisplay.textContent =
                `${this.selectedAccountData.ledger_ref} - ${this.selectedAccountData.account_ref}`;
            this.elements.selectAccountBtn.disabled = false;
        } else {
            this.elements.selectedAccountDisplay.textContent = 'None';
            this.elements.selectAccountBtn.disabled = true;
        }
    }

    /**
     * Apply selected account to form
     */
    selectAccountToForm() {
        if (!this.selectedAccountData || !this.selectedAccountData.id) {
            alert('Please select an account first');
            return;
        }

        // Check if form fields exist
        const formFields = {
            chartOfAccountsId: document.getElementById('chartOfAccountsId'),
            accountRefField: document.getElementById('accountRefHidden'),
            ledgerRefField: document.getElementById('ledgerRefHidden'),
            coaDescriptionField: document.getElementById('coaDescriptionHidden')
        };

        // Set the values
        if (formFields.chartOfAccountsId) {
            formFields.chartOfAccountsId.value = this.selectedAccountData.id;
        }

        if (formFields.accountRefField) {
            formFields.accountRefField.value = this.selectedAccountData.account_ref || '';
        }

        if (formFields.ledgerRefField) {
            formFields.ledgerRefField.value = this.selectedAccountData.ledger_ref || '';
        }

        if (formFields.coaDescriptionField) {
            formFields.coaDescriptionField.value = this.selectedAccountData.description || '';
        }

        const chartOfAccountDetails = document.getElementById('chartOfAccountDetails');
        const chartLedgerRef = document.getElementById('chartLedgerRef');
        const chartAccountRef = document.getElementById('chartAccountRef');
        const chartDescription = document.getElementById('chartDescription');
        const accountBalance = document.getElementById('accountBalance');

        if (chartOfAccountDetails) {
            // Show the details section
            chartOfAccountDetails.style.display = 'block';
        }

        if (chartLedgerRef) {
            chartLedgerRef.textContent = this.selectedAccountData.ledger_ref || 'N/A';
        }

        if (chartAccountRef) {
            chartAccountRef.textContent = this.selectedAccountData.account_ref || 'N/A';
        }

        if (chartDescription) {
            chartDescription.textContent = this.selectedAccountData.description || 'No description available';
        }

        if (accountBalance) {
            const balance = parseFloat(this.selectedAccountData.balance || 0);
            accountBalance.textContent = `£${balance.toFixed(2)}`;
        }

        // Update visual elements
        const coaPlaceholder = document.getElementById('coaPlaceholder');
        if (coaPlaceholder) {
            const displayText =
                `${this.selectedAccountData.ledger_ref || 'NO_LEDGER'} - ${this.selectedAccountData.account_ref || 'NO_ACCOUNT'}`;
            coaPlaceholder.textContent = displayText;
        }

        this.closeModal();
    }

    /**
     * Reset modal selection
     */
    resetSelection() {
        this.selectedLedgerRef = null;
        this.selectedAccountData = null;

        const selectedItems = document.querySelectorAll('.coa-item.selected');
        selectedItems.forEach(item => item.classList.remove('selected'));

        if (this.elements.accountRefList) {
            this.elements.accountRefList.innerHTML = '<div class="coa-empty">Select a Ledger Ref to view accounts</div>';
        }

        // Clear search inputs
        if (this.elements.ledgerSearchInput) this.elements.ledgerSearchInput.value = '';
        if (this.elements.accountSearchInput) this.elements.accountSearchInput.value = '';

        this.updateSelectedAccountDisplay();
    }

    /**
     * Get current modal data
     */
    getModalData() {
        return this.coaModalData;
    }

    /**
     * Get selected account data
     */
    getSelectedAccount() {
        return this.selectedAccountData;
    }
}

// Create global instance
window.coaModal = new CoaModalManager();