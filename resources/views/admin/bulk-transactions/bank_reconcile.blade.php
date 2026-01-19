@extends('admin.layout.app')
<style>
    .inner-card {
        background-color: #fff;
        border: 1px solid #6b6561 !important;
    }

    @media (min-width: 1900px) {
        .col-md-6 {
            flex: 0 0 33.333333% !important;
            max-width: 33.333333% !important;
        }
    }
</style>
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header align-items-center">
                            <span class="page-title">Banking</span>

                        </div>

                        <div class="d-flex justify-content-between">
                            <div class="d-flex flex-wrap gap-4">
                                @if ($hasConnectedBanks)
                                    {{-- 🆕 Show Bank Feed for connected banks --}}
                                    <a href="{{ route('finexer.settings') }}" class="nav-link-btn active">
                                        </i> Bank Feed
                                    </a>
                                @else
                                    {{-- 🆕 Show Upload Transactions for manual banks --}}
                                    <a href="{{ route('bulk-transactions.upload') }}" class="nav-link-btn active">
                                        Upload Transactions
                                    </a>
                                @endif

                                <a href="{{ route('transactions.index', ['view' => 'day_book']) }}" class="nav-link-btn">
                                    Manual Entry
                                </a>
                            </div>

                            {{-- bank_reconcile.blade.php --}}

                            <div class="d-flex flex-wrap gap-2" style="margin-right: -7px">
                                <a href="{{ route('banks.create', auth()->id()) }}" class="btn addbutton btn-wave">
                                    <i class="fas fa-plus me-1"></i>New Bank
                                </a>

                                @if ($hasConnectedBanks)
                                    <a href="{{ route('finexer.connect') }}" class="btn addbutton">
                                        <i class="fas fa-link me-1"></i> Connect Bank
                                    </a>
                                    <button class="btn addbutton" onclick="syncAllBanks()">
                                        <i class="fas fa-sync-alt me-1"></i> Sync All
                                    </button>

                                    {{-- 🆕 Show Import if session enabled --}}
                                    @if (session('show_import_button', false))
                                        <a href="{{ route('bulk-transactions.upload') }}" class="btn addbutton">
                                            <i class="fas fa-file-import me-1"></i> Import Transaction
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('bulk-transactions.upload') }}" class="btn addbutton">
                                        <i class="fas fa-file-import me-1"></i> Import Transaction
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="row mt-4">
                            @forelse ($bankAccounts  as $index => $account)
                                <div class="col-md-6 col-1900-4 mb-4">

                                    <div class="inner-card p-4 shadow-sm">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $account->logo }}" alt="{{ $account->bank_name }}"
                                                    class="bank-logo me-3">

                                                <div>
                                                    <h5 class="mb-0">
                                                        {{ $account->bank_name }}

                                                        {{-- 🆕 Connection Status Badge --}}
                                                        @if ($account->is_connected ?? false)
                                                            <span class="badge bg-success ms-2" style="font-size: 10px;">
                                                                <i class="fas fa-check-circle"></i> Connected
                                                            </span>
                                                        @else
                                                            <span class="badge bg-warning ms-2" style="font-size: 10px;">
                                                                <i class="fas fa-upload"></i> not connected
                                                            </span>
                                                        @endif
                                                    </h5>
                                                    <small>
                                                        Sort Code: {{ $account->sort_code }} |
                                                        Account No:
                                                        {{ substr($account->account_number, -4) ? '****' . substr($account->account_number, -4) : $account->account_number }}
                                                    </small>
                                                </div>
                                            </div>
                                            <!-- Filter Buttons -->

                                            <button class="btn btn-link">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                        </div>


                                        <div class="d-flex justify-content-between align-items-start mb-1 gap-2">
                                            <div class="flex-shrink-0" style="min-width: 175px;">
                                                <a href="{{ route('bulk-transactions.pending', ['bankAccountId' => $account->id]) }}"
                                                    style="text-decoration: none; color: inherit;">
                                                    <div class="bg-teal text-white px-3 py-2 rounded-0 d-block"
                                                        style="white-space: nowrap;">
                                                        <span class="text-white">Items to Reconcile:
                                                            {{ $account->items_to_reconcile }}</span>
                                                    </div>
                                                </a>
                                            </div>

                                            <div style="flex: 1 1 auto; min-width: 0;">
                                                <div class="d-flex justify-content-end align-items-center mb-1">
                                                    <small class="text-muted me-3" style="white-space: nowrap;">Statement
                                                        Balance</small>
                                                    <div class="fw-bold text-end" style="min-width: 100px;">
                                                        £{{ number_format($account->statement_balance, 2) }}
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end align-items-center">
                                                    <small class="text-muted me-2" style="white-space: nowrap;">Fast Ledger
                                                        Balance</small>
                                                    <div class="fw-bold text-end" style="min-width: 100px;">
                                                        £{{ number_format($account->fast_ledger_balance, 2) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="flex-shrink-0" style="min-width:180px;">
                                                <div class="d-flex gap-2">
                                                    <button class="teal-outline-btn active  px-2 text-nowrap"
                                                        data-bank-id="{{ $account->id }}" data-filter="week"
                                                        onclick="filterChart({{ $account->id }}, 'week', this)">
                                                        Week
                                                    </button>
                                                    <button class="teal-outline-btn px-2 text-nowrap"
                                                        data-bank-id="{{ $account->id }}" data-filter="month"
                                                        onclick="filterChart({{ $account->id }}, 'month', this)">
                                                        Month
                                                    </button>
                                                    <button class="teal-outline-btn px-2 text-nowrap"
                                                        data-bank-id="{{ $account->id }}" data-filter="year"
                                                        onclick="filterChart({{ $account->id }}, 'year', this)">
                                                        Year
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-end align-items-center">
                                                    <small class="text-muted me-2 text-nowrap">Balance to reconcile</small>
                                                    <div class="fw-bold text-end {{ $account->balance_to_reconcile < 0 ? 'text-danger' : 'text-dark' }}"
                                                        style="min-width: 100px;">
                                                        £{{ number_format(abs($account->balance_to_reconcile), 2) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>



                                        <!-- Chart Area -->
                                        <div class="reconcile-area border rounded-0 px-2"
                                            style="min-height: 60px; background-color: #f8f9fa;">
                                            <div id="reconcileChart{{ $account->id }}"></div>
                                        </div>
                                    </div>
                                </div>

                            @empty
                            @endforelse


                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Store chart instances globally
        const chartInstances = {};

        document.addEventListener('DOMContentLoaded', function() {
            const bankAccounts = @json($bankAccounts);

            function getChartOptions(bankName, months, totals) {
                return {
                    series: [{
                        name: "Total Movement",
                        data: totals
                    }],
                    chart: {
                        height: 100,
                        type: 'line',
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            speed: 400
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    colors: ['#13667d'],
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: months,
                        labels: {
                            style: {
                                fontSize: '10px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            formatter: function(val) {
                                return "£" + val.toFixed(0);
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return "£" + val.toFixed(2);
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e7e7e7',
                        strokeDashArray: 3,
                    }
                };
            }

            // Initialize chart for each bank account (default: week view)
            bankAccounts.forEach(function(account) {
                const chartElement = document.querySelector("#reconcileChart" + account.id);

                if (chartElement && account.chart_data) {
                    const chart = new ApexCharts(
                        chartElement,
                        getChartOptions(
                            account.bank_name,
                            account.chart_data.months,
                            account.chart_data.totals
                        )
                    );
                    chart.render();

                    // Store chart instance
                    chartInstances[account.id] = chart;
                }
            });

            // Set initial active state for all Week buttons
            document.querySelectorAll('.teal-outline-btn[data-filter="week"]').forEach(btn => {
                btn.classList.add('active');
            });
        });

        // Filter Chart Function
        function filterChart(bankId, filterType, button) {
            // Update active button state for THIS specific bank card only
            const parentDiv = button.parentElement;
            parentDiv.querySelectorAll('.teal-outline-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            button.classList.add('active');

            // Show loading state
            const chartElement = document.querySelector("#reconcileChart" + bankId);
            if (chartElement) {
                chartElement.style.opacity = '0.5';
                chartElement.style.transition = 'opacity 0.2s';
            }

            // Make AJAX request
            fetch(`{{ route('bulk-transactions.bank.chart.filter') }}?bank_id=${bankId}&filter=${filterType}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && chartInstances[bankId]) {
                        // Update chart data with animation
                        chartInstances[bankId].updateOptions({
                            xaxis: {
                                categories: data.data.months,
                                labels: {
                                    style: {
                                        fontSize: '10px'
                                    }
                                }
                            }
                        }, false, true);

                        chartInstances[bankId].updateSeries([{
                            name: "Total Movement",
                            data: data.data.totals
                        }], true);
                    }

                    // Remove loading state
                    if (chartElement) {
                        chartElement.style.opacity = '1';
                    }
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);

                    // Show user-friendly error
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'alert alert-danger alert-dismissible fade show';
                    errorMsg.innerHTML = `
                    <small>Failed to load chart data. Please try again.</small>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                    chartElement.parentElement.insertBefore(errorMsg, chartElement);

                    // Remove loading state
                    if (chartElement) {
                        chartElement.style.opacity = '1';
                    }

                    // Auto-dismiss after 3 seconds
                    setTimeout(() => {
                        if (errorMsg.parentNode) {
                            errorMsg.remove();
                        }
                    }, 3000);
                });
        }
    </script>
@endsection
