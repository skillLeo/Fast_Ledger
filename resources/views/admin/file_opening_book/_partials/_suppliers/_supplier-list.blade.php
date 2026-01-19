{{-- resources/views/admin/file_opening_book/_partials/_suppliers/_supplier-list.blade.php --}}

<div class="left-side" style="border: 1px solid #dee2e6 !important;">
    <div class="ledger-table-container rounded-0 p-4">
        <table class="table account-table">
            <thead>
                <tr>
                    <th style="text-align: left; padding-left:4px !important; width: 60%;">Supplier Name</th>
                    <th style="width: 40%;">Balance</th>
                </tr>
            </thead>
        </table>

        <div class="ledger-tbody-wrapper">
            <table class="table account-table">
                <colgroup>
                    <col style="width: 60%;">
                    <col style="width: 40%;">
                </colgroup>
                <tbody id="supplier-table-body">
                    @forelse($suppliers as $supplier)
                        <tr class="supplier-row" 
                            data-supplier-id="{{ $supplier->id }}"
                            data-supplier-ref="{{ $supplier->account_number }}"
                            data-contact-name="{{ $supplier->contact_name }}"
                            data-first-name="{{ $supplier->first_name }}"
                            data-last-name="{{ $supplier->last_name }}"
                            data-email="{{ $supplier->email }}"
                            data-phone="{{ $supplier->phone }}"
                            data-billing-address="{{ $supplier->billing_address }}"
                            data-website="{{ $supplier->website }}"
                            data-vat-number="{{ $supplier->vat_number }}"
                            data-payment-terms="{{ $supplier->payment_terms }}"
                            data-status="{{ $supplier->status }}"
                            style="cursor: pointer;">
                            <td data-column="suppliername" style="text-align: left">
                                {{ $supplier->account_number }} - {{ $supplier->contact_name ?? ($supplier->first_name . ' ' . $supplier->last_name) }}
                            </td>
                            <td data-column="balance">
                                {{ number_format($supplier->balance ?? 0, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center">No suppliers found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>