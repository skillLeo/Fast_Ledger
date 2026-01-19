@extends('admin.layout.app')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    @include('admin.partial.errors')
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="page-title mb-0">View Supplier</h4>
                            <div>
                                <a href="{{ $isCompanyModule ? route('company.suppliers.edit', $supplier->id) : route('suppliers.edit', $supplier->id) }}"
                                    class="btn btn-primary btn-wave me-2">
                                    Edit Supplier
                                </a>
                                <a href="{{ $isCompanyModule ? route('company.suppliers.index') : route('suppliers.index') }}"
                                    class="btn btn-secondary btn-wave">
                                    Back to List
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-4">

                            <div class="row">
                                <!-- COLUMN 1 -->
                                <div class="col-lg-4">
                                    <!-- Contact Information -->
                                    <div class="section-content" id="contact-details">
                                        <div class="custom-card mb-4">
                                            <div class="fw-semibold">Contact Information</div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="contact_name" class="form-label">Contact Name *</label>
                                                        <input type="text" class="form-control" id="contact_name"
                                                            name="contact_name" 
                                                            value="{{ $supplier->contact_name ?? '' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="account_number" class="form-label">Account Number</label>
                                                        <input type="text" class="form-control" id="account_number"
                                                            name="account_number"
                                                            value="{{ $supplier->account_number ?? '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="phone" class="form-label">Phone Number</label>
                                                        <input type="text" class="form-control" id="phone"
                                                            name="phone"
                                                            value="{{ $supplier->phone ?? '' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="email" class="form-label">Email</label>
                                                        <input type="email" class="form-control" id="email"
                                                            name="email"
                                                            value="{{ $supplier->email ?? '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Primary Person -->
                                    <div class="section-content" id="primary-person">
                                        <div class="custom-card mb-4">
                                            <div class="card-header fw-semibold">Primary Person</div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="first_name" class="form-label">First Name</label>
                                                        <input type="text" class="form-control" id="first_name"
                                                            name="first_name"
                                                            value="{{ $supplier->first_name ?? '' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="last_name" class="form-label">Last Name</label>
                                                        <input type="text" class="form-control" id="last_name"
                                                            name="last_name"
                                                            value="{{ $supplier->last_name ?? '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="website" class="form-label">Website</label>
                                                        <input type="url" class="form-control" id="website"
                                                            name="website"
                                                            value="{{ $supplier->website ?? '' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="company_reg" class="form-label">Company Reg No</label>
                                                        <input type="text" class="form-control" id="company_reg"
                                                            name="company_reg"
                                                            value="{{ $supplier->company_reg ?? '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional Notes -->
                                    <div class="section-content" id="notes-section">
                                        <div class="custom-card mb-4">
                                            <div class="card-header fw-semibold">Additional Notes</div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-12 mb-3">
                                                        <label for="notes" class="form-label">Notes</label>
                                                        <textarea class="form-control" id="notes" name="notes" rows="3" readonly>{{ $supplier->notes ?? '' }}</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- COLUMN 2 -->
                                <div class="col-lg-4">
                                    <!-- Addresses -->
                                    <div class="section-content" id="addresses">
                                        <div class="custom-card mb-4">
                                            <div class="card-header fw-semibold">Addresses</div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="billing_address" class="form-label">Billing Address</label>
                                                        <input type="text" class="form-control"
                                                            id="billing_address" name="billing_address"
                                                            value="{{ $supplier->billing_address ?? '' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="delivery_address" class="form-label">Delivery Address</label>
                                                        <input type="text" class="form-control"
                                                            id="delivery_address" name="delivery_address"
                                                            value="{{ $supplier->delivery_address ?? '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="city" class="form-label">City</label>
                                                        <input type="text" class="form-control" id="city"
                                                            name="city"
                                                            value="{{ $supplier->city ?? '' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="postal_code" class="form-label">Postal Code</label>
                                                        <input type="text" class="form-control" id="postal_code"
                                                            name="postal_code"
                                                            value="{{ $supplier->postal_code ?? '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Financial Details -->
                                    <div class="section-content" id="financial-details">
                                        <div class="custom-card mb-4">
                                            <div class="card-header fw-semibold">Financial Details</div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="bank_account_name" class="form-label">Bank Account Name</label>
                                                        <input type="text" class="form-control"
                                                            id="bank_account_name" name="bank_account_name"
                                                            value="{{ $supplier->bank_account_name ?? '' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="sort_code" class="form-label">Sort Code</label>
                                                        <input type="text" class="form-control" id="sort_code"
                                                            name="sort_code"
                                                            value="{{ $supplier->sort_code ?? '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="bank_account_number" class="form-label">Bank Account No</label>
                                                        <input type="text" class="form-control"
                                                            id="bank_account_number" name="bank_account_number"
                                                            value="{{ $supplier->bank_account_number ?? '' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="reference" class="form-label">Reference</label>
                                                        <input type="text" class="form-control" id="reference"
                                                            name="reference"
                                                            value="{{ $supplier->reference ?? '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- VAT Details -->
                                    <div class="section-content" id="vat-details">
                                        <div class="custom-card mb-4">
                                            <div class="card-header fw-semibold">VAT Details</div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="vat_number" class="form-label">VAT Number</label>
                                                        <input type="text" class="form-control" id="vat_number"
                                                            name="vat_number"
                                                            value="{{ $supplier->vat_number ?? '' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="vat_status" class="form-label">VAT Status</label>
                                                        <input type="text" class="form-control" id="vat_status"
                                                            name="vat_status"
                                                            value="{{ $supplier->vat_status ? ucfirst(str_replace('_', ' ', $supplier->vat_status)) : '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="tax_id" class="form-label">Tax ID</label>
                                                        <input type="text" class="form-control" id="tax_id"
                                                            name="tax_id"
                                                            value="{{ $supplier->tax_id ?? '' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="currency" class="form-label">Currency</label>
                                                        <input type="text" class="form-control" id="currency"
                                                            name="currency"
                                                            value="{{ $supplier->currency ?? '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- COLUMN 3 -->
                                <div class="col-lg-4">
                                    <!-- Business Details -->
                                    <div class="section-content" id="business-details">
                                        <div class="custom-card mb-4">
                                            <div class="card-header fw-semibold">Business Details</div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="business_type" class="form-label">Business Type</label>
                                                        <input type="text" class="form-control" id="business_type"
                                                            name="business_type"
                                                            value="{{ $supplier->business_type ? ucfirst($supplier->business_type) : '' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="industry" class="form-label">Industry</label>
                                                        <input type="text" class="form-control" id="industry"
                                                            name="industry"
                                                            value="{{ $supplier->industry ?? '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="established_date" class="form-label">Established Date</label>
                                                        <input type="text" class="form-control"
                                                            id="established_date" name="established_date"
                                                            value="{{ $supplier->established_date ? \Carbon\Carbon::parse($supplier->established_date)->format('d M Y') : '' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="employee_count" class="form-label">Employee Count</label>
                                                        <input type="text" class="form-control"
                                                            id="employee_count" name="employee_count"
                                                            value="{{ $supplier->employee_count ?? '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Payment Terms -->
                                    <div class="section-content" id="payment-terms">
                                        <div class="custom-card mb-4">
                                            <div class="card-header fw-semibold">Payment Terms</div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="payment_terms" class="form-label">Payment Terms</label>
                                                        <input type="text" class="form-control" id="payment_terms"
                                                            name="payment_terms"
                                                            value="{{ $supplier->payment_terms ? ucfirst(str_replace('_', ' ', $supplier->payment_terms)) : '' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="credit_limit" class="form-label">Credit Limit</label>
                                                        <input type="text" class="form-control" id="credit_limit"
                                                            name="credit_limit"
                                                            value="{{ $supplier->credit_limit ? number_format($supplier->credit_limit, 2) : '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="discount_percentage" class="form-label">Discount Percentage</label>
                                                        <input type="text" class="form-control"
                                                            id="discount_percentage" name="discount_percentage"
                                                            value="{{ $supplier->discount_percentage ? $supplier->discount_percentage . '%' : '' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="payment_method" class="form-label">Payment Method</label>
                                                        <input type="text" class="form-control" id="payment_method"
                                                            name="payment_method"
                                                            value="{{ $supplier->payment_method ? ucfirst(str_replace('_', ' ', $supplier->payment_method)) : '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status & Rating -->
                                    <div class="section-content" id="status-rating">
                                        <div class="custom-card mb-4">
                                            <div class="card-header fw-semibold">Status & Rating</div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="status" class="form-label">Account Status</label>
                                                        <input type="text" class="form-control" id="status"
                                                            name="status"
                                                            value="{{ $supplier->status ? ucfirst($supplier->status) : '' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="rating" class="form-label">Supplier Rating</label>
                                                        <input type="text" class="form-control" id="rating"
                                                            name="rating"
                                                            value="{{ $supplier->rating ? $supplier->rating . ' Star' : '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="preferred_supplier" class="form-label">Preferred Supplier</label>
                                                        <input type="text" class="form-control" id="preferred_supplier"
                                                            name="preferred_supplier"
                                                            value="{{ $supplier->preferred_supplier ? 'Yes' : 'No' }}"
                                                            readonly>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="last_order_date" class="form-label">Last Order Date</label>
                                                        <input type="text" class="form-control"
                                                            id="last_order_date" name="last_order_date"
                                                            value="{{ $supplier->last_order_date ? \Carbon\Carbon::parse($supplier->last_order_date)->format('d M Y') : '' }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="text-center mt-4 mb-4">
                                <a href="{{ $isCompanyModule ? route('company.suppliers.edit', $supplier->id) : route('suppliers.edit', $supplier->id) }}"
                                    class="teal-custom-btn p-2 me-2">
                                    Edit Supplier
                                </a>
                                <form action="{{ $isCompanyModule ? route('company.suppliers.destroy', $supplier->id) : route('suppliers.destroy', $supplier->id) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('Are you sure you want to delete this supplier? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-lg rounded-0"
                                        style="background-color: #dc3545; padding: 0.4rem 0.8rem; color: #fff;">
                                        Delete Supplier
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection