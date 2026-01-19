<div class="row g-3">
    @if(!empty($breakdown['allowances']))
    <div class="col-12">
        <h5 class="mb-3"><i class="fas fa-gift me-2"></i> Allowances & Deductions</h5>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($breakdown['allowances']['personalAllowance']))
                    <tr>
                        <td>Personal Allowance</td>
                        <td class="text-end fw-bold text-success">
                            @php
                                $value = $breakdown['allowances']['personalAllowance'];
                                $amount = is_numeric($value) ? $value : (is_array($value) ? ($value['amount'] ?? 0) : 0);
                            @endphp
                            £{{ number_format($amount, 2) }}
                        </td>
                    </tr>
                    @endif

                    @if(isset($breakdown['allowances']['giftAidTax']))
                    <tr>
                        <td>Gift Aid Tax</td>
                        <td class="text-end text-success">
                            @php
                                $value = $breakdown['allowances']['giftAidTax'];
                                $amount = is_numeric($value) ? $value : (is_array($value) ? ($value['amount'] ?? 0) : 0);
                            @endphp
                            £{{ number_format($amount, 2) }}
                        </td>
                    </tr>
                    @endif

                    @if(isset($breakdown['allowances']['marriageAllowanceTransferOut']))
                    <tr>
                        <td>Marriage Allowance Transfer Out</td>
                        <td class="text-end text-success">
                            @php
                                $value = $breakdown['allowances']['marriageAllowanceTransferOut'];
                                $amount = is_numeric($value) ? $value : (is_array($value) ? ($value['amount'] ?? 0) : 0);
                            @endphp
                            £{{ number_format($amount, 2) }}
                        </td>
                    </tr>
                    @endif

                    @if(isset($breakdown['allowances']['pensionContributions']))
                    <tr>
                        <td>Pension Contributions</td>
                        <td class="text-end text-success">
                            @php
                                $value = $breakdown['allowances']['pensionContributions'];
                                $amount = is_numeric($value) ? $value : (is_array($value) ? ($value['amount'] ?? 0) : 0);
                            @endphp
                            £{{ number_format($amount, 2) }}
                        </td>
                    </tr>
                    @endif

                    @if(isset($breakdown['allowances']['totalAllowancesAndDeductions']))
                    <tr class="border-top">
                        <td class="fw-bold">Total Allowances & Deductions</td>
                        <td class="text-end fw-bold text-success fs-5">
                            @php
                                $value = $breakdown['allowances']['totalAllowancesAndDeductions'];
                                $amount = is_numeric($value) ? $value : (is_array($value) ? ($value['amount'] ?? 0) : 0);
                            @endphp
                            £{{ number_format($amount, 2) }}
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No allowances and deductions data available in this calculation.
        </div>
    </div>
    @endif
</div>

