<div class="row g-3">
    @if(!empty($breakdown['tax']))
    <div class="col-12">
        <h5 class="mb-3"><i class="fas fa-file-invoice-dollar me-2"></i> Income Tax Calculation</h5>
        
        <!-- Tax Bands -->
        @if(!empty($breakdown['tax']['bands']))
        <div class="mb-4">
            <h6 class="mb-3">Tax Bands</h6>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Band</th>
                            <th>Rate</th>
                            <th class="text-end">Taxable Income</th>
                            <th class="text-end">Tax Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($breakdown['tax']['bands'] as $band)
                        <tr>
                            <td>{{ ucfirst($band['name'] ?? 'Unknown') }}</td>
                            <td>{{ $band['rate'] ?? 0 }}%</td>
                            <td class="text-end">
                                £{{ number_format($band['income'] ?? 0, 2) }}
                            </td>
                            <td class="text-end fw-bold text-danger">
                                £{{ number_format($band['taxAmount'] ?? 0, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Pay, Pensions & Profit -->
        @if(!empty($breakdown['tax']['pay_pensions_profit']))
        <div class="mb-4">
            <h6 class="mb-3">Pay, Pensions & Profit</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <tbody>
                        @if(isset($breakdown['tax']['pay_pensions_profit']['totalSelfEmploymentProfit']))
                        <tr>
                            <td>Self-Employment Profit</td>
                            <td class="text-end fw-bold">
                                £{{ number_format($breakdown['tax']['pay_pensions_profit']['totalSelfEmploymentProfit'], 2) }}
                            </td>
                        </tr>
                        @endif

                        @if(isset($breakdown['tax']['pay_pensions_profit']['totalPayeEmploymentAndLumpSumIncome']))
                        <tr>
                            <td>PAYE Employment Income</td>
                            <td class="text-end fw-bold">
                                £{{ number_format($breakdown['tax']['pay_pensions_profit']['totalPayeEmploymentAndLumpSumIncome'], 2) }}
                            </td>
                        </tr>
                        @endif

                        @if(isset($breakdown['tax']['pay_pensions_profit']['totalBenefitsInKind']))
                        <tr>
                            <td>Benefits in Kind</td>
                            <td class="text-end fw-bold">
                                £{{ number_format($breakdown['tax']['pay_pensions_profit']['totalBenefitsInKind'], 2) }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Savings & Gains -->
        @if(!empty($breakdown['tax']['savings_and_gains']))
        <div class="mb-4">
            <h6 class="mb-3">Savings & Gains</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <tbody>
                        @if(isset($breakdown['tax']['savings_and_gains']['totalIncome']))
                        <tr>
                            <td>Total Savings Income</td>
                            <td class="text-end fw-bold">
                                £{{ number_format($breakdown['tax']['savings_and_gains']['totalIncome'], 2) }}
                            </td>
                        </tr>
                        @endif

                        @if(isset($breakdown['tax']['savings_and_gains']['taxableIncome']))
                        <tr>
                            <td>Taxable Savings Income</td>
                            <td class="text-end fw-bold">
                                £{{ number_format($breakdown['tax']['savings_and_gains']['taxableIncome'], 2) }}
                            </td>
                        </tr>
                        @endif

                        @if(isset($breakdown['tax']['savings_and_gains']['incomeTaxAmount']))
                        <tr>
                            <td>Tax on Savings</td>
                            <td class="text-end fw-bold text-danger">
                                £{{ number_format($breakdown['tax']['savings_and_gains']['incomeTaxAmount'], 2) }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Dividends -->
        @if(!empty($breakdown['tax']['dividends']))
        <div class="mb-4">
            <h6 class="mb-3">Dividends</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <tbody>
                        @if(isset($breakdown['tax']['dividends']['totalIncome']))
                        <tr>
                            <td>Total Dividend Income</td>
                            <td class="text-end fw-bold">
                                £{{ number_format($breakdown['tax']['dividends']['totalIncome'], 2) }}
                            </td>
                        </tr>
                        @endif

                        @if(isset($breakdown['tax']['dividends']['taxableIncome']))
                        <tr>
                            <td>Taxable Dividend Income</td>
                            <td class="text-end fw-bold">
                                £{{ number_format($breakdown['tax']['dividends']['taxableIncome'], 2) }}
                            </td>
                        </tr>
                        @endif

                        @if(isset($breakdown['tax']['dividends']['incomeTaxAmount']))
                        <tr>
                            <td>Tax on Dividends</td>
                            <td class="text-end fw-bold text-danger">
                                £{{ number_format($breakdown['tax']['dividends']['incomeTaxAmount'], 2) }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
    @else
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No tax calculation data available yet.
        </div>
    </div>
    @endif
</div>

