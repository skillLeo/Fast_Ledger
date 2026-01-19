@extends('admin.layout.app')
<style>
    .thead {
        background-color: #f2f2f2 !important;
    }
</style>
@section('content')
    @extends('admin.partial.errors')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <h4 class="page-title">Cheque Records</h4>
                            <div>
                                <a href="{{ route('transactions.create') }}" class="btn addbutton"
                                    role="button">Add New</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                      <table class="table table-bordered table-striped">

                                    <thead class="thead">
                                        <tr>
                                            <th>Transaction Date</th>
                                            <th>Ledger Ref</th>
                                            <th>Bank Account (Type)</th>
                                            <th>Paid In/Out</th>
                                            <th>Reference</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Cheque No</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transactions as $transaction)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($transaction->Transaction_Date)->format('d/m/Y') }}
                                                </td>
                                                <td>{{ $transaction->file->Ledger_Ref ?? '' }}</td>
                                                <td>{{ $transaction->Bank_Account_Name ?? '' }}</td>
                                                <td>
                                                    @if ($transaction->Paid_In_Out == 1)
                                                        Paid In
                                                    @elseif ($transaction->Paid_In_Out == 2)
                                                        Paid Out
                                                    @else
                                                        {{ $transaction->Paid_In_Out }}
                                                    @endif
                                                </td>
                                                <td>{{ $transaction->Reference ?? '' }}</td>
                                                <td>
                                                    <!-- Amount input -->
                                                    <form action="{{ route('bank.cheque.save') }}" method="POST"
                                                        style="margin-bottom:0;">
                                                        @csrf
                                                        <input type="hidden" name="transaction_id"
                                                            value="{{ $transaction->Transaction_ID }}">
                                                        <input type="hidden" name="transaction_type" value="2">
                                                        <input type="text" name="amount"
                                                            value="{{ old('amount', $transaction->Amount) }}"
                                                            class="form-control" placeholder="Enter amount"
                                                            @if ($transaction->bankReconciliation) readonly @endif>
                                                </td>
                                                <td>
                                                    <!-- Date input -->
                                                    <input type="date" name="transaction_date"
                                                        value="{{ $transaction->bankReconciliation ? \Carbon\Carbon::parse($transaction->bankReconciliation->Chq_Date)->format('Y-m-d') : '' }}"
                                                        class="form-control" placeholder="Select date"
                                                        @if ($transaction->bankReconciliation) readonly @endif>
                                                </td>
                                                <td>
                                                    <!-- Cheque No input -->
                                                    <input type="text" name="Cheque"
                                                        value="{{ $transaction->Cheque ?? '' }}" class="form-control"
                                                        placeholder="Enter Cheque No"
                                                        @if ($transaction->bankReconciliation) readonly @endif>
                                                </td>
                                                <td>
                                                    <button type="submit" class="btn btn-sm btn-success"
                                                        id="saveButton-{{ $transaction->Transaction_ID }}"
                                                        @if ($transaction->bankReconciliation) disabled @endif>
                                                        {{ $transaction->bankReconciliation ? 'Saved' : 'Save' }}
                                                    </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>


                                </table>

                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const saveButton = document.getElementById('saveButton');

            if (form && saveButton) {
                form.addEventListener('submit', function() {
                    saveButton.disabled = true;
                    saveButton.innerText = 'Saving...';
                });
            }
        });
    </script>
@endsection
