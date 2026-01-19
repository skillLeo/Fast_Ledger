<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
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
        $titleFont = $templateData['titleFont'] ?? 'Arial, sans-serif';
        $bodyFont = $templateData['bodyFont'] ?? 'Arial, sans-serif';
        $fontSize = $templateData['fontSize'] ?? '11px';
        
        // Table styles
        $tableHeaderColor = $templateData['tableHeaderColor'] ?? '#b3d9ff';
        $tableHeaderTextColor = $templateData['tableHeaderTextColor'] ?? '#000000';
        $tableBorderColor = $templateData['tableBorderColor'] ?? '#6c757d';
        $tableFontSize = $templateData['tableFontSize'] ?? '11px';
        
        $logoPath = $template->logo_path ?? null;
        $logoFilename = $logoPath ? basename($logoPath) : null;
    @endphp

    <!-- Email Container -->
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px 0;">
        <tr>
            <td align="center">
                <!-- Invoice Container -->
                <table width="800" cellpadding="0" cellspacing="0" style="background-color: #ffffff; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin: 0 auto;">
                    <!-- Header Section with Title and Logo -->
                    <tr>
                        <td style="padding: 40px 40px 20px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="50%" style="vertical-align: top;">
                                        <h1 style="font-size: 36px; font-weight: bold; margin: 0; font-family: {{ $titleFont }}; color: #000;">
                                            TAX INVOICE
                                        </h1>
                                    </td>
                                    <td width="50%" align="right" style="vertical-align: top;">
                                        @if (isset($template) && $template && $template->logo_path)
                                            <img src="{{ route('uploadfiles.show', ['folder' => 'invoice_logos', 'filename' => $logoFilename]) }}" 
                                                 alt="Logo" 
                                                 style="max-height: 60px; max-width: 200px; display: block;">
                                        @else
                                            <table cellpadding="0" cellspacing="0" style="display: inline-table;">
                                                <tr>
                                                    <td style="background-color: {{ $primaryColor }}; color: #ffffff; padding: 10px 20px; border-radius: 8px 0 0 8px; font-weight: 600; font-size: 18px;">
                                                        Energy
                                                    </td>
                                                    <td style="background-color: {{ $secondaryColor }}; color: #ffffff; padding: 10px 20px; border-radius: 0 8px 8px 0; font-weight: 600; font-size: 18px;">
                                                        Saviour ✓
                                                    </td>
                                                </tr>
                                            </table>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Client Info, Invoice Meta, and Company Info -->
                    <tr>
                        <td style="padding: 20px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <!-- Client Info (33%) -->
                                    <td width="33%" style="vertical-align: top; font-size: {{ $fontSize }}; line-height: 1.7; font-family: {{ $bodyFont }};">
                                        <strong style="font-size: 13px; display: block; margin-bottom: 8px;">Invoice to:</strong>
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
                                        @if (isset($client->Email) && $client->Email)
                                            <div>E: {{ $client->Email }}</div>
                                        @endif
                                    </td>

                                    <!-- Invoice Meta (33%) -->
                                    <td width="33%" style="vertical-align: top; font-size: {{ $fontSize }}; line-height: 1.9; font-family: {{ $bodyFont }}; padding-left: 20px;">
                                        <div><strong>Invoice Date:</strong> {{ $invoiceDate }}</div>
                                        <div><strong>Inv Due Date:</strong> {{ $dueDate }}</div>
                                        <div><strong>Invoice No:</strong> {{ $invoiceNo }}</div>
                                        <div><strong>Invoice Ref:</strong> {{ $invoiceRef }}</div>
                                    </td>

                                    <!-- Company Info (33%) -->
                                    <td width="33%" style="vertical-align: top; font-size: {{ $fontSize }}; line-height: 1.7; font-family: {{ $bodyFont }}; padding-left: 20px;">
                                        <strong style="display: block; margin-bottom: 8px;">{{ $client->Business_Name ?? 'Your Business' }}</strong>
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
                        </td>
                    </tr>

                    <!-- Invoice Items Table -->
                    <tr>
                        <td style="padding: 20px 40px;">
                            <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; font-family: {{ $bodyFont }};">
                                <thead>
                                    <tr style="background-color: {{ $tableHeaderColor }};">
                                        <th style="border: 1px solid {{ $tableBorderColor }}; padding: 8px; text-align: center; font-size: 12px; font-weight: 600; color: {{ $tableHeaderTextColor }}; width: 70px;">
                                            Image
                                        </th>
                                        <th style="border: 1px solid {{ $tableBorderColor }}; padding: 8px; text-align: left; font-size: 12px; font-weight: 600; color: {{ $tableHeaderTextColor }};">
                                            Description
                                        </th>
                                        <th style="border: 1px solid {{ $tableBorderColor }}; padding: 8px; text-align: center; font-size: 12px; font-weight: 600; color: {{ $tableHeaderTextColor }}; width: 60px;">
                                            Qty
                                        </th>
                                        <th style="border: 1px solid {{ $tableBorderColor }}; padding: 8px; text-align: right; font-size: 12px; font-weight: 600; color: {{ $tableHeaderTextColor }}; width: 100px;">
                                            Unit Price
                                        </th>
                                        <th style="border: 1px solid {{ $tableBorderColor }}; padding: 8px; text-align: right; font-size: 12px; font-weight: 600; color: {{ $tableHeaderTextColor }}; width: 120px;">
                                            VAT
                                        </th>
                                        <th style="border: 1px solid {{ $tableBorderColor }}; padding: 8px; text-align: right; font-size: 12px; font-weight: 600; color: {{ $tableHeaderTextColor }}; width: 110px;">
                                            Total Amount
                                        </th>
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
                                                <td style="border: 1px solid {{ $tableBorderColor }}; padding: 8px; font-size: {{ $tableFontSize }}; text-align: center; vertical-align: middle;">
                                                    @if ($productImage)
                                                        <img src="{{ $productImage }}" 
                                                             alt="{{ $item['item_code'] ?? 'Product' }}"
                                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6; display: block; margin: 0 auto;">
                                                    @else
                                                        <div style="width: 50px; height: 50px; background-color: #f8f9fa; border: 1px dashed #dee2e6; border-radius: 4px; margin: 0 auto;"></div>
                                                    @endif
                                                </td>
                                                <td style="border: 1px solid {{ $tableBorderColor }}; padding: 8px; font-size: {{ $tableFontSize }};">
                                                    {{ $item['description'] ?? ($item['item_code'] ?? 'N/A') }}
                                                </td>
                                                <td style="border: 1px solid {{ $tableBorderColor }}; padding: 8px; font-size: {{ $tableFontSize }}; text-align: center;">
                                                    {{ $quantity }}
                                                </td>
                                                <td style="border: 1px solid {{ $tableBorderColor }}; padding: 8px; font-size: {{ $tableFontSize }}; text-align: right;">
                                                    £{{ number_format($unitAmount, 2) }}
                                                </td>
                                                <td style="border: 1px solid {{ $tableBorderColor }}; padding: 8px; font-size: {{ $tableFontSize }}; text-align: right;">
                                                    £{{ number_format($vatAmount, 2) }} ({{ $vatRate }}%)
                                                </td>
                                                <td style="border: 1px solid {{ $tableBorderColor }}; padding: 8px; font-size: {{ $tableFontSize }}; text-align: right;">
                                                    £{{ number_format($lineTotal, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="6" style="border: 1px solid {{ $tableBorderColor }}; padding: 8px; text-align: center;">
                                                No items found
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    <!-- Payment Details and Totals -->
                    <tr>
                        <td style="padding: 20px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <!-- Payment Details (60%) -->
                                    <td width="60%" style="vertical-align: top; font-size: {{ $fontSize }}; line-height: 1.7; font-family: {{ $bodyFont }};">
                                        <strong style="display: block; margin-bottom: 8px;">Please make electronic payment to</strong>
                                        <div>Name: {{ $client->Business_Name ?? 'Business Name' }}</div>
                                        <div>Sort Code: {{ $bankAccount->Sort_Code ?? 'N/A' }}</div>
                                        <div>Account No: {{ $bankAccount->Account_No ?? 'N/A' }}</div>
                                        <div>Payment Ref: {{ $invoiceNo }}</div>
                                    </td>

                                    <!-- Totals (40%) -->
                                    <td width="40%" style="vertical-align: top; padding-left: 20px;">
                                        <table width="100%" cellpadding="8" cellspacing="0" style="font-size: 12px; font-family: {{ $bodyFont }};">
                                            <tr>
                                                <td style="text-align: right;"><strong>NET</strong></td>
                                                <td style="text-align: right; font-weight: 600;">£{{ $netAmount }}</td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: right;"><strong>VAT</strong></td>
                                                <td style="text-align: right; font-weight: 600;">£{{ $vatAmount }}</td>
                                            </tr>
                                            <tr style="border-top: 2px solid #000;">
                                                <td style="padding-top: 8px; text-align: right;"><strong>TOTAL</strong></td>
                                                <td style="padding-top: 8px; text-align: right; font-weight: bold; font-size: 14px;">£{{ $totalAmount }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 40px; border-top: 1px solid #ddd; text-align: center;">
                            <p style="font-size: 9px; color: #666; margin: 0; font-family: {{ $bodyFont }};">
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
                            </p>
                        </td>
                    </tr>

                    <!-- Email Footer Message -->
                    <tr>
                        <td style="background-color: #f8f9fa; padding: 20px 40px; text-align: center;">
                            <p style="font-size: 12px; color: #666; margin: 0;">
                                This is an automated email. Please do not reply to this message.<br>
                                &copy; {{ date('Y') }} {{ $client->Business_Name ?? 'Your Business' }}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>