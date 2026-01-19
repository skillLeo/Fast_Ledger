{{-- Supplier Transactions Table --}}
<div id="supplier-tables-container-wrapper">
    <div class="table-container" style="width: 100%;">
        <table class="table account-table mb-0" style="width: 100%; table-layout: fixed !important;">
            <colgroup>
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 20%;">
                <col style="width: 8%;">
                <col style="width: 8%;">
                <col style="width: 8%;">
                <col style="width: 12%;">
                <col style="width: 12%;">
                <col style="width: 12%;">
            </colgroup>
            <thead>
                <tr>
                    <th class="details-head text-center">Reference</th>
                    <th class="details-head text-center">Date</th>
                    <th class="details-head text-center">Due Date</th>
                    <th class="details-head text-center">Description</th>
                    <th class="office-head text-center">📄</th>
                    <th class="office-head text-center">📧</th>
                    <th class="office-head text-center">📎</th>
                    <th class="client-head text-end">Debit</th>
                    <th class="client-head text-end">Credit</th>
                    <th class="client-head text-end">Balance</th>
                </tr>
            </thead>
        </table>

        <div id="tables-container">
            <table class="table account-table mb-0" style="width: 100%; table-layout: fixed !important;">
                <colgroup>
                    <col style="width: 10%;">
                    <col style="width: 10%;">
                    <col style="width: 10%;">
                    <col style="width: 20%;">
                    <col style="width: 8%;">
                    <col style="width: 8%;">
                    <col style="width: 8%;">
                    <col style="width: 12%;">
                    <col style="width: 12%;">
                    <col style="width: 12%;">
                </colgroup>
                <tbody id="supplier-transaction-body">
                    <!-- Dynamic content -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="text-center mt-3" id="supplier-default-message">
    <h5>Select a supplier from the left to view details</h5>
</div>