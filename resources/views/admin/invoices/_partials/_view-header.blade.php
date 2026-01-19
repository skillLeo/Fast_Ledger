{{-- ========================================================================
     READ-ONLY INVOICE HEADER
     Exact match to _invoice-form.blade.php header structure
     ======================================================================== --}}

<div class="row mt-2">
    <div class="col-md-12">
        <div class="row">

            {{-- Customer Field (Read-only) --}}
            <div class="col-md-2">
                <div class="mb-1">
                    <label class="form-label fw-bold">{{ __('company.customer') }}</label>
                    @php
                        $customerId = $invoiceData['customer_id'] ?? null; // ✅ FIXED: Use customer_id not file_id
                        $customerName = __('company.n_a');
                    
                        if ($customerId) {
                            // ✅ FIXED: Check context to determine which model to use
                            if ($isCompanyModule ?? false) {
                                // Company context: Look in customers table
                                $customer = \App\Models\CompanyModule\Customer::find($customerId);
                                // dd($customer);
                                if ($customer) {
                                    $customerName = $customer->Legal_Name_Company_Name ?? ($customer->Legal_Name_Company_Name ?? __('company.n_a'));
                                }
                            } else {
                                // Client context: Look in files table
                                $customer = \App\Models\File::find($customerId);
                                if ($customer) {
                                    $customerName = trim(
                                        ($customer->First_Name ?? '') . ' ' . ($customer->Last_Name ?? ''),
                                    );
                                    if (empty($customerName) || $customerName === ' ') {
                                        $customerName = $customer->Ledger_Ref ?? __('company.n_a');
                                    }
                                }
                            }
                        }
                    @endphp
                    <input type="text" value="{{ $customerName }}"
                        class="form-control custom-border p-1 rounded-0 read-only-input" readonly>
                </div>
            </div>

            {{-- Invoice Date (Read-only) --}}
            <div class="col-md-2">
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('company.invoice_date') }}</label>
                    <input type="text"
                        value="{{ isset($invoiceData['Transaction_Date']) ? \Carbon\Carbon::parse($invoiceData['Transaction_Date'])->format('d/m/Y') : __('company.n_a') }}"
                        class="form-control custom-border rounded-0 read-only-input" readonly>
                </div>
            </div>

            {{-- Due Date (Read-only) --}}
            <div class="col-md-2">
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('company.due_date') }}</label>
                    <input type="text"
                        value="{{ isset($invoiceData['Inv_Due_Date']) ? \Carbon\Carbon::parse($invoiceData['Inv_Due_Date'])->format('d/m/Y') : __('company.n_a') }}"
                        class="form-control custom-border rounded-0 read-only-input" readonly>
                </div>
            </div>

            {{-- Invoice Number (Read-only) --}}
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('company.invoice_no') }}</label>
                    @php
                        $invoiceNo = $invoiceData['invoice_no'] ?? __('company.n_a');
                        $prefix = preg_replace('/\d/', '', $invoiceNo);
                        $suffix = preg_replace('/\D/', '', $invoiceNo);
                    @endphp
                    <div class="input-group">
                        <span class="input-group-text bg-light fw-bold custom-border p-1 rounded-0">
                            {{ $prefix }}
                        </span>
                        <input type="text" value="{{ $suffix }}"
                            class="form-control custom-border rounded-0 p-1 read-only-input" readonly>
                    </div>
                </div>
            </div>

            {{-- Invoice Reference (Read-only) --}}
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('company.invoice_ref') }}</label>
                    <input type="text" value="{{ $invoiceData['invoice_ref'] ?? __('company.n_a') }}"
                        class="form-control custom-border rounded-0 read-only-input"
                        placeholder="{{ __('company.invoice_reference') }}" readonly>
                </div>
            </div>

        </div>
    </div>
</div>
