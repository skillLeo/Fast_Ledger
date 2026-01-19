{{-- Transactions Table --}}
<div id="tables-container-wrapper">
    <div class="table-container" style="width: 100%;">
        <!-- Fixed Header Table -->
        <table class="table account-table mb-0" style="width: 100%; table-layout: fixed !important;">
            <colgroup>
                <col style="width: 10%;">
                <col style="width: 25%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 10%;">
                <col style="width: 15%;">
            </colgroup>
            <thead>
                <tr>
                    <th colspan="2" class="details-head text-center">Details</th>
                    <th colspan="3" class="office-head text-center">Office Account</th>
                    <th colspan="3" class="client-head text-center">Client Account</th>
                </tr>
                <tr>
                    <th class="details-head text-center">Date</th>
                    <th class="details-head text-center">Description</th>
                    <th class="office-head text-end">Debit</th>
                    <th class="office-head text-end">Credit</th>
                    <th class="office-head text-end">Balance</th>
                    <th class="client-head text-end">Debit</th>
                    <th class="client-head text-end">Credit</th>
                    <th class="client-head text-end">Balance</th>
                </tr>
            </thead>
        </table>

        <!-- Scrollable Body Wrapper -->
        <div id="tables-container">
            <table class="table account-table mb-0" style="width: 100%; table-layout: fixed !important;">
                <colgroup>
                    <col style="width: 10%;">
                    <col style="width: 25%;">
                    <col style="width: 10%;">
                    <col style="width: 10%;">
                    <col style="width: 10%;">
                    <col style="width: 10%;">
                    <col style="width: 10%;">
                    <col style="width: 15%;">
                </colgroup>
                <tbody id="combined-table-body">
                    <!-- Dynamic content will be loaded here -->
                </tbody>
            </table>
        </div>
    </div>
</div>
