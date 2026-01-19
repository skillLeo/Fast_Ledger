@extends('admin.layout.app')
@section('content')
    @extends('admin.partial.errors')
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
        }

        .tabletop-style {
            color: black !important;
            background-color: rgba(10, 10, 10, 0.13);
            font-family: Arial, sans-serif;
        }

        th,
        td {
            padding: 8px 12px;
            border: 1px solid #eeeaea;
            text-align: left;
            border: 1px solid #474747;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .table_title {
            background-color: #ededed;
            font-weight: bold;
            font-size: 16px;
            border: none;
        }

        .subsection-title {
            font-weight: bold;
            background-color: #fafafa;
            border: 1px solid #474747;
        }

        .total-row {
            font-weight: bold;
            background-color: #d6d6d6;
        }

        .net-profit {
            font-weight: bold;
            background-color: #f5f5f5;
        }

        a {
            color: blue;
            text-decoration: underline;
        }
    </style>
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header pb-3">
                            <h4 class="page-title">Profit And Loss Report</h4>
                        </div>
                        <div class="card-body">
                            <!-- Report Filters -->
                            <div class="mb-3 d-flex align-items-end justify-content-between flex-wrap">
                                <!-- Left group: Bank Name, From/To Date, View Report -->
                                <form method="GET" action="{{ route('profit.and.loos') }}">

                                    <div class="d-flex flex-wrap gap-3 align-items-end">
                                        <!-- From Date -->
                                        <div>
                                            <label for="from-date">From Date:</label>
                                            <input type="date" id="from-date" name="from_date" class="form-control"
                                                value="{{ request('from_date', now()->format('Y-m-d')) }}">
                                        </div>

                                        <!-- To Date -->
                                        <div>
                                            <label for="to-date">To Date:</label>
                                            <input type="date" name="to_date" id="to-date" class="form-control"
                                                value="{{ request('to_date', now()->format('Y-m-d')) }}">
                                        </div>

                                        <!-- View Report Button -->
                                        <div>
                                            <button type="submit" class="btn teal-custom mt-2" id="view-report-btn">View
                                                Report</button>
                                        </div>

                                    </div>
                                </form>

                                <div class="mb-2">
                                    <x-download-dropdown pdf-id="printPdf" csv-id="downloadCSV" />
                                </div>
                            </div>
                            <!-- Reconciliation Table -->
                            <div class="table-responsive">
                                <div id="tabletop"
                                    class="mb-2 p-2 t-size-20px fs-4 tabletop-style   text-white font-weight-bold">
                                    Profit And Loss Report</div>
                                <table>
                                    <tr>
                                        <td colspan="3" class="table_title">Profit and Loss Report</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="subsection-title">
                                            Transactions Report | From Date: {{ $fromDate }} To Date:
                                            {{ $toDate }}
                                        </td>
                                    </tr>
                                    <!-- Income -->
                                    <tr>
                                        <td colspan="3" class="table_title">Income</td>
                                    </tr>
                                    @foreach ($reportData['income']['data'] as $income)
                                        <tr>
                                            <td><a href="#">{{ $income->Description }}</a></td>
                                            <td></td>
                                            <td>{{ number_format($income->NetOfVat, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="total-row">
                                        <td><strong>Total VAT</strong></td>
                                        <td></td>
                                        <td><strong>{{ number_format($reportData['vat']['netOfVatSum'], 2) }}</strong>
                                        </td>
                                    </tr>
                                    <!-- Cost of Sales -->
                                    <tr>
                                        <td colspan="3" class="table_title">Cost of Sales</td>
                                    </tr>
                                    @foreach ($reportData['cost']['data'] as $cost)
                                        <!-- Use array access -->
                                        <tr>
                                            <td colspan="2"><a href="#">{{ $cost->Description }}</a></td>
                                            <td>{{ number_format($cost->NetOfVat, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    <!-- Gross Profit -->
                                    <tr class="total-row">
                                        <td></td>
                                        <td></td>
                                        <td><strong>{{ number_format($reportData['cost']['netOfVatSum'], 2) }}</strong>
                                            {{-- <td><strong>{{ number_format($reportData->NetOfVat, 2) }}</strong></td> --}}
                                            <!-- Use object notation -->
                                    </tr>
                                    <tr class="total-row">
                                        <td><strong>Gross Profit</strong></td>
                                        <td></td>
                                        <td><strong>{{ number_format($reportData['vat']['netOfVatSum'] - $reportData['cost']['netOfVatSum'], 2) }}</strong>
                                        </td>
                                    </tr>
                                    <!-- Expenses -->
                                    <tr>
                                        <td colspan="3" class="table_title">Expenses</td>
                                    </tr>
                                    {{-- @foreach ($reportData->expenses as $expense) --}}
                                    @foreach ($reportData['expense']['data'] as $expense)
                                        <!-- Use object notation -->
                                        <tr>
                                            <td><a href="#">{{ $expense->Description }}</a></td>
                                            <!-- Use object notation -->
                                            <td></td>
                                            <td>{{ number_format($expense->NetOfVat, 2) }}</td>
                                            <!-- Use object notation -->
                                        </tr>
                                    @endforeach
                                    <tr class="total-row">
                                        <td><strong>Total Expenses</strong></td>
                                        <td></td>
                                        <!-- You can calculate total expenses here if needed -->
                                        <td><strong>{{ number_format($reportData['expense']['netOfVatSum'], 2) }}</strong>
                                        </td>
                                        <!-- Use object notation -->
                                    </tr>
                                    <!-- Net Profit -->
                                    <!-- Uncomment and adjust if needed -->
                                    <tr class="total-row">
                                        <td><strong>Net Profit before interest & tax</strong></td>
                                        <td><strong>
                                        <td><strong>{{ number_format($reportData['vat']['netOfVatSum'] - $reportData['cost']['netOfVatSum'] - $reportData['expense']['netOfVatSum'], 2) }}</strong>
                                        </td>
                                        </strong></td>
                                    </tr>
                                    @foreach ($reportData['interestReceived']['data'] as $interestReceived)
                                        <!-- Use object notation -->
                                        <tr>
                                            <td><a href="#">{{ $interestReceived->Description }}</a></td>
                                            <!-- Use object notation -->
                                            <td></td>
                                            <td>{{ number_format($interestReceived->NetOfVat, 2) }}</td>
                                            <!-- Use object notation -->
                                        </tr>
                                    @endforeach
                                    <tr class="net-profit">
                                        <td><strong>Net Profit before tax</strong></td>
                                        <td></td>
                                        <td><strong>{{ number_format($reportData['vat']['netOfVatSum'] - $reportData['cost']['netOfVatSum'] - $reportData['expense']['netOfVatSum'] - $reportData['interestReceived']['netOfVatSum'], 2) }}</strong>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const printPdfBtn = document.getElementById('printPdf');
        if (printPdfBtn) {
            printPdfBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const fromDate = document.getElementById('from-date').value;
                const toDate = document.getElementById('to-date').value;

                const url =
                    `{{ route('profit.and.loss.pdf') }}?from_date=${fromDate}&to_date=${toDate}`;
                window.location.href = url;
            });
        }
    });
</script>
