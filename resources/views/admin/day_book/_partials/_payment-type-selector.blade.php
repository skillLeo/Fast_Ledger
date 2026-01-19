{{-- ========================================================================
     PAYMENT TYPE SELECTOR BUTTONS
     Different button sets for different account types
     ======================================================================== --}}

<div class="payment-type-selection card-body">
    <div class="d-flex flex-wrap gap-2 mb-2">
        
        @if ($paymentType === 'journal')
            {{-- Journal Only --}}
            <button type="button" class="btn-simple active" data-payment-type="journal">
                Journal
            </button>

        @elseif($paymentType === 'payment' || $type === 'office')
            {{-- Office Account Types --}}
            <button type="button" 
                    class="btn-simple {{ $paymentType === 'payment' ? 'active' : '' }}" 
                    data-payment-type="payment">
                Payment
            </button>
            
            <button type="button" 
                    class="btn-simple {{ $paymentType === 'receipt' ? 'active' : '' }}" 
                    data-payment-type="receipt">
                Receipt
            </button>
            
            <button type="button" 
                    class="btn-simple {{ $paymentType === 'cheque' ? 'active' : '' }}" 
                    data-payment-type="cheque">
                Cheque
            </button>

            <button type="button" 
                    class="btn-simple {{ $paymentType === 'inter_bank_office' ? 'active' : '' }}" 
                    data-payment-type="inter_bank_office">
                Inter Bank Office
            </button>

        @else
            {{-- Sales/Purchase Types --}}
            <button type="button" 
                    class="btn-simple {{ $paymentType === 'sales_invoice' ? 'active' : '' }}" 
                    data-payment-type="sales_invoice">
                Sales Invoice
            </button>
            
            <button type="button" 
                    class="btn-simple {{ $paymentType === 'sales_credit' ? 'active' : '' }}" 
                    data-payment-type="sales_credit">
                Sales Credit
            </button>
            
            <button type="button" 
                    class="btn-simple {{ $paymentType === 'purchase' ? 'active' : '' }}" 
                    data-payment-type="purchase">
                Purchase
            </button>
            
            <button type="button" 
                    class="btn-simple {{ $paymentType === 'purchase_credit' ? 'active' : '' }}" 
                    data-payment-type="purchase_credit">
                Purchase Credit
            </button>
        @endif
        
    </div>
</div>