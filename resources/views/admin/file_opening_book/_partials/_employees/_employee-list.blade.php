{{-- Employee List Table --}}
<div class="left-side" style="border: 1px solid #dee2e6 !important;">
    <div class="ledger-table-container rounded-0 p-4">
        <!-- Fixed Header Table -->
        <table class="table account-table">
            <thead>
                <tr>
                    <th style="text-align: left; padding-left:4px !important; width: 60%;">Employee Name</th>
                    <th style="width: 40%;">Balance</th>
                </tr>
            </thead>
        </table>

        <!-- Scrollable Body Wrapper -->
        <div class="ledger-tbody-wrapper">
            <table class="table account-table">
                <colgroup>
                    <col style="width: 60%;">
                    <col style="width: 40%;">
                </colgroup>
                <tbody id="employee-table-body">
                    @forelse($employees as $employee)
                        <tr class="employee-row" 
                            data-employee-id="{{ $employee->id }}" 
                            data-employee-balance="{{ number_format($employee->balance ?? 0, 2) }}"
                            style="cursor: pointer;">
                            <td data-column="employeename" style="text-align: left">
                                {{ $employee->ni_number }} - {{ $employee->full_name }}
                            </td>
                            <td data-column="balance">
                                {{ number_format($employee->balance ?? 0, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center">No employees found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>