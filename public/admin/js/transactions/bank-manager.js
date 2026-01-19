/**
 * ========================================================================
 * BANK ACCOUNT MANAGER
 * ========================================================================
 * Manages bank account dropdowns and inter-bank office functionality:
 * - Switching between single and dual bank account fields
 * - Loading bank accounts via AJAX
 * - Filtering bank account options
 * - Validation for inter-bank transfers
 */

class BankManager {
    constructor() {
        this.elements = {
            singleBankAccountField: null,
            bankAccountDropdown: null,
            bankAccountFromField: null,
            bankAccountToField: null,
            bankAccountFromDropdown: null,
            bankAccountToDropdown: null
        };
    }

    /**
     * Initialize DOM element references
     */
    initializeElements() {
        this.elements.singleBankAccountField = document.getElementById('singleBankAccountField');
        this.elements.bankAccountDropdown = document.getElementById('BankAccountDropdown');
        this.elements.bankAccountFromField = document.getElementById('bankAccountFromField');
        this.elements.bankAccountToField = document.getElementById('bankAccountToField');
        this.elements.bankAccountFromDropdown = document.getElementById('BankAccountFromDropdown');
        this.elements.bankAccountToDropdown = document.getElementById('BankAccountToDropdown');

        console.log('Bank Manager elements initialized');

        // Setup event listener for From dropdown
        if (this.elements.bankAccountFromDropdown) {
            this.elements.bankAccountFromDropdown.addEventListener('change', () => {
                this.filterBankAccountToOptions();
            });
        }
    }

    /**
     * Fetch bank accounts from server
     * @param {string} scope - 'office' or 'all'
     */
    async fetchBankAccounts(scope = 'office') {
        const url = `/bank-accounts?scope=${encodeURIComponent(scope)}`;
        
        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            
            if (!data.success) {
                throw new Error('Failed to load bank accounts');
            }

            return data.banks; // [{Bank_Account_ID, Bank_Name, Bank_Type_ID}, ...]
        } catch (error) {
            console.error('Error fetching bank accounts:', error);
            throw error;
        }
    }

    /**
     * Render bank account options HTML
     * @param {Array} banks - Array of bank account objects
     */
    renderOptionsForBanks(banks) {
        return banks.map(bank => {
            const typeTag = String(bank.Bank_Type_ID) === '1' ? 'CF' : 'OF'; // 1=Client, 2=Office
            return `<option value="${bank.Bank_Account_ID}" data-bank-type="${bank.Bank_Type_ID}">
                ${bank.Bank_Name} (${typeTag})
            </option>`;
        }).join('');
    }

    /**
     * Load bank accounts for From dropdown
     * @param {string} scope - 'office' or 'all'
     */
    async loadFromBanksForScope(scope) {
        if (!this.elements.bankAccountFromDropdown) return;

        // Reset with placeholder
        this.elements.bankAccountFromDropdown.innerHTML = 
            '<option value="" disabled selected>Select Source Bank Account</option>';

        try {
            const banks = await this.fetchBankAccounts(scope);
            this.elements.bankAccountFromDropdown.insertAdjacentHTML('beforeend', this.renderOptionsForBanks(banks));
        } catch (error) {
            console.error('Failed to load bank accounts:', error);
            this.elements.bankAccountFromDropdown.innerHTML = 
                '<option value="" disabled>Error loading bank accounts</option>';
        }
    }

    /**
     * Reset all office bank fields to hidden state
     */
    resetOfficeBankFields() {
        if (this.elements.singleBankAccountField) {
            this.elements.singleBankAccountField.style.display = 'none';
        }
        if (this.elements.bankAccountFromField) {
            this.elements.bankAccountFromField.style.display = 'none';
        }
        if (this.elements.bankAccountToField) {
            this.elements.bankAccountToField.style.display = 'none';
        }

        // Remove name and required attributes
        if (this.elements.bankAccountDropdown) {
            this.elements.bankAccountDropdown.removeAttribute('name');
            this.elements.bankAccountDropdown.removeAttribute('required');
        }
        if (this.elements.bankAccountFromDropdown) {
            this.elements.bankAccountFromDropdown.removeAttribute('name');
            this.elements.bankAccountFromDropdown.removeAttribute('required');
        }
        if (this.elements.bankAccountToDropdown) {
            this.elements.bankAccountToDropdown.removeAttribute('name');
            this.elements.bankAccountToDropdown.removeAttribute('required');
        }
    }

    /**
     * Populate To dropdown by cloning From dropdown options
     */
    populateBankAccountTo() {
        if (!this.elements.bankAccountFromDropdown || !this.elements.bankAccountToDropdown) return;

        this.elements.bankAccountToDropdown.innerHTML = 
            '<option value="" disabled selected>Select Destination Bank Account</option>';

        const fromOptions = this.elements.bankAccountFromDropdown.querySelectorAll('option:not([value=""])');
        fromOptions.forEach(option => {
            const copy = option.cloneNode(true);
            this.elements.bankAccountToDropdown.appendChild(copy);
        });

        this.filterBankAccountToOptions();
    }

    /**
     * Filter To dropdown to prevent selecting same account as From
     */
    filterBankAccountToOptions() {
        if (!this.elements.bankAccountFromDropdown || !this.elements.bankAccountToDropdown) return;

        const fromValue = this.elements.bankAccountFromDropdown.value;
        
        this.elements.bankAccountToDropdown.querySelectorAll('option').forEach(option => {
            if (option.value === '' || option.value !== fromValue) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
                if (option.selected) {
                    this.elements.bankAccountToDropdown.value = '';
                }
            }
        });
    }

    /**
     * Setup fields for inter-bank office transfers (dual dropdowns)
     */
    async setupInterBankOfficeFields() {
        this.resetOfficeBankFields();

        if (this.elements.bankAccountFromField) {
            this.elements.bankAccountFromField.style.display = '';
        }
        if (this.elements.bankAccountToField) {
            this.elements.bankAccountToField.style.display = '';
        }

        if (this.elements.bankAccountFromDropdown) {
            this.elements.bankAccountFromDropdown.setAttribute('name', 'Bank_Account_From_ID');
            this.elements.bankAccountFromDropdown.setAttribute('required', 'required');
        }
        if (this.elements.bankAccountToDropdown) {
            this.elements.bankAccountToDropdown.setAttribute('name', 'Bank_Account_To_ID');
            this.elements.bankAccountToDropdown.setAttribute('required', 'required');
            this.elements.bankAccountToDropdown.setAttribute('style', 'width: 290px; height: 25px;');
        }

        // Load ALL banks for inter-bank office
        try {
            await this.loadFromBanksForScope('all');
        } catch (error) {
            console.error('Could not load ALL bank accounts:', error);
        }

        // Clone From options to To dropdown
        this.populateBankAccountTo();
    }

    /**
     * Setup fields for single bank account (normal transactions)
     */
    setupSingleOfficeFields() {
        this.resetOfficeBankFields();

        if (this.elements.singleBankAccountField) {
            this.elements.singleBankAccountField.style.display = '';
        }
        if (this.elements.bankAccountDropdown) {
            this.elements.bankAccountDropdown.setAttribute('name', 'Bank_Account_ID');
            this.elements.bankAccountDropdown.setAttribute('required', 'required');
        }
    }

    /**
     * Toggle between single and dual bank account fields
     * @param {string} paymentType - The current payment type
     */
    async toggleOfficeBankFields(paymentType) {
        if (paymentType === 'inter_bank_office') {
            await this.setupInterBankOfficeFields();
        } else {
            this.setupSingleOfficeFields();
        }
    }

    /**
     * Validate inter-bank office transfer
     * @returns {boolean|string} - true if valid, error message if invalid
     */
    validateInterBankTransfer() {
        if (!this.elements.bankAccountFromDropdown || !this.elements.bankAccountToDropdown) {
            return 'Bank account fields not found';
        }

        const fromId = this.elements.bankAccountFromDropdown.value || '';
        const toId = this.elements.bankAccountToDropdown.value || '';

        if (!fromId || !toId) {
            return 'Please select BOTH source and destination bank accounts.';
        }

        if (fromId === toId) {
            return 'Source and destination bank accounts cannot be the same.';
        }

        return true;
    }
}

// Create global instance
window.bankManager = new BankManager();