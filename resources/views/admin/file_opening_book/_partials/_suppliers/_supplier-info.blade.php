{{-- resources/views/admin/file_opening_book/_partials/_suppliers/_supplier-info.blade.php --}}

<div class="client-info-section" id="supplier-info" style="display: none;">
    <div class="align-items-center mb-3">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <div class="d-flex align-items-center">
                    <h5 class="mb-0" id="supplier-name">-</h5>
                    <span class="mx-2">-</span>
                    <h5 class="mb-0" id="supplier-ref-badge" style="font-weight: 500;">-</h5>
                </div>

                <div>
                    <small class="me-3" id="supplier-address">-</small>
                    <small class="" id="supplier-contact">-</small>
                </div>

                <div>
                    <small class="me-3" id="supplier-email">-</small>
                </div>
            </div>

            <!-- ✅ FIXED: Buttons positioned to the right -->
            <div>
                <!-- View Mode Buttons -->
                <div id="supplier-view-actions" style="display: none;">
                    <button type="button" class="btn btn-sm btn-primary" id="edit-supplier-btn">
                        <i class="fa-solid fa-edit me-1"></i> Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" id="delete-supplier-btn">
                        <i class="fa-solid fa-trash me-1"></i> Delete
                    </button>
                </div>

                <!-- Edit Mode Buttons -->
                <div id="supplier-edit-actions" style="display: none;">
                    <button type="button" class="btn btn-sm btn-success" id="save-supplier-btn">
                        <i class="fa-solid fa-save me-1"></i> Save
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" id="cancel-supplier-edit-btn">
                        <i class="fa-solid fa-times me-1"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ FIXED Navigation Tabs -->
    <ul class="nav nav-tabs supplier-main-tabs border-0 mb-3">
        <li class="nav-item">
            <a class="nav-link supplier-tab-link active" 
               href="#" 
               data-tab="transactions">
                Transactions
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link supplier-tab-link" 
               href="#" 
               data-tab="details">
                Supplier Details
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link supplier-tab-link" 
               href="#" 
               data-tab="documents">
                Document Files
            </a>
        </li>
    </ul>

    <!-- Date Range -->
    <div id="supplier-date-range">
        @if(View::exists('admin.file_opening_book._components._date-range-picker'))
            @include('admin.file_opening_book._components._date-range-picker')
        @endif
    </div>
</div>

<style>
/* ✅ Supplier Tab Styling */
.supplier-main-tabs .nav-link {
    border: none !important;
    border-bottom: 2px solid transparent !important;
    color: #6c757d !important;
    padding: 0.5rem 1rem;
    background: transparent !important;
}

.supplier-main-tabs .nav-link.active {
    border-bottom: 2px solid #13667d !important;
    color: #13667d !important;
    font-weight: 500;
}

.supplier-main-tabs .nav-link:hover {
    color: #13667d !important;
}
</style>