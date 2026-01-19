<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - {{ $validated['invoice_no'] ?? 'N/A' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }
        
        /* Header Styles */
        .invoice-header {
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header-content {
            display: table;
            width: 100%;
        }
        
        .header-left {
            display: table-cell;
            vertical-align: top;
            width: 60%;
        }
        
        .header-right {
            display: table-cell;
            vertical-align: top;
            text-align: right;
            width: 40%;
        }
        
        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        
        .company-info {
            display: table;
        }
        
        .logo-icon {
            display: table-cell;
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, #1e3a8a, #059669);
            border-radius: 50%;
            text-align: center;
            vertical-align: middle;
            color: white;
            font-weight: bold;
            font-size: 18px;
            margin-right: 10px;
        }
        
        .company-details {
            display: table-cell;
            vertical-align: middle;
            padding-left: 10px;
        }
        
        .company-details h2 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            color: #1e3a8a;
        }
        
        .company-details p {
            margin: 2px 0;
            color: #666;
            font-size: 10px;
        }
        
        /* Invoice Details */
        .invoice-details {
            margin-bottom: 20px;
        }
        
        .details-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 20px 0;
        }
        
        .details-table td {
            vertical-align: top;
            width: 33.33%;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 12px;
            color: #333;
            margin-bottom: 8px;
        }
        
        .detail-item {
            margin-bottom: 8px;
        }
        
        .detail-label {
            font-weight: bold;
            font-size: 11px;
            color: #333;
        }
        
        .detail-value {
            font-size: 11px;
            margin-top: 2px;
        }
        
        /* Table Styles */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border: 2px solid #dee2e6;
        }
        
        .invoice-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
            padding: 10px 8px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        
        .invoice-table td {
            padding: 8px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
            font-size: 11px;
        }
        
        .item-description {
            text-align: left;
        }
        
        .item-code {
            font-weight: bold;
            margin-bottom: 4px;
        }
        
        .description {
            color: #666;
        }
        
        .references {
            font-size: 9px;
            margin-top: 4px;
            color: #999;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        /* Bottom Section */
        .invoice-bottom {
            margin-top: 20px;
        }
        
        .bottom-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 30px 0;
        }
        
        .bottom-table td {
            vertical-align: top;
        }
        
        .payment-info {
            width: 60%;
        }
        
        .invoice-totals {
            width: 40%;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #dee2e6;
            font-size: 11px;
        }
        
        .totals-table td {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
        }
        
        .totals-table .label {
            font-weight: bold;
            background-color: #f5f5f5;
            text-align: right;
        }
        
        .totals-table .value {
            font-weight: bold;
            text-align: right;
        }
        
        .totals-table .total-row td {
            background-color: #e8e8e8;
            font-weight: bold;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
        }
        
        /* Additional Table */
        .additional-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border: 2px solid #dee2e6;
        }
        
        .additional-table td {
            height: 25px;
            width: 25%;
            border: 1px solid #dee2e6;
        }
        
        /* Footer */
        .invoice-footer {
            text-align: center;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            font-size: 10px;
            color: #666;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="header-content">
                <div class="header-left">
                    <h1 class="invoice-title">TAX INVOICE</h1>
                </div>
                <div class="header-right">
                    <div class="company-info">
                        <div class="logo-icon">ES</div>
                        <div class="company-details">
                            <h2>Energy Saviour</h2>
                            <p>Green Energy Solutions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <table class="details-table">
                <tr>
                    <!-- Client Information -->
                    <td>
                        <div class="section-title">Invoice to:</div>
                        <div class="detail-value">
                            <p style="margin: 2px 0;">{{ $client->name ?? 'ABC Company' }}</p>
                            <p style="margin: 2px 0;">{{ $client->address ?? 'ABC Road Slough' }}</p>
                            <p style="margin: 2px 0;">{{ $client->city ?? 'United Kingdom' }}</p>
                            <p style="margin: 2px 0;">{{ $client->postcode ?? 'PI98 7HV' }}</p>
                            <p style="margin: 2px 0;">T: {{ $client->phone ?? '07456764343' }}</p>
                            <p style="margin: 2px 0;">E: {{ $client->email ?? 'abc@company.co.uk' }}</p>
                            <p style="margin: 2px 0;">VAT No: {{ $client->vat_no ?? '15674537' }}</p>
                        </div>
                    </td>

                    <!-- Invoice Meta Information -->
                    <td>
                        <div class="detail-item">
                            <div class="detail-label">Invoice Date</div>
                            <div class="detail-value">{{ \Carbon\Carbon::parse($validated['Transaction_Date'])->format('d M Y') }}</div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Inv Due Date</div>
                            <div class="detail-value">{{ \Carbon\Carbon::parse($validated['Inv_Due_Date'])->format('d/M/Y') }}</div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Invoice No</div>
                            <div class="detail-value">{{ $validated['invoice_no'] }}</div>
                        </div>

                        <div class="detail-item">
                            <div class="detail-label">Invoice Ref</div>
                            <div class="detail-value">{{ $validated['invoice_ref'] }}</div>
                        </div>
                    </td>

                    <!-- Company Information -->
                    <td>
                        <div class="detail-value">
                            <p style="margin: 2px 0; font-weight: bold;">Energy Saviour Ltd</p>
                            <p style="margin: 2px 0;">First line of address</p>
                            <p style="margin: 2px 0;">Second line of address</p>
                            <p style="margin: 2px 0;">Town, County</p>
                            <p style="margin: 2px 0;">Postcode</p>
                            <p style="margin: 2px 0;">T: 07673767623</p>
                            <p style="margin: 2px 0;">E: office@energysaviourltd.co.uk</p>
                            <p style="margin: 2px 0;">VAT No: 157676554</p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Invoice Items Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 45%;">Description</th>
                    <th style="width: 8%;">Qty</th>
                    <th style="width: 17%;">Unit Price</th>
                    <th style="width: 15%;">VAT</th>
                    <th style="width: 15%;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @if (isset($validated['items']) && is_array($validated['items']))
                    @foreach ($validated['items'] as $item)
                        <tr>
                            <td class="item-description">
                                <div class="item-code">{{ $item['item_code'] ?? '' }}</div>
                                <div class="description">{{ $item['description'] ?? '' }}</div>
                                @if (isset($item['ledger_ref']) && !empty($item['ledger_ref']))
                                    <div class="references">{{ $item['ledger_ref'] }}@if(!empty($item['account_ref'])) - {{ $item['account_ref'] }}@endif</div>
                                @endif
                            </td>
                            <td class="text-center">1</td>
                            <td class="text-center">£{{ number_format($item['unit_amount'] ?? 0, 2) }}</td>
                            <td class="text-center">
                                £{{ number_format($item['vat_amount'] ?? 0, 2) }}
                                @if(($item['vat_rate'] ?? 0) > 0)({{ $item['vat_rate'] }}%)@endif
                            </td>
                            <td class="text-center">£{{ number_format($item['net_amount'] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                @endif

                @php
                    $itemCount = isset($validated['items']) ? count($validated['items']) : 0;
                    $emptyRows = 7 - $itemCount;
                @endphp

                @for ($i = 0; $i < $emptyRows; $i++)
                    <tr style="height: 35px;">
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <!-- Bottom Section -->
        <div class="invoice-bottom">
            <table class="bottom-table">
                <tr>
                    <!-- Payment Information -->
                    <td class="payment-info">
                        <div class="section-title">Please make electronic payment to</div>
                        <div class="detail-value">
                            <p style="margin: 2px 0;">Name: Energy Saviour Ltd</p>
                            <p style="margin: 2px 0;">Sort Code: 12-34-32</p>
                            <p style="margin: 2px 0;">Account No: 43456754</p>
                            <p style="margin: 2px 0;">Payment Ref: {{ $validated['invoice_no'] }}</p>
                        </div>

                        @if (isset($validated['invoice_notes']) && !empty($validated['invoice_notes']))
                            <div style="margin-top: 15px;">
                                <div class="section-title">Notes:</div>
                                <div class="detail-value">{{ $validated['invoice_notes'] }}</div>
                            </div>
                        @endif
                    </td>

                    <!-- Summary Totals -->
                    <td class="invoice-totals">
                        <table class="totals-table">
                            <tr>
                                <td class="label">NET</td>
                                <td class="value">£{{ number_format($validated['invoice_net_amount'] ?? 0, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="label">VAT</td>
                                <td class="value">£{{ number_format($validated['invoice_vat_amount'] ?? 0, 2) }}</td>
                            </tr>
                            <tr class="total-row">
                                <td class="label">TOTAL</td>
                                <td class="value">£{{ number_format($validated['invoice_total_amount'] ?? 0, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Additional Empty Table -->
        <table class="additional-table">
            @for ($i = 0; $i < 6; $i++)
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endfor
        </table>

        <!-- Footer -->
        <div class="invoice-footer">
            <p>Company Registration No: 76767554 &nbsp;&nbsp;&nbsp; Registered Office: Unit 30, Business Village, Wexham Road, Slough, SL1 5HF</p>
        </div>
    </div>
</body>
</html>