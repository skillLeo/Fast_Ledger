/**
 * ========================================================================
 * DATA LOADER - COMPLETE WITH SUPPLIER SUPPORT
 * ========================================================================
 * Handles loading of:
 * - Customers (File table for main app / Customer table for company module)
 * - Suppliers (Supplier table with user_id + company_id)
 * - Chart of Accounts
 * - Ledger Refs
 */

class DataLoader {
    constructor() {
        // ✅ Detect context: main app vs company module
        const contextMeta = document.querySelector('meta[name="app-context"]');
        this.context = contextMeta ? contextMeta.getAttribute('content') : 'main_app';
        console.log('🔍 DataLoader Context:', this.context);

        // ✅ Get company ID if in company module
        const companyMeta = document.querySelector('meta[name="company-id"]');
        this.companyId = companyMeta ? companyMeta.getAttribute('content') : null;

        // ✅ SEPARATE: Customer data
        this.customerDropdownData = [];
        this.isCustomerDataLoaded = false;
        
        // ✅ NEW: Supplier data
        this.supplierDropdownData = [];
        this.isSupplierDataLoaded = false;
        
        // ✅ SEPARATE: Chart of Accounts data
        this.ledgerRefsData = [];
        this.chartOfAccountsById = {};
        this.isLedgerDataLoaded = false;

        // ✅ LEGACY: Keep old property names for backward compatibility
        this.chartOfAccountsDropdownData = [];
        this.ledgerRefsDropdownData = [];
        this.isChartDataLoaded = false;
    }

    /**
     * Get CSRF token from meta tag
     */
    getCsrfToken() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        return csrfToken ? csrfToken.getAttribute('content') : '';
    }

    /**
     * ✅ Load customers based on context
     */
    loadCustomersForDropdown() {
        const endpoint = this.context === 'company_module' 
            ? '/company/customers-dropdown'
            : '/charts-of-accounts/dropdown';

        console.log('📡 Loading customers from:', endpoint, 'Context:', this.context);

        return fetch(endpoint, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('📦 Raw customer data received:', data);

            if (this.context === 'company_module') {
                // ✅ Company Module: customers array from Customer table
                if (data.success && data.customers) {
                    this.customerDropdownData = data.customers.map(customer => ({
                        id: customer.id,
                        ledger_ref: customer.Legal_Name_Company_Name,
                        description: customer.Email ? `${customer.Tax_ID_Number} - ${customer.Email}` : customer.Tax_ID_Number,
                        email: customer.Email,
                        tax_id: customer.Tax_ID_Number
                    }));
                    this.chartOfAccountsDropdownData = this.customerDropdownData;
                    this.isCustomerDataLoaded = true;
                    this.isChartDataLoaded = true;
                    console.log('✅ Customers loaded:', this.customerDropdownData.length);
                    console.log('📋 Sample customer:', this.customerDropdownData[0]);
                    return this.customerDropdownData;
                }
            } else {
                // ✅ Main App: chart_of_accounts array from File table
                if (data.success && data.chart_of_accounts) {
                    this.customerDropdownData = data.chart_of_accounts;
                    this.chartOfAccountsDropdownData = data.chart_of_accounts;
                    this.isCustomerDataLoaded = true;
                    this.isChartDataLoaded = true;
                    console.log('✅ Customers loaded:', this.customerDropdownData.length);
                    return this.customerDropdownData;
                }
            }
            
            throw new Error('No customer data received');
        })
        .catch(error => {
            console.error('Failed to load customers:', error);
            this.customerDropdownData = [];
            this.chartOfAccountsDropdownData = [];
            this.isCustomerDataLoaded = true;
            this.isChartDataLoaded = true;
            return this.customerDropdownData;
        });
    }

    /**
     * ✅ NEW: Load suppliers based on context
     */
    loadSuppliersForDropdown() {
        const endpoint = this.context === 'company_module'
            ? '/company/suppliers-dropdown'
            : '/suppliers-dropdown';

        console.log('📡 Loading suppliers from:', endpoint, 'Context:', this.context);

        return fetch(endpoint, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.getCsrfToken()
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('📦 Raw supplier data received:', data);

            if (data.success && data.suppliers) {
                this.supplierDropdownData = data.suppliers.map(supplier => ({
                    id: supplier.id,
                    name: supplier.contact_name || `${supplier.first_name || ''} ${supplier.last_name || ''}`.trim(),
                    email: supplier.email,
                    phone: supplier.phone,
                    account_number: supplier.account_number,
                    company_id: supplier.company_id
                }));
                this.isSupplierDataLoaded = true;
                console.log('✅ Suppliers loaded:', this.supplierDropdownData.length);
                console.log('📋 Sample supplier:', this.supplierDropdownData[0]);
                return this.supplierDropdownData;
            }
            
            throw new Error('No supplier data received');
        })
        .catch(error => {
            console.error('❌ Failed to load suppliers:', error);
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
     * ✅ Load ledger refs (ChartOfAccount table) for item rows
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
     * ✅ Load ALL chart of accounts for ID lookup
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
     * ✅ Load all data at once (with optional payment type)
     */
    async loadAllData(paymentType = 'sales_invoice') {
        console.log('🔄 Loading all dropdown data for context:', this.context);
        console.log('💡 Payment type:', paymentType);
        
        try {
            const promises = [
                this.loadLedgerRefsForDropdown()
            ];

            // ✅ Load customers OR suppliers based on payment type
            const isPurchaseType = ['purchase', 'purchase_credit'].includes(paymentType);
            
            if (isPurchaseType) {
                console.log('📦 Loading SUPPLIERS for payment type:', paymentType);
                promises.push(this.loadSuppliersForDropdown());
            } else {
                console.log('👥 Loading CUSTOMERS for payment type:', paymentType);
                promises.push(this.loadCustomersForDropdown());
            }

            await Promise.all(promises);
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
     * ✅ Populate customer dropdown
     */
    populateCustomerDropdown(preserveValue = null, paymentType = 'sales_invoice') {
        const dropdown = document.getElementById('customerDropdown');
        if (!dropdown) {
            console.warn("⚠️ Customer dropdown not found!");
            return;
        }

        const currentValue = preserveValue || dropdown.value;
        
        // ✅ Determine label based on payment type
        const isPurchaseType = ['purchase', 'purchase_credit'].includes(paymentType);
        const label = isPurchaseType ? 'Supplier' : 'Customer';
        
        dropdown.innerHTML = `<option value="">Select ${label}</option>`;

        // ✅ Use customerDropdownData
        this.customerDropdownData.forEach(customer => {
            const option = document.createElement('option');
            option.value = customer.id;
            option.textContent = customer.ledger_ref || customer.name;
            if (customer.description) {
                option.textContent += ` (${customer.description})`;
            }
            dropdown.appendChild(option);
        });

        if (currentValue && currentValue !== '') {
            dropdown.value = currentValue;
            console.log('✅ Customer dropdown value restored:', currentValue);
        }

        console.log(`✅ ${label} dropdown populated with ${this.customerDropdownData.length} items`);
    }

    /**
     * ✅ NEW: Populate supplier dropdown
     */
    populateSuppliersDropdown(preserveValue = null) {
        console.log('🔄 populateSuppliersDropdown called');
        
        const dropdown = document.getElementById('customerDropdown');
        if (!dropdown) {
            console.warn("⚠️ Supplier dropdown element not found!");
            return;
        }

        const currentValue = preserveValue || dropdown.value;
        dropdown.innerHTML = '<option value="">Select Supplier</option>';

        console.log('📦 Supplier data to populate:', this.supplierDropdownData.length, 'items');

        if (this.supplierDropdownData.length === 0) {
            console.warn('⚠️ No suppliers available to populate');
            dropdown.innerHTML += '<option value="" disabled>No suppliers found</option>';
            return;
        }

        // ✅ Use supplierDropdownData
        this.supplierDropdownData.forEach(supplier => {
            const option = document.createElement('option');
            option.value = supplier.id;
            option.textContent = supplier.name || 'Unnamed Supplier';
            
            if (supplier.email) {
                option.textContent += ` (${supplier.email})`;
            } else if (supplier.account_number) {
                option.textContent += ` (${supplier.account_number})`;
            }
            
            dropdown.appendChild(option);
        });

        if (currentValue && currentValue !== '') {
            dropdown.value = currentValue;
            console.log('✅ Supplier dropdown value restored:', currentValue);
        }

        console.log(`✅ Supplier dropdown populated with ${this.supplierDropdownData.length} items`);
    }

    /**
     * ✅ NEW: Reload dropdown for payment type (used when switching)
     */
    async reloadDropdownForPaymentType(paymentType) {
        console.log('🔄 reloadDropdownForPaymentType called with:', paymentType);
        
        const isPurchaseType = ['purchase', 'purchase_credit'].includes(paymentType);
        
        if (isPurchaseType) {
            console.log('📦 Switching to SUPPLIERS...');
            // Load suppliers
            await this.loadSuppliersForDropdown();
            this.populateSuppliersDropdown();
        } else {
            console.log('👥 Switching to CUSTOMERS...');
            // Load customers
            await this.loadCustomersForDropdown();
            this.populateCustomerDropdown(null, paymentType);
        }
    }

    /**
     * ✅ NEW: Reload suppliers dropdown (for explicit supplier reload)
     */
    async reloadSuppliersDropdown() {
        console.log('🔄 Reloading suppliers dropdown...');
        await this.loadSuppliersForDropdown();
        this.populateSuppliersDropdown();
    }

    /**
     * Get Account Refs by Ledger
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
     * ✅ Get account by ID (for edit mode)
     */
    getAccountById(id) {
        return this.customerDropdownData.find(acc => acc.id == id);
    }

    /**
     * ✅ Get supplier by ID
     */
    getSupplierById(id) {
        return this.supplierDropdownData.find(sup => sup.id == id);
    }

    /**
     * ✅ Check if all data is loaded
     */
    isAllDataLoaded() {
        return this.isLedgerDataLoaded && 
               (this.isCustomerDataLoaded || this.isSupplierDataLoaded);
    }

    /**
     * ✅ Force refresh customer dropdown
     */
    refreshCustomerDropdown(selectedValue = null) {
        console.log('🔄 Refreshing customer dropdown, preserving:', selectedValue);
        this.populateCustomerDropdown(selectedValue);
    }

    /**
     * ✅ Force refresh supplier dropdown
     */
    refreshSupplierDropdown(selectedValue = null) {
        console.log('🔄 Refreshing supplier dropdown, preserving:', selectedValue);
        this.populateSuppliersDropdown(selectedValue);
    }

    /**
     * ✅ Get chart of accounts data
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
     * ✅ Get suppliers data
     */
    getSuppliersData() {
        return this.supplierDropdownData;
    }
}

// Initialize and expose globally
window.DataLoader = DataLoader;
window.dataLoader = null;

document.addEventListener('DOMContentLoaded', async () => {
    console.log('🔄 Initializing DataLoader...');
    window.dataLoader = new DataLoader();

    // ✅ Determine initial payment type from URL or form
    const urlParams = new URLSearchParams(window.location.search);
    const initialPaymentType = urlParams.get('payment_type') || 'sales_invoice';
    console.log('💡 Initial payment type detected:', initialPaymentType);

    // ✅ Load data based on initial payment type
    await window.dataLoader.loadAllData(initialPaymentType);

    // ✅ Populate appropriate dropdown
    const isPurchaseType = ['purchase', 'purchase_credit'].includes(initialPaymentType);
    if (isPurchaseType) {
        console.log('📦 Initial: Populating suppliers dropdown');
        window.dataLoader.populateSuppliersDropdown();
    } else {
        console.log('👥 Initial: Populating customers dropdown');
        window.dataLoader.populateCustomerDropdown();
    }

    // ✅ Dispatch event to notify other scripts
    window.dispatchEvent(new CustomEvent('dataLoaderReady', {
        detail: { dataLoader: window.dataLoader }
    }));

    console.log('✅ DataLoader initialized and data preloaded');
});