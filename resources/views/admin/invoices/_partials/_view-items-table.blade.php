{{-- ========================================================================
     READ-ONLY INVOICE ITEMS TABLE
     FIXED: Shows ALL items (handles associative arrays)
     ======================================================================== --}}

<div class="invoice-items-section" id="invoiceItemsSection">
    
    <div class="table-responsive">
        <table class="invoice-table table table-bordered">
            <thead>
                <tr>
                    <th style="width: 12%;">{{ __('company.item_code') }}</th>
                    <th style="width: 18%;">{{ __('company.description') }}</th>
                    <th style="width: 14%;">{{ __('company.ledger_ref') }}</th>
                    <th style="width: 14%;">{{ __('company.account_ref') }}</th>
                    <th style="width: 9%;">{{ __('company.unit_amount') }}</th>
                    <th style="width: 7%;">{{ __('company.vat_rate') }}</th>
                    <th style="width: 9%;">{{ __('company.vat_amount') }}</th>
                    <th style="width: 9%;">{{ __('company.net_amount') }}</th>
                    <th style="width: 60px;">{{ __('company.image') }}</th>
                </tr>
            </thead>
            <tbody id="invoiceItemsTable">
                @php
                    // ✅ CRITICAL FIX: Handle both array and object structures
                    $itemsToDisplay = [];
                    
                    if (!empty($items)) {
                        // If items is already an array, use it directly
                        if (is_array($items)) {
                            $itemsToDisplay = $items;
                        }
                        // If items is a string (JSON), decode it
                        elseif (is_string($items)) {
                            $itemsToDisplay = json_decode($items, true) ?? [];
                        }
                    }
                @endphp
                
                @if(!empty($itemsToDisplay))
                    @foreach($itemsToDisplay as $index => $item)
                        <tr>
                            {{-- Item Code --}}
                            <td>
                                <input type="text" value="{{ $item['item_code'] ?? '' }}" 
                                       class="border-0 bg-transparent shadow-none " 
                                       readonly 
                                       placeholder="{{ __('company.n_a') }}">
                            </td>
                            
                            {{-- Description --}}
                            <td>
                                <input type="text" value="{{ $item['description'] ?? '' }}" 
                                       class="border-0 bg-transparent shadow-none " 
                                       readonly>
                            </td>
                            
                            {{-- Ledger Ref --}}
                            <td>
                                @php
                                    $ledgerRef = '';
                                    if (!empty($item['ledger_id'])) {
                                        $chartAccount = \App\Models\ChartOfAccount::find($item['ledger_id']);
                                        if ($chartAccount) {
                                            $ledgerRef = $chartAccount->ledger_ref ?? '';
                                        }
                                    }
                                @endphp
                                <input type="text" value="{{ $ledgerRef }}" 
                                       class="border-0 bg-transparent shadow-none " 
                                       readonly 
                                       placeholder="{{ __('company.n_a') }}">
                            </td>
                            
                            {{-- Account Ref --}}
                            <td>
                                <input type="text" value="{{ $item['account_ref'] ?? '' }}" 
                                       class="border-0 bg-transparent shadow-none " 
                                       readonly 
                                       placeholder="{{ __('company.n_a') }}">
                            </td>
                            
                            {{-- Unit Amount --}}
                            <td>
                                <input type="text" value="{{ number_format($item['unit_amount'] ?? 0, 2) }}" 
                                       class="border-0 bg-transparent shadow-none  text-end" 
                                       readonly>
                            </td>
                            
                            {{-- VAT Rate --}}
                            <td>
                                @php
                                    $vatRate = 0;
                                    if (!empty($item['vat_form_label_id'])) {
                                        $vatType = \App\Models\VatFormLabel::find($item['vat_form_label_id']);
                                        if ($vatType) {
                                            $vatRate = $vatType->vat_rate ?? 0;
                                        }
                                    }
                                @endphp
                                <input type="text" value="{{ number_format($vatRate, 0) }}%" 
                                       class="border-0 bg-transparent shadow-none  text-center" 
                                       readonly>
                            </td>
                            
                            {{-- VAT Amount --}}
                            <td>
                                <input type="text" value="{{ number_format($item['vat_amount'] ?? 0, 2) }}" 
                                       class="border-0 bg-transparent shadow-none  text-end" 
                                       readonly>
                            </td>
                            
                            {{-- Net Amount --}}
                            <td>
                                <input type="text" value="{{ number_format($item['net_amount'] ?? 0, 2) }}" 
                                       class="border-0 bg-transparent shadow-none  text-end" 
                                       readonly>
                            </td>
                            
                            {{-- Product Image --}}
                            <td class="text-center">
                                @if(!empty($item['product_image']))
                                    <img src="{{ $item['product_image'] }}" 
                                         alt="{{ __('company.product_label') }}" 
                                         class="product-thumbnail"
                                         onclick="showImagePreview('{{ $item['product_image'] }}')"
                                         title="{{ __('company.view_full_size') }}">
                                @else
                                    <div class="no-image-placeholder">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            {{ __('company.no_items_found') }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Summary Section --}}
    <div class="row mt-4">
        <div class="col-md-9"></div>
        <div class="col-md-3">
            <div class="summary-box">
                <div class="summary-row">
                    <span>{{ __('company.net_amount') }}:</span>
                    <span id="summaryNetAmount">£{{ number_format($invoiceData['invoice_net_amount'] ?? 0, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span>{{ __('company.total_vat') }}:</span>
                    <span id="summaryTotalVAT">£{{ number_format($invoiceData['invoice_vat_amount'] ?? 0, 2) }}</span>
                </div>
                <div class="summary-row total">
                    <span>{{ __('company.total_amount') }}:</span>
                    <span id="summaryTotalAmount">£{{ number_format($invoiceData['invoice_total_amount'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

</div>