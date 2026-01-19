<!DOCTYPE html>
<html>
<head>
    <title>Client Bank Reconciliation Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Client Bank Reconciliation Report</h2>
    <p>Date: {{ date('Y-m-d') }}</p>

    <table>
        <thead>
            <tr>
                <th>Ledger Ref</th>
                <th>Client Name</th>
                <th>Client Balance</th>
                <th>Office Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resultSet as $row)
                <tr>
                    <td>{{ $row->Ledger_Ref }}</td>
                    <td>{{ $row->Client_Name }}</td>
                    <td>{{ number_format($row->{'Client Balance'}, 2) }}</td>
                    <td>{{ number_format($row->Office_Balance, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
