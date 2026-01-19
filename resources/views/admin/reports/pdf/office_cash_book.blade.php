<!DOCTYPE html>
<html>
<head>
    <title>office Cashbook Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 2px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Office Cashbook Report</h2>
    <p><strong>From:</strong> {{ request('from_date') }} <strong>To:</strong> {{ request('to_date') }}</p>
    {{-- <p><strong>Bank Account:</strong> {{ request('bank_account_id') }}</p> --}}
    <div>

        <p><strong>Account No:</strong> {{ $accountNo }} <strong>Sort Code:</strong> {{ $sortCode }}</p>
        <strong>Initial Balance: </strong>
        <span>{{ number_format($initialBalance, 2) }}</span>
       
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Trans Type</th>
                <th>Cheque No</th>
                <th>Description</th>
                <th>Account Ref</th>
                <th>Ledger Ref</th>
                <th>Payments (DR)</th>
                <th>Receipts (CR)</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->Transaction_Date }}</td>
                    <td>{{ $transaction->Transaction_Type }}</td>
                    <td>{{ $transaction->Cheque }}</td>
                    <td>{{ $transaction->Description }}</td>
                    <td>{{ $transaction->Account_Ref }}</td>
                    <td>{{ $transaction->Ledger_Ref }}</td>
                    <td>{{ number_format($transaction->Payments, 2) }}</td>
                    <td>{{ number_format($transaction->Receipts, 2) }}</td>
                    <td>{{ number_format($transaction->Balance, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
