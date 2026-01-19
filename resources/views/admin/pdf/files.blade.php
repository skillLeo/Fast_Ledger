<!DOCTYPE html>
<html>
<head>
    <title>Files Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2 style="text-align: center">Files opening book</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Ledger Ref</th>
                <th>Matter</th>
                <th>Name</th>
                <th>Address</th>
                <th>Post Code</th>
                <th>Fee Earner</th>
            
            </tr>
        </thead>
        <tbody>
            @foreach ($files as $file)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($file->File_Date)->format('Y-m-d') }}</td>
                    <td>{{ $file->Ledger_Ref }}</td>
                    <td>{{ $file->Matter }}</td>
                    <td>{{ $file->First_Name }} {{ $file->Last_Name }}</td>
                    <td>{{ $file->Address1 }}</td>
                    <td>{{ $file->Post_Code }}</td>
                    <td>{{ $file->Fee_Earner }}</td>
                
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
