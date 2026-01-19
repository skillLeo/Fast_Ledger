@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h4 class="page-title">Invoice Reporting</h4>
                        </div>

                        <div class="card-body">
                            {{-- Filter Form --}}
                            <form method="GET" id="filter-form" action="{{ url()->current() }}">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-1">
                                        <label for="from_date" class="form-label">From Date</label>
                                        <input type="date" id="from_date" name="from_date" class="form-control"
                                            value="{{ $from ?? request('from_date', now()->startOfMonth()->toDateString()) }}">
                                    </div>

                                    <div class="col-md-1">
                                        <label for="to_date" class="form-label">To Date</label>
                                        <input type="date" id="to_date" name="to_date" class="form-control"
                                            value="{{ $to ?? request('to_date', now()->toDateString()) }}">
                                    </div>

                                    <div class="col-md-1">
                                        <label for="payment_type" class="form-label">Payment Type</label>
                                        <select id="payment_type" name="payment_type" class="form-control">
                                            @foreach ($paymentTypeOptions as $val => $label)
                                                <option value="{{ $val }}" @selected(($selectedPaymentType ?? 'all') === $val)>
                                                    {{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>



                                    <div class="col-md-2">
                                        <a href="{{ route('invoices.statement', request()->all()) }}"
                                            class="btn btnstyle w-100">
                                            Invoice Statement
                                        </a>
                                    </div>

                                    <div class="col d-flex justify-content-end">
                                        <button type="submit" name="export" value="pdf" class="btn downloadpdf me-2">
                                            <i class="fas fa-file-pdf"></i> Print PDF
                                        </button>
                                        <button type="submit" name="export" value="csv" class="btn downloadcsv">
                                            <i class="fas fa-file-csv"></i> Excel
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <script>
                                // Auto-submit when any filter changes
                                document.getElementById('payment_type').addEventListener('change', () => document.getElementById('filter-form')
                                    .submit());
                                document.getElementById('from_date').addEventListener('change', () => document.getElementById('filter-form')
                                    .submit());
                                document.getElementById('to_date').addEventListener('change', () => document.getElementById('filter-form')
                                    .submit());
                            </script>

                            {{-- Table --}}
                            <div class="table-responsive mt-3">
                                <div id="tabletop" class="tabletop-style p-2 fs-5 text-white fw-bold">
                                    Invoice Report
                                </div>

                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Invoice No</th>
                                            <th>Invoice Ref</th>
                                            <th>Invoice Date</th>
                                            <th>Due Date</th>
                                            <th>Net</th>
                                            <th>VAT</th>
                                            <th>Total</th>
                                            <th>Paid</th>
                                            <th>Balance</th>
                                            <th>Status</th>
                                            <th>Docs</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($rows as $r)
                                            @php
                                                $net = (float) ($r->net_amount ?? 0);
                                                $vat = (float) ($r->vat_amount ?? 0);
                                                $total = (float) ($r->total_amount ?? 0);
                                                $paid = (float) ($r->paid_amount ?? 0);
                                                $balance = max($total - $paid, 0);
                                                $status = $balance <= 0 ? 'Paid' : ($paid > 0 ? 'Part Paid' : 'Unpaid');
                                                $class =
                                                    $status === 'Paid'
                                                        ? 'bg-success'
                                                        : ($status === 'Part Paid'
                                                            ? 'bg-warning'
                                                            : 'bg-danger');
                                            @endphp
                                            <tr>
                                                <td>{{ $r->ledger_ref }}</td>
                                                <td>{{ $r->account_ref }}</td>
                                                <td>{{ $r->invoice_date ? \Carbon\Carbon::parse($r->invoice_date)->format('Y-m-d') : '' }}
                                                </td>
                                                <td>{{ $r->due_date ? \Carbon\Carbon::parse($r->due_date)->format('Y-m-d') : '' }}
                                                </td>
                                                <td>£{{ number_format($net, 2) }}</td>
                                                <td>£{{ number_format($vat, 2) }}</td>
                                                <td>£{{ number_format($total, 2) }}</td>
                                                <td>£{{ number_format($paid, 2) }}</td>
                                                <td>£{{ number_format($balance, 2) }}</td>
                                                <td><span class="badge {{ $class }}">{{ $status }}</span>
                                                </td>
                                                <td>
                                                    @if (!empty($r->doc_url))
                                                        <a href="{{ $r->doc_url }}" target="_blank" rel="noopener"><i
                                                                class="fas fa-file-alt"></i></a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="text-center">No records found for the selected
                                                    filters.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                                @if (method_exists($rows, 'links'))
                                    <div class="mt-2">
                                        {{ $rows->withQueryString()->links() }}
                                    </div>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
