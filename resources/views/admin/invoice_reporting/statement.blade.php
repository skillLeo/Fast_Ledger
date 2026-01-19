@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
  <div class="container-fluid">

    <div class="card custom-card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="page-title mb-0">
          Invoice Statement
          <small class="text-muted">({{ $from }} to {{ $to }})</small>
          @if ($selectedPaymentType !== 'all')
            <span class="badge bg-primary ms-2">
              {{ $prefixMap[$selectedPaymentType] ?? strtoupper($selectedPaymentType) }}
            </span>
          @endif
        </h4>
        {{-- fix route name --}}
        <a href="{{ route('invoices.reporting', request()->except('page')) }}" class="btn btn-secondary btn-sm">← Back</a>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-striped align-middle">
            <thead>
              <tr>
                <th>Ledger Ref</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Address</th>
                <th class="text-end">Number of Entries</th>
                <th class="text-end">Amount (Total)</th>
                <th class="text-end">Outstanding Balance</th>
                <th>Over Due</th>
              </tr>
            </thead>

            <tbody>
              @forelse ($rows as $r)
                <tr>
                  <td>{{ $r->ledger_ref }}</td>

                  {{-- placeholders until you add these fields in the query --}}
                  <td>{{ $r->contact_name ?? '—' }}</td>
                  <td>{{ $r->contact_email ?? '—' }}</td>
                  <td>{{ $r->contact_address ?? '—' }}</td>

                  <td class="text-end">{{ number_format($r->entry_count) }}</td>
                  <td class="text-end">£{{ number_format($r->amount, 2) }}</td>

                  {{-- placeholders for balances/overdue (wire these up later) --}}
                  <td class="text-end">
                    £{{ number_format(($r->outstanding_balance ?? 0), 2) }}
                  </td>
                  <td>{{ $r->overdue ?? '—' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="8" class="text-center">No data for this period.</td>
                </tr>
              @endforelse

              {{-- === Type totals (e.g., one SIN row summarizing all ledgers) === --}}
              @foreach (($typeTotals ?? []) as $t)
                <tr class="table-light fw-semibold">
                  <td>Type Total ({{ $t->prefix }})</td>
                  <td>—</td>
                  <td>—</td>
                  <td>—</td>
                  <td class="text-end">{{ number_format($t->entry_count) }}</td>
                  <td class="text-end">£{{ number_format($t->amount, 2) }}</td>
                  <td class="text-end">—</td>
                  <td>—</td>
                </tr>
              @endforeach
            </tbody>

            <tfoot>
              <tr class="fw-bold">
                {{-- 8 columns total; label spans first 5 so the amount sits in the 6th --}}
                <td colspan="5" class="text-end">Grand Total</td>
                <td class="text-end">£{{ number_format($grandAmount ?? 0, 2) }}</td>
                <td class="text-end">—</td>
                <td>—</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
