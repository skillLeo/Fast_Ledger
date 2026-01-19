@php
    // Extract template data if available
    $templateData = [];
    if (isset($template) && $template) {
        if (is_string($template->template_data)) {
            $templateData = json_decode($template->template_data, true) ?? [];
        } elseif (is_array($template->template_data)) {
            $templateData = $template->template_data;
        }
    }

    // Get colors and styling
    $primaryColor = $templateData['primaryColor'] ?? '#1e3a8a';
    $secondaryColor = $templateData['secondaryColor'] ?? '#16a34a';
    $titleFont = $templateData['titleFont'] ?? 'Arial';
    $bodyFont = $templateData['bodyFont'] ?? 'Arial';
    $fontSize = $templateData['fontSize'] ?? '11px';
    
    // Table styles
    $tableHeaderColor = $templateData['tableHeaderColor'] ?? '#b3d9ff';
    $tableHeaderTextColor = $templateData['tableHeaderTextColor'] ?? '#000000';
    $tableBorderColor = $templateData['tableBorderColor'] ?? '#6c757d';
    $tableFontSize = $templateData['tableFontSize'] ?? '11px';
    
    $logoPath = $template->logo_path ?? null;
    $logoFilename = $logoPath ? basename($logoPath) : null;
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: {{ $bodyFont }}, Arial, sans-serif;
            font-size: {{ $fontSize }};
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        .logo-energy {
            background-color: {{ $primaryColor }};
            color: white;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 18px;
            display: inline-block;
        }
        .logo-saviour {
            background-color: {{ $secondaryColor }};
            color: white;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 18px;
            display: inline-block;
        }
        .section-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 5px;
        }
        .invoice-table th {
            background-color: {{ $tableHeaderColor }};
            color: {{ $tableHeaderTextColor }};
            border: 1px solid {{ $tableBorderColor }};
            padding: 8px;
            font-size: 12px;
            font-weight: 600;
            text-align: left;
        }
        .invoice-table td {
            border: 1px solid {{ $tableBorderColor }};
            padding: 8px;
            font-size: {{ $tableFontSize }};
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer-text {
            font-size: 9px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header: Title and Logo -->
        <table style="margin-bottom: 20px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <h1 style="font-size: 36px; font-weight: bold; margin: 0; font-family: {{ $titleFont }};">TAX INVOICE</h1>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    @if (isset($template) && $template && $template->logo_path)
                        <img src="{{ public_path('storage/invoice_logos/' . $logoFilename) }}" 
                             alt="Logo" 
                             style="max-height: 60px; max-width: 200px;">
                    @else
                        <div style="display: inline-block;">
                            <span class="logo-energy">Energy</span><span class="logo-saviour">Saviour ✓</span>
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Client Info, Invoice Meta, Company Info -->
        <table style="margin-bottom: 20px;">
            <tr>
                <!-- Client Info (Left Column) -->
                <td style="width: 33%; vertical-align: top; padding-right: 15px;">
                    <div class="section-title">Invoice to:</div>
                    <div><strong>{{ $client->Business_Name ?? 'Client Name' }}</strong></div>
                    @if (isset($client->Contact_Name) && $client->Contact_Name)
                        <div>{{ $client->Contact_Name }}</div>
                    @endif
                    @if (isset($client->Address1) && $client->Address1)
                        <div>{{ $client->Address1 }}</div>
                    @endif
                    @if (isset($client->Address2) && $client->Address2)
                        <div>{{ $client->Address2 }}</div>
                    @endif
                    @if (isset($client->Town) && $client->Town)
                        <div>{{ $client->Town }}</div>
                    @endif
                    @if (isset($client->Post_Code) && $client->Post_Code)
                        <div>{{ $client->Post_Code }}</div>
                    @endif
                    @if (isset($client->Phone) && $client->Phone)
                        <div>T: {{ $client->Phone }}</div>
                    @endif
                </td>

                <!-- Invoice Meta (Middle Column) -->
                <td style="width: 33%; vertical-align: top; padding-right: 15px;">
                    <div><strong>Invoice Date:</strong> {{ $invoiceDate }}</div>
                    <div><strong>Inv Due Date:</strong> {{ $dueDate }}</div>
                    <div><strong>Invoice No:</strong> {{ $invoiceNo }}</div>
                    <div><strong>Invoice Ref:</strong> {{ $invoiceRef }}</div>
                </td>

                <!-- Company Info (Right Column) -->
                <td style="width: 34%; vertical-align: top;">
                    <div><strong>{{ $client->Business_Name ?? 'Your Business' }}</strong></div>
                    @if (isset($client->Address1) && $client->Address1)
                        <div>{{ $client->Address1 }}</div>
                    @endif
                    @if (isset($client->Address2) && $client->Address2)
                        <div>{{ $client->Address2 }}</div>
                    @endif
                    @if (isset($client->Town) && $client->Town)
                        <div>{{ $client->Town }}</div>
                    @endif
                    @if (isset($client->Post_Code) && $client->Post_Code)
                        <div>{{ $client->Post_Code }}</div>
                    @endif
                    @if (isset($client->VAT_Registration_No) && $client->VAT_Registration_No)
                        <div>VAT No: {{ $client->VAT_Registration_No }}</div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Invoice Items Table -->
        <table class="invoice-table" style="margin-bottom: 20px;">
            <thead>
                <tr>
                    <th style="width: 70px; text-align: center;">Image</th>
                    <th>Description</th>
                    <th style="width: 60px; text-align: center;">Qty</th>
                    <th style="width: 100px; text-align: right;">Unit Price</th>
                    <th style="width: 120px; text-align: right;">VAT</th>
                    <th style="width: 110px; text-align: right;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @if (isset($items) && is_array($items) && count($items) > 0)
                    @foreach ($items as $item)
                        @php
                            $quantity = 1;
                            $unitAmount = floatval($item['unit_amount'] ?? 0);
                            $vatAmount = floatval($item['vat_amount'] ?? 0);
                            $vatRate = intval($item['vat_rate'] ?? 20);
                            $lineTotal = $unitAmount + $vatAmount;
                            $productImage = $item['product_image'] ?? null;
                        @endphp
                        <tr>
                            <td style="text-align: center; vertical-align: middle;">
                                @if ($productImage)
                                    <img src="{{ $productImage }}" 
                                         alt="Product"
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6;">
                                @else
                                    <div style="width: 50px; height: 50px; background-color: #f8f9fa; border: 1px dashed #dee2e6; margin: 0 auto;"></div>
                                @endif
                            </td>
                            <td>{{ $item['description'] ?? ($item['item_code'] ?? 'N/A') }}</td>
                            <td class="text-center">{{ $quantity }}</td>
                            <td class="text-right">£{{ number_format($unitAmount, 2) }}</td>
                            <td class="text-right">£{{ number_format($vatAmount, 2) }} ({{ $vatRate }}%)</td>
                            <td class="text-right">£{{ number_format($lineTotal, 2) }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center">No items found</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <!-- Payment Details and Totals -->
        <table style="margin-bottom: 20px;">
            <tr>
                <!-- Payment Details (Left - 60%) -->
                <td style="width: 60%; vertical-align: top; padding-right: 20px;">
                    <div class="section-title">Please make electronic payment to</div>
                    <div>Name: {{ $client->Business_Name ?? 'Business Name' }}</div>
                    @if (isset($bankAccount) && $bankAccount)
                        <div>Sort Code: {{ $bankAccount->Sort_Code ?? 'N/A' }}</div>
                        <div>Account No: {{ $bankAccount->Account_No ?? 'N/A' }}</div>
                    @else
                        <div>Sort Code: N/A</div>
                        <div>Account No: N/A</div>
                    @endif
                    <div>Payment Ref: {{ $invoiceNo }}</div>
                </td>

                <!-- Totals (Right - 40%) -->
                <td style="width: 40%; vertical-align: top;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="padding: 5px; text-align: right;"><strong>NET</strong></td>
                            <td style="padding: 5px; text-align: right; font-weight: 600;">£{{ $netAmount }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 5px; text-align: right;"><strong>VAT</strong></td>
                            <td style="padding: 5px; text-align: right; font-weight: 600;">£{{ $vatAmount }}</td>
                        </tr>
                        <tr style="border-top: 2px solid #000;">
                            <td style="padding: 8px 5px 5px 5px; text-align: right;"><strong>TOTAL</strong></td>
                            <td style="padding: 8px 5px 5px 5px; text-align: right; font-weight: bold; font-size: 14px;">£{{ $totalAmount }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer-text">
            @php
                $footerParts = [];
                if (isset($client->Company_Reg_No) && $client->Company_Reg_No) {
                    $footerParts[] = 'Company Registration No: ' . $client->Company_Reg_No;
                }
                
                $addressParts = array_filter([
                    $client->Address1 ?? null,
                    $client->Address2 ?? null,
                    $client->Town ?? null,
                    $client->Post_Code ?? null
                ]);
                
                if (!empty($addressParts)) {
                    $footerParts[] = 'Registered Office: ' . implode(', ', $addressParts);
                }
                
                echo implode(' | ', $footerParts);
            @endphp
        </div>
    </div>
</body>
</html>