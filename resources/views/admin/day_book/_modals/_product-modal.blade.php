{{-- ========================================================================
     PRODUCT MODAL (Reusable for Create & Edit)
     ======================================================================== --}}

<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            {{-- Modal Header --}}
            <div class="modal-header bg-teal">
                <h5 class="modal-title text-white" id="productModalLabel">
                    <i class="fas fa-box me-2"></i>Add New Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body">
                <form id="productForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="productEditId" value="">

                    {{-- ========================================
                         COMMON FIELDS SECTION (ALWAYS VISIBLE)
                         ======================================== --}}
                    {{-- ========================================
     COMMON FIELDS SECTION (ALWAYS VISIBLE)
     ======================================== --}}
                    <div class="mb-4 p-3 bg-light border rounded">
                        <div class="row">
                            {{-- Item Code --}}
                            <div class="col-md-3">
                                <label for="commonItemCode" class="fw-bold small">
                                    Item Code <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-sm" id="commonItemCode"
                                    name="item_code" placeholder="e.g., ITEM-001" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- Item Name --}}
                            <div class="col-md-3">
                                <label for="commonItemName" class="fw-bold small">
                                    Item Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-sm" id="commonItemName"
                                    name="name" placeholder="e.g., Laptop Computer" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- Item Image with Preview on Right --}}
                            <div class="col-md-6">
                                <label for="commonItemImage" class="fw-bold small">
                                    Item Image (Optional)
                                </label>
                                <div class="d-flex align-items-start gap-2">
                                    {{-- File Input Section --}}
                                    <div class="flex-grow-1">
                                        <input type="file" class="form-control form-control-sm" id="commonItemImage"
                                            name="item_image" accept=".jpg,.jpeg,.png,.gif,.webp">
                                        <div class="form-text small">
                                            Max 5MB. Allowed: JPG, PNG, GIF, WEBP
                                        </div>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    {{-- Preview Box (Always Visible) --}}
                                    <div class="preview-box" style="min-width: 120px; width: 120px;">
                                        {{-- New Image Preview --}}
                                        <div id="newImagePreview" style="display: none;">
                                            <div class="position-relative">
                                                <img id="newImageThumb" src="" class="img-thumbnail"
                                                    style="width: 120px; height: 120px; object-fit: cover; border: 2px solid #17a2b8; cursor: pointer;"
                                                    onclick="showFullImageModal(this.src)"
                                                    title="Click to view full size">
                                                <button type="button" class="btn btn-sm btn-danger position-absolute"
                                                    style="top: 2px; right: 2px; width: 22px; height: 22px; padding: 0; border-radius: 50%; font-size: 10px;"
                                                    onclick="clearImagePreview()" title="Remove image">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <small class="text-muted d-block text-center mt-1"
                                                    style="font-size: 10px;">Preview</small>
                                            </div>
                                        </div>

                                        {{-- Current Image Preview (Edit Mode) --}}
                                        <div id="currentImagePreview" style="display: none;">
                                            <div class="position-relative">
                                                <img id="currentImageThumb" src="" class="img-thumbnail"
                                                    style="width: 120px; height: 120px; object-fit: cover; border: 2px solid #28a745; cursor: pointer;"
                                                    onclick="showFullImageModal(this.src)"
                                                    title="Click to view full size">
                                                <small class="text-success d-block text-center mt-1"
                                                    style="font-size: 10px;">
                                                    <i class="fas fa-check-circle"></i> Current
                                                </small>
                                            </div>
                                        </div>

                                        {{-- Empty Placeholder --}}
                                        <div id="emptyImagePlaceholder">
                                            <div class="border rounded d-flex align-items-center justify-content-center"
                                                style="width: 120px; height: 120px; background-color: #f8f9fa;">
                                                <div class="text-center text-muted">
                                                    <i class="fas fa-image fa-2x mb-2" style="opacity: 0.3;"></i>
                                                    <div style="font-size: 10px;">No image</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================
                         PURCHASE PRODUCT SECTION
                         ======================================== --}}
                    <div class="mb-3">
                        {{-- Purchase Checkbox --}}
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="createPurchaseCheck"
                                name="create_purchase">
                            <label class="form-check-label fw-bold" for="createPurchaseCheck">
                                <i class="fas fa-shopping-cart text-dark me-1"></i>
                                Purchase
                            </label>
                        </div>

                        {{-- Purchase Form --}}
                        <div id="purchaseSection" class="product-section" style="display: none;">
                            <div class="p-3 bg-light border rounded">
                                <div class="row">
                                    {{-- Description --}}
                                    <div class="col-md-4">
                                        <label for="purchaseDescription" class="fw-bold small">
                                            Description <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="purchaseDescription" name="purchase_description"
                                            placeholder="item description">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    {{-- Ledger Ref --}}
                                    <div class="col-md-2">
                                        <label for="purchaseLedgerRef" class="fw-bold small">
                                            Ledger Ref <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select form-select-sm" id="purchaseLedgerRef"
                                            name="purchase_ledger_id">
                                            <option value="">Select Ledger</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    {{-- Account Ref --}}
                                    <div class="col-md-2">
                                        <label for="purchaseAccountRef" class="fw-bold small">
                                            Account Ref
                                        </label>
                                        <select class="form-select form-select-sm" id="purchaseAccountRef"
                                            name="purchase_account_ref">
                                            <option value="">Select Account</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    {{-- Unit Amount --}}
                                    <div class="col-md-2">
                                        <label for="purchaseUnitAmount" class="fw-bold small">
                                            Amount <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control form-control-sm"
                                            id="purchaseUnitAmount" name="purchase_unit_amount" step="0.01"
                                            min="0" placeholder="0.00">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    {{-- VAT Rate --}}
                                    <div class="col-md-2">
                                        <label for="purchaseVatRate" class="fw-bold small">
                                            VAT Rate
                                        </label>
                                        <select class="form-select form-select-sm" id="purchaseVatRate"
                                            name="purchase_vat_rate_id">
                                            <option value="">Select VAT</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================
                         SALES PRODUCT SECTION
                         ======================================== --}}
                    <div class="mb-3">
                        {{-- Sales Checkbox --}}
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="createSalesCheck"
                                name="create_sales">
                            <label class="form-check-label fw-bold" for="createSalesCheck">
                                <i class="fas fa-shopping-bag text-dark me-1"></i>
                                Sales
                            </label>
                        </div>

                        {{-- Sales Form --}}
                        <div id="salesSection" class="product-section" style="display: none;">
                            <div class="p-3 bg-light border rounded">
                                <div class="row">
                                    {{-- Description --}}
                                    <div class="col-md-4">
                                        <label for="salesDescription" class="fw-bold small">
                                            Description <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-sm"
                                            id="salesDescription" name="sales_description"
                                            placeholder="item description">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    {{-- Ledger Ref --}}
                                    <div class="col-md-2">
                                        <label for="salesLedgerRef" class="fw-bold small">
                                            Ledger Ref <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select form-select-sm" id="salesLedgerRef"
                                            name="sales_ledger_id">
                                            <option value="">Select Ledger</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    {{-- Account Ref --}}
                                    <div class="col-md-2">
                                        <label for="salesAccountRef" class="fw-bold small">
                                            Account Ref
                                        </label>
                                        <select class="form-select form-select-sm" id="salesAccountRef"
                                            name="sales_account_ref">
                                            <option value="">Select Account</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    {{-- Unit Amount --}}
                                    <div class="col-md-2">
                                        <label for="salesUnitAmount" class="fw-bold small">
                                            Amount <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control form-control-sm"
                                            id="salesUnitAmount" name="sales_unit_amount" step="0.01"
                                            min="0" placeholder="0.00">
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    {{-- VAT Rate --}}
                                    <div class="col-md-2">
                                        <label for="salesVatRate" class="fw-bold small">
                                            VAT Rate
                                        </label>
                                        <select class="form-select form-select-sm" id="salesVatRate"
                                            name="sales_vat_rate_id">
                                            <option value="">Select VAT</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- No Category Selected Warning --}}
                    <div id="noCategoryWarning" class="alert alert-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Please select at least one category</strong> (Purchase or Sales) to add a product.
                    </div>

                </form>
            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="teal-custom-btn p-1" id="saveProductBtn">
                    <i class="fas fa-save me-1"></i>Save Product
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ========================================================================
     FULL-SIZE IMAGE PREVIEW MODAL
     ======================================================================== --}}
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white" id="imagePreviewModalLabel">
                    <i class="fas fa-image me-2"></i>Image Preview
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="fullSizeImage" src="" class="img-fluid"
                    style="max-height: 70vh; border-radius: 8px;">
            </div>
        </div>
    </div>
</div>

{{-- ========================================
     MODAL STYLES
     ======================================================================== --}}
<style>
    /* Modal sizing */
    #productModal .modal-dialog.modal-xl {
        max-width: 1000px !important;
        width: 95% !important;
        margin: 1.75rem auto !important;
    }

    #productModal .modal-content {
        width: 100% !important;
        max-width: none !important;
        padding: 0 !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2) !important;
        background: white !important;
    }

    #productModal.modal.show {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #productModal .modal-dialog {
        display: flex;
        align-items: center;
        min-height: calc(100% - 3.5rem);
    }

    /* Transitions */
    #productModal .product-section {
        transition: all 0.3s ease-in-out;
    }

    /* Validation styles */
    #productModal .invalid-feedback {
        display: block;
    }

    #productModal .form-control.is-invalid,
    #productModal .form-select.is-invalid {
        border-color: #dc3545;
    }

    /* Alert styles */
    #productModal .alert {
        border-left: 4px solid;
    }

    #productModal .alert-warning {
        border-left-color: #ffc107;
    }

    /* Padding */
    #productModal .modal-header {
        padding: 1rem 1.5rem !important;
    }

    #productModal .modal-body {
        padding: 1.5rem !important;
    }

    #productModal .modal-footer {
        padding: 1rem 1.5rem !important;
    }

    /* ========================================
       IMAGE PREVIEW STYLES
       ======================================== */
    #newImagePreview .position-relative:hover,
    #currentImagePreview .position-relative:hover {
        opacity: 0.9;
    }

    #newImagePreview img:hover,
    #currentImagePreview img:hover {
        transform: scale(1.05);
        transition: transform 0.2s ease;
    }

    #newImagePreview .btn-danger {
        width: 24px;
        height: 24px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 10px;
        opacity: 0.8;
    }

    #newImagePreview .btn-danger:hover {
        opacity: 1;
    }

    /* Full-size image modal */
    #imagePreviewModal .modal-content {
        background-color: rgba(0, 0, 0, 0.95) !important;
    }

    #imagePreviewModal img {
        box-shadow: 0 4px 20px rgba(255, 255, 255, 0.1);
    }

    /* Image border animations */
    #newImageThumb {
        animation: borderPulseBlue 2s infinite;
    }

    #currentImageThumb {
        animation: borderPulseGreen 2s infinite;
    }

    @keyframes borderPulseBlue {

        0%,
        100% {
            border-color: #17a2b8;
        }

        50% {
            border-color: #0d6efd;
        }
    }

    @keyframes borderPulseGreen {

        0%,
        100% {
            border-color: #28a745;
        }

        50% {
            border-color: #20c997;
        }
    }
</style>

