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
                            <h4 class="page-title mb-0">{{ isset($supplier) ? 'Edit Supplier' : 'Add Supplier' }}</h4>
                            <div>
                                <a href="{{ $isCompanyModule ? route('company.suppliers.index') : route('suppliers.index') }}"
                                    class="btn btn-secondary btn-wave">
                                    Back to List
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-4">

                            <form id="agentAdminForm" method="POST"
                                action="{{ isset($supplier)
                                    ? ($isCompanyModule
                                        ? route('company.suppliers.update', $supplier->id)
                                        : route('suppliers.update', $supplier->id))
                                    : ($isCompanyModule
                                        ? route('company.suppliers.store')
                                        : route('suppliers.store')) }}">
                                @csrf
                                @if (isset($supplier))
                                    @method('PUT')
                                @endif
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
                                                                value="{{ old('contact_name', $supplier->contact_name ?? '') }}"
                                                                required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="account_number" class="form-label">Account Number</label>
                                                            <input type="text" class="form-control" id="account_number"
                                                                name="account_number"
                                                                value="{{ old('account_number', $supplier->account_number ?? '') }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="phone" class="form-label">Phone Number</label>
                                                            <input type="text" class="form-control" id="phone"
                                                                name="phone"
                                                                value="{{ old('phone', $supplier->phone ?? '') }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="email" class="form-label">Email</label>
                                                            <input type="email" class="form-control" id="email"
                                                                name="email"
                                                                value="{{ old('email', $supplier->email ?? '') }}">
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
                                                                value="{{ old('first_name', $supplier->first_name ?? '') }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="last_name" class="form-label">Last Name</label>
                                                            <input type="text" class="form-control" id="last_name"
                                                                name="last_name"
                                                                value="{{ old('last_name', $supplier->last_name ?? '') }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="website" class="form-label">Website</label>
                                                            <input type="url" class="form-control" id="website"
                                                                name="website"
                                                                value="{{ old('website', $supplier->website ?? '') }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="company_reg" class="form-label">Company Reg No</label>
                                                            <input type="text" class="form-control" id="company_reg"
                                                                name="company_reg"
                                                                value="{{ old('company_reg', $supplier->company_reg ?? '') }}">
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
                                                            <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="4000">{{ old('notes', $supplier->notes ?? '') }}</textarea>
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
                                                                value="{{ old('billing_address', $supplier->billing_address ?? '') }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="delivery_address" class="form-label">Delivery Address</label>
                                                            <input type="text" class="form-control"
                                                                id="delivery_address" name="delivery_address"
                                                                value="{{ old('delivery_address', $supplier->delivery_address ?? '') }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="city" class="form-label">City</label>
                                                            <input type="text" class="form-control" id="city"
                                                                name="city"
                                                                value="{{ old('city', $supplier->city ?? '') }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="postal_code" class="form-label">Postal Code</label>
                                                            <input type="text" class="form-control" id="postal_code"
                                                                name="postal_code"
                                                                value="{{ old('postal_code', $supplier->postal_code ?? '') }}">
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
                                                                value="{{ old('bank_account_name', $supplier->bank_account_name ?? '') }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="sort_code" class="form-label">Sort Code</label>
                                                            <input type="text" class="form-control" id="sort_code"
                                                                name="sort_code"
                                                                value="{{ old('sort_code', $supplier->sort_code ?? '') }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="bank_account_number" class="form-label">Bank Account No</label>
                                                            <input type="text" class="form-control"
                                                                id="bank_account_number" name="bank_account_number"
                                                                value="{{ old('bank_account_number', $supplier->bank_account_number ?? '') }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="reference" class="form-label">Reference</label>
                                                            <input type="text" class="form-control" id="reference"
                                                                name="reference"
                                                                value="{{ old('reference', $supplier->reference ?? '') }}">
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
                                                                value="{{ old('vat_number', $supplier->vat_number ?? '') }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="vat_status" class="form-label">VAT Status</label>
                                                            <select class="form-select" id="vat_status" name="vat_status">
                                                                <option value="">Select VAT status</option>
                                                                <option value="registered" {{ old('vat_status', $supplier->vat_status ?? '') == 'registered' ? 'selected' : '' }}>Registered</option>
                                                                <option value="not_registered" {{ old('vat_status', $supplier->vat_status ?? '') == 'not_registered' ? 'selected' : '' }}>Not Registered</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="tax_id" class="form-label">Tax ID</label>
                                                            <input type="text" class="form-control" id="tax_id"
                                                                name="tax_id"
                                                                value="{{ old('tax_id', $supplier->tax_id ?? '') }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="currency" class="form-label">Currency</label>
                                                            <select class="form-select" id="currency" name="currency">
                                                                <option value="">Select Currency</option>
                                                                <option value="USD" {{ old('currency', $supplier->currency ?? '') == 'USD' ? 'selected' : '' }}>USD</option>
                                                                <option value="EUR" {{ old('currency', $supplier->currency ?? '') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                                                <option value="GBP" {{ old('currency', $supplier->currency ?? '') == 'GBP' ? 'selected' : '' }}>GBP</option>
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
                                        <div class="section-content" id="business-details">
                                            <div class="custom-card mb-4">
                                                <div class="card-header fw-semibold">Business Details</div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="business_type" class="form-label">Business Type</label>
                                                            <select class="form-select" id="business_type" name="business_type">
                                                                <option value="">Select Type</option>
                                                                <option value="manufacturer" {{ old('business_type', $supplier->business_type ?? '') == 'manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                                                                <option value="distributor" {{ old('business_type', $supplier->business_type ?? '') == 'distributor' ? 'selected' : '' }}>Distributor</option>
                                                                <option value="wholesaler" {{ old('business_type', $supplier->business_type ?? '') == 'wholesaler' ? 'selected' : '' }}>Wholesaler</option>
                                                                <option value="retailer" {{ old('business_type', $supplier->business_type ?? '') == 'retailer' ? 'selected' : '' }}>Retailer</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="industry" class="form-label">Industry</label>
                                                            <input type="text" class="form-control" id="industry"
                                                                name="industry"
                                                                value="{{ old('industry', $supplier->industry ?? '') }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="established_date" class="form-label">Established Date</label>
                                                            <input type="date" class="form-control"
                                                                id="established_date" name="established_date"
                                                                value="{{ old('established_date', $supplier->established_date ?? '') }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="employee_count" class="form-label">Employee Count</label>
                                                            <input type="number" class="form-control"
                                                                id="employee_count" name="employee_count"
                                                                value="{{ old('employee_count', $supplier->employee_count ?? '') }}">
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
                                                            <select class="form-select" id="payment_terms" name="payment_terms">
                                                                <option value="">Select Terms</option>
                                                                <option value="net_15" {{ old('payment_terms', $supplier->payment_terms ?? '') == 'net_15' ? 'selected' : '' }}>Net 15</option>
                                                                <option value="net_30" {{ old('payment_terms', $supplier->payment_terms ?? '') == 'net_30' ? 'selected' : '' }}>Net 30</option>
                                                                <option value="net_60" {{ old('payment_terms', $supplier->payment_terms ?? '') == 'net_60' ? 'selected' : '' }}>Net 60</option>
                                                                <option value="net_90" {{ old('payment_terms', $supplier->payment_terms ?? '') == 'net_90' ? 'selected' : '' }}>Net 90</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="credit_limit" class="form-label">Credit Limit</label>
                                                            <input type="number" class="form-control" id="credit_limit"
                                                                name="credit_limit" step="0.01"
                                                                value="{{ old('credit_limit', $supplier->credit_limit ?? '') }}">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="discount_percentage" class="form-label">Discount Percentage</label>
                                                            <input type="number" class="form-control"
                                                                id="discount_percentage" name="discount_percentage"
                                                                min="0" max="100" step="0.01"
                                                                value="{{ old('discount_percentage', $supplier->discount_percentage ?? '') }}">
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="payment_method" class="form-label">Payment Method</label>
                                                            <select class="form-select" id="payment_method" name="payment_method">
                                                                <option value="">Select Method</option>
                                                                <option value="bank_transfer" {{ old('payment_method', $supplier->payment_method ?? '') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                                                <option value="check" {{ old('payment_method', $supplier->payment_method ?? '') == 'check' ? 'selected' : '' }}>Check</option>
                                                                <option value="cash" {{ old('payment_method', $supplier->payment_method ?? '') == 'cash' ? 'selected' : '' }}>Cash</option>
                                                                <option value="credit_card" {{ old('payment_method', $supplier->payment_method ?? '') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                                            </select>
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
                                                            <select class="form-select" id="status" name="status">
                                                                <option value="active" {{ old('status', $supplier->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                                                <option value="inactive" {{ old('status', $supplier->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                                <option value="pending" {{ old('status', $supplier->status ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                                <option value="suspended" {{ old('status', $supplier->status ?? '') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="rating" class="form-label">Supplier Rating</label>
                                                            <select class="form-select" id="rating" name="rating">
                                                                <option value="">Select Rating</option>
                                                                <option value="5" {{ old('rating', $supplier->rating ?? '') == '5' ? 'selected' : '' }}>5 Star</option>
                                                                <option value="4" {{ old('rating', $supplier->rating ?? '') == '4' ? 'selected' : '' }}>4 Star</option>
                                                                <option value="3" {{ old('rating', $supplier->rating ?? '') == '3' ? 'selected' : '' }}>3 Star</option>
                                                                <option value="2" {{ old('rating', $supplier->rating ?? '') == '2' ? 'selected' : '' }}>2 Star</option>
                                                                <option value="1" {{ old('rating', $supplier->rating ?? '') == '1' ? 'selected' : '' }}>1 Star</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="preferred_supplier" class="form-label">Preferred Supplier</label>
                                                            <select class="form-select" id="preferred_supplier" name="preferred_supplier">
                                                                <option value="0" {{ old('preferred_supplier', $supplier->preferred_supplier ?? '0') == '0' ? 'selected' : '' }}>No</option>
                                                                <option value="1" {{ old('preferred_supplier', $supplier->preferred_supplier ?? '0') == '1' ? 'selected' : '' }}>Yes</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="last_order_date" class="form-label">Last Order Date</label>
                                                            <input type="date" class="form-control"
                                                                id="last_order_date" name="last_order_date"
                                                                value="{{ old('last_order_date', $supplier->last_order_date ?? '') }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="text-center mt-4 mb-4">
                                    <button type="submit" class="teal-custom-btn p-2 me-2" id="saveAllBtn">
                                        {{ isset($supplier) ? 'Update Supplier' : 'Save Supplier' }}
                                    </button>
                                    <button type="reset" class="btn btn-lg rounded-0"
                                        style="background-color: #b4e1ef; padding: 0.4rem 0.8rem; color: #fff;"
                                        id="resetBtn">
                                        Reset Form
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection