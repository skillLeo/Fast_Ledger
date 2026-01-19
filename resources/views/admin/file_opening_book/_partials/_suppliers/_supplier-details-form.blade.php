{{-- resources/views/admin/file_opening_book/_partials/_suppliers/_supplier-details-form.blade.php --}}

<div id="supplier-details-form" style="display: none;">
    <form id="supplier-preview-form" class="supplier-form" data-supplier-id="">
        @csrf

        <div class="row">
            <!-- COLUMN 1 -->
            <div class="col-lg-4">
                <!-- Contact Information -->
                <div class="section-content" id="supplier-contact-details">
                    <div class="card-header fw-semibold">Contact Information</div>
                    <div class="custom-card rounded-0 mb-3">

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_contact_name" class="form-label">Contact Name *</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_contact_name" name="contact_name" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_account_number" class="form-label">Account Number</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_account_number" name="account_number" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control supplier-form-field bg-light"
                                        id="supplier_phone" name="phone" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_email" class="form-label">Email</label>
                                    <input type="email" class="form-control supplier-form-field bg-light"
                                        id="supplier_email" name="email" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Primary Person -->
                <div class="section-content" id="supplier-primary-person">
                    <div class="card-header fw-semibold">Primary Person</div>
                    <div class="custom-card rounded-0 mb-3">

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_first_name" name="first_name" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_last_name" name="last_name" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_website" class="form-label">Website</label>
                                    <input type="url" class="form-control supplier-form-field bg-light"
                                        id="supplier_website" name="website" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_company_reg_no" class="form-label">Company Reg No</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_company_reg_no" name="company_reg_no" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="section-content" id="supplier-notes-section">
                    <div class="card-header fw-semibold">Additional Notes</div>
                    <div class="custom-card rounded-0 mb-3">

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="supplier_notes" class="form-label">Notes</label>
                                    <textarea class="form-control supplier-form-field bg-light" id="supplier_notes" name="notes" rows="3"
                                        maxlength="4000" readonly></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMN 2 -->
            <div class="col-lg-4">
                <!-- Addresses -->
                <div class="section-content" id="supplier-addresses">
                    <div class="card-header fw-semibold">Addresses</div>
                    <div class="custom-card rounded-0 mb-3">

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_billing_address" class="form-label">Billing Address</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_billing_address" name="billing_address" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_delivery_address" class="form-label">Delivery Address</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_delivery_address" name="delivery_address" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_city" class="form-label">City</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_city" name="city" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_postal_code" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_postal_code" name="postal_code" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Details -->
                <div class="section-content" id="supplier-financial-details">
                    <div class="card-header fw-semibold">Financial Details</div>
                    <div class="custom-card rounded-0 mb-3">

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_bank_account_name" class="form-label">Bank Account
                                        Name</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_bank_account_name" name="bank_account_name" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_sort_code" class="form-label">Sort Code</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_sort_code" name="sort_code" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_bank_account_number" class="form-label">Bank Account
                                        No</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_bank_account_number" name="bank_account_number" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_reference" class="form-label">Reference</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_reference" name="reference" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VAT Details -->
                <div class="section-content" id="supplier-vat-details">
                    <div class="card-header fw-semibold">VAT Details</div>
                    <div class="custom-card rounded-0 mb-3">

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_vat_number" class="form-label">VAT Number</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_vat_number" name="vat_number" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_vat_status" class="form-label">VAT Status</label>
                                    <select class="form-select p-1 rounded-0 bg-light" id="supplier_vat_status"
                                        name="vat_status" disabled>
                                        <option value="">Select VAT status</option>
                                        <option value="registered">Registered</option>
                                        <option value="not_registered">Not Registered</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_tax_id" class="form-label">Tax ID</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_tax_id" name="tax_id" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_currency" class="form-label">Currency</label>
                                    <select class="form-select p-1 rounded-0 bg-light" id="supplier_currency"
                                        name="currency" disabled>
                                        <option value="">Select Currency</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                        <option value="GBP">GBP</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMN 3 -->
            <div class="col-lg-4">
                <!-- Business Details -->
                <div class="section-content" id="supplier-business-details">
                    <div class="card-header fw-semibold">Business Details</div>
                    <div class="custom-card rounded-0 mb-3">

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_business_type" class="form-label">Business Type</label>
                                    <select class="form-select p-1 rounded-0 bg-light" id="supplier_business_type"
                                        name="business_type" disabled>
                                        <option value="">Select Type</option>
                                        <option value="manufacturer">Manufacturer</option>
                                        <option value="distributor">Distributor</option>
                                        <option value="wholesaler">Wholesaler</option>
                                        <option value="retailer">Retailer</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_industry" class="form-label">Industry</label>
                                    <input type="text" class="form-control supplier-form-field bg-light"
                                        id="supplier_industry" name="industry" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_established_date" class="form-label">Established Date</label>
                                    <input type="date" class="form-control supplier-form-field bg-light"
                                        id="supplier_established_date" name="established_date" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_employee_count" class="form-label">Employee Count</label>
                                    <input type="number" class="form-control supplier-form-field bg-light"
                                        id="supplier_employee_count" name="employee_count" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Terms -->
                <div class="section-content" id="supplier-payment-terms">
                    <div class="card-header fw-semibold">Payment Terms</div>
                    <div class="custom-card rounded-0 mb-3">

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_payment_terms" class="form-label">Payment Terms</label>
                                    <select class="form-select p-1 rounded-0 bg-light" id="supplier_payment_terms"
                                        name="payment_terms" disabled>
                                        <option value="">Select Terms</option>
                                        <option value="net_15">Net 15</option>
                                        <option value="net_30">Net 30</option>
                                        <option value="net_60">Net 60</option>
                                        <option value="net_90">Net 90</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_credit_limit" class="form-label">Credit Limit</label>
                                    <input type="number" class="form-control  supplier-form-field bg-light"
                                        id="supplier_credit_limit" name="credit_limit" step="0.01" readonly>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_discount_percentage" class="form-label">Discount
                                        Percentage</label>
                                    <input type="number" class="form-control rounded-0 supplier-form-field bg-light"
                                        id="supplier_discount_percentage" name="discount_percentage" min="0"
                                        max="100" step="0.01" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_payment_method" class="form-label">Payment Method</label>
                                    <select class="form-select p-1 rounded-0 bg-light" id="supplier_payment_method"
                                        name="payment_method" disabled>
                                        <option value="">Select Method</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="check">Check</option>
                                        <option value="cash">Cash</option>
                                        <option value="credit_card">Credit Card</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status & Rating -->
                <div class="section-content" id="supplier-status-rating">
                    <div class="card-header fw-semibold">Status & Rating</div>
                    <div class="custom-card rounded-0 mb-3">

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_status" class="form-label">Account Status</label>
                                    <select class="form-select p-1 rounded-0 bg-light" id="supplier_status"
                                        name="status" disabled>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="pending">Pending</option>
                                        <option value="suspended">Suspended</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_rating" class="form-label">Supplier Rating</label>
                                    <select class="form-select p-1 rounded-0 bg-light" id="supplier_rating"
                                        name="rating" disabled>
                                        <option value="">Select Rating</option>
                                        <option value="5">5 Star</option>
                                        <option value="4">4 Star</option>
                                        <option value="3">3 Star</option>
                                        <option value="2">2 Star</option>
                                        <option value="1">1 Star</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_preferred_supplier" class="form-label">Preferred
                                        Supplier</label>
                                    <select class="form-select p-1 rounded-0 bg-light"
                                        id="supplier_preferred_supplier" name="preferred_supplier" disabled>
                                        <option value="0">No</option>
                                        <option value="1">Yes</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="supplier_last_order_date" class="form-label">Last Order Date</label>
                                    <input type="date" class="form-control supplier-form-field bg-light"
                                        id="supplier_last_order_date" name="last_order_date" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    /* Card styling to match create form */
    #supplier-details-form .custom-card {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #fff;
        overflow: hidden;
    }

    #supplier-details-form .custom-card .card-header {
        padding: 0.75rem 1.25rem;
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        font-weight: 600;
        color: #212529;
    }

    #supplier-details-form .custom-card .card-body {
        padding: 1.25rem;
    }

    /* Edit Mode Styling */
    #supplier-details-form.edit-mode .supplier-form-field:not([readonly]):not([disabled]) {
        background-color: #fff !important;
        border-color: #13667d;
    }

    #supplier-details-form.edit-mode .supplier-form-field:focus {
        border-color: #13667d;
        box-shadow: 0 0 0 0.2rem rgba(19, 102, 125, 0.25);
    }

    /* Form labels */
    #supplier-details-form .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    /* Input fields styling */
    #supplier-details-form .form-control,
    #supplier-details-form .form-select {
        border: 1px solid #ced4da;
    }


    @media (max-width: 767px) {
        #supplier-details-form .form-label {
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        #supplier-details-form .form-control,
        #supplier-details-form .form-select {
            font-size: 0.875rem;
            padding: 0.5rem;
        }

        #supplier-details-form .custom-card .card-body {
            padding: 1rem;
        }
    }
</style>
