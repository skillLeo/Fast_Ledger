{{-- ========================================================================
     JOURNAL ENTRIES TABLE
     Double-entry bookkeeping rows (Debit/Credit)
     ======================================================================== --}}

<div class="journal-items-section" id="journalItemsSection" style="display: none;">
    
    {{-- Tax Type Selector --}}
    <div class="d-flex justify-content-end align-items-center mb-3">
        <label for="taxType" class="me-2 mb-0">Amounts are</label>
        <select id="taxType" class="form-select form-select-sm w-auto shadow-none">
            <option selected>No Tax</option>
            <option>Tax Inclusive</option>
            <option>Tax Exclusive</option>
        </select>
    </div>

    {{-- Journal Table --}}
    <div class="table-responsive">
        <table class="table table-bordered align-middle text-nowrap mb-0">
            <thead class="table-light text-center">
                <tr>
                    <th style="width: 30px;"></th>
                    <th style="width: 25%">Description</th>
                    <th style="width: 15%;">Ledger Ref</th>
                    <th style="width: 15%;">Account Ref</th>
                    <th style="width: 10%">Tax Rate</th>
                    <th style="width: 10%">Region</th>
                    <th style="width: 10%">Debit GBP</th>
                    <th style="width: 10%">Credit GBP</th>
                    <th style="width: 5%"></th>
                </tr>
            </thead>
            <tbody id="journalRows">
                {{-- Template Row (hidden, cloned by JS) --}}
                <tr id="journalRowTemplate" class="d-none" data-template-row>
                    <td class="text-center align-middle">
                        <i class="fas fa-grip-vertical drag-handle text-muted"></i>
                    </td>
                    <td>
                        <input type="text" class="border-0 bg-transparent shadow-none" 
                               placeholder="Description">
                    </td>
                    <td>
                        <select class="form-select form-select-sm border-0 bg-transparent shadow-none">
                            <option value="">Select Ledger</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-select form-select-sm border-0 bg-transparent shadow-none">
                            <option value="">Select Account</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" class="border-0 bg-transparent shadow-none text-center" 
                               placeholder="0%">
                    </td>
                    <td>
                        <input type="text" class="border-0 bg-transparent shadow-none" 
                               placeholder="Region">
                    </td>
                    <td>
                        <input type="number" class="border-0 bg-transparent shadow-none text-end" 
                               step="0.01" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" class="border-0 bg-transparent shadow-none text-end" 
                               step="0.01" placeholder="0.00">
                    </td>
                    <td class="text-center">
                        <button type="button" 
                                class="btn btn-danger btn-sm remove-btn" 
                                style="width: 20px; height: 20px;">×</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Add New Line Button --}}
    <div class="mt-3">
        <button type="button" id="addJournalLineBtn" class="btn teal-custom">
            <i class="fas fa-plus me-1"></i>Add a new line
        </button>
    </div>

    {{-- Totals Section --}}
    <div class="row mt-4">
        <div class="col-md-9"></div>
        <div class="col-md-3">
            
            {{-- Subtotal --}}
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                <span class="fw-semibold">Subtotal</span>
                <div class="d-flex gap-4">
                    <div class="text-end" style="min-width: 80px;">
                        <span id="journalSubtotalDebit">0.00</span>
                    </div>
                    <div class="text-end" style="min-width: 80px;">
                        <span id="journalSubtotalCredit">0.00</span>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 fw-bold">
                <span>Total VAT</span>
                <div class="d-flex gap-4">
                    <div class="text-end" style="min-width: 80px;">
                        <span id="journalTotalDebitVat">0.00</span>
                    </div>
                    <div class="text-end" style="min-width: 80px;">
                        <span id="journalTotalCreditVat">0.00</span>
                    </div>
                </div>
            </div>


            {{-- Total --}}
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 fw-bold">
                <span>Total</span>
                <div class="d-flex gap-4">
                    <div class="text-end" style="min-width: 80px;">
                        <span id="journalTotalDebit">0.00</span>
                    </div>
                    <div class="text-end" style="min-width: 80px;">
                        <span id="journalTotalCredit">0.00</span>
                    </div>
                </div>
            </div>

            {{-- Balance Check --}}
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-semibold text-danger">Total is out by:</span>
                <div class="text-end fw-bold">
                    <span id="journalBalanceDifference" class="text-danger">0.00</span>
                </div>
            </div>

            {{-- Balance Status --}}
            <div class="mt-2">
                <small id="balanceStatus" class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Journal entries must balance (Debit = Credit)
                </small>
            </div>

        </div>
    </div>

</div>