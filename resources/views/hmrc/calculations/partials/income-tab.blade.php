<div class="row g-3">
    @if(!empty($breakdown['income']['businesses']))
    <div class="col-12">
        <h5 class="mb-3"><i class="fas fa-briefcase me-2"></i> Business Income</h5>
        
        @foreach($breakdown['income']['businesses'] as $business)
        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>Business ID:</strong> {{ $business['business_id'] }}
            </div>
            <div class="card-body">
                <!-- Income -->
                @if(!empty($business['income']))
                <h6 class="text-success mb-2">Income</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-borderless">
                        @foreach($business['income'] as $key => $value)
                            @if($value > 0)
                            <tr>
                                <td class="ps-0">{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                <td class="text-end pe-0 text-success">
                                    £{{ number_format($value, 2) }}
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </table>
                </div>
                @endif

                <!-- Expenses -->
                @if(!empty($business['expenses']))
                <h6 class="text-danger mb-2">Expenses</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-borderless">
                        @foreach($business['expenses'] as $key => $value)
                            @if($value > 0)
                            <tr>
                                <td class="ps-0">{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                                <td class="text-end pe-0 text-danger">
                                    £{{ number_format($value, 2) }}
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </table>
                </div>
                @endif

                <!-- Net Profit -->
                @if(isset($business['net_profit']))
                <div class="border-top pt-2">
                    <div class="d-flex justify-content-between">
                        <strong>Net Profit:</strong>
                        <strong class="{{ $business['net_profit'] >= 0 ? 'text-primary' : 'text-danger' }}">
                            £{{ number_format($business['net_profit'], 2) }}
                        </strong>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No business income data available in this calculation.
        </div>
    </div>
    @endif
</div>

