/**
 * ✅ UPDATED: Data Loading Module
 * Supports BOTH Main App (File model) AND Company Module (Customer model)
 */

class DataLoader {
    constructor() {
        // DETECT CONTEXT: Are we in Company Module or Main App?
        this.context = this.detectContext();
        console.log('🔍 DataLoader Context:', this.context);

        // Customer data (File table OR Customer table depending on context)
        this.customerDropdownData = [];
        this.isCustomerDataLoaded = false;

        // Supplier data
        this.supplierDropdownData = [];
        this.isSupplierDataLoaded = false;

        // Chart of Accounts data (chart_of_accounts table - same for both)
        this.ledgerRefsData = [];
        this.chartOfAccountsById = {};
        this.isLedgerDataLoaded = false;

        // LEGACY: Keep old property names for backward compatibility
        this.chartOfAccountsDropdownData = [];
        this.ledgerRefsDropdownData = [];
        this.isChartDataLoaded = false;
    }

    /**
     * ✅ NEW: Detect if we're in Company Module or Main App
     */
    detectContext() {
        const url = window.location.pathname;

        // Check URL patterns
        if (url.includes('/company/invoices') || url.includes('/company/')) {
            return 'company_module';
        }

        // Check for meta tag (optional - can add to blade)
        const contextMeta = document.querySelector('meta[name="app-context"]');
        if (contextMeta) {
            return contextMeta.getAttribute('content');
        }

        // Default to main app
        return 'main_app';
    }

    /**
     * Get CSRF token from meta tag
     */
    getCsrfToken() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        return csrfToken ? csrfToken.getAttribute('content') : '';
    }

    /**
     * Load customers - uses different endpoint based on context
     */
    loadCustomersForDropdown() {
        // ✅ Different endpoints for different contexts
        const endpoint = this.context === 'company_module'
            ? '/company/customers-dropdown'      // ✅ Company Module: Customer model
            : '/charts-of-accounts/dropdown';     // Main App: File model

        console.log('📡 Loading customers from:', endpoint, 'Context:', this.context);

        return fetch(endpoint, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            credentials: 'same-origin'  // ✅ Include cookies/session
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('📦 Raw customer data received:', data);

                // ✅ Handle both response formats
                let customers = [];

                if (this.context === 'company_module') {
                    // Company Module response: { success: true, customers: [...] }
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to load customers');
                    }
                    customers = data.customers || [];
                } else {
                    // Main App response: { success: true, chart_of_accounts: [...] }
                    customers = data.success && data.chart_of_accounts ? data.chart_of_accounts : [];
                }

                this.customerDropdownData = customers;
                this.chartOfAccountsDropdownData = customers;  // LEGACY alias
                this.isCustomerDataLoaded = true;
                this.isChartDataLoaded = true;  // LEGACY alias

                console.log('✅ Customers loaded:', this.customerDropdownData.length, 'items');
                console.log('📋 Sample customer:', this.customerDropdownData[0]);

                return this.customerDropdownData;
            })
            .catch(error => {
                console.error('❌ Failed to load customers:', error);
                console.error('Context:', this.context);
                console.error('Endpoint:', endpoint);

                // Fallback data
                this.customerDropdownData = [];
                this.chartOfAccountsDropdownData = [];
                this.isCustomerDataLoaded = true;
                this.isChartDataLoaded = true;

                return this.customerDropdownData;
            });
    }

    /**
     * Load suppliers for Purchase transactions
     */

    loadSuppliersForDropdown() {
        // ✅ Different endpoints for different contexts
        const endpoint = this.context === 'company_module'
            ? '/company/suppliers-dropdown'      // Company Module
            : '/suppliers-dropdown';             // Main App

        console.log('📡 Loading suppliers from:', endpoint, 'Context:', this.context);

        return fetch(endpoint, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            credentials: 'same-origin'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('📦 Raw supplier data received:', data);

                if (!data.success) {
                    throw new Error(data.message || 'Failed to load suppliers');
                }

                this.supplierDropdownData = data.suppliers || [];
                this.isSupplierDataLoaded = true;

                console.log('✅ Suppliers loaded:', this.supplierDropdownData.length, 'items');
                console.log('📋 Sample supplier:', this.supplierDropdownData[0]);
                console.log('🏢 Context:', data.context || this.context);

                return this.supplierDropdownData;
            })
            .catch(error => {
                console.error('❌ Failed to load suppliers:', error);
                console.error('Context:', this.context);
                console.error('Endpoint:', endpoint);

                this.supplierDropdownData = [];
                this.isSupplierDataLoaded = true;

                return this.supplierDropdownData;
            });
    }

    /**
     * ✅ LEGACY: Backward compatibility alias
     */
    loadChartOfAccountsForDropdown() {
        console.warn('⚠️ loadChartOfAccountsForDropdown() is deprecated. Use loadCustomersForDropdown() instead.');
        return this.loadCustomersForDropdown();
    }

    /**
     * ✅ Load ledger refs (ChartOfAccount table) - SAME for both contexts
     */
    loadLedgerRefsForDropdown() {
        return fetch('/api/ledger-refs-dropdown', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.ledger_refs) {
                    this.ledgerRefsData = data.ledger_refs;
                    this.ledgerRefsDropdownData = data.ledger_refs;
                    this.isLedgerDataLoaded = true;
                    console.log('✅ Ledger refs loaded:', this.ledgerRefsData.length);

                    // ✅ Also load ALL ChartOfAccount records for lookup
                    return this.loadAllChartOfAccounts();
                }
                throw new Error('No data');
            })
            .catch(error => {
                console.error('Failed to load ledger refs:', error);
                this.ledgerRefsData = [];
                this.ledgerRefsDropdownData = [];
                this.isLedgerDataLoaded = true;
                return this.ledgerRefsData;
            });
    }

    /**
     * ✅ Load ALL chart of accounts for ID lookup - SAME for both contexts
     */
    loadAllChartOfAccounts() {
        return fetch('/api/chart-of-accounts-all', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.accounts) {
                    this.chartOfAccountsById = {};
                    data.accounts.forEach(acc => {
                        this.chartOfAccountsById[acc.id] = acc;
                    });
                    console.log('✅ Chart of Accounts indexed:', Object.keys(this.chartOfAccountsById).length);
                    return this.chartOfAccountsById;
                }
                throw new Error('No data');
            })
            .catch(error => {
                console.error('Failed to load all chart of accounts:', error);
                this.chartOfAccountsById = {};
                return this.chartOfAccountsById;
            });
    }

    /**
     * ✅ Load all data at once
     */
    /**
 * ✅ FIXED: Load all data based on payment type
 */
    async loadAllData(paymentType = null) {
        console.log('🔄 Loading all dropdown data for context:', this.context);
        console.log('📋 Payment type:', paymentType);

        try {
            // ✅ Determine if we need suppliers or customers
            const purchaseTypes = ['purchase', 'purchase_credit'];
            const needsSuppliers = paymentType && purchaseTypes.includes(paymentType);

            if (needsSuppliers) {
                console.log('🔄 Loading SUPPLIERS for purchase transaction');
                await Promise.all([
                    this.loadSuppliersForDropdown(),   // ✅ Load suppliers
                    this.loadLedgerRefsForDropdown()   // Same for both
                ]);
            } else {
                console.log('🔄 Loading CUSTOMERS for sales transaction');
                await Promise.all([
                    this.loadCustomersForDropdown(),   // ✅ Load customers
                    this.loadLedgerRefsForDropdown()   // Same for both
                ]);
            }

            console.log('✅ All dropdown data loaded successfully');
        } catch (error) {
            console.error('❌ Error loading dropdown data:', error);
        }
    }

    /**
     * ✅ Wait for all data to be loaded
     */
    waitForDataLoad() {
        return new Promise((resolve) => {
            const checkInterval = setInterval(() => {
                if (this.isAllDataLoaded()) {
                    clearInterval(checkInterval);
                    console.log('✅ All data ready for use');
                    resolve(true);
                }
            }, 100);
        });
    }

    /**
     * ✅ UPDATED: Populate customer dropdown - handles both models
     */
    populateCustomerDropdown(preserveValue = null) {
        const dropdown = document.getElementById('customerDropdown');
        if (!dropdown) {
            console.warn("Customer dropdown not found!");
            return;
        }

        const currentValue = preserveValue || dropdown.value;
        const label = document.getElementById('customerFieldLabel');
        if (label) {
            label.textContent = 'Customer';
        }

        dropdown.innerHTML = '<option value="">Select Customer</option>';

        this.customerDropdownData.forEach(customer => {
            const option = document.createElement('option');

            // ✅ CONTEXT-AWARE: Different field names for different models
            if (this.context === 'company_module') {
                // Customer model fields
                option.value = customer.id;
                option.textContent = customer.Legal_Name_Company_Name || customer.legal_name_company_name;
                if (customer.Tax_ID_Number || customer.tax_id_number) {
                    option.textContent += ` (${customer.Tax_ID_Number || customer.tax_id_number})`;
                }
            } else {
                // File model fields
                option.value = customer.id;
                option.textContent = customer.ledger_ref;
                if (customer.description) {
                    option.textContent += ` (${customer.description})`;
                }
            }

            dropdown.appendChild(option);
        });

        if (currentValue && currentValue !== '') {
            dropdown.value = currentValue;
            console.log('✅ Customer dropdown value restored:', currentValue);
        }
    }



    /**
     * Populate supplier dropdown with better formatting
     */
    populateSuppliersDropdown(preserveValue = null) {  // ✅ Changed to PLURAL
        const dropdown = document.getElementById('customerDropdown');
        if (!dropdown) {
            console.warn("Customer dropdown not found!");
            return;
        }

        const currentValue = preserveValue || dropdown.value;

        // ✅ Change label
        const label = document.getElementById('customerFieldLabel');
        if (label) {
            label.textContent = 'Supplier';
        }

        dropdown.innerHTML = '<option value="">Select Supplier</option>';

        this.supplierDropdownData.forEach(supplier => {
            const option = document.createElement('option');
            option.value = supplier.id;

            // ✅ Handle both response formats (main app vs company module)
            if (this.context === 'company_module') {
                // Company Module format
                option.textContent = supplier.contact_name ||
                    `${supplier.first_name || ''} ${supplier.last_name || ''}`.trim();
            } else {
                // Main App format (has display_name)
                option.textContent = supplier.display_name || supplier.contact_name;
            }

            // Add account number if available
            if (supplier.account_number) {
                option.textContent += ` (${supplier.account_number})`;
            }

            dropdown.appendChild(option);
        });

        if (currentValue && currentValue !== '') {
            dropdown.value = currentValue;
            console.log('✅ Supplier dropdown value restored:', currentValue);
        }

        console.log('✅ Supplier dropdown populated with', this.supplierDropdownData.length, 'suppliers');
    }

    /**
     * Get Account Refs by Ledger - SAME for both contexts
     */
    getAccountRefsByLedger(ledgerRef, callback) {
        fetch('/api/account-refs-by-ledger', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            },
            body: JSON.stringify({
                ledger_ref: ledgerRef
            })
        })
            .then(response => response.json())
            .then(data => {
                callback(data.success && data.account_refs ? data.account_refs : []);
            })
            .catch(error => {
                console.error('Failed to load account refs:', error);
                callback([]);
            });
    }

    /**
     * ✅ Get ChartOfAccount record by id
     */
    getChartAccountById(coaId) {
        return this.chartOfAccountsById[coaId] || null;
    }

    /**
     * ✅ Get account by ID (File_ID or Customer id)
     */
    getAccountById(accountId) {
        return this.customerDropdownData.find(acc => acc.id == accountId);
    }

    /**
     * ✅ Check if all data is loaded
     */
    isAllDataLoaded() {
        // For purchase types, we need suppliers; for sales, we need customers
        const hasRequiredCustomerData = this.isSupplierDataLoaded || this.isCustomerDataLoaded;
        return hasRequiredCustomerData && this.isLedgerDataLoaded;
    }

    /**
     * ✅ Force refresh customer dropdown
     */
    refreshCustomerDropdown(selectedValue = null) {
        console.log('🔄 Refreshing customer dropdown, preserving:', selectedValue);
        this.populateCustomerDropdown(selectedValue);
    }

    /**
     * ✅ LEGACY: Get chart of accounts data
     */
    getChartOfAccountsData() {
        return this.customerDropdownData;
    }

    /**
     * ✅ Get ledger refs data
     */
    getLedgerRefsData() {
        return this.ledgerRefsData;
    }

    /**
     * ✅ NEW: Get current context
     */
    getContext() {
        return this.context;
    }

    /**
     * Switch dropdown based on payment type
     */
    async switchDropdownByPaymentType(paymentType) {
        console.log('🔄 Switching dropdown for payment type:', paymentType);

        const purchaseTypes = ['purchase', 'purchase_credit'];

        if (purchaseTypes.includes(paymentType)) {
            // Load suppliers if not already loaded
            if (!this.isSupplierDataLoaded) {
                await this.loadSuppliersForDropdown();
            }
            this.populateSuppliersDropdown();  // ✅ Changed to PLURAL
        } else {
            // Load customers if not already loaded
            if (!this.isCustomerDataLoaded) {
                await this.loadCustomersForDropdown();
            }
            this.populateCustomerDropdown();
        }
    }

    /**
     *  Reload dropdown data based on payment type
     * This ensures fresh data is loaded when switching payment types
     */
    async reloadDropdownForPaymentType(paymentType) {
        console.log('🔄 Reloading dropdown data for payment type:', paymentType);

        const purchaseTypes = ['purchase', 'purchase_credit'];
        const preservedValue = document.getElementById('customerDropdown')?.value;

        if (purchaseTypes.includes(paymentType)) {
            // ✅ Reload suppliers
            console.log('📦 Reloading suppliers...');
            await this.loadSuppliersForDropdown();
            this.populateSuppliersDropdown(preservedValue);  // ✅ Changed to PLURAL
        } else {
            // ✅ Reload customers
            console.log('📦 Reloading customers...');
            await this.loadCustomersForDropdown();
            this.populateCustomerDropdown(preservedValue);
        }

        console.log('✅ Dropdown reloaded successfully');
    }
}

// Initialize and expose globally
window.DataLoader = DataLoader;
window.dataLoader = null;

document.addEventListener('DOMContentLoaded', async () => {
    console.log('🔄 Initializing DataLoader...');
    window.dataLoader = new DataLoader();

    // ✅ DON'T load data here - let the blade file handle it with payment type
    // Data will be loaded in create_office.blade.php with correct payment type

    // ✅ Dispatch event to notify other scripts
    window.dispatchEvent(new CustomEvent('dataLoaderReady', {
        detail: {
            dataLoader: window.dataLoader,
            context: window.dataLoader.getContext()
        }
    }));

    console.log('✅ DataLoader initialized (data will be loaded by blade file)');
});