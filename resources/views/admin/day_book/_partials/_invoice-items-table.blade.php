{{-- ========================================================================
     INVOICE ITEMS TABLE
     Line items for Sales Invoice, Purchase, Credit Notes
     ======================================================================== --}}

<div class="invoice-items-section" id="invoiceItemsSection" style="display: none;">
    
    <div class="table-responsive">
        <table class="invoice-table table table-bordered">
            <thead>
                <tr>
                    <th style="width: 30px;"></th>
                    <th style="width: 10%;">Item Code</th>
                    <th style="width: 16%;">Description</th>
                    <th style="width: 12%;">Ledger Ref</th>
                    <th style="width: 12%;">Account Ref</th>
                    <th style="width: 7%;">Qty</th> {{-- ✅ NEW QTY COLUMN --}}
                    <th style="width: 8%;">Unit Amount</th>
                    <th style="width: 7%;">VAT Rate</th>
                    <th style="width: 8%;">VAT Amount</th>
                    <th style="width: 8%;">Net Amount</th>
                    <th style="width: 60px;">Image</th>
                    <th style="width: 30px;"></th>
                </tr>
            </thead>
            <tbody id="invoiceItemsTable">
                {{-- Dynamic rows added by JavaScript --}}
            </tbody>
        </table>

        {{-- Add Item Button --}}
        <div class="table-actions mt-3">
            <button type="button" class="btn teal-custom" id="addItemBtn">
                <i class="fas fa-plus me-1"></i>Add Row
            </button>

            <button type="button" class="btn teal-custom" id="addFileBtn">
                <i class="fas fa-plus me-1"></i>Add File
            </button>
        </div>

    </div>

    {{-- Summary Section --}}
    <div class="row mt-4">
        <div class="col-md-9"></div>
        <div class="col-md-3">
            <div class="summary-box">
                <div class="summary-row">
                    <span>Net Amount:</span>
                    <span id="summaryNetAmount">£0.00</span>
                </div>
                <div class="summary-row">
                    <span>Total VAT:</span>
                    <span id="summaryTotalVAT">£0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total Amount:</span>
                    <span id="summaryTotalAmount">£0.00</span>
                </div>
            </div>
        </div>
    </div>
</div>