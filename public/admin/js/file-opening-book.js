/* ========================================
   FILE OPENING BOOK JAVASCRIPT - UPDATED
   ======================================== */

class FileOpeningBook {
    constructor() {
        this.routes = {
            ledgerData: '/files/file-opening-book/ledger-data',
            supplierData: '/files/file-opening-book/supplier-data',
            employeeData: '/files/file-opening-book/employee-data'
        };
        this.init();
    }

    /**
     * Initialize all components
     */
    init() {
        this.setupDropdowns();
        this.setupNavigation();
        this.setupSearch();
        this.setupLedgerRows();
        this.setupSupplierRows();
        this.setupEmployeeRows();
        this.setupEmployeeTabs();
        this.setupEmployeeDetailTabs();
        this.setupEmployeeActions();

        this.setupSupplierTabs();
        this.setupSupplierDetailTabs();
        this.setupSupplierActions();

        this.setupMatterFilter();
        this.loadFirstLedger();
    }

    /**
     * Setup dropdown active states
     */
    setupDropdowns() {
        const $dropdowns = $('.split-dropdown-wrapper');

        $dropdowns.on('show.bs.dropdown', function () {
            $(this).addClass('active');
        });

        $dropdowns.on('hidden.bs.dropdown', function () {
            $(this).removeClass('active');
        });

        $('.split-dropdown-btn').on('click', function () {
            const $wrapper = $(this).closest('.split-dropdown-wrapper');
            setTimeout(function () {
                if ($wrapper.find('.dropdown-menu').hasClass('show')) {
                    $wrapper.addClass('active');
                } else {
                    $wrapper.removeClass('active');
                }
            }, 50);
        });
    }

    /**
     * Setup navigation between sections
     */
    setupNavigation() {
        $('.nav-link-btn').on('click', function (e) {
            e.preventDefault();

            $('.nav-link-btn').removeClass('active');
            $(this).addClass('active');

            const section = $(this).data('section');

            $('.content-section').hide();
            $(`#${section}-content`).show();

            // ✅ FIXED: Auto-load first item when switching tabs
            if (section === 'matters') {
                $('#matter-filters').removeClass('d-none').addClass('d-flex');
                setTimeout(function () {
                    new FileOpeningBook().loadFirstLedger();
                }, 100);
            } else {
                $('#matter-filters').removeClass('d-flex').addClass('d-none');
            }

            // ✅ Auto-load first supplier
            if (section === 'suppliers') {
                setTimeout(function () {
                    new FileOpeningBook().loadFirstSupplier();
                }, 100);
            }

            // ✅ Auto-load first employee
            if (section === 'employees') {
                setTimeout(function () {
                    new FileOpeningBook().loadFirstEmployee();
                }, 100);
            }

            $('.action-buttons-group').addClass('d-none').removeClass('d-flex');
            $(`#${section}-buttons`).removeClass('d-none').addClass('d-flex');

            if (section === 'suppliers' || section === 'employees') {
                $('.left-side').addClass('narrow');
                $('.filters-wrapper').css('width', '22%');
            } else {
                $('.left-side').removeClass('narrow');
                $('.filters-wrapper').css('width', '32%');
            }

            const placeholders = {
                'matters': 'Search ledger or name...',
                'suppliers': 'Search supplier...',
                'employees': 'Search employee...'
            };
            $('.search-input').attr('placeholder', placeholders[section] || 'Search...');
        });
    }

    /**
     * Setup search functionality
     */
    setupSearch() {
        let searchTimeout;

        $('.search-input').on('keyup', function () {
            clearTimeout(searchTimeout);
            const searchValue = $(this).val().toLowerCase().trim();

            searchTimeout = setTimeout(function () {
                FileOpeningBook.searchLedgers(searchValue);
            }, 300);
        });

        $('#searchButton').on('click', function () {
            const searchValue = $('.search-input').val().toLowerCase().trim();
            FileOpeningBook.searchLedgers(searchValue);
        });
    }

    /**
     * Search through ledger rows
     */
    static searchLedgers(searchValue) {
        const $ledgerRows = $('.ledger-row, .supplier-row, .employee-row');

        if (searchValue === '') {
            $ledgerRows.show();
            return;
        }

        $ledgerRows.each(function () {
            const text = $(this).text().toLowerCase();
            if (text.includes(searchValue)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    /**
     * Setup ledger row click handlers (Matters)
     */
    setupLedgerRows() {
        $('.ledger-row').on('click', function () {
            const ledgerRef = $(this).data('ledger-ref');

            $('.ledger-row').removeClass('table-active');
            $(this).addClass('table-active');

            FileOpeningBook.loadLedgerData(ledgerRef);
        });
    }

    /**
     * Setup supplier row click handlers
     */
    setupSupplierRows() {
        $(document).on('click', '.supplier-row', function () {
            const supplierId = $(this).data('supplier-id');

            $('.supplier-row').removeClass('table-active');
            $(this).addClass('table-active');

            FileOpeningBook.loadSupplierData(supplierId);
        });
    }

    /**
     * Setup employee row click handlers
     */
    setupEmployeeRows() {
        $(document).on('click', '.employee-row', function () {
            const employeeId = $(this).data('employee-id');

            $('.employee-row').removeClass('table-active');
            $(this).addClass('table-active');

            FileOpeningBook.loadEmployeeData(employeeId);
        });
    }

    /**
     * Setup employee main tab switching (Transactions vs Details)
     */
    setupEmployeeTabs() {
        $(document).on('click', '.employee-tab-link', function (e) {
            e.preventDefault();

            const tab = $(this).data('tab');

            $('.employee-tab-link').removeClass('active');
            $(this).addClass('active');

            if (tab === 'transactions') {
                $('#employee-date-range').show();
                $('#employee-transactions-table').show();
                $('#employee-details-form').hide();
                $('#employee-view-actions').hide();  // Hide buttons
                $('#employee-edit-actions').hide();

            } else if (tab === 'details') {
                $('#employee-date-range').hide();
                $('#employee-transactions-table').hide();
                $('#employee-details-form').show();
                $('#employee-view-actions').show();  // ✅ Show Edit/Delete
                $('#employee-edit-actions').hide();  // Hide Save/Cancel

                // Reset to first tab
                $('.employee-tabs .tab-item').removeClass('active').first().addClass('active');
                $('.tab-content-pane').removeClass('active').first().addClass('active');

            } else if (tab === 'documents') {
                $('#employee-date-range').hide();
                $('#employee-transactions-table').hide();
                $('#employee-details-form').hide();
                $('#employee-view-actions').hide();
                $('#employee-edit-actions').hide();
            }
        });
    }


    /**
   * Setup employee action buttons with edit mode toggle
   */
    setupEmployeeActions() {
        // ========================================
        // EDIT BUTTON - Enable Edit Mode
        // ========================================
        $(document).on('click', '#edit-employee-btn', function () {
            const employeeId = $('.employee-row.table-active').data('employee-id');

            if (!employeeId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Employee Selected',
                    text: 'Please select an employee first.',
                    confirmButtonColor: '#13667d'
                });
                return;
            }

            console.log('✏️ Entering edit mode for employee:', employeeId);

            // Store original form data BEFORE enabling edit mode
            FileOpeningBook.storeOriginalFormData();

            // Enable edit mode
            FileOpeningBook.enableEditMode(employeeId);
        });

        // ========================================
        // SAVE BUTTON - Save with Change Detection
        // ========================================
        $(document).on('click', '#save-employee-btn', function () {
            console.log('🔴 SAVE BUTTON CLICKED');

            const employeeId = $('#employee-preview-form').data('employee-id');
            console.log('🔴 Employee ID:', employeeId);

            if (!employeeId) {
                console.log('🔴 NO EMPLOYEE ID FOUND');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Employee ID not found.',
                    confirmButtonColor: '#13667d'
                });
                return;
            }

            // Check if any changes were made
            const hasChanges = FileOpeningBook.hasFormChanges();
            console.log('🔴 Has Changes:', hasChanges);

            if (!hasChanges) {
                console.log('🔴 NO CHANGES DETECTED');
                Swal.fire({
                    icon: 'info',
                    title: 'No Changes',
                    text: 'No changes were made to the employee data.',
                    confirmButtonColor: '#13667d',
                    timer: 2500
                }).then(() => {
                    FileOpeningBook.disableEditMode();
                });
                return;
            }

            // Show confirmation with change summary
            const changedFields = FileOpeningBook.getChangedFields();
            const changeCount = Object.keys(changedFields).length;

            console.log('🔴 Changed Fields:', changedFields);
            console.log('🔴 Change Count:', changeCount);

            Swal.fire({
                title: 'Save Changes?',
                html: `
            <p>You have made <strong>${changeCount}</strong> change${changeCount > 1 ? 's' : ''}.</p>
            <p class="text-muted small">Do you want to save these changes?</p>
        `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa-solid fa-save me-2"></i>Yes, save changes',
                cancelButtonText: '<i class="fa-solid fa-times me-2"></i>Cancel',
                reverseButtons: true
            }).then((result) => {
                console.log('🔴 Confirmation result:', result);
                if (result.isConfirmed) {
                    console.log('🔴 CALLING SAVE EMPLOYEE');
                    FileOpeningBook.saveEmployee(employeeId, changedFields);
                }
            });
        });

        // ========================================
        // CANCEL BUTTON - Discard Changes
        // ========================================
        $(document).on('click', '#cancel-edit-btn', function () {
            const hasChanges = FileOpeningBook.hasFormChanges();

            if (hasChanges) {
                const changedFields = FileOpeningBook.getChangedFields();
                const changeCount = Object.keys(changedFields).length;

                Swal.fire({
                    title: 'Discard Changes?',
                    html: `
                    <p>You have <strong>${changeCount}</strong> unsaved change${changeCount > 1 ? 's' : ''}.</p>
                    <p class="text-danger">These changes will be lost!</p>
                `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fa-solid fa-trash me-2"></i>Yes, discard',
                    cancelButtonText: '<i class="fa-solid fa-arrow-left me-2"></i>Keep editing',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        FileOpeningBook.disableEditMode();
                    }
                });
            } else {
                FileOpeningBook.disableEditMode();
            }
        });

        // ========================================
        // DELETE BUTTON
        // ========================================
        $(document).on('click', '#delete-employee-btn', function () {
            const employeeId = $('.employee-row.table-active').data('employee-id');
            const employeeName = $('#employee-name').text();

            if (!employeeId) return;

            Swal.fire({
                title: 'Are you sure?',
                html: `You are about to delete <strong>${employeeName}</strong>.<br><br>This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa-solid fa-trash me-2"></i>Yes, delete it!',
                cancelButtonText: '<i class="fa-solid fa-times me-2"></i>Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    FileOpeningBook.deleteEmployee(employeeId, employeeName);
                }
            });
        });
    }


    /**
    * Setup supplier main tab switching (Transactions vs Details)
    */
    setupSupplierTabs() {
        $(document).on('click', '.supplier-tab-link', function (e) {
            e.preventDefault();

            const tab = $(this).data('tab');

            // ✅ Remove active from all tabs
            $('.supplier-tab-link').removeClass('active');

            // ✅ Add active to clicked tab
            $(this).addClass('active');

            if (tab === 'transactions') {
                $('#supplier-date-range').show();
                $('#supplier-tables-container-wrapper').show();
                $('#supplier-details-form').hide();
                $('#supplier-view-actions').hide();
                $('#supplier-edit-actions').hide();

            } else if (tab === 'details') {
                $('#supplier-date-range').hide();
                $('#supplier-tables-container-wrapper').hide();
                $('#supplier-details-form').show();
                $('#supplier-view-actions').show();  // ✅ Show buttons on details tab
                $('#supplier-edit-actions').hide();

            } else if (tab === 'documents') {
                $('#supplier-date-range').hide();
                $('#supplier-tables-container-wrapper').hide();
                $('#supplier-details-form').hide();
                $('#supplier-view-actions').hide();
                $('#supplier-edit-actions').hide();
            }
        });
    }


    /**
     * Setup supplier details tab switching (Contact Info, Business, etc.)
     */
    setupSupplierDetailTabs() {
        $(document).on('click', '#supplier-details-form .supplier-tabs .tab-item', function () {
            const tab = $(this).data('tab');

            $('#supplier-details-form .supplier-tabs .tab-item').removeClass('active');
            $(this).addClass('active');

            $('#supplier-details-form .tab-content-pane').removeClass('active');
            $(`#supplier-details-form #${tab}`).addClass('active');
        });
    }

    /**
     * Setup supplier action buttons with edit mode toggle
     */
    setupSupplierActions() {
        // EDIT BUTTON
        $(document).on('click', '#edit-supplier-btn', function () {
            const supplierId = $('.supplier-row.table-active').data('supplier-id');

            if (!supplierId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Supplier Selected',
                    text: 'Please select a supplier first.',
                    confirmButtonColor: '#13667d'
                });
                return;
            }

            FileOpeningBook.storeOriginalSupplierFormData();
            FileOpeningBook.enableSupplierEditMode(supplierId);
        });

        // SAVE BUTTON
        $(document).on('click', '#save-supplier-btn', function () {
            const supplierId = $('#supplier-preview-form').data('supplier-id');

            if (!supplierId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Supplier ID not found.',
                    confirmButtonColor: '#13667d'
                });
                return;
            }

            const hasChanges = FileOpeningBook.hasSupplierFormChanges();

            if (!hasChanges) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Changes',
                    text: 'No changes were made to the supplier data.',
                    confirmButtonColor: '#13667d',
                    timer: 2500
                }).then(() => {
                    FileOpeningBook.disableSupplierEditMode();
                });
                return;
            }

            const changedFields = FileOpeningBook.getSupplierChangedFields();
            const changeCount = Object.keys(changedFields).length;

            Swal.fire({
                title: 'Save Changes?',
                html: `
                    <p>You have made <strong>${changeCount}</strong> change${changeCount > 1 ? 's' : ''}.</p>
                    <p class="text-muted small">Do you want to save these changes?</p>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa-solid fa-save me-2"></i>Yes, save changes',
                cancelButtonText: '<i class="fa-solid fa-times me-2"></i>Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    FileOpeningBook.saveSupplier(supplierId, changedFields);
                }
            });
        });

        // CANCEL BUTTON
        $(document).on('click', '#cancel-supplier-edit-btn', function () {
            const hasChanges = FileOpeningBook.hasSupplierFormChanges();

            if (hasChanges) {
                const changedFields = FileOpeningBook.getSupplierChangedFields();
                const changeCount = Object.keys(changedFields).length;

                Swal.fire({
                    title: 'Discard Changes?',
                    html: `
                        <p>You have <strong>${changeCount}</strong> unsaved change${changeCount > 1 ? 's' : ''}.</p>
                        <p class="text-danger">These changes will be lost!</p>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fa-solid fa-trash me-2"></i>Yes, discard',
                    cancelButtonText: '<i class="fa-solid fa-arrow-left me-2"></i>Keep editing',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        FileOpeningBook.disableSupplierEditMode();
                    }
                });
            } else {
                FileOpeningBook.disableSupplierEditMode();
            }
        });

        // DELETE BUTTON
        $(document).on('click', '#delete-supplier-btn', function () {
            const supplierId = $('.supplier-row.table-active').data('supplier-id');
            const supplierName = $('#supplier-name').text();

            if (!supplierId) return;

            Swal.fire({
                title: 'Are you sure?',
                html: `You are about to delete <strong>${supplierName}</strong>.<br><br>This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa-solid fa-trash me-2"></i>Yes, delete it!',
                cancelButtonText: '<i class="fa-solid fa-times me-2"></i>Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    FileOpeningBook.deleteSupplier(supplierId, supplierName);
                }
            });
        });
    }

    /**
     * Store original form data before editing
     */
    static storeOriginalFormData() {
        const originalData = {};

        $('.employee-form-field').each(function () {
            const $field = $(this);
            const name = $field.attr('name');

            if (!name) return;

            if ($field.is(':checkbox')) {
                originalData[name] = $field.is(':checked') ? '1' : '0';
            } else if ($field.is(':radio')) {
                if ($field.is(':checked')) {
                    originalData[name] = $field.val();
                }
            } else {
                originalData[name] = $field.val() || '';
            }
        });

        $('#employee-preview-form').data('original-data', JSON.stringify(originalData));
        console.log('💾 Original form data stored');
    }

    /**
     * Check if form has any changes
     */
    static hasFormChanges() {
        const originalData = JSON.parse($('#employee-preview-form').data('original-data') || '{}');
        const currentData = {};

        $('.employee-form-field').each(function () {
            const $field = $(this);
            const name = $field.attr('name');

            if (!name) return;

            if ($field.is(':checkbox')) {
                currentData[name] = $field.is(':checked') ? '1' : '0';
            } else if ($field.is(':radio')) {
                if ($field.is(':checked')) {
                    currentData[name] = $field.val();
                }
            } else {
                currentData[name] = $field.val() || '';
            }
        });

        for (const key in originalData) {
            if (originalData[key] !== currentData[key]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get changed fields
     */
    static getChangedFields() {
        const originalData = JSON.parse($('#employee-preview-form').data('original-data') || '{}');
        const changedFields = {};

        $('.employee-form-field').each(function () {
            const $field = $(this);
            const name = $field.attr('name');

            if (!name) return;

            let currentValue;

            if ($field.is(':checkbox')) {
                currentValue = $field.is(':checked') ? '1' : '0';
            } else if ($field.is(':radio')) {
                if ($field.is(':checked')) {
                    currentValue = $field.val();
                } else {
                    return;
                }
            } else {
                currentValue = $field.val() || '';
            }

            const originalValue = originalData[name] || '';

            if (originalValue !== currentValue) {
                changedFields[name] = {
                    old: originalValue,
                    new: currentValue
                };
            }
        });

        return changedFields;
    }

    /**
     * Enable edit mode
     */
    static enableEditMode(employeeId) {
        console.log('🔓 Enabling edit mode');

        $('#employee-preview-form').attr('data-employee-id', employeeId);

        // Switch buttons
        $('#employee-view-actions').hide();
        $('#employee-edit-actions').show();

        $('#employee-details-form').addClass('edit-mode');

        // Enable fields
        $('.employee-form-field').each(function () {
            const $field = $(this);

            if ($field.is('input[type="text"], input[type="email"], input[type="tel"], input[type="number"], input[type="date"], textarea')) {
                $field.prop('readonly', false).removeClass('bg-light');
            }
        });

        $('select.employee-form-field').prop('disabled', false).removeClass('bg-light');
        $('input[type="checkbox"].employee-form-field, input[type="radio"].employee-form-field').prop('disabled', false);

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: '✏️ Edit mode enabled',
            showConfirmButton: false,
            timer: 2000
        });
    }

    /**
     * Disable edit mode
     */
    static disableEditMode() {
        console.log('🔒 Disabling edit mode');

        $('#employee-view-actions').show();
        $('#employee-edit-actions').hide();

        $('#employee-details-form').removeClass('edit-mode');

        // Disable fields
        $('.employee-form-field').each(function () {
            const $field = $(this);

            if ($field.is('input[type="text"], input[type="email"], input[type="tel"], input[type="number"], input[type="date"], textarea')) {
                $field.prop('readonly', true).addClass('bg-light');
            }
        });

        $('select.employee-form-field').prop('disabled', true).addClass('bg-light');
        $('input[type="checkbox"].employee-form-field, input[type="radio"].employee-form-field').prop('disabled', true);

        $('.employee-form-field').removeClass('is-valid is-invalid');
        $('.invalid-feedback').remove();

        const employeeId = $('.employee-row.table-active').data('employee-id');
        if (employeeId) {
            FileOpeningBook.loadEmployeeData(employeeId);
        }
    }

    /**
     * Save employee
     */
    static saveEmployee(employeeId, changedFields) {
        const $saveBtn = $('#save-employee-btn');
        const formData = new FormData();

        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('_method', 'PUT');

        $('.employee-form-field').each(function () {
            const $field = $(this);
            const name = $field.attr('name');

            if (!name) return;

            if ($field.is(':checkbox')) {
                formData.append(name, $field.is(':checked') ? 1 : 0);
            } else if ($field.is(':radio')) {
                if ($field.is(':checked')) {
                    formData.append(name, $field.val());
                }
            } else {
                formData.append(name, $field.val() || '');
            }
        });

        $saveBtn.addClass('btn-loading').prop('disabled', true);

        Swal.fire({
            title: 'Saving...',
            html: '<div class="spinner-border text-success"></div>',
            allowOutsideClick: false,
            showConfirmButton: false
        });

        $.ajax({
            url: `/employees/${employeeId}`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                const changeCount = Object.keys(changedFields).length;

                Swal.fire({
                    icon: 'success',
                    title: 'Changes Saved!',
                    html: `<strong>${changeCount}</strong> field${changeCount > 1 ? 's' : ''} updated successfully.`,
                    confirmButtonColor: '#13667d',
                    timer: 2000
                }).then(() => {
                    FileOpeningBook.disableEditMode();
                    FileOpeningBook.loadEmployeeData(employeeId);
                });
            },
            error: function (xhr) {
                let errorHtml = '<ul class="text-start">';

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    Object.keys(xhr.responseJSON.errors).forEach(field => {
                        const messages = xhr.responseJSON.errors[field];
                        messages.forEach(message => {
                            errorHtml += `<li>${message}</li>`;
                        });
                    });
                }

                errorHtml += '</ul>';

                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: errorHtml,
                    confirmButtonColor: '#13667d'
                });
            },
            complete: function () {
                $saveBtn.removeClass('btn-loading').prop('disabled', false);
            }
        });
    }

    /**
     * Delete employee
     */
    static deleteEmployee(employeeId, employeeName) {
        $.ajax({
            url: `/employees/${employeeId}`,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            beforeSend: function () {
                Swal.fire({
                    title: 'Deleting...',
                    html: '<div class="spinner-border text-danger"></div>',
                    allowOutsideClick: false,
                    showConfirmButton: false
                });
            },
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: `${employeeName} has been deleted.`,
                    timer: 2000
                }).then(() => {
                    $(`.employee-row[data-employee-id="${employeeId}"]`).fadeOut(300, function () {
                        $(this).remove();
                    });

                    $('#employee-info').hide();
                    $('#employee-default-message').show();

                    const $firstEmployee = $('.employee-row').first();
                    if ($firstEmployee.length > 0) {
                        setTimeout(() => $firstEmployee.trigger('click'), 400);
                    }
                });
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete employee.',
                    confirmButtonColor: '#13667d'
                });
            }
        });
    }


    /**
     * Store original supplier form data
     */
    static storeOriginalSupplierFormData() {
        const originalData = {};

        $('.supplier-form-field').each(function () {
            const $field = $(this);
            const name = $field.attr('name');

            if (!name) return;

            if ($field.is(':checkbox')) {
                originalData[name] = $field.is(':checked') ? '1' : '0';
            } else if ($field.is(':radio')) {
                if ($field.is(':checked')) {
                    originalData[name] = $field.val();
                }
            } else {
                originalData[name] = $field.val() || '';
            }
        });

        $('#supplier-preview-form').data('original-data', JSON.stringify(originalData));
    }

    /**
     * Check if supplier form has changes
     */
    static hasSupplierFormChanges() {
        const originalData = JSON.parse($('#supplier-preview-form').data('original-data') || '{}');
        const currentData = {};

        $('.supplier-form-field').each(function () {
            const $field = $(this);
            const name = $field.attr('name');

            if (!name) return;

            if ($field.is(':checkbox')) {
                currentData[name] = $field.is(':checked') ? '1' : '0';
            } else if ($field.is(':radio')) {
                if ($field.is(':checked')) {
                    currentData[name] = $field.val();
                }
            } else {
                currentData[name] = $field.val() || '';
            }
        });

        for (const key in originalData) {
            if (originalData[key] !== currentData[key]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get changed supplier fields
     */
    static getSupplierChangedFields() {
        const originalData = JSON.parse($('#supplier-preview-form').data('original-data') || '{}');
        const changedFields = {};

        $('.supplier-form-field').each(function () {
            const $field = $(this);
            const name = $field.attr('name');

            if (!name) return;

            let currentValue;

            if ($field.is(':checkbox')) {
                currentValue = $field.is(':checked') ? '1' : '0';
            } else if ($field.is(':radio')) {
                if ($field.is(':checked')) {
                    currentValue = $field.val();
                } else {
                    return;
                }
            } else {
                currentValue = $field.val() || '';
            }

            const originalValue = originalData[name] || '';

            if (originalValue !== currentValue) {
                changedFields[name] = {
                    old: originalValue,
                    new: currentValue
                };
            }
        });

        return changedFields;
    }

    /**
     * Enable supplier edit mode
     */
    static enableSupplierEditMode(supplierId) {
        $('#supplier-preview-form').attr('data-supplier-id', supplierId);

        $('#supplier-view-actions').hide();
        $('#supplier-edit-actions').show();

        $('#supplier-details-form').addClass('edit-mode');

        $('.supplier-form-field').each(function () {
            const $field = $(this);

            if ($field.is('input[type="text"], input[type="email"], input[type="tel"], input[type="number"], input[type="date"], input[type="url"], textarea')) {
                $field.prop('readonly', false).removeClass('bg-light');
            }
        });

        $('select.supplier-form-field').prop('disabled', false).removeClass('bg-light');
        $('input[type="checkbox"].supplier-form-field, input[type="radio"].supplier-form-field').prop('disabled', false);

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: '✏️ Edit mode enabled',
            showConfirmButton: false,
            timer: 2000
        });
    }

    /**
     * Disable supplier edit mode
     */
    static disableSupplierEditMode() {
        $('#supplier-view-actions').show();
        $('#supplier-edit-actions').hide();

        $('#supplier-details-form').removeClass('edit-mode');

        $('.supplier-form-field').each(function () {
            const $field = $(this);

            if ($field.is('input[type="text"], input[type="email"], input[type="tel"], input[type="number"], input[type="date"], input[type="url"], textarea')) {
                $field.prop('readonly', true).addClass('bg-light');
            }
        });

        $('select.supplier-form-field').prop('disabled', true).addClass('bg-light');
        $('input[type="checkbox"].supplier-form-field, input[type="radio"].supplier-form-field').prop('disabled', true);

        $('.supplier-form-field').removeClass('is-valid is-invalid');
        $('.invalid-feedback').remove();

        const supplierId = $('.supplier-row.table-active').data('supplier-id');
        if (supplierId) {
            FileOpeningBook.loadSupplierData(supplierId);
        }
    }

    /**
     * Save supplier
     */
    static saveSupplier(supplierId, changedFields) {
        const $saveBtn = $('#save-supplier-btn');
        const formData = new FormData();

        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('_method', 'PUT');

        $('.supplier-form-field').each(function () {
            const $field = $(this);
            const name = $field.attr('name');

            if (!name) return;

            if ($field.is(':checkbox')) {
                formData.append(name, $field.is(':checked') ? 1 : 0);
            } else if ($field.is(':radio')) {
                if ($field.is(':checked')) {
                    formData.append(name, $field.val());
                }
            } else {
                formData.append(name, $field.val() || '');
            }
        });

        $saveBtn.addClass('btn-loading').prop('disabled', true);

        Swal.fire({
            title: 'Saving...',
            html: '<div class="spinner-border text-success"></div>',
            allowOutsideClick: false,
            showConfirmButton: false
        });

        $.ajax({
            url: `/suppliers/${supplierId}`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function (response) {
                const changeCount = Object.keys(changedFields).length;

                Swal.fire({
                    icon: 'success',
                    title: 'Changes Saved!',
                    html: `<strong>${changeCount}</strong> field${changeCount > 1 ? 's' : ''} updated successfully.`,
                    confirmButtonColor: '#13667d',
                    timer: 2000
                }).then(() => {
                    FileOpeningBook.disableSupplierEditMode();
                    FileOpeningBook.loadSupplierData(supplierId);
                });
            },
            error: function (xhr) {
                let errorHtml = '<ul class="text-start">';

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    Object.keys(xhr.responseJSON.errors).forEach(field => {
                        const messages = xhr.responseJSON.errors[field];
                        messages.forEach(message => {
                            errorHtml += `<li>${message}</li>`;
                        });
                    });
                }

                errorHtml += '</ul>';

                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: errorHtml,
                    confirmButtonColor: '#13667d'
                });
            },
            complete: function () {
                $saveBtn.removeClass('btn-loading').prop('disabled', false);
            }
        });
    }

    /**
     * Delete supplier
     */
    static deleteSupplier(supplierId, supplierName) {
        $.ajax({
            url: `/suppliers/${supplierId}`,
            type: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            beforeSend: function () {
                Swal.fire({
                    title: 'Deleting...',
                    html: '<div class="spinner-border text-danger"></div>',
                    allowOutsideClick: false,
                    showConfirmButton: false
                });
            },
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: `${supplierName} has been deleted.`,
                    timer: 2000
                }).then(() => {
                    $(`.supplier-row[data-supplier-id="${supplierId}"]`).fadeOut(300, function () {
                        $(this).remove();
                    });

                    $('#supplier-info').hide();
                    $('#supplier-default-message').show();

                    const $firstSupplier = $('.supplier-row').first();
                    if ($firstSupplier.length > 0) {
                        setTimeout(() => $firstSupplier.trigger('click'), 400);
                    }
                });
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete supplier.',
                    confirmButtonColor: '#13667d'
                });
            }
        });
    }


    /**
     * Setup employee details tab switching (Personal, Employment, etc.)
     */
    setupEmployeeDetailTabs() {
        $(document).on('click', '.employee-tabs .tab-item', function () {
            const tab = $(this).data('tab');
            console.log('📋 Switching to detail tab:', tab); // Debug

            // Update active state
            $('.employee-tabs .tab-item').removeClass('active');
            $(this).addClass('active');

            // Show corresponding tab content
            $('.tab-content-pane').removeClass('active');
            $(`#${tab}`).addClass('active');
        });
    }

    /**
     * Setup matter filter dropdown
     */
    setupMatterFilter() {
        $('.matter-filter').on('click', function (e) {
            e.preventDefault();
            const matterText = $(this).text();
            $('#matter-dropdown .dropdown-text').text(matterText);
        });
    }

    /**
     * Load ledger data (Matters)
     */
    static loadLedgerData(ledgerRef) {
        $.ajax({
            url: '/files/file-opening-book/ledger-data',
            type: 'GET',
            data: { ledger_ref: ledgerRef },
            beforeSend: function () {
                $('#combined-table-body').html(
                    '<tr><td colspan="8" class="text-center">Loading...</td></tr>'
                );
            },
            success: function (response) {
                FileOpeningBook.displayLedgerData(response);
            },
            error: function (xhr, status, error) {
                console.error('Error loading ledger data:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error loading ledger data. Please try again.',
                    confirmButtonColor: '#13667d'
                });
            }
        });
    }

    /**
     * Load supplier data
     */
    static loadSupplierData(supplierId) {
        $.ajax({
            url: '/files/get-supplier-data',
            type: 'GET',
            data: { supplier_id: supplierId },
            beforeSend: function () {
                $('#supplier-transaction-body').html(
                    '<tr><td colspan="10" class="text-center">Loading...</td></tr>'
                );
            },
            success: function (response) {
                // 🔍 DEBUG: Log the response to see what data we're getting
                console.log('📦 Supplier Response:', response);
                console.log('📦 Supplier Full Data:', response.supplier_full);

                FileOpeningBook.displaySupplierData(response);
            },
            error: function (xhr, status, error) {
                console.error('❌ Error loading supplier data:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error loading supplier data. Please try again.',
                    confirmButtonColor: '#13667d'
                });
            }
        });
    }


    /**
 * Load first supplier when switching to suppliers tab
 */
    loadFirstSupplier() {
        const $firstSupplier = $('.supplier-row').first();

        if ($firstSupplier.length > 0) {
            $firstSupplier.addClass('table-active');
            const firstSupplierId = $firstSupplier.data('supplier-id');
            FileOpeningBook.loadSupplierData(firstSupplierId);
        }
    }

    /**
     * Display supplier data
     */
    static displaySupplierData(data) {
        $('#supplier-default-message').hide();
        $('#supplier-info').show();

        const supplier = data.supplier;

        $('#supplier-name').text(supplier.contact_name || '-');
        $('#supplier-ref-badge').text(supplier.account_number || '-');
        $('#supplier-address').html(`<i class="fa-solid fa-map-pin"></i> ${supplier.billing_address || '-'}`);
        $('#supplier-contact').html(`<i class="fa-solid fa-phone"></i> ${supplier.phone || '-'}`);
        $('#supplier-email').html(`<i class="fa-solid fa-envelope"></i> ${supplier.email || '-'}`);

        FileOpeningBook.populateSupplierTransactions(data.transactions);

        if (data.supplier_full) {
            FileOpeningBook.populateSupplierForm(data.supplier_full);
        }

        // ✅ FIXED: Always reset to first tab (Transactions) when loading a new supplier
        $('.supplier-tab-link').removeClass('active');
        $('.supplier-tab-link[data-tab="transactions"]').addClass('active');

        // ✅ Always show transactions view by default
        $('#supplier-date-range').show();
        $('#supplier-tables-container-wrapper').show();
        $('#supplier-details-form').hide();
        $('#supplier-view-actions').hide();
        $('#supplier-edit-actions').hide();
    }

    /**
     * Populate supplier transactions
     */
    static populateSupplierTransactions(transactions) {
        const $tbody = $('#supplier-transaction-body');
        $tbody.empty();

        if (transactions && transactions.length > 0) {
            transactions.forEach(transaction => {
                const row = `
                <tr>
                    <td class="text-center">${transaction.reference || '-'}</td>
                    <td class="text-center">${transaction.date || '-'}</td>
                    <td class="text-center">${transaction.due_date || '-'}</td>
                    <td>${transaction.description || '-'}</td>
                    <td class="text-center">📄</td>
                    <td class="text-center">📧</td>
                    <td class="text-center">📎</td>
                    <td class="text-end">${transaction.debit || '0.00'}</td>
                    <td class="text-end">${transaction.credit || '0.00'}</td>
                    <td class="text-end">${transaction.balance || '0.00'}</td>
                </tr>
            `;
                $tbody.append(row);
            });
        } else {
            $tbody.html('<tr><td colspan="10" class="text-center text-muted py-4">No transactions found</td></tr>');
        }
    }

    /**
     * Populate supplier form fields
     */
    static populateSupplierForm(supplier) {
        console.log('📝 Populating form with:', supplier);

        // Contact Info
        $('#supplier_contact_name').val(supplier.contact_name || '');
        $('#supplier_account_number').val(supplier.account_number || '');
        $('#supplier_phone').val(supplier.phone || '');
        $('#supplier_email').val(supplier.email || '');

        // Primary Person
        $('#supplier_first_name').val(supplier.first_name || '');
        $('#supplier_last_name').val(supplier.last_name || '');
        $('#supplier_website').val(supplier.website || '');
        $('#supplier_company_reg_no').val(supplier.company_reg_no || '');

        // Addresses
        $('#supplier_billing_address').val(supplier.billing_address || '');
        $('#supplier_delivery_address').val(supplier.delivery_address || '');
        $('#supplier_city').val(supplier.city || '');
        $('#supplier_postal_code').val(supplier.postal_code || '');

        // Financial Details
        $('#supplier_bank_account_name').val(supplier.bank_account_name || '');
        $('#supplier_sort_code').val(supplier.sort_code || '');
        $('#supplier_bank_account_number').val(supplier.bank_account_number || '');
        $('#supplier_reference').val(supplier.reference || '');

        // VAT Details
        $('#supplier_vat_number').val(supplier.vat_number || '');
        $('#supplier_vat_status').val(supplier.vat_status || '');
        $('#supplier_tax_id').val(supplier.tax_id || '');
        $('#supplier_currency').val(supplier.currency || '');

        // Business Details
        $('#supplier_business_type').val(supplier.business_type || '');
        $('#supplier_industry').val(supplier.industry || '');
        $('#supplier_established_date').val(supplier.established_date || '');
        $('#supplier_employee_count').val(supplier.employee_count || '');

        // Payment Terms
        $('#supplier_payment_terms').val(supplier.payment_terms || '');
        $('#supplier_credit_limit').val(supplier.credit_limit || '');
        $('#supplier_discount_percentage').val(supplier.discount_percentage || '');
        $('#supplier_payment_method').val(supplier.payment_method || '');

        // Status & Rating
        $('#supplier_status').val(supplier.status || 'active');
        $('#supplier_rating').val(supplier.rating || '');

        // ✅ FIXED: Use select dropdown for preferred_supplier
        $('#supplier_preferred_supplier').val(supplier.preferred_supplier ? '1' : '0');

        $('#supplier_last_order_date').val(supplier.last_order_date || '');
        $('#supplier_notes').val(supplier.notes || '');

        console.log('✅ Form population complete');
    }

    /**
     * Load employee data
     */
    static loadEmployeeData(employeeId) {
        console.log('🔍 Loading employee data for ID:', employeeId); // Debug

        $.ajax({
            url: '/files/file-opening-book/employee-data',
            type: 'GET',
            data: { employee_id: employeeId },
            beforeSend: function () {
                $('#employee-transaction-body').html(
                    '<tr><td colspan="10" class="text-center">Loading...</td></tr>'
                );
            },
            success: function (response) {
                console.log('✅ Employee data loaded:', response); // Debug
                FileOpeningBook.displayEmployeeData(response);
            },
            error: function (xhr, status, error) {
                console.error('❌ Error loading employee data:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error loading employee data. Please try again.',
                    confirmButtonColor: '#13667d'
                });
            }
        });
    }

    /**
     * Display employee data (SINGLE VERSION - NO DUPLICATES)
     */
    static displayEmployeeData(data) {
        console.log('📊 Displaying employee data'); // Debug

        $('#employee-default-message').hide();
        $('#employee-info').show();

        const employee = data.employee;

        // Update header info
        $('#employee-name').text(employee.full_name || '-');
        $('#employee-ref-badge').text(`EMP${String(employee.id).padStart(3, '0')}`);
        $('#employee-position').html(`<i class="fa-solid fa-briefcase"></i> ${employee.job_title || '-'}`);
        $('#employee-contact').html(`<i class="fa-solid fa-phone"></i> ${employee.primary_phone || '-'}`);
        $('#employee-email').html(`<i class="fa-solid fa-envelope"></i> ${employee.email || '-'}`);
        $('#employee-dob').html(`<i class="fa-solid fa-calendar"></i> DOB: ${employee.date_of_birth || '-'}`);
        $('#employee-ni').html(`<i class="fa-solid fa-id-card"></i> NI: ${employee.ni_number || '-'}`);

        // Populate transactions
        FileOpeningBook.populateEmployeeTransactions(data.transactions);

        // Populate form fields
        if (data.employee_full) {
            console.log('📝 Populating employee form'); // Debug
            FileOpeningBook.populateEmployeeForm(data.employee_full);
        }

        // Show transactions by default
        $('#employee-date-range').show();
        $('#employee-transactions-table').show();
        $('#employee-details-form').hide();
    }

    /**
     * Populate employee transactions
     */
    static populateEmployeeTransactions(transactions) {
        const $tbody = $('#employee-transaction-body');
        $tbody.empty();

        if (transactions && transactions.length > 0) {
            transactions.forEach(transaction => {
                const row = `
                <tr>
                    <td>${transaction.reference || '-'}</td>
                    <td>${transaction.date || '-'}</td>
                    <td>${transaction.due_date || '-'}</td>
                    <td>${transaction.description || '-'}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td class="text-end">${transaction.debit || '0.00'}</td>
                    <td class="text-end">${transaction.credit || '0.00'}</td>
                    <td class="text-end">${transaction.balance || '0.00'}</td>
                </tr>
            `;
                $tbody.append(row);
            });
        } else {
            $tbody.html('<tr><td colspan="10" class="text-center text-muted py-4">No transactions found</td></tr>');
        }
    }

    /**
     * Populate all employee form fields
     */
    static populateEmployeeForm(employee) {
        // Personal Details
        $('#personal #title').val(employee.title || '');
        $('#personal #first_name').val(employee.first_name || '');
        $('#personal #surname').val(employee.surname || '');
        $('#personal #known_as').val(employee.known_as || '');
        $('#personal #date_of_birth').val(employee.date_of_birth || '');
        $('#personal #gender').val(employee.gender || '');
        $('#personal #ni_number').val(employee.ni_number || '');
        $('#personal #passport_number').val(employee.passport_number || '');
        $('#personal #nationality').val(employee.nationality || '');
        $('#personal #address_line_1').val(employee.address_line_1 || '');
        $('#personal #address_line_2').val(employee.address_line_2 || '');
        $('#personal #address_line_3').val(employee.address_line_3 || '');
        $('#personal #city_town').val(employee.city_town || '');
        $('#personal #county').val(employee.county || '');
        $('#personal #postcode').val(employee.postcode || '');
        $('#personal #country').val(employee.country || '');
        $('#personal #primary_phone').val(employee.primary_phone || '');
        $('#personal #secondary_phone').val(employee.secondary_phone || '');
        $('#personal #email').val(employee.email || '');
        $('#personal #emergency_contact_name').val(employee.emergency_contact_name || '');
        $('#personal #emergency_contact_phone').val(employee.emergency_contact_phone || '');
        $('#personal #emergency_contact_relationship').val(employee.emergency_contact_relationship || '');

        // Employment
        $('#employment #starter_type').val(employee.starter_type || '');
        $('#employment #employment_start_date').val(employee.employment_start_date || '');
        $('#employment #hmrc_declaration').val(employee.hmrc_declaration || '');
        $('#employment input[name="has_p45"]').prop('checked', employee.has_p45 == 1);
        $('#employment input[name="student_loan"][value="' + (employee.student_loan || 'none') + '"]').prop('checked', true);
        $('#employment input[name="postgrad_loan"]').prop('checked', employee.postgrad_loan == 1);
        $('#employment #tax_code_preview').val(employee.tax_code_preview || '');
        $('#employment #ni_category_letter').val(employee.ni_category_letter || '');
        $('#employment #job_title').val(employee.job_title || '');
        $('#employment #work_department').val(employee.work_department || '');
        $('#employment #work_hours').val(employee.work_hours || '');
        $('#employment #works_number').val(employee.works_number || '');
        $('#employment #ni_number_work').val(employee.ni_number_work || '');
        $('#employment #date_started').val(employee.date_started || '');
        $('#employment #date_left').val(employee.date_left || '');

        // NIC
        $('#nic input[name="no_employer_nic"]').prop('checked', employee.no_employer_nic == 1);
        $('#nic input[name="exclude_nmw"]').prop('checked', employee.exclude_nmw == 1);
        $('#nic input[name="holiday_fund_free"]').prop('checked', employee.holiday_fund_free == 1);
        $('#nic #employee_widows_orphans').val(employee.employee_widows_orphans || '');
        $('#nic #veteran_first_day').val(employee.veteran_first_day || '');
        $('#nic input[name="off_payroll_worker"]').prop('checked', employee.off_payroll_worker == 1);
        $('#nic #workplace_postcode').val(employee.workplace_postcode || '');
        $('#nic input[name="director_flag"]').prop('checked', employee.director_flag == 1);
        $('#nic input[name="was_director"]').prop('checked', employee.was_director == 1);
        $('#nic #director_start_date').val(employee.director_start_date || '');
        $('#nic #director_end_date').val(employee.director_end_date || '');
        $('#nic input[name="director_nic_method"][value="' + (employee.director_nic_method || '') + '"]').prop('checked', true);

        // HMRC
        $('#hmrc input[name="starter_type_hmrc"][value="' + (employee.starter_type_hmrc || '') + '"]').prop('checked', true);
        $('#hmrc input[name="exclude_from_assessment"]').prop('checked', employee.exclude_from_assessment == 1);
        $('#hmrc #auto_enrolment_pension').val(employee.auto_enrolment_pension || '');
        $('#hmrc #employee_group').val(employee.employee_group || '');
        $('#hmrc #assessment').val(employee.assessment || '');
        $('#hmrc #defer_postpone_until').val(employee.defer_postpone_until || '');
        $('#hmrc #date_joined').val(employee.date_joined || '');
        $('#hmrc #date_left_hmrc').val(employee.date_left_hmrc || '');
        $('#hmrc #date_opted_out').val(employee.date_opted_out || '');
        $('#hmrc #date_opted_in').val(employee.date_opted_in || '');
        $('#hmrc input[name="do_not_reassess"]').prop('checked', employee.do_not_reassess == 1);
        $('#hmrc input[name="continue_to_assess"]').prop('checked', employee.continue_to_assess == 1);
        $('#hmrc #auto_enrolled_letter_date').val(employee.auto_enrolled_letter_date || '');
        $('#hmrc #not_enrolled_letter_date').val(employee.not_enrolled_letter_date || '');
        $('#hmrc #postponement_letter_date').val(employee.postponement_letter_date || '');
        $('#hmrc #contribution_percentages').val(employee.contribution_percentages || '');

        // Contacts
        $('#contacts #contact1_name').val(employee.contact1_name || '');
        $('#contacts #contact1_relationship').val(employee.contact1_relationship || '');
        $('#contacts #contact1_telephone').val(employee.contact1_telephone || '');
        $('#contacts #contact1_mobile').val(employee.contact1_mobile || '');
        $('#contacts #contact1_address').val(employee.contact1_address || '');
        $('#contacts #contact1_postcode').val(employee.contact1_postcode || '');
        $('#contacts #contact1_notes').val(employee.contact1_notes || '');
        $('#contacts #contact2_name').val(employee.contact2_name || '');
        $('#contacts #contact2_relationship').val(employee.contact2_relationship || '');
        $('#contacts #contact2_telephone').val(employee.contact2_telephone || '');
        $('#contacts #contact2_mobile').val(employee.contact2_mobile || '');
        $('#contacts #contact2_address').val(employee.contact2_address || '');
        $('#contacts #contact2_postcode').val(employee.contact2_postcode || '');
        $('#contacts #contact2_notes').val(employee.contact2_notes || '');

        // Terms
        $('#terms #hours_per_week').val(employee.hours_per_week || '');
        $('#terms input[name="paid_overtime"]').prop('checked', employee.paid_overtime == 1);
        $('#terms #weeks_notice').val(employee.weeks_notice || '');
        $('#terms #days_sickness_full_pay').val(employee.days_sickness_full_pay || '');
        $('#terms #retirement_age').val(employee.retirement_age || '');
        $('#terms input[name="may_join_pension"]').prop('checked', employee.may_join_pension == 1);
        $('#terms #days_holiday_per_year').val(employee.days_holiday_per_year || '');
        $('#terms #max_days_carry_over').val(employee.max_days_carry_over || '');

        // Payment
        $('#payment #pay_frequency').val(employee.pay_frequency || '');
        $('#payment #pay_method').val(employee.pay_method || '');
        $('#payment #annual_pay').val(employee.annual_pay || '');
        $('#payment #pay_per_period').val(employee.pay_per_period || '');
        $('#payment #delivery_method').val(employee.delivery_method || '');
        $('#payment #bank_name').val(employee.bank_name || '');
        $('#payment #sort_code').val(employee.sort_code || '');
        $('#payment #account_number').val(employee.account_number || '');
        $('#payment #account_name').val(employee.account_name || '');
        $('#payment #payment_reference').val(employee.payment_reference || '');
        $('#payment #building_society_ref').val(employee.building_society_ref || '');
    }

    /**
     * Display ledger data
     */
    static displayLedgerData(data) {
        $('#default-message').hide();
        $('#client-info').show();

        this.updateClientInfo(data.file_data);
        this.populateTransactions(data.transactions);
    }

    /**
     * Update client information
     */
    static updateClientInfo(fileData) {
        $('#client-name').text(`${fileData.First_Name} ${fileData.Last_Name}`);
        $('#ledger-ref-badge').text(fileData.Ledger_Ref);

        const addressParts = [
            fileData.Address1,
            fileData.Address2,
            fileData.Town
        ].filter(part => part && part.trim() !== '');

        const fullAddress = addressParts.length > 0 ? addressParts.join(', ') : '-';
        $('#client-address').html(`<i class="fa-solid fa-map-pin"></i> ${fullAddress}`);

        const contactNumber = fileData.Contact_No || '-';
        $('#client-contact').html(`<i class="fa-solid fa-phone"></i> ${contactNumber}`);

        const email = fileData.Email || '-';
        $('#email').html(`<i class="fa-solid fa-envelope"></i> ${email}`);

        let matterText = fileData.Matter || '-';
        if (fileData.Sub_Matter) {
            matterText += ` - ${fileData.Sub_Matter}`;
        }
        $('#matter').html(`<i class="fa-solid fa-scale-balanced"></i> ${matterText}`);

        const feeEarner = fileData.Fee_Earner || '-';
        $('#fee-earner').html(`<i class="fa-solid fa-user"></i> ${feeEarner}`);

        const statusMap = {
            'L': 'Live',
            'C': 'Closed',
            'A': 'Abortive',
            'I': 'Close Abortive'
        };
        const statusText = statusMap[fileData.Status] || '-';
        $('#status').html(`<i class="fa-light fa-loader"></i> ${statusText}`);
    }

    /**
     * Populate transactions
     */
    static populateTransactions(transactions) {
        const $tbody = $('#combined-table-body');
        $tbody.empty();

        if (transactions && transactions.length > 0) {
            transactions.forEach(transaction => {
                const row = `
                    <tr>
                        <td data-column="date">${transaction.TransactionDate || '-'}</td>
                        <td data-column="description" style="text-align: center !important;">
                            ${transaction.Description || '-'}
                        </td>
                        <td>${this.formatAmount(transaction.Office_Debit)}</td>
                        <td>${this.formatAmount(transaction.Office_Credit)}</td>
                        <td>${this.formatAmount(transaction.Office_Balance)}</td>
                        <td>${this.formatAmount(transaction.Client_Debit)}</td>
                        <td>${this.formatAmount(transaction.Client_Credit)}</td>
                        <td>${this.formatAmount(transaction.Client_Balance)}</td>
                    </tr>
                `;
                $tbody.append(row);
            });
        } else {
            $tbody.html('<tr><td colspan="8" class="text-center">No transactions found</td></tr>');
        }
    }

    /**
     * Format amount
     */
    static formatAmount(amount) {
        if (!amount || amount === '0' || amount === 0) {
            return '0.00';
        }
        return amount;
    }

    /**
     * Load first ledger on page load
     */
    loadFirstLedger() {
        const $firstLedger = $('.ledger-row').first();

        if ($firstLedger.length > 0) {
            $firstLedger.addClass('table-active');
            const firstLedgerRef = $firstLedger.data('ledger-ref');
            FileOpeningBook.loadLedgerData(firstLedgerRef);
        }
    }

    /**
     * Load first employee when switching to employees tab
     */
    loadFirstEmployee() {
        const $firstEmployee = $('.employee-row').first();

        if ($firstEmployee.length > 0) {
            $firstEmployee.addClass('table-active');
            const firstEmployeeId = $firstEmployee.data('employee-id');
            FileOpeningBook.loadEmployeeData(firstEmployeeId);
        }
    }
}

/* Initialize on document ready */
$(document).ready(function () {
    console.log('🚀 Initializing FileOpeningBook');
    new FileOpeningBook();
});