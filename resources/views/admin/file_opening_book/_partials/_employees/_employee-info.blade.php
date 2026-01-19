{{-- Employee Information Section --}}
<div class="client-info-section" id="employee-info" style="display: none;">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <!-- Left: Employee Info -->
        <div>
            <!-- Row 1: Name -->
            <div class="d-flex align-items-center mb-2">
                <h5 class="mb-0" id="employee-name">-</h5>
                <span class="mx-2">-</span>
                <h5 class="mb-0" id="employee-ref-badge" style="font-weight: 500;">-</h5>
            </div>

            <!-- Row 2: Position and Contact -->
            <div class="mb-1">
                <small class="me-3" id="employee-position">-</small>
                <small class="me-3" id="employee-contact">-</small>
            </div>

            <!-- Row 3: Email, DOB, and NI -->
            <div>
                <small class="me-3" id="employee-email">-</small>
                <small class="me-3" id="employee-dob">-</small>
                <small class="me-3" id="employee-ni">-</small>
            </div>
        </div>

        <!-- Right: Action Buttons -->
        <div>
            <!-- VIEW MODE BUTTONS (Edit & Delete) -->
            <div id="employee-view-actions" style="display: none;">
                <button type="button" class="btn btn-sm btn-primary me-2" id="edit-employee-btn">
                    <i class="fa-solid fa-edit me-1"></i> Edit
                </button>
                <button type="button" class="btn btn-sm btn-danger" id="delete-employee-btn">
                    <i class="fa-solid fa-trash me-1"></i> Delete
                </button>
            </div>

            <!-- EDIT MODE BUTTONS (Save & Cancel) -->
            <div id="employee-edit-actions" style="display: none;">
                <button type="button" class="btn btn-sm btn-success me-2" id="save-employee-btn">
                    <i class="fa-solid fa-save me-1"></i> Save Changes
                </button>
                <button type="button" class="btn btn-sm btn-secondary" id="cancel-edit-btn">
                    <i class="fa-solid fa-times me-1"></i> Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs border-0 mb-3 employee-main-tabs">
        <li class="nav-item">
            <a class="nav-link employee-tab-link active" href="#" data-tab="transactions">
                Transactions
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link employee-tab-link" href="#" data-tab="details">
                Employee Details
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link employee-tab-link" href="#" data-tab="documents">
                Document Files
            </a>
        </li>
    </ul>

    <!-- Date Range (only visible on Transactions tab) -->
    <div id="employee-date-range">
        @include('admin.file_opening_book._components._date-range-picker')
    </div>
</div>