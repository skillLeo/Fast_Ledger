{{-- ========================================================================
     READ-ONLY INVOICE SUMMARY
     Displays totals with localization
     ======================================================================== --}}

<div class="row mb-4">
    <div class="col-md-8"></div>
    <div class="col-md-4">
        <div class="summary-box">
            <div class="summary-row">
                <span>{{ __('company.net_amount') }}:</span>
                <span class="fw-bold">£{{ number_format($invoiceData['invoice_net_amount'] ?? 0, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>{{ __('company.total_vat') }}:</span>
                <span class="fw-bold">£{{ number_format($invoiceData['invoice_vat_amount'] ?? 0, 2) }}</span>
            </div>
            <div class="summary-row total">
                <span>{{ __('company.total_amount') }}:</span>
                <span class="text-primary">£{{ number_format($invoiceData['invoice_total_amount'] ?? 0, 2) }}</span>
            </div>
        </div>
    </div>
</div>