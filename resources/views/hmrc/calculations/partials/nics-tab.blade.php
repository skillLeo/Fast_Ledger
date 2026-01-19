<div class="row g-3">
    @if(!empty($breakdown['nics']))
    <div class="col-12">
        <h5 class="mb-3"><i class="fas fa-id-card me-2"></i> National Insurance Contributions</h5>
        
        <!-- Class 2 NICs -->
        @if(!empty($breakdown['nics']['class2']))
        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>Class 2 National Insurance</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless">
                        @if(isset($breakdown['nics']['class2']['amount']))
                        <tr>
                            <td class="ps-0">Class 2 NICs Amount</td>
                            <td class="text-end pe-0 fw-bold text-danger">
                                £{{ number_format($breakdown['nics']['class2']['amount'], 2) }}
                            </td>
                        </tr>
                        @endif

                        @if(isset($breakdown['nics']['class2']['weeklyRate']))
                        <tr>
                            <td class="ps-0">Weekly Rate</td>
                            <td class="text-end pe-0">
                                £{{ number_format($breakdown['nics']['class2']['weeklyRate'], 2) }}
                            </td>
                        </tr>
                        @endif

                        @if(isset($breakdown['nics']['class2']['weeks']))
                        <tr>
                            <td class="ps-0">Number of Weeks</td>
                            <td class="text-end pe-0">
                                {{ $breakdown['nics']['class2']['weeks'] }}
                            </td>
                        </tr>
                        @endif

                        @if(isset($breakdown['nics']['class2']['limit']))
                        <tr>
                            <td class="ps-0">Small Profits Threshold</td>
                            <td class="text-end pe-0">
                                £{{ number_format($breakdown['nics']['class2']['limit'], 2) }}
                            </td>
                        </tr>
                        @endif

                        @if(isset($breakdown['nics']['class2']['underSmallProfitThreshold']))
                        <tr>
                            <td class="ps-0">Under Small Profits Threshold?</td>
                            <td class="text-end pe-0">
                                <span class="badge {{ $breakdown['nics']['class2']['underSmallProfitThreshold'] ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $breakdown['nics']['class2']['underSmallProfitThreshold'] ? 'Yes' : 'No' }}
                                </span>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Class 4 NICs -->
        @if(!empty($breakdown['nics']['class4']))
        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>Class 4 National Insurance</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-borderless mb-3">
                        @if(isset($breakdown['nics']['class4']['totalAmount']))
                        <tr>
                            <td class="ps-0 fw-bold">Total Class 4 NICs</td>
                            <td class="text-end pe-0 fw-bold text-danger fs-5">
                                £{{ number_format($breakdown['nics']['class4']['totalAmount'], 2) }}
                            </td>
                        </tr>
                        @endif

                        @if(isset($breakdown['nics']['class4']['totalIncomeLiableToClass4Charge']))
                        <tr>
                            <td class="ps-0">Income Liable to Class 4</td>
                            <td class="text-end pe-0">
                                £{{ number_format($breakdown['nics']['class4']['totalIncomeLiableToClass4Charge'], 2) }}
                            </td>
                        </tr>
                        @endif
                    </table>

                    <!-- Class 4 Bands -->
                    @if(!empty($breakdown['nics']['class4']['nic4Bands']))
                    <h6 class="mb-2">Class 4 Bands</h6>
                    <table class="table table-striped table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Band</th>
                                <th>Rate</th>
                                <th class="text-end">Income</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($breakdown['nics']['class4']['nic4Bands'] as $band)
                            <tr>
                                <td>{{ ucfirst($band['name'] ?? 'Unknown') }}</td>
                                <td>{{ $band['rate'] ?? 0 }}%</td>
                                <td class="text-end">
                                    £{{ number_format($band['income'] ?? 0, 2) }}
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    £{{ number_format($band['amount'] ?? 0, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Total NICs Summary -->
        @php
            $totalNics = 0;
            if (isset($breakdown['nics']['class2']['amount'])) {
                $totalNics += $breakdown['nics']['class2']['amount'];
            }
            if (isset($breakdown['nics']['class4']['totalAmount'])) {
                $totalNics += $breakdown['nics']['class4']['totalAmount'];
            }
        @endphp

        @if($totalNics > 0)
        <div class="alert alert-primary">
            <div class="d-flex justify-content-between align-items-center">
                <strong>Total National Insurance Contributions:</strong>
                <strong class="fs-4">£{{ number_format($totalNics, 2) }}</strong>
            </div>
        </div>
        @endif
    </div>
    @else
    <div class="col-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No National Insurance data available in this calculation.
        </div>
    </div>
    @endif
</div>

