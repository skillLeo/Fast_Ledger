<div class="row g-3">
    @if(!empty($breakdown['summary']))
    <div class="col-12">
        <h5 class="mb-3"><i class="fas fa-chart-bar me-2"></i> Summary</h5>
        
        <div class="table-responsive">
            <table class="table table-borderless">
                <tbody>
                    @if(isset($breakdown['summary']['total_income_received']))
                    <tr>
                        <td class="ps-0">Total Income Received</td>
                        <td class="text-end pe-0 fw-bold text-primary">
                            £{{ number_format($breakdown['summary']['total_income_received'], 2) }}
                        </td>
                    </tr>
                    @endif
                    
                    @if(isset($breakdown['summary']['total_allowances_deducted']))
                    <tr>
                        <td class="ps-0">Less: Allowances & Deductions</td>
                        <td class="text-end pe-0 text-success">
                            -£{{ number_format($breakdown['summary']['total_allowances_deducted'], 2) }}
                        </td>
                    </tr>
                    @endif
                    
                    @if(isset($breakdown['summary']['total_taxable_income']))
                    <tr class="border-top">
                        <td class="ps-0 fw-bold">Total Taxable Income</td>
                        <td class="text-end pe-0 fw-bold text-info fs-5">
                            £{{ number_format($breakdown['summary']['total_taxable_income'], 2) }}
                        </td>
                    </tr>
                    @endif
                    
                    @if(isset($breakdown['summary']['income_tax_charged']))
                    <tr class="border-top">
                        <td class="ps-0">Income Tax Charged</td>
                        <td class="text-end pe-0 fw-bold text-danger">
                            £{{ number_format($breakdown['summary']['income_tax_charged'], 2) }}
                        </td>
                    </tr>
                    @endif
                    
                    @if(isset($breakdown['summary']['total_income_tax_and_nics_due']))
                    <tr class="border-top">
                        <td class="ps-0 fw-bold fs-5">Total Tax & NICs Due</td>
                        <td class="text-end pe-0 fw-bold text-danger fs-4">
                            £{{ number_format($breakdown['summary']['total_income_tax_and_nics_due'], 2) }}
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
            Summary data not available yet. The calculation may still be processing.
        </div>
    </div>
    @endif
</div>

