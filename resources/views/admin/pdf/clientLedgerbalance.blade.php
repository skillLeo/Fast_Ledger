<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>14 Days Passed Check</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 3px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body style="margin-left:-3%">
    <h2>14 Days Passed Check</h2>
    <table>
        <thead>
            <tr>
                <th>Ledger Status</th>
                <th>Last Transaction Date</th>
                <th>A/C Balance</th>
                <th style="width:5%">Ledger Ref</th>
                <th style="width:5%">Matter</th>
                <th>Name</th>
                <th>Address</th>
                <th>Fee Earner</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fileSummaries as $clientsdata)
            <tr>
                <td>{{ $clientsdata['Days_Since_Last_Transaction'] }}</td>
                <td>{{ $clientsdata['Last_Transaction_Date'] }}</td>
                <td>{{ number_format($clientsdata['Total_Balance'], 2) }}</td>
                <td>{{ $clientsdata['Ledger_Ref'] }}</td>
                <td>{{ $clientsdata['Matter'] }}</td>
                <td>{{ $clientsdata['First_Name'] }} {{ $clientsdata['Last_Name'] }}</td>
                <td>{{ $clientsdata['Address1'] }} {{ $clientsdata['Address2'] }}</td>
                <td>{{ $clientsdata['Fee_Earner'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
