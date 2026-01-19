{{-- ========================================================================
     TRANSACTION FORM HEADER
     Shows page title, breadcrumb, and action buttons
     ======================================================================== --}}

<div class="card-header d-flex justify-content-between align-items-center"
     @if (request()->get('type') === 'office' && in_array(request()->get('payment_type'), ['sales_invoice', 'journal'])) 
         style="margin-top: -5px;" 
     @endif>

    {{-- Page Title --}}
    <div class="page-title mb-3">
        @if (request()->routeIs('transactions.create') && request('payment_type') === 'sales_invoice')
            <h4 class="page-title">Invoicing</h4>
        
        @elseif(request()->routeIs('transactions.create') && request('payment_type') === 'journal')
            <h4 class="page-title">Journals</h4>
        
        @elseif(request()->routeIs('transactions.create') && request('payment_type') === 'payment')
            <span onclick="window.location='{{ route('transactions.index', ['view' => 'day_book']) }}'"
                  class="page-title me-2" style="cursor: pointer;">
                Banking
            </span>
            <span class="page-title" style="text-decoration: underline;">
                Office Account
            </span>
        
        @elseif(request()->routeIs('transactions.create') && request('type') === 'office' && !request()->has('payment_type'))
            <span onclick="window.location='{{ route('transactions.index', ['view' => 'day_book']) }}'"
                  class="page-title me-2" style="cursor: pointer;">
                Banking
            </span>
            <span class="page-title" style="text-decoration: underline;">
                Office Account
            </span>
        
        @elseif(request()->get('view') === 'day_book')
            <span onclick="window.location='{{ route('transactions.index', ['view' => 'day_book']) }}'"
                  class="page-title me-2" style="cursor: pointer;">
                Banking
            </span>
            <span class="page-title" style="text-decoration: underline;">
                Day Book
            </span>
        
        @else
            <h4 class="page-title" style="text-decoration: underline;">Banking</h4>
        @endif
    </div>

    {{-- Action Buttons --}}
    

</div>