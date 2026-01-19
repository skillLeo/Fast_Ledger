<!DOCTYPE html>
<html>
@php
    $isCompanyModule = $isCompanyModule ?? false;

    if ($isCompanyModule) {
        $invoiceToData = $customerData ?? $client;
        $yourBusinessData = $companyData ?? $client;
    } else {
        $invoiceToData = $fileData ?? $client;
        $yourBusinessData = $client;
    }
@endphp

<head>
    <meta charset="utf-8">
    <title>{{ __('company.invoice') }} {{ $validated['invoice_no'] ?? 'N/A' }}</title>
    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }

        .invoice-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        /* Header Section */
        .invoice-header {
            width: 100%;
            margin-bottom: 20px;
            padding-bottom: 15px;
        }

        .invoice-header table {
            width: 100%;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            font-family: {{ $titleFont ?? 'Arial' }};
        }

        .logo-container {
            text-align: right;
        }

        .logo-energy {
            background-color: {{ $primaryColor ?? '#1e3a8a' }};
            color: white;
            padding: 8px 16px;
            border-radius: 8px 0 0 8px;
            font-weight: 600;
            font-size: 16px;
            display: inline-block;
        }

        .logo-saviour {
            background-color: {{ $secondaryColor ?? '#16a34a' }};
            color: white;
            padding: 8px 16px;
            border-radius: 0 8px 8px 0;
            font-weight: 600;
            font-size: 16px;
            display: inline-block;
        }

        /* Info Section */
        .info-section {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-section table {
            width: 100%;
        }

        .info-section td {
            vertical-align: top;
            padding: 0 10px;
        }

        .section-label {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .info-text {
            font-size: {{ $fontSize ?? '11px' }};
            line-height: 1.6;
            font-family: {{ $bodyFont ?? 'Arial' }};
        }

        /* Invoice Table */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .invoice-table th {
            background-color: {{ $tableHeaderColor ?? '#b3d9ff' }} !important;
            color: {{ $tableHeaderTextColor ?? '#000000' }} !important;
            font-weight: 600;
            font-size: {{ $tableFontSize ?? '11px' }};
            padding: {{ $tableRowHeight ?? '12px' }};
            text-align: center;
            border: 1px solid {{ $tableBorderColor ?? '#6c757d' }};
        }

        .invoice-table td {
            border: 1px solid {{ $tableBorderColor ?? '#6c757d' }};
            padding: {{ $tableRowHeight ?? '12px' }};
            font-size: {{ $tableFontSize ?? '11px' }};
            text-align: center;
        }

        .invoice-table td:first-child {
            text-align: left;
        }

        .invoice-table td img {
            max-width: 50px;
            max-height: 50px;
            object-fit: cover;
        }

        /* Footer Section */
        .footer-section {
            width: 100%;
            margin-top: 20px;
        }

        .footer-section table {
            width: 100%;
        }

        .footer-section td {
            vertical-align: top;
        }

        .totals-table {
            width: 100%;
            font-size: 12px;
        }

        .totals-table td {
            padding: 6px 8px;
            text-align: right;
        }

        .totals-table .total-label {
            font-weight: 600;
        }

        .totals-table .final-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #000 !important;
            padding-top: 8px;
        }

        .footer-text {
            text-align: center;
            font-size: 9px;
            color: #666;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <table>
                <tr>
                    <td style="width: 50%;">
                        <div class="invoice-title">{{ __('company.tax_invoice') }}</div>
                    </td>
                    <td style="width: 50%;">
                        <div class="logo-container">
                            @if (isset($logoFullPath) && $logoFullPath && file_exists($logoFullPath))
                                <img src="{{ $logoFullPath }}" alt="{{ __('company.logo') }}"
                                    style="max-height: 60px; max-width: 200px; object-fit: contain;">
                            @elseif(isset($logoPath) && $logoPath)
                                <img src="{{ storage_path('app/public/' . $logoPath) }}" alt="{{ __('company.logo') }}"
                                    style="max-height: 60px; max-width: 200px; object-fit: contain;">
                            @else
                                <span class="logo-energy">Energy</span><span class="logo-saviour">Saviour ✓</span>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <table>
                <tr>
                    <td style="width: 33%;">
                        <div class="section-label">{{ __('company.invoice_to') }}</div>
                        <div class="info-text">
                            @if ($isCompanyModule)
                                {{-- Company Module: Show Customer --}}
                                <strong>{{ $invoiceToData->Legal_Name_Company_Name ?? ($invoiceToData->Business_Name ?? __('company.customer_name')) }}</strong><br>
                                @if (isset($invoiceToData->Street_Address) && $invoiceToData->Street_Address)
                                    {{ $invoiceToData->Street_Address }}<br>
                                @endif
                                @if (isset($invoiceToData->City) && $invoiceToData->City)
                                    {{ $invoiceToData->City }}<br>
                                @endif
                                @if (isset($invoiceToData->Postal_Code) && $invoiceToData->Postal_Code)
                                    {{ $invoiceToData->Postal_Code }}<br>
                                @endif
                                @if (isset($invoiceToData->Phone) && $invoiceToData->Phone)
                                    {{ __('company.phone_short') }}: {{ $invoiceToData->Phone }}<br>
                                @endif
                                @if (isset($invoiceToData->Email) && $invoiceToData->Email)
                                    {{ __('company.email_short') }}: {{ $invoiceToData->Email }}<br>
                                @endif
                                @if (isset($invoiceToData->Tax_ID_Number) && $invoiceToData->Tax_ID_Number)
                                    {{ __('company.vat_no') }}: {{ $invoiceToData->Tax_ID_Number }}<br>
                                @endif
                            @else
                                {{-- Main App: Show File --}}
                                @if (isset($fileData) && $fileData)
                                    <strong>{{ $fileData->First_Name }} {{ $fileData->Last_Name }}</strong><br>
                                    @if ($fileData->Address1)
                                        {{ $fileData->Address1 }}<br>
                                    @endif
                                    @if ($fileData->Town)
                                        {{ $fileData->Town }}<br>
                                    @endif
                                    @if ($fileData->Post_Code)
                                        {{ $fileData->Post_Code }}<br>
                                    @endif
                                    @if ($fileData->Phone)
                                        {{ __('company.phone_short') }}: {{ $fileData->Phone }}<br>
                                    @endif
                                    @if ($fileData->Email)
                                        {{ __('company.email_short') }}: {{ $fileData->Email }}<br>
                                    @endif
                                @else
                                    <strong>{{ $client->Business_Name ?? __('company.client_name') }}</strong><br>
                                    @if (isset($client->Address1))
                                        {{ $client->Address1 }}<br>
                                    @endif
                                @endif
                            @endif
                        </div>
                    </td>
                    <td style="width: 33%;">
                        <div class="info-text">
                            <strong>{{ __('company.invoice_date') }}</strong>
                            {{ isset($validated['Transaction_Date']) ? \Carbon\Carbon::parse($validated['Transaction_Date'])->format('d/m/Y') : date('d/m/Y') }}<br>
                            <strong>{{ __('company.inv_due_date') }}</strong>
                            {{ isset($validated['Inv_Due_Date']) ? \Carbon\Carbon::parse($validated['Inv_Due_Date'])->format('d/m/Y') : date('d/m/Y', strtotime('+30 days')) }}<br>
                            <strong>{{ __('company.invoice_no') }}</strong> {{ $validated['invoice_no'] ?? 'N/A' }}<br>
                            <strong>{{ __('company.invoice_ref') }}</strong> {{ $validated['invoice_ref'] ?? 'N/A' }}
                        </div>
                    </td>
                    <td style="width: 33%;">
                        <div class="info-text">
                            <strong>{{ $yourBusinessData->Company_Name ?? ($yourBusinessData->Business_Name ?? __('company.your_business')) }}</strong><br>
                            @if (isset($yourBusinessData->Street_Address) && $yourBusinessData->Street_Address)
                                {{ $yourBusinessData->Street_Address }}<br>
                            @elseif (isset($yourBusinessData->Address1) && $yourBusinessData->Address1)
                                {{ $yourBusinessData->Address1 }}<br>
                            @endif
                            @if (isset($yourBusinessData->City) && $yourBusinessData->City)
                                {{ $yourBusinessData->City }}<br>
                            @elseif (isset($yourBusinessData->Town) && $yourBusinessData->Town)
                                {{ $yourBusinessData->Town }}<br>
                            @endif
                            @if (isset($yourBusinessData->Postal_Code) && $yourBusinessData->Postal_Code)
                                {{ $yourBusinessData->Postal_Code }}<br>
                            @elseif (isset($yourBusinessData->Post_Code) && $yourBusinessData->Post_Code)
                                {{ $yourBusinessData->Post_Code }}<br>
                            @endif
                            @if (isset($yourBusinessData->Contact_Phone) && $yourBusinessData->Contact_Phone)
                                {{ __('company.phone_short') }}: {{ $yourBusinessData->Contact_Phone }}<br>
                            @elseif (isset($yourBusinessData->Phone) && $yourBusinessData->Phone)
                                {{ __('company.phone_short') }}: {{ $yourBusinessData->Phone }}<br>
                            @endif
                            @if (isset($yourBusinessData->Contact_Email) && $yourBusinessData->Contact_Email)
                                {{ __('company.email_short') }}: {{ $yourBusinessData->Contact_Email }}<br>
                            @elseif (isset($yourBusinessData->Email) && $yourBusinessData->Email)
                                {{ __('company.email_short') }}: {{ $yourBusinessData->Email }}<br>
                            @endif
                            @if (isset($yourBusinessData->Tax_ID) && $yourBusinessData->Tax_ID)
                                {{ __('company.vat_no') }}: {{ $yourBusinessData->Tax_ID }}<br>
                            @elseif (isset($yourBusinessData->VAT_Registration_No) && $yourBusinessData->VAT_Registration_No)
                                {{ __('company.vat_no') }}: {{ $yourBusinessData->VAT_Registration_No }}<br>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Items Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 10%;">{{ __('company.image') }}</th>
                    <th style="width: 35%;">{{ __('company.description') }}</th>
                    <th style="width: 10%;">{{ __('company.qty') }}</th>
                    <th style="width: 15%;">{{ __('company.unit_price') }}</th>
                    <th style="width: 15%;">{{ __('company.vat') }}</th>
                    <th style="width: 15%;">{{ __('company.total_amount') }}</th>
                </tr>
            </thead>

            <tbody>
                @if (isset($validated['items']) && is_array($validated['items']) && count($validated['items']) > 0)
                    @foreach ($validated['items'] as $item)
                        @php
                            $quantity = intval($item['qty'] ?? 1);
                            $unitAmount = floatval($item['unit_amount'] ?? 0);
                            $vatAmount = floatval($item['vat_amount'] ?? 0);
                            $vatRate = intval($item['vat_rate'] ?? 20);

                            $lineSubtotal = $unitAmount * $quantity;
                            $lineVatTotal = $vatAmount;
                            $lineTotal = $lineSubtotal + $lineVatTotal;

                            $productImage = $item['product_image'] ?? null;
                        @endphp
                        <tr>
                            <td style="text-align: center;">
                                @if ($productImage && file_exists(public_path(str_replace(url('/'), '', $productImage))))
                                    <img src="{{ public_path(str_replace(url('/'), '', $productImage)) }}"
                                        alt="{{ __('company.product') }}">
                                @else
                                    <div style="font-size: 9px; color: #999;">{{ __('company.no_image') }}</div>
                                @endif
                            </td>
                            <td>{{ $item['description'] ?? ($item['item_code'] ?? 'N/A') }}</td>
                            <td>{{ $quantity }}</td>
                            <td>£{{ number_format($lineSubtotal, 2) }}</td>
                            <td>£{{ number_format($lineVatTotal, 2) }}</td>
                            <td>£{{ number_format($lineTotal, 2) }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <!-- Invoice Notes -->
        @if (isset($invoiceNotes) && count($invoiceNotes) > 0)
            <div style="margin-bottom: 20px; margin-top: 20px;">
                <div
                    style="font-weight: 600; color: {{ $primaryColor ?? '#1e3a8a' }}; font-family: {{ $titleFont ?? 'Arial' }}; font-size: 13px; margin-bottom: 10px;">
                    {{ __('company.additional_notes') }}
                </div>

                @foreach ($invoiceNotes as $note)
                    @if ($note['has_table'] && $note['table_html'])
                        @php
                            $styledTable = preg_replace(
                                ['/<table>/', '/<thead>/', '/<tbody>/', '/<tr>/', '/<th([^>]*)>/', '/<td([^>]*)>/'],
                                [
                                    '<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">',
                                    '<thead>',
                                    '<tbody>',
                                    '<tr>',
                                    '<th$1 style="background-color: ' .
                                    ($tableHeaderColor ?? '#b3d9ff') .
                                    ' !important; color: ' .
                                    ($tableHeaderTextColor ?? '#000000') .
                                    ' !important; font-weight: 600; font-size: ' .
                                    ($tableFontSize ?? '11px') .
                                    '; padding: ' .
                                    ($tableRowHeight ?? '12px') .
                                    '; text-align: center; border: 1px solid ' .
                                    ($tableBorderColor ?? '#6c757d') .
                                    ';">',
                                    '<td$1 style="border: 1px solid ' .
                                    ($tableBorderColor ?? '#6c757d') .
                                    '; padding: ' .
                                    ($tableRowHeight ?? '12px') .
                                    '; font-size: ' .
                                    ($tableFontSize ?? '11px') .
                                    '; text-align: center;">',
                                ],
                                $note['table_html'],
                            );
                        @endphp
                        {!! $styledTable !!}
                    @endif

                    @if (!empty($note['text']))
                        <div
                            style="font-size: {{ $fontSize ?? '11px' }}; font-family: {{ $bodyFont ?? 'Arial' }}; line-height: 1.5; color: #333; margin-top: 8px; margin-bottom: 15px;">
                            {{ $note['text'] }}
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        <!-- Footer Section -->
        <div class="footer-section">
            <table>
                <tr>
                    <td style="width: 60%;">
                        <!-- Empty space for alignment -->
                    </td>
                    <td style="width: 40%;">
                        <table class="totals-table">
                            <tr>
                                <td class="total-label">{{ __('company.net') }}</td>
                                <td>£{{ number_format(floatval($validated['invoice_net_amount'] ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <td class="total-label">{{ __('company.vat') }}</td>
                                <td>£{{ number_format(floatval($validated['invoice_vat_amount'] ?? 0), 2) }}</td>
                            </tr>
                            <tr class="final-total" style="border-top: #000">
                                <td class="total-label">{{ __('company.total') }}</td>
                                <td>£{{ number_format(floatval($validated['invoice_total_amount'] ?? 0), 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer Text -->
        <div class="footer-text">
            @php
                $footerParts = [];

                if (isset($yourBusinessData->Company_Reg_No) && $yourBusinessData->Company_Reg_No) {
                    $footerParts[] = __('company.company_registration_no') . ': ' . $yourBusinessData->Company_Reg_No;
                }

                $address = __('company.registered_office') . ': ';
                $addressParts = [];

                if (isset($yourBusinessData->Street_Address) && $yourBusinessData->Street_Address) {
                    $addressParts[] = $yourBusinessData->Street_Address;
                } elseif (isset($yourBusinessData->Address1) && $yourBusinessData->Address1) {
                    $addressParts[] = $yourBusinessData->Address1;
                }

                if (isset($yourBusinessData->City) && $yourBusinessData->City) {
                    $addressParts[] = $yourBusinessData->City;
                } elseif (isset($yourBusinessData->Town) && $yourBusinessData->Town) {
                    $addressParts[] = $yourBusinessData->Town;
                }

                if (isset($yourBusinessData->Postal_Code) && $yourBusinessData->Postal_Code) {
                    $addressParts[] = $yourBusinessData->Postal_Code;
                } elseif (isset($yourBusinessData->Post_Code) && $yourBusinessData->Post_Code) {
                    $addressParts[] = $yourBusinessData->Post_Code;
                }

                $address .= implode(', ', $addressParts);
                $footerParts[] = $address;

                echo implode(' | ', $footerParts);
            @endphp
        </div>
    </div>
</body>

</html>