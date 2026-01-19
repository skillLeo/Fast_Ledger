{{-- ========================================================================
     SALES INVOICE FORM
     For: Sales Invoice, Sales Credit, Purchase, Purchase Credit, Journal
     ======================================================================== --}}
<style>
    .custom-border {
        border: 1px solid #000 !important;
    }
</style>
<div class="sales-invoice-form" id="salesInvoiceForm">
    <form method="POST" action="{{ route('transactions.store') }}" id="salesInvoiceTransactionForm">
        @csrf

        {{-- Hidden Fields --}}
        <input type="hidden" name="current_payment_type" id="salesInvoicePaymentType" value="{{ $paymentType }}">
        <input type="hidden" name="account_type" value="office">
        <input type="hidden" name="Amount" id="hiddenMainAmount" value="0">
        <input type="hidden" name="invoice_documents" id="invoiceDocuments_data" value="[]">

        @if (isset($editData) && $editData)
            <input type="hidden" name="edit_invoice_id" value="{{ $editData['invoice']->id }}">
        @endif

        <div class="background-light">

            {{-- Header Row --}}
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class="row">

                        {{-- Customer Field --}}
                        <div class="col-md-2">
                            <div class="mb-1">
                                <label class="form-label fw-bold" id="customerFieldLabel">Customer</label>
                                <select name="file_id" id="customerDropdown" 
                                    class="form-select custom-border p-1 rounded-0 @error('chart_of_account_id') is-invalid @enderror">
                                    <option value="">Select Customer</option>
                                </select>
                                @error('chart_of_account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Invoice Date --}}
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label fw-bold" id="invoiceDateLabel">Invoice Date</label>
                                <input type="date" name="Transaction_Date" value="{{ date('Y-m-d') }}"
                                    class="form-control custom-border rounded-0 @error('Transaction_Date') is-invalid @enderror">
                                @error('Transaction_Date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Due Date --}}
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Due Date</label>
                                <input type="date" name="Inv_Due_Date"
                                    class="form-control custom-border rounded-0 @error('Inv_Due_Date') is-invalid @enderror">
                                @error('Inv_Due_Date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Invoice Number --}}
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold" id="invoiceNoLabel">Invoice No</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold custom-border p-1 rounded-0"
                                        id="invoicePrefix">{{ $currentPrefix }}</span>
                                    <input type="text" id="invoiceSuffix" class="form-control custom-border rounded-0 p-1"
                                        value="{{ str_pad($minSuffixNum, $suffixLen, '0', STR_PAD_LEFT) }}">
                                </div>
                                <div id="invoiceCodeMsg" class="mt-1"></div>
                                <input type="hidden" name="Transaction_Code" id="invoiceTransactionCode"
                                    value="{{ $autoCode }}">
                                <input type="hidden" name="invoice_no" id="invoiceNoHidden"
                                    value="{{ $autoCode }}">
                            </div>
                        </div>

                        {{-- Invoice Reference --}}
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold" id="invoiceRefLabel">Invoice Ref</label>
                                <input type="text" name="invoice_ref" value="{{ old('invoice_ref') }}"
                                    class="form-control custom-border rounded-0 @error('invoice_ref') is-invalid @enderror"
                                    placeholder="Invoice Reference">
                                @error('invoice_ref')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Invoice Items Table --}}
            @include('admin.day_book._partials._invoice-items-table')

            {{-- Journal Entries Table --}}
            @include('admin.day_book._partials._journal-entries-table')

            {{-- Notes Editor --}}
            @include('admin.day_book._partials._notes-editor')

            {{-- ✅ NEW: Activity Log Button Section (ONLY in Edit Mode) --}}
            @if(isset($editData) && $editData)
            <div class="mb-4">
                <h6><strong>Activity History</strong></h6>
                
                {{-- View Activity Log Button --}}
                <button class="btn addbutton" id="viewActivityLogBtn" type="button" 
                        onclick="showInvoiceActivityLog({{ $editData['invoice']->id }})">
                    <span>
                        <i class="fas fa-history"></i> View Activity Log
                    </span>
                </button>
                
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Track all changes and actions performed on this invoice
                </small>
            </div>
            @endif

            {{-- Hidden Summary Fields --}}
            <input type="hidden" name="invoice_net_amount" id="hiddenInvoiceNetAmount" value="0">
            <input type="hidden" name="invoice_vat_amount" id="hiddenInvoiceVATAmount" value="0">
            <input type="hidden" name="invoice_total_amount" id="hiddenInvoiceTotalAmount" value="0">

        </div>
    </form>
</div>

{{-- Include Product Modal --}}
@include('admin.day_book._modals._product-modal')
{{-- Include Invoice File Upload Modal --}}
@include('admin.day_book._modals._invoice-file-upload-modal')

<style>
/* Activity Log Button Styles - Matches Notes Button */
#viewActivityLogBtn {
    transition: all 0.3s ease;
}

#viewActivityLogBtn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

#viewActivityLogBtn i {
    transition: transform 0.3s ease;
}

#viewActivityLogBtn:hover i {
    transform: rotate(360deg);
}
</style>