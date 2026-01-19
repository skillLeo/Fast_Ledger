<!DOCTYPE html>
<html>

<head>
    <title>Office Bank Reconciliation Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 5px;
        }

        th {
            background-color: #f2f2f2;
        }

        .section-heading {
            margin-top: 20px;
            font-size: 14px;
            font-weight: bold;
        }

        .totals {
            text-align: right;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <h2 style="text-align:center; margin-bottom: 10px;">Office Bank Reconciliation Report</h2>



    <div
        style="border: 1px solid #ccc; padding: 15px; margin-top: 20px; font-family: Arial, sans-serif; font-size: 13px;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 8px;">Reconciliation Summary</h3>

        {{-- Centered Client Name --}}
        <div style="text-align: center; font-weight: bold; font-size: 16px; margin-bottom: 10px;">
            {{ $clientRef }}
        </div>

        {{-- Two-column layout --}}
        <div style="display: flex; flex-wrap: wrap; justify-content: space-between;">
            <!-- Row 1 -->
            <div style="width: 48%; margin-bottom: 8px;">
                <strong>Bank:</strong> {{ $bankName }}
            </div>
            <div style="width: 48%; margin-bottom: 8px;">
                <strong>Initial Balance:</strong> £{{ number_format($initialBalance, 2) }}
            </div>

            <!-- Row 2 -->
            <div style="width: 48%; margin-bottom: 8px;">
                <strong>Bank Balance:</strong> £{{ number_format($bankBalance, 2) }}
            </div>
            <div style="width: 48%; margin-bottom: 8px;">
                <strong>Difference:</strong>
                <span style="color: {{ $finalDifference < 0 ? 'red' : 'green' }};">
                    £{{ number_format($finalDifference, 2) }}
                </span>
            </div>
        </div>

    </div>



    @php
        $sections = [
            'book_ledger' => 'Book Ledger',
            'disbursments' => 'Disbursements',
            'sales_book' => 'Sales Book',
            'payment_refund' => 'Payment Refund',
            'payment_transfer' => 'Payment Transfer',
            'miscellaneous' => 'Miscellaneous',
        ];
    @endphp

    @foreach ($sections as $key => $label)
        @php $sectionData = $banks->$key ?? null; @endphp
        @if (!empty($sectionData))
            <div class="section-heading">{{ $label }}</div>
            <table>
                <thead>
                    <tr>
                        <th>Ledger Ref</th>
                        <th>Amount</th>
                        <th>Client Name</th>
                        <th>Account Ref Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sectionData as $item)
                        <tr>
                            <td>{{ $item->Ledger_Ref ?? 'N/A' }}</td>
                            <td style="color: {{ $item->Amount < 0 ? 'red' : 'black' }}">
                                £{{ number_format($item->Amount ?? 0, 2) }}
                            </td>
                            <td>{{ $item->Client_Name ?? 'N/A' }}</td>
                            <td>{{ $item->AccountRefDescription ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="totals">Total {{ $label }}:</td>
                        <td class="totals">£{{ number_format($totals[$key] ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    @endforeach

    <br>
    <table>
        <tr>
            <td class="totals" colspan="4">Net Cash (Inflow/Outflow):</td>
            <td class="totals">£{{ number_format($grandTotal, 2) }}</td>
        </tr>
        <tr>
            <td class="totals" colspan="4">Final Flow Balance:</td>
            <td class="totals">£{{ number_format($flowBalance, 2) }}</td>
        </tr>
    </table>
    @php
        $totalInterest = 0;
        $totalCheque = 0;

        foreach ($interestRows ?? [] as $row) {
            $totalInterest += floatval($row['amount']);
        }

        foreach ($chequeRows ?? [] as $row) {
            $totalCheque += floatval($row['amount']);
        }
    @endphp


    @if (!empty($interestRows))
        <h4 style="background-color: #f2f2f2; padding: 8px;">Interest Paid</h4>

        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead style="background-color: #e0e0e0;">
                <tr>
                    <th>Cheque</th>
                    <th style="border: 1px solid #000; padding: 5px;">Ref</th>
                    <th style="border: 1px solid #000; padding: 5px;">Amount (£)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($interestRows as $row)
                    <tr>
                        <td>{{ $row['cheque'] ?? '-' }}</td>

                        <td>{{ $row['ref'] }}</td>
                        <td>£{{ number_format($row['amount'], 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold;">Total Interest:</td>
                    <td style="font-weight: bold;">£{{ number_format($totalInterest, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif


    @if (!empty($chequeRows))
        <h5>Cheques in Transit</h5>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>Cheque</th>
                    <th>Ref</th>
                    <th>Amount (£)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($chequeRows as $row)
                    <tr>
                        <td>{{ $row['cheque'] ?? '-' }}</td>

                        <td>{{ $row['ref'] }}</td>
                        <td>£{{ number_format($row['amount'], 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold;">Total Cheques:</td>
                    <td style="font-weight: bold;">£{{ number_format($totalCheque, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif


    <h4>Balance as per Bank Statement</h4>
    <p><strong>Bank Balance:</strong> £{{ number_format($bankBalance, 2) }}</p>
    <p><strong>Difference:</strong> £{{ number_format($finalDifference, 2) }}</p>




    <p style="text-align:right; font-size: 10px; margin-top: 40px;">
        Report generated on {{ \Carbon\Carbon::now()->format('j F Y, h:i A') }}
    </p>

</body>

</html>
