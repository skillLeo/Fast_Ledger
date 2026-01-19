<!DOCTYPE html>
<html>
<head>
    <title>Transactions Day Book</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 90%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 2px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>{{ $client_name }}</h2>
    <h2>Transactions Day Book</h2>
    <table>
        <thead>
            <tr>
                <th>Transaction Date</th>
                <th>Ledger Ref</th>
                <th>Bank Account</th>
                <th>Paid In/Out</th>
                <th>Account Ref#</th>
                <th>Payment Type</th>
                <th>Cheque/Payin Ref</th>
              
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($transaction->Transaction_Date)->format('Y-m-d') }}</td>
                    <td>{{ $transaction->file->Ledger_Ref ?? 'N/A' }}</td>
                    <td>
                        @if ($transaction->Is_Bill == 1)
                            Bill of Costs
                        @elseif ($transaction->bankAccount)
                            {{ $transaction->bankAccount->Account_Name ?? 'N/A' }} 
                            ({{ $transaction->bankAccount->bankAccountType->Bank_Type ?? 'N/A' }})
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $transaction->Paid_In_Out == 1 ? 'Paid In' : ($transaction->Paid_In_Out == 2 ? 'Paid Out' : 'N/A') }}</td>
                    <td>{{ $transaction->accountRef->Reference ?? 'N/A' }}</td>
                    <td>{{ $transaction->paymentType->Payment_Type_Name ?? 'N/A' }}</td>
                    <td>{{ $transaction->Cheque ?? 'N/A' }}</td>

                    
                    <td>{{ number_format($transaction->Amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
