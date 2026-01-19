<!-- filepath: e:\New folder\fastedger-v1\resources\views\admin\reports\profit_and_loos_pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Profit and Loss Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-top: 20px;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .net-profit {
            font-weight: bold;
            background-color: #e8f5e9;
        }
    </style>
</head>
<body>
    <h1>Profit and Loss Report</h1>
    <p><strong>Client:</strong> {{ $clientInfo->name ?? 'N/A' }}</p>
    <p><strong>From:</strong> {{ $fromDate ?? 'N/A' }} <strong>To:</strong> {{ $toDate ?? 'N/A' }}</p>

    @foreach ($reportData as $key => $data)
        <h2 class="section-title">{{ ucfirst($key) }}</h2>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>VAT</th>
                    <th>Total (With VAT)</th>
                    <th>Net (Without VAT)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['data'] as $item)
                    <tr>
                        <td>{{ $item->Description }}</td>
                        <td>{{ number_format($item->Amount, 2) }}</td>
                        <td>{{ number_format($item->VatAmount, 2) }}</td>
                        <td>{{ number_format($item->TotalWithVat, 2) }}</td>
                        <td>{{ number_format($item->NetOfVat, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p><strong>Total Net (Without VAT):</strong> {{ number_format($data['netOfVatSum'], 2) }}</p>
    @endforeach

    <h2 class="net-profit">Net Profit: {{ number_format($reportData['vat']['netOfVatSum'] - $reportData['cost']['netOfVatSum'] - $reportData['expense']['netOfVatSum'], 2) }}</h2>
</body>
</html>