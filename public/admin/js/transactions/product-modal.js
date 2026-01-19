/**
 * Product Modal & Dropdown Manager
 * Handles product creation, editing, dropdown selection, and auto-fill
 * Updated to work on both transaction forms and product index page
 */
class ProductModalManager {
    constructor() {
        this.productModalInstance = null;
        this.isEditMode = false;
        this.editProductId = null;
        this.currentProductRow = null;
        this.loadedProducts = [];
        this.activeProductDropdown = null;
        this.currentProductCategory = null;
        this.isIndexPage = false; // Track if we're on the index page
    }

    /**
     * Initialize product modal
     */
    initialize() {
        this.detectPageContext();
        this.initializeModal();
        this.initializeDropdowns();
        this.bindGlobalEvents();
        console.log('✅ Product modal initialized', { isIndexPage: this.isIndexPage });
    }

    /**
     * Detect if we're on the index page or transaction form
     */
    detectPageContext() {
        const hasTransactionForm = document.getElementById('salesInvoiceForm') ||
            document.getElementById('regularOfficeForm');
        this.isIndexPage = !hasTransactionForm;
    }

    /**
     * Initialize Bootstrap modal instance
     */
    initializeModal() {
        const productModalEl = document.getElementById('productModal');
        if (productModalEl) {
            this.productModalInstance = new bootstrap.Modal(productModalEl);
        }

        this.bindModalEvents();
        this.bindImageEvents();
        this.bindFormEvents();
    }

    /**
     * Bind modal-specific events
     */
    bindModalEvents() {
        // Modal close event
        const productModalEl = document.getElementById('productModal');
        if (productModalEl) {
            productModalEl.addEventListener('hidden.bs.modal', () => {
                this.resetProductForm();
                this.isEditMode = false;
                this.editProductId = null;
            });
        }

        // Checkbox toggle handlers
        const purchaseCheck = document.getElementById('createPurchaseCheck');
        const salesCheck = document.getElementById('createSalesCheck');

        if (purchaseCheck) {
            purchaseCheck.addEventListener('change', () => this.togglePurchaseSection());
        }
        if (salesCheck) {
            salesCheck.addEventListener('change', () => this.toggleSalesSection());
        }

        // Save button
        const saveProductBtn = document.getElementById('saveProductBtn');
        if (saveProductBtn) {
            saveProductBtn.addEventListener('click', () => this.saveProduct());
        }

        // Ledger change handlers
        this.setupProductLedgerHandlers();
    }

    /**
     * Bind image upload events
     */
    bindImageEvents() {
        const imageInput = document.getElementById('commonItemImage');
        if (imageInput) {
            imageInput.addEventListener('change', (e) => this.handleImageUpload(e));
        }

        // Global clear image function
        window.clearImagePreview = () => this.clearImagePreview();
        window.showFullImageModal = (imageSrc) => this.showFullImageModal(imageSrc);
    }

    /**
     * Bind form events
     */
    bindFormEvents() {
        // Existing form bindings are handled in bindModalEvents
    }

    /**
     * Handle image upload and preview
     */
    handleImageUpload(event) {
        const file = event.target.files[0];

        if (file) {
            // Validate file size (5MB max)
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('File size must be less than 5MB');
                event.target.value = '';
                return;
            }

            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Only JPG, PNG, GIF, and WEBP images are allowed');
                event.target.value = '';
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = (e) => {
                const newImageThumb = document.getElementById('newImageThumb');
                const newImagePreview = document.getElementById('newImagePreview');
                const currentImagePreview = document.getElementById('currentImagePreview');
                const emptyPlaceholder = document.getElementById('emptyImagePlaceholder');

                if (newImageThumb) newImageThumb.src = e.target.result;
                if (newImagePreview) newImagePreview.style.display = 'block';
                if (currentImagePreview) currentImagePreview.style.display = 'none';
                if (emptyPlaceholder) emptyPlaceholder.style.display = 'none';
            };
            reader.readAsDataURL(file);
        } else {
            // If no file selected, hide new preview
            this.handleNoFileSelected();
        }
    }

    /**
     * Handle when no file is selected
     */
    handleNoFileSelected() {
        const newImagePreview = document.getElementById('newImagePreview');
        const currentImagePreview = document.getElementById('currentImagePreview');
        const currentImageThumb = document.getElementById('currentImageThumb');
        const emptyPlaceholder = document.getElementById('emptyImagePlaceholder');

        if (newImagePreview) newImagePreview.style.display = 'none';

        if (this.isEditMode && currentImageThumb && currentImageThumb.src) {
            if (currentImagePreview) currentImagePreview.style.display = 'block';
            if (emptyPlaceholder) emptyPlaceholder.style.display = 'none';
        } else {
            if (currentImagePreview) currentImagePreview.style.display = 'none';
            if (emptyPlaceholder) emptyPlaceholder.style.display = 'block';
        }
    }

    /**
     * Clear image preview
     */
    clearImagePreview() {
        const imageInput = document.getElementById('commonItemImage');
        if (imageInput) imageInput.value = '';

        this.handleNoFileSelected();
    }

    /**
     * Show full image in modal
     */
    showFullImageModal(imageSrc) {
        const fullSizeImage = document.getElementById('fullSizeImage');
        if (fullSizeImage) {
            fullSizeImage.src = imageSrc;
            const imageModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
            imageModal.show();
        }
    }

    /**
     * Determine category based on context
     */
    determineCategory(defaultCategory = 'purchase') {
        // If we have a manually set category, use it
        if (this.currentProductCategory) {
            return this.currentProductCategory;
        }

        // If we're on the transaction form, use formManager
        if (!this.isIndexPage && window.formManager && typeof window.formManager.getCurrentPaymentType === 'function') {
            const currentPaymentType = window.formManager.getCurrentPaymentType();

            if (currentPaymentType === 'purchase' || currentPaymentType === 'purchase_credit') {
                return 'purchase';
            } else if (currentPaymentType === 'sales_invoice' || currentPaymentType === 'sales_credit') {
                return 'sales';
            }
        }

        // Default fallback
        return defaultCategory;
    }

    /**
     * Open modal for CREATE
     */
    openProductModalForCreate(category = 'purchase') {
        this.closeAllProductDropdowns();
        this.currentProductCategory = category;
        this.isEditMode = false;
        this.editProductId = null;
        this.resetProductForm();

        // Set modal title
        const modalLabel = document.getElementById('productModalLabel');
        const saveBtn = document.getElementById('saveProductBtn');

        if (modalLabel) {
            modalLabel.innerHTML = '<i class="fas fa-box me-2"></i>Add New Item';
        }
        if (saveBtn) {
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Product';
        }

        // Auto-check category
        if (category === 'purchase') {
            const purchaseCheck = document.getElementById('createPurchaseCheck');
            if (purchaseCheck) {
                purchaseCheck.checked = true;
                this.togglePurchaseSection();
            }
        } else if (category === 'sales') {
            const salesCheck = document.getElementById('createSalesCheck');
            if (salesCheck) {
                salesCheck.checked = true;
                this.toggleSalesSection();
            }
        }

        if (this.productModalInstance) {
            this.productModalInstance.show();
        }
    }

    /**
     * Open modal for EDIT
     */
    openProductModalForEdit(productId) {
        this.isEditMode = true;
        this.editProductId = productId;
        this.resetProductForm();

        // Set modal title
        const modalLabel = document.getElementById('productModalLabel');
        const saveBtn = document.getElementById('saveProductBtn');

        if (modalLabel) {
            modalLabel.innerHTML = '<i class="fas fa-edit me-2"></i>Edit Product';
        }
        if (saveBtn) {
            saveBtn.innerHTML = '<i class="fas fa-save me-1"></i>Update Product';
        }

        // Load product data
        this.loadProductData(productId);
    }

    /**
     * Load product data for editing
     */
    loadProductData(productId) {
        fetch(`/products/${productId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.product) {
                    this.populateFormWithProduct(data.product);
                    if (this.productModalInstance) {
                        this.productModalInstance.show();
                    }
                } else {
                    alert('Failed to load product data');
                }
            })
            .catch(error => {
                console.error('Error loading product:', error);
                alert('Error loading product data');
            });
    }

    /**
     * Populate form with product data
     */

    populateFormWithProduct(product) {
        console.log('📝 Populating form with product:', product);

        // Store edit ID
        const editIdInput = document.getElementById('productEditId');
        if (editIdInput) editIdInput.value = product.id;

        // Fill common fields
        const itemCodeInput = document.getElementById('commonItemCode');
        const itemNameInput = document.getElementById('commonItemName');

        if (itemCodeInput) itemCodeInput.value = product.item_code || '';
        if (itemNameInput) itemNameInput.value = product.name || '';

        // Show current image if exists
        if (product.file_url) {
            const currentImagePreview = document.getElementById('currentImagePreview');
            const currentImageThumb = document.getElementById('currentImageThumb');
            const emptyPlaceholder = document.getElementById('emptyImagePlaceholder');

            if (currentImagePreview) currentImagePreview.style.display = 'block';
            if (currentImageThumb) currentImageThumb.src = product.file_url;
            if (emptyPlaceholder) emptyPlaceholder.style.display = 'none';
        } else {
            const currentImagePreview = document.getElementById('currentImagePreview');
            const emptyPlaceholder = document.getElementById('emptyImagePlaceholder');

            if (currentImagePreview) currentImagePreview.style.display = 'none';
            if (emptyPlaceholder) emptyPlaceholder.style.display = 'block';
        }

        // Hide new image preview
        const newImagePreview = document.getElementById('newImagePreview');
        if (newImagePreview) newImagePreview.style.display = 'none';

        // Check and fill category-specific section
        if (product.category === 'purchase') {
            const purchaseCheck = document.getElementById('createPurchaseCheck');
            if (purchaseCheck) {
                purchaseCheck.checked = true;
                this.togglePurchaseSection();
            }

            // ✅ CRITICAL: Load dropdowns FIRST, then populate
            this.loadProductDropdownsAndPopulate('purchase', product);

        } else if (product.category === 'sales') {
            const salesCheck = document.getElementById('createSalesCheck');
            if (salesCheck) {
                salesCheck.checked = true;
                this.toggleSalesSection();
            }

            // ✅ CRITICAL: Load dropdowns FIRST, then populate
            this.loadProductDropdownsAndPopulate('sales', product);
        }
    }


    /**
 * ✅ NEW: Load dropdowns and wait for them to populate before setting values
 */
    loadProductDropdownsAndPopulate(category, product) {
        console.log('🔄 Loading dropdowns for category:', category);

        const prefix = category; // 'purchase' or 'sales'

        // Step 1: Load Ledger dropdown
        this.loadLedgerRefsForProductEdit(prefix, () => {
            console.log('✅ Ledger dropdown loaded');

            // Step 2: Set ledger value
            const ledgerSelect = document.getElementById(`${prefix}LedgerRef`);
            if (ledgerSelect && product.ledger_id) {
                ledgerSelect.value = product.ledger_id;
                console.log('✅ Ledger set to:', product.ledger_id);

                // Step 3: Get ledger_ref from selected option
                const selectedOption = ledgerSelect.options[ledgerSelect.selectedIndex];
                const ledgerRef = selectedOption ? selectedOption.dataset.ledgerRef : null;

                if (ledgerRef) {
                    // Step 4: Load Account Refs based on ledger
                    this.loadAccountRefsForProductEdit(prefix, ledgerRef, () => {
                        console.log('✅ Account dropdown loaded');

                        // Step 5: Set account value
                        const accountSelect = document.getElementById(`${prefix}AccountRef`);
                        if (accountSelect && product.account_ref) {
                            accountSelect.value = product.account_ref;
                            console.log('✅ Account set to:', product.account_ref);
                        }
                    });
                }
            }

            // Step 6: Fill other fields
            this.fillCategoryFieldsForEdit(prefix, product);
        });

        // Step 7: Load VAT dropdown separately
        this.loadVatTypesForProductEdit(prefix, product);
    }

    /**
     * ✅ NEW: Fill remaining category fields
     */
    fillCategoryFieldsForEdit(prefix, product) {
        const descInput = document.getElementById(`${prefix}Description`);
        const unitInput = document.getElementById(`${prefix}UnitAmount`);

        if (descInput) descInput.value = product.description || '';
        if (unitInput) unitInput.value = product.unit_amount || '';

        console.log('✅ Description and unit amount filled');
    }

    /**
     * ✅ NEW: Load ledger refs with callback
     */
    loadLedgerRefsForProductEdit(prefix, callback) {
        const ledgerSelect = document.getElementById(`${prefix}LedgerRef`);
        if (!ledgerSelect) {
            console.error('❌ Ledger select not found');
            return;
        }

        ledgerSelect.innerHTML = '<option value="">Loading...</option>';

        // Check if dataLoader exists
        if (window.dataLoader && typeof window.dataLoader.loadLedgerRefs === 'function') {
            window.dataLoader.loadLedgerRefs(() => {
                const ledgerData = window.dataLoader.getLedgerRefsData();
                this.populateLedgerDropdown(ledgerSelect, ledgerData);
                callback();
            });
        } else {
            // Fallback API call
            fetch('/api/ledger-refs-dropdown', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.ledger_refs) {
                        this.populateLedgerDropdown(ledgerSelect, data.ledger_refs);
                        callback();
                    } else {
                        ledgerSelect.innerHTML = '<option value="">No ledgers available</option>';
                    }
                })
                .catch(error => {
                    console.error('Failed to load ledger refs:', error);
                    ledgerSelect.innerHTML = '<option value="">Error loading ledgers</option>';
                });
        }
    }

    /**
     * ✅ NEW: Populate ledger dropdown with options
     */
    populateLedgerDropdown(selectElement, ledgers) {
        selectElement.innerHTML = '<option value="">Select Ledger</option>';

        if (ledgers && ledgers.length > 0) {
            ledgers.forEach(ledger => {
                const option = document.createElement('option');
                option.value = ledger.id;
                option.dataset.ledgerRef = ledger.ledger_ref;
                option.textContent = ledger.ledger_ref;
                selectElement.appendChild(option);
            });
        }
    }

    /**
     * ✅ NEW: Load account refs with callback
     */
    loadAccountRefsForProductEdit(prefix, ledgerRef, callback) {
        const accountSelect = document.getElementById(`${prefix}AccountRef`);
        if (!accountSelect || !ledgerRef) {
            console.error('❌ Account select not found or no ledger ref');
            return;
        }

        accountSelect.innerHTML = '<option value="">Loading...</option>';
        accountSelect.disabled = true;

        if (window.dataLoader && typeof window.dataLoader.getAccountRefsByLedger === 'function') {
            window.dataLoader.getAccountRefsByLedger(ledgerRef, (accountRefs) => {
                this.populateAccountDropdown(accountSelect, accountRefs);
                accountSelect.disabled = false;
                callback();
            });
        } else {
            // Fallback API call
            fetch(`/api/account-refs-by-ledger?ledger_ref=${ledgerRef}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.account_refs) {
                        this.populateAccountDropdown(accountSelect, data.account_refs);
                    } else {
                        accountSelect.innerHTML = '<option value="">No accounts available</option>';
                    }
                    accountSelect.disabled = false;
                    callback();
                })
                .catch(error => {
                    console.error('Failed to load account refs:', error);
                    accountSelect.innerHTML = '<option value="">Error loading accounts</option>';
                    accountSelect.disabled = false;
                });
        }
    }

    /**
     * ✅ NEW: Populate account dropdown with options
     */
    populateAccountDropdown(selectElement, accounts) {
        selectElement.innerHTML = '<option value="">Select Account</option>';

        if (accounts && accounts.length > 0) {
            accounts.forEach(account => {
                const option = document.createElement('option');
                option.value = account.account_ref;
                option.dataset.accountId = account.id;
                option.dataset.vatId = account.vat_id || '';
                option.textContent = account.account_ref;
                if (account.description) {
                    option.textContent += ` (${account.description})`;
                }
                selectElement.appendChild(option);
            });
        }
    }

    /**
     * ✅ NEW: Load VAT types with product value
     */
    loadVatTypesForProductEdit(prefix, product) {
        const vatSelect = document.getElementById(`${prefix}VatRate`);
        if (!vatSelect) {
            console.error('❌ VAT select not found');
            return;
        }

        const paymentType = prefix === 'purchase' ? 'purchase' : 'sales_invoice';
        vatSelect.innerHTML = '<option value="">Loading...</option>';

        if (window.vatManager && typeof window.vatManager.loadVatTypesByForm === 'function') {
            window.vatManager.loadVatTypesByForm(paymentType, (vatTypes) => {
                vatSelect.innerHTML = window.vatManager.createVatDropdownOptions(vatTypes);

                if (product.vat_type_id) { // ✅ Use vat_type_id
                    const vatOption = Array.from(vatSelect.options).find(opt =>
                        opt.dataset.vatTypeId == product.vat_type_id
                    );

                    if (vatOption) {
                        vatSelect.value = vatOption.value;
                        console.log('✅ VAT rate set to:', vatOption.value + '%', 'for vat_type_id:', product.vat_type_id);
                    }
                }
            });
        } else {
            // Fallback - load basic VAT options
            vatSelect.innerHTML = `
            <option value="">Select VAT Rate</option>
            <option value="0" data-vat-id="5">0% (No VAT)</option>
            <option value="20" data-vat-id="1">20% (Standard)</option>
            <option value="5" data-vat-id="2">5% (Reduced)</option>
        `;

            // Set value
            if (product.vat_rate_id) {
                const vatOption = Array.from(vatSelect.options).find(opt =>
                    opt.dataset.vatId == product.vat_rate_id
                );
                if (vatOption) {
                    vatSelect.value = vatOption.value;
                }
            }
        }
    }



    /**
     * Reset product form
     */
    resetProductForm() {
        const form = document.getElementById('productForm');
        if (form) form.reset();

        const editIdInput = document.getElementById('productEditId');
        if (editIdInput) editIdInput.value = '';

        const purchaseCheck = document.getElementById('createPurchaseCheck');
        const salesCheck = document.getElementById('createSalesCheck');
        const purchaseSection = document.getElementById('purchaseSection');
        const salesSection = document.getElementById('salesSection');

        if (purchaseCheck) purchaseCheck.checked = false;
        if (salesCheck) salesCheck.checked = false;
        if (purchaseSection) purchaseSection.style.display = 'none';
        if (salesSection) salesSection.style.display = 'none';

        // Clear image previews
        const currentImagePreview = document.getElementById('currentImagePreview');
        const newImagePreview = document.getElementById('newImagePreview');
        const emptyPlaceholder = document.getElementById('emptyImagePlaceholder');
        const currentImageThumb = document.getElementById('currentImageThumb');
        const newImageThumb = document.getElementById('newImageThumb');

        if (currentImagePreview) currentImagePreview.style.display = 'none';
        if (newImagePreview) newImagePreview.style.display = 'none';
        if (emptyPlaceholder) emptyPlaceholder.style.display = 'block';
        if (currentImageThumb) currentImageThumb.src = '';
        if (newImageThumb) newImageThumb.src = '';

        this.clearProductValidationErrors();
        this.updateCategoryWarning();
    }

    /**
     * Toggle purchase section
     */
    togglePurchaseSection() {
        const purchaseCheck = document.getElementById('createPurchaseCheck');
        const purchaseSection = document.getElementById('purchaseSection');

        const isChecked = purchaseCheck?.checked || false;

        if (purchaseSection) {
            purchaseSection.style.display = isChecked ? 'block' : 'none';
        }

        if (isChecked && !this.isEditMode) {
            this.loadProductDropdowns('purchase');
        }

        this.updateCategoryWarning();
    }

    /**
     * Toggle sales section
     */
    toggleSalesSection() {
        const salesCheck = document.getElementById('createSalesCheck');
        const salesSection = document.getElementById('salesSection');

        const isChecked = salesCheck?.checked || false;

        if (salesSection) {
            salesSection.style.display = isChecked ? 'block' : 'none';
        }

        if (isChecked && !this.isEditMode) {
            this.loadProductDropdowns('sales');
        }

        this.updateCategoryWarning();
    }

    /**
     * Update category warning
     */
    updateCategoryWarning() {
        const purchaseCheck = document.getElementById('createPurchaseCheck');
        const salesCheck = document.getElementById('createSalesCheck');
        const warning = document.getElementById('noCategoryWarning');

        const purchaseChecked = purchaseCheck?.checked || false;
        const salesChecked = salesCheck?.checked || false;

        if (warning) {
            warning.style.display = (!purchaseChecked && !salesChecked) ? 'block' : 'none';
        }
    }

    /**
     * Load dropdowns for product modal
     */
    loadProductDropdowns(category) {
        this.loadLedgerRefsForProduct(category);
        this.loadVatTypesForProduct(category);
    }

    /**
     * Load ledger refs for product
     */
    loadLedgerRefsForProduct(prefix) {
        const ledgerSelect = document.getElementById(`${prefix}LedgerRef`);
        if (!ledgerSelect) return;

        ledgerSelect.innerHTML = '<option value="">Loading...</option>';

        // Check if dataLoader exists and has the method
        if (window.dataLoader && typeof window.dataLoader.loadLedgerRefs === 'function') {
            window.dataLoader.loadLedgerRefs(() => {
                const ledgerData = window.dataLoader.getLedgerRefsData();
                ledgerSelect.innerHTML = '<option value="">Select Ledger</option>';

                if (ledgerData && ledgerData.length > 0) {
                    ledgerData.forEach(ledger => {
                        const option = document.createElement('option');
                        option.value = ledger.id;
                        option.dataset.ledgerRef = ledger.ledger_ref;
                        option.textContent = ledger.ledger_ref;
                        ledgerSelect.appendChild(option);
                    });
                }
            });
        } else {
            // Fallback API call
            fetch('/api/ledger-refs-dropdown', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
                .then(response => response.json())
                .then(data => {
                    ledgerSelect.innerHTML = '<option value="">Select Ledger</option>';

                    if (data.success && data.ledger_refs) {
                        data.ledger_refs.forEach(ledger => {
                            const option = document.createElement('option');
                            option.value = ledger.id;
                            option.dataset.ledgerRef = ledger.ledger_ref;
                            option.textContent = ledger.ledger_ref;
                            ledgerSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Failed to load ledger refs:', error);
                    ledgerSelect.innerHTML = '<option value="">Error loading ledgers</option>';
                });
        }
    }

    /**
     * Load VAT types for product
     */
    loadVatTypesForProduct(prefix) {
        const vatSelect = document.getElementById(`${prefix}VatRate`);
        if (!vatSelect) return;

        const paymentType = prefix === 'purchase' ? 'purchase' : 'sales_invoice';
        vatSelect.innerHTML = '<option value="">Loading...</option>';

        if (window.vatManager && typeof window.vatManager.loadVatTypesByForm === 'function') {
            window.vatManager.loadVatTypesByForm(paymentType, function (vatTypes) {
                vatSelect.innerHTML = window.vatManager.createVatDropdownOptions(vatTypes);
            });
        } else {
            // Fallback - load basic VAT options
            vatSelect.innerHTML = `
                <option value="">Select VAT Rate</option>
                <option value="0" data-vat-id="5">0% (No VAT)</option>
                <option value="20" data-vat-id="1">20% (Standard)</option>
                <option value="5" data-vat-id="2">5% (Reduced)</option>
            `;
        }
    }

    /**
     * Setup ledger change handlers
     */
    setupProductLedgerHandlers() {
        const purchaseLedger = document.getElementById('purchaseLedgerRef');
        const salesLedger = document.getElementById('salesLedgerRef');

        if (purchaseLedger) {
            purchaseLedger.addEventListener('change', (e) => {
                const ledgerRef = e.target.options[e.target.selectedIndex]?.dataset.ledgerRef;
                if (ledgerRef) {
                    this.loadAccountRefsForProduct('purchase', ledgerRef);
                }
            });
        }

        if (salesLedger) {
            salesLedger.addEventListener('change', (e) => {
                const ledgerRef = e.target.options[e.target.selectedIndex]?.dataset.ledgerRef;
                if (ledgerRef) {
                    this.loadAccountRefsForProduct('sales', ledgerRef);
                }
            });
        }
    }

    /**
     * Load account refs for product
     */
    loadAccountRefsForProduct(prefix, ledgerRef) {
        const accountSelect = document.getElementById(`${prefix}AccountRef`);
        if (!accountSelect || !ledgerRef) return;

        accountSelect.innerHTML = '<option value="">Loading...</option>';
        accountSelect.disabled = true;

        if (window.dataLoader && typeof window.dataLoader.getAccountRefsByLedger === 'function') {
            window.dataLoader.getAccountRefsByLedger(ledgerRef, function (accountRefs) {
                accountSelect.innerHTML = '<option value="">Select Account</option>';
                accountSelect.disabled = false;

                if (accountRefs && accountRefs.length > 0) {
                    accountRefs.forEach(account => {
                        const option = document.createElement('option');
                        option.value = account.account_ref;
                        option.dataset.accountId = account.id;
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
            // Fallback API call
            fetch(`/api/account-refs-by-ledger?ledger_ref=${ledgerRef}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            })
                .then(response => response.json())
                .then(data => {
                    accountSelect.innerHTML = '<option value="">Select Account</option>';
                    accountSelect.disabled = false;

                    if (data.success && data.account_refs) {
                        data.account_refs.forEach(account => {
                            const option = document.createElement('option');
                            option.value = account.account_ref;
                            option.dataset.accountId = account.id;
                            option.textContent = account.account_ref;
                            if (account.description) {
                                option.textContent += ` (${account.description})`;
                            }
                            accountSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Failed to load account refs:', error);
                    accountSelect.innerHTML = '<option value="">Error loading accounts</option>';
                    accountSelect.disabled = false;
                });
        }
    }

    /**
 * Save product
 */
    saveProduct() {
        const form = document.getElementById('productForm');
        if (!form) return;

        this.clearProductValidationErrors();

        const formData = new FormData(form);
        const saveBtn = document.getElementById('saveProductBtn');

        // ✅ CRITICAL FIX: Extract VAT IDs from data-vat-id attributes
        const purchaseVatSelect = document.getElementById('purchaseVatRate');
        const salesVatSelect = document.getElementById('salesVatRate');

        // Remove the percentage values that were automatically added by FormData
        formData.delete('purchase_vat_rate_id');
        formData.delete('sales_vat_rate_id');

        // ✅ Extract and append the actual database IDs from data-vat-id
        if (purchaseVatSelect && purchaseVatSelect.value !== '') {
            const selectedPurchaseOption = purchaseVatSelect.options[purchaseVatSelect.selectedIndex];
            const purchaseVatId = selectedPurchaseOption ? selectedPurchaseOption.dataset.vatId : null;

            if (purchaseVatId) {
                formData.append('purchase_vat_rate_id', purchaseVatId);
                console.log('✅ Purchase VAT - Percentage:', purchaseVatSelect.value + '%, Database ID:', purchaseVatId);
            }
        }

        if (salesVatSelect && salesVatSelect.value !== '') {
            const selectedSalesOption = salesVatSelect.options[salesVatSelect.selectedIndex];
            const salesVatId = selectedSalesOption ? selectedSalesOption.dataset.vatId : null;

            if (salesVatId) {
                formData.append('sales_vat_rate_id', salesVatId);
                console.log('✅ Sales VAT - Percentage:', salesVatSelect.value + '%, Database ID:', salesVatId);
            }
        }

        // ✅ DEBUG: Log all form data being sent
        console.log('📤 Form data being sent:');
        for (let [key, value] of formData.entries()) {
            console.log(`  ${key}:`, value);
        }

        // Disable button
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
        }

        // Determine URL
        let url = '/products/store';
        if (this.isEditMode && this.editProductId) {
            url = `/products/${this.editProductId}`;
            formData.append('_method', 'PUT');
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('✅ Product saved:', data);

                    // ✅ CRITICAL: Update table BEFORE showing alert
                    if (this.isIndexPage) {
                        // We're on the products index page
                        if (this.isEditMode) {
                            // UPDATE existing row
                            if (data.product) {
                                if (typeof window.updateProductRowInTable === 'function') {
                                    window.updateProductRowInTable(data.product);
                                }
                            }
                        } else {
                            // ADD new row(s)
                            if (data.products && Array.isArray(data.products)) {
                                // Multiple products (purchase + sales)
                                data.products.forEach(productData => {
                                    if (productData.product && typeof window.addProductRowToTable === 'function') {
                                        window.addProductRowToTable(productData.product);
                                    }
                                });
                            } else if (data.product && typeof window.addProductRowToTable === 'function') {
                                // Single product
                                window.addProductRowToTable(data.product);
                            }
                        }

                        // Close modal and show success
                        if (this.productModalInstance) {
                            this.productModalInstance.hide();
                        }
                        alert(this.isEditMode ? 'Product updated successfully!' : 'Product(s) created successfully!');

                    } else {
                        // We're on the transaction form - use existing auto-fill logic
                        if (data.products && data.products.length > 0) {
                            this.autoFillInvoiceRow(data.products);
                        }

                        if (this.productModalInstance) {
                            this.productModalInstance.hide();
                        }
                        alert(this.isEditMode ? 'Product updated successfully!' : 'Product(s) created successfully!');

                        // Reload to refresh dropdown
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    }
                } else {
                    if (data.errors) {
                        console.error('❌ Validation errors:', data.errors);
                        this.displayProductValidationErrors(data.errors);
                    } else {
                        alert(data.message || 'Failed to save product');
                    }
                }
            })
            .catch(error => {
                console.error('Error saving product:', error);
                alert('An error occurred while saving the product');
            })
            .finally(() => {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = this.isEditMode ?
                        '<i class="fas fa-save me-1"></i>Update Product' :
                        '<i class="fas fa-save me-1"></i>Save Product';
                }
            });
    }

    /**
     * Display validation errors
     */
    displayProductValidationErrors(errors) {
        Object.keys(errors).forEach(fieldName => {
            const input = document.querySelector(`[name="${fieldName}"]`);
            if (input) {
                input.classList.add('is-invalid');
                const feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.textContent = errors[fieldName][0];
                }
            }
        });
    }

    /**
     * Clear validation errors
     */
    clearProductValidationErrors() {
        document.querySelectorAll('#productForm .is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('#productForm .invalid-feedback').forEach(el => {
            el.textContent = '';
        });
    }

    /**
     * Auto-fill invoice row with newly created product
     * Only works on transaction form page
     */
    autoFillInvoiceRow(products) {
        // Skip if on index page
        if (this.isIndexPage) {
            console.log('⚠️ Auto-fill skipped - not on transaction form');
            return;
        }

        // Check if window.formManager exists
        if (!window.formManager || typeof window.formManager.getCurrentPaymentType !== 'function') {
            console.log('⚠️ Auto-fill skipped - formManager not available');
            return;
        }

        const currentPaymentType = window.formManager.getCurrentPaymentType();
        let productToFill = null;

        if (currentPaymentType === 'purchase' || currentPaymentType === 'purchase_credit') {
            productToFill = products.find(p => p.category === 'purchase')?.product;
        } else if (currentPaymentType === 'sales_invoice' || currentPaymentType === 'sales_credit') {
            productToFill = products.find(p => p.category === 'sales')?.product;
        }

        if (!productToFill) {
            productToFill = products[0]?.product;
        }

        if (productToFill && this.currentProductRow) {
            console.log('✅ Auto-filling newly created product:', productToFill.item_code);

            // Add to loaded products cache
            const existingIndex = this.loadedProducts.findIndex(p => p.id === productToFill.id);
            if (existingIndex >= 0) {
                this.loadedProducts[existingIndex] = productToFill;
            } else {
                this.loadedProducts.push(productToFill);
            }

            // Get row index
            const itemCodeInput = this.currentProductRow.querySelector('.item-code-input');
            const rowIndex = itemCodeInput?.dataset.row;

            if (rowIndex) {
                this.selectProduct(productToFill.id, rowIndex);
                this.refreshProductDropdown(productToFill.category);
            }
        }

        window.lastCreatedProduct = productToFill;
    }

    // ========================================
    // PRODUCT DROPDOWN FUNCTIONALITY
    // ========================================

    /**
     * Initialize product dropdowns
     */
    initializeDropdowns() {
        // Only initialize dropdowns if NOT on index page
        if (this.isIndexPage) {
            console.log('⚠️ Product dropdowns disabled on index page');
            return;
        }

        console.log('✅ Initializing product dropdowns');

        // Delegate click event
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('item-code-input')) {
                e.stopPropagation();
                const rowIndex = e.target.dataset.row;
                this.currentProductRow = e.target.closest('tr');
                this.showProductDropdown(e.target, rowIndex);
            }
        });
    }

    /**
     * Bind global events for dropdowns
     */
    bindGlobalEvents() {
        // Only bind dropdown events if NOT on index page
        if (this.isIndexPage) {
            return;
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.classList.contains('item-code-input') &&
                !e.target.closest('.product-dropdown') &&
                !e.target.closest('.add-product-btn')) {
                this.closeAllProductDropdowns();
            }
        });

        // ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllProductDropdowns();
            }
        });

        // Reposition on scroll
        window.addEventListener('scroll', () => {
            if (this.activeProductDropdown && this.activeProductDropdown.style.display === 'block') {
                const inputElement = document.querySelector('.item-code-input:focus') ||
                    this.currentProductRow?.querySelector('.item-code-input');
                if (inputElement) {
                    this.positionDropdown(this.activeProductDropdown, inputElement);
                }
            }
        }, true);

        // Reposition on resize
        window.addEventListener('resize', () => {
            if (this.activeProductDropdown && this.activeProductDropdown.style.display === 'block') {
                const inputElement = document.querySelector('.item-code-input:focus') ||
                    this.currentProductRow?.querySelector('.item-code-input');
                if (inputElement) {
                    this.positionDropdown(this.activeProductDropdown, inputElement);
                }
            }
        });
    }

    /**
     * Show product dropdown
     */
    showProductDropdown(inputElement, rowIndex) {
        let dropdown = document.getElementById(`productDropdown_${rowIndex}`);

        if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.id = `productDropdown_${rowIndex}`;
            dropdown.className = 'product-dropdown';
            document.body.appendChild(dropdown);
        }

        this.closeAllProductDropdowns();

        // Use the new method to determine category
        const category = this.determineCategory();

        if (!category) {
            console.warn('Cannot determine product category');
            dropdown.innerHTML = `
                <div class="product-dropdown-header">
                    <div class="text-center text-muted py-2">
                        <small>Product dropdown not available</small>
                    </div>
                </div>
            `;
            dropdown.style.display = 'block';
            this.positionDropdown(dropdown, inputElement);
            return;
        }

        dropdown.innerHTML = `
            <div class="product-dropdown-header">
                <button type="button" class="add-product-btn" onclick="window.productModal.openProductModalForCreate('${category}')">
                    <i class="fas fa-plus me-1"></i> Add Product
                </button>
            </div>
            <div class="product-dropdown-list">
                <div class="product-dropdown-item no-products">
                    <i class="fas fa-spinner fa-spin me-2"></i>Loading products...
                </div>
            </div>
        `;

        dropdown.style.display = 'block';
        this.positionDropdown(dropdown, inputElement);
        this.activeProductDropdown = dropdown;

        this.loadProductsForDropdown(category, (products) => {
            this.renderProductDropdown(dropdown, products, category, rowIndex);
            this.positionDropdown(dropdown, inputElement);
        });
    }

    /**
     * Position dropdown
     */
    positionDropdown(dropdown, inputElement) {
        const rect = inputElement.getBoundingClientRect();
        dropdown.style.top = `${rect.bottom + 2}px`;
        dropdown.style.left = `${rect.left}px`;
        dropdown.style.minWidth = `${Math.max(rect.width, 150)}px`;
    }

    /**
     * Load products for dropdown
     */
    loadProductsForDropdown(category, callback) {
        fetch(`/products/dropdown?category=${category}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.products) {
                    this.loadedProducts = data.products;
                    callback(data.products);
                } else {
                    console.warn('No products returned from server');
                    this.loadedProducts = [];
                    callback([]);
                }
            })
            .catch(error => {
                console.error('Failed to load products:', error);
                this.loadedProducts = [];
                callback([]);
            });
    }

    /**
     * Render product dropdown
     */
    renderProductDropdown(dropdown, products, category, rowIndex) {
        let html = `
            <div class="product-dropdown-header">
                <button type="button" class="add-product-btn bg-teal" onclick="window.productModal.openProductModalForCreate('${category}')">
                    <i class="fas fa-plus me-1"></i> Add Product
                </button>
            </div>
            <div class="product-dropdown-list">
        `;

        if (products.length === 0) {
            html += `
                <div class="product-dropdown-item no-products">
                    <i class="fas fa-inbox me-2"></i>No products found. Click "+ Add Product" to create one.
                </div>
            `;
        } else {
            products.forEach(product => {
                const displayAmount = product.unit_amount ? `£${parseFloat(product.unit_amount).toFixed(2)}` : '';
                const displayDesc = product.description ? product.description : '<em>No description</em>';

                html += `
                    <div class="product-dropdown-item" onclick="window.productModal.selectProduct(${product.id}, ${rowIndex})">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="product-item-code">${product.item_code}</div>
                                <div class="product-item-desc">${displayDesc}</div>
                            </div>
                            ${displayAmount ? `<div class="product-item-amount ms-2">${displayAmount}</div>` : ''}
                        </div>
                    </div>
                `;
            });
        }

        html += `</div>`;
        dropdown.innerHTML = html;
    }

    /**
     * Select a product (only works on transaction form)
     */
    selectProduct(productId, rowIndex) {
        // Check if we're on transaction form
        if (this.isIndexPage) {
            console.warn('⚠️ Product selection not available on index page');
            return;
        }

        console.log('✅ Selecting product:', productId, 'for row:', rowIndex);

        const product = this.loadedProducts.find(p => p.id === productId);
        if (!product) {
            console.error('Product not found in cache:', productId);
            return;
        }

        const row = document.querySelector(`tr[data-item-id="${rowIndex}"]`);
        if (!row) {
            console.error('Row not found:', rowIndex);
            return;
        }

        // ✅ SET THE FLAG - disable account change handler
        row.dataset.isAutoFilling = 'true';
        console.log('🔒 Auto-fill mode enabled for row', rowIndex);

        // Fill item code
        const itemCodeInput = row.querySelector('.item-code-input');
        if (itemCodeInput) {
            itemCodeInput.value = product.item_code;
        }

        // Fill description
        const descInput = row.querySelector('input[name*="[description]"]');
        if (descInput) {
            descInput.value = product.description || '';
        }

        // Fill unit amount
        const unitAmountInput = row.querySelector('.unit-amount');
        if (unitAmountInput) {
            unitAmountInput.value = parseFloat(product.unit_amount || 0).toFixed(2);
        }

        // ✅ Fill VAT FIRST (before account)
        // ✅ Fill VAT by matching vat_type_id (master ID)
        const vatSelect = row.querySelector('.vat-rate');
        if (vatSelect && product.vat_type_id) { // ✅ Use vat_type_id from API
            console.log('🔍 Searching for VAT match:', {
                product_vat_type_id: product.vat_type_id,
                product_vat_rate_id: product.vat_rate_id,
                available_options: Array.from(vatSelect.options).map(opt => ({
                    percentage: opt.value,
                    vat_id: opt.dataset.vatId,
                    vat_type_id: opt.dataset.vatTypeId
                }))
            });

            // ✅ Match by vat_type_id (works across all forms)
            const vatOption = Array.from(vatSelect.options).find(opt =>
                opt.dataset.vatTypeId == product.vat_type_id
            );

            if (vatOption) {
                vatSelect.value = vatOption.value; // Set percentage
                const vatIdField = row.querySelector('.item-vat-id');
                if (vatIdField) {
                    vatIdField.value = vatOption.dataset.vatId; // Set form-specific ID for submission
                }
                console.log('✅ Auto-filled VAT:', {
                    matched_by: 'vat_type_id',
                    vat_type_id: product.vat_type_id,
                    percentage: vatOption.value + '%',
                    form_specific_vat_id: vatOption.dataset.vatId
                });
            } else {
                console.error('❌ No matching VAT option found', {
                    looking_for_vat_type_id: product.vat_type_id,
                    available_vat_type_ids: Array.from(vatSelect.options)
                        .map(opt => opt.dataset.vatTypeId)
                        .filter(Boolean)
                });
            }
        }

        // Fill ledger and account
        const ledgerSelect = row.querySelector('.ledger-select');
        if (ledgerSelect && product.ledger_id) {
            ledgerSelect.value = product.ledger_id;

            const selectedLedgerOption = ledgerSelect.options[ledgerSelect.selectedIndex];
            const ledgerRef = selectedLedgerOption ? selectedLedgerOption.dataset.ledgerRef : null;

            if (ledgerRef && window.dataLoader) {
                const accountSelect = row.querySelector('.account-select');
                accountSelect.innerHTML = '<option value="">Loading...</option>';
                accountSelect.disabled = true;

                window.dataLoader.getAccountRefsByLedger(ledgerRef, (accountRefs) => {
                    accountSelect.innerHTML = '<option value="">Select Account</option>';
                    accountSelect.disabled = false;

                    if (accountRefs && accountRefs.length > 0) {
                        accountRefs.forEach(account => {
                            const option = document.createElement('option');
                            option.value = account.account_ref;
                            option.dataset.accountRef = account.account_ref;
                            option.dataset.vatId = account.vat_id || '';
                            option.textContent = account.account_ref;
                            if (account.description) {
                                option.textContent += ` (${account.description})`;
                            }
                            accountSelect.appendChild(option);
                        });

                        if (product.account_ref) {
                            const accountOption = Array.from(accountSelect.options).find(opt =>
                                opt.value === product.account_ref
                            );
                            if (accountOption) {
                                accountSelect.value = product.account_ref;
                                console.log('✅ Auto-filled account:', product.account_ref);

                                // ✅ REMOVED: Don't call autoFillVatForRow - VAT is already set above
                                // const vatIdFromAccount = accountOption.dataset.vatId;
                                // if (vatIdFromAccount) {
                                //     this.autoFillVatForRow(row, vatIdFromAccount);
                                // }
                            }
                        }
                    }

                    // ✅ IMPORTANT: Re-enable account change handler AFTER auto-fill completes
                    setTimeout(() => {
                        row.dataset.isAutoFilling = 'false';
                        console.log('🔓 Auto-fill mode disabled for row', rowIndex);
                    }, 100);
                });
            }
        } else {
            // ✅ If no ledger, re-enable immediately
            setTimeout(() => {
                row.dataset.isAutoFilling = 'false';
                console.log('🔓 Auto-fill mode disabled for row', rowIndex);
            }, 100);
        }

        // Fill product image
        const imagePreviewContainer = row.querySelector('.item-image-preview');
        const imageUrlField = row.querySelector('.item-image-url');

        if (imagePreviewContainer && product.file_url) {
            imagePreviewContainer.innerHTML = `
            <img src="${product.file_url}" 
                alt="${product.item_code}" 
                class="product-thumbnail"
                onclick="showFullImageModal('${product.file_url}')"
                title="Click to view full size">
        `;

            if (imageUrlField) {
                imageUrlField.value = product.file_url;
            }

            console.log('✅ Product image loaded:', product.file_url);
        } else if (imagePreviewContainer) {
            imagePreviewContainer.innerHTML = `
            <div class="no-image-placeholder">
                <i class="fas fa-image text-muted"></i>
            </div>
        `;

            if (imageUrlField) {
                imageUrlField.value = '';
            }
        }

        // Trigger calculation
        setTimeout(() => {
            const unitInput = row.querySelector('.unit-amount');
            if (unitInput) {
                const inputEvent = new Event('input', { bubbles: true });
                unitInput.dispatchEvent(inputEvent);
                console.log('✅ Triggered calculation for row');
            }
        }, 200);

        this.closeAllProductDropdowns();

        console.log('✅ Product auto-filled successfully:', product.item_code);
    }

    /**
     * Auto-fill VAT for row
     */
    autoFillVatForRow(row, vatId) {
        const vatSelect = row.querySelector('.vat-rate');
        if (!vatSelect) return;

        if (vatId === '5') {
            const noVatOption = Array.from(vatSelect.options).find(opt =>
                opt.dataset.vatId === '5' || opt.value === '0'
            );

            if (noVatOption) {
                vatSelect.value = noVatOption.value;
                vatSelect.disabled = true;
                vatSelect.style.backgroundColor = '#e9ecef';
                vatSelect.style.cursor = 'not-allowed';
                vatSelect.title = 'VAT rate is fixed for this account';

                const vatIdField = row.querySelector('.item-vat-id');
                if (vatIdField && noVatOption.dataset.vatId) {
                    vatIdField.value = noVatOption.dataset.vatId;
                }
            }
        } else {
            vatSelect.disabled = false;
            vatSelect.style.backgroundColor = '';
            vatSelect.style.cursor = '';
            vatSelect.title = '';

            const matchingOption = Array.from(vatSelect.options).find(opt =>
                opt.dataset.vatId === vatId
            );

            if (matchingOption) {
                vatSelect.value = matchingOption.value;
                const vatIdField = row.querySelector('.item-vat-id');
                if (vatIdField) {
                    vatIdField.value = matchingOption.dataset.vatId;
                }
                console.log('✅ Auto-selected VAT for product:', matchingOption.value + '%');
            }
        }
    }

    /**
     * Close all product dropdowns
     */
    closeAllProductDropdowns() {
        document.querySelectorAll('.product-dropdown').forEach(dropdown => {
            dropdown.style.display = 'none';
        });
        this.activeProductDropdown = null;
        this.currentProductRow = null;
    }

    /**
     * Refresh product dropdown
     */
    refreshProductDropdown(category) {
        if (this.activeProductDropdown && this.currentProductRow) {
            const itemCodeInput = this.currentProductRow.querySelector('.item-code-input');
            const rowIndex = itemCodeInput?.dataset.row;

            if (rowIndex) {
                this.loadProductsForDropdown(category, (products) => {
                    this.renderProductDropdown(this.activeProductDropdown, products, category, rowIndex);
                });
            }
        }
    }
}

// Initialize global instance
window.productModal = new ProductModalManager();

// Expose global functions for backwards compatibility
window.openProductModalForCreate = (category) => window.productModal.openProductModalForCreate(category);
window.openProductModalForEdit = (productId) => window.productModal.openProductModalForEdit(productId);