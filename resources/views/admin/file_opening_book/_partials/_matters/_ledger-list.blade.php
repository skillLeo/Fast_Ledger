{{-- Ledger List Table --}}
<div class="left-side" style="border: 1px solid #dee2e6 !important;">
    <div class="ledger-table-container rounded-0 p-4">
        <!-- Fixed Header Table -->
        <table class="table account-table">
            <thead>
                <tr>
                    <th style="text-align: left; padding-left:4px !important; width: auto;">Ledger Name</th>
                    <th style="width: 91px;">BAL (office)</th>
                    <th style="width: 97px;">BAL (client)</th>
                </tr>
            </thead>
        </table>

        <!-- Scrollable Body Wrapper -->
        <div class="ledger-tbody-wrapper">
            <table class="table account-table">
                <colgroup>
                    <col style="width: auto;">
                    <col style="width: 90px;">
                    <col style="width: 90px;">
                </colgroup>
                <tbody id="ledger-table-body">
                    @forelse($files as $file)
                        <tr class="ledger-row" data-ledger-ref="{{ $file->Ledger_Ref }}"
                            data-office-balance="{{ $file->office_balance ?? 0 }}"
                            data-client-balance="{{ $file->client_balance ?? 0 }}">
                            <td data-column="ledgerref" style="text-align: left">
                                {{ $file->Ledger_Ref }} - {{ $file->First_Name }} {{ $file->Last_Name }}
                            </td>
                            <td data-column="office-balance">
                                {{ number_format($file->office_balance ?? 0, 2) }}
                            </td>
                            <td data-column="client-balance">
                                {{ number_format($file->client_balance ?? 0, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">No files found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>