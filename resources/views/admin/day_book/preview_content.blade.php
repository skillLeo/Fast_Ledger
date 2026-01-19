@php
    // ✅ CRITICAL: Extract template data FIRST (before anything else)
    $templateData = [];
    if (isset($template) && $template) {
        if (is_string($template->template_data)) {
            $templateData = json_decode($template->template_data, true) ?? [];
        } elseif (is_array($template->template_data)) {
            $templateData = $template->template_data;
        }
    }

    // ✅ NOW determine which data to use
    $isCompanyModule = $isCompanyModule ?? false;

    if ($isCompanyModule) {
        // Company Module: Customer in "Invoice To", Company in "Your Business"
        $invoiceToData = $customerData ?? $client;
        $yourBusinessData = $companyData ?? $client;

        Log::info('Blade: Using Company Module data', [
            'has_customer' => isset($customerData),
            'has_company' => isset($companyData),
            'customer_name' => $customerData->Business_Name ?? 'none',
            'company_name' => $companyData->Business_Name ?? 'none',
        ]);
    } else {
        // Main App: File in "Invoice To", Client in "Your Business"
        $invoiceToData = $fileData ?? $client;
        $yourBusinessData = $client;

        Log::info('Blade: Using Main App data', [
            'has_file' => isset($fileData),
            'has_client' => isset($client),
        ]);
    }

    // Get colors and styling (this now works because $templateData is defined)
    $primaryColor = $templateData['primaryColor'] ?? '#1e3a8a';
    $secondaryColor = $templateData['secondaryColor'] ?? '#16a34a';
    $titleFont = $templateData['titleFont'] ?? 'Arial';
    $bodyFont = $templateData['bodyFont'] ?? 'Arial';
    $fontSize = $templateData['fontSize'] ?? '11px';
    $positions = $templateData['positions'] ?? [];

    // ADD THESE NEW LINES FOR TABLE STYLES
    $tableHeaderColor = $templateData['tableHeaderColor'] ?? '#b3d9ff';
    $tableHeaderTextColor = $templateData['tableHeaderTextColor'] ?? '#000000';
    $tableBorderColor = $templateData['tableBorderColor'] ?? '#6c757d';
    $tableRowHeight = $templateData['tableRowHeight'] ?? '4px';
    $tableFontSize = $templateData['tableFontSize'] ?? '11px';

    // Helper function to get position styles
    function getPositionStyle($elementKey, $positions, $containerWidth = 810)
    {
        if (isset($positions[$elementKey])) {
            $pos = $positions[$elementKey];
            $style = '';

            if (isset($pos['left'])) {
                $style .= "left: {$pos['left']}px; ";
            }
            if (isset($pos['top'])) {
                $style .= "top: {$pos['top']}px; ";
            }
            if (isset($pos['right'])) {
                $style .= "right: {$pos['right']}px; ";
            }
            if (isset($pos['bottom'])) {
                $style .= "bottom: {$pos['bottom']}px; ";
            }

            return $style;
        }
        return '';
    }

    $logoPosition = getPositionStyle('invoice-logo', $positions) ?: 'top: 20px; right: 20px;';
    $logoPath = $template->logo_path ?? null;
    $logoFilename = $logoPath ? basename($logoPath) : null;
@endphp

<div class="invoice-container" id="invoice-container"
    style="background-color: white; padding: 40px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); position: relative; min-height: 1100px; overflow: hidden; max-width: 850px; margin: 0 auto;">
    
    <!-- Title -->
    <div id="invoice-title"
        style="position: absolute; {{ getPositionStyle('invoice-title', $positions) ?: 'top: 20px; left: 20px;' }} z-index: 10;">
        <h4 style="font-size: 36px; font-weight: bold; margin: 0; font-family: {{ $titleFont }};">
            {{ __('company.tax_invoice') }}
        </h4>
    </div>

    <!-- Logo -->
    <div id="invoice-logo"
        style="position: absolute; {{ getPositionStyle('invoice-logo', $positions) ?: 'top: 20px; right: 20px;' }} z-index: 10; max-width: 250px;">
        <div class="logo-container d-inline-flex">
            @if (isset($template) && $template && $template->logo_path)
                <img src="{{ route('uploadfiles.show', ['folder' => 'invoice_logos', 'filename' => $logoFilename]) }}"
                    alt="{{ __('company.logo') }}" style="max-height: 60px; max-width: 200px; object-fit: contain;">
            @else
                <div class="logo-energy"
                    style="background-color: {{ $primaryColor }}; color: white; padding: 10px 20px; border-radius: 8px 0 0 8px; font-weight: 600; font-size: 18px;">
                    Energy
                </div>
                <div class="logo-saviour"
                    style="background-color: {{ $secondaryColor }}; color: white; padding: 10px 20px; border-radius: 0 8px 8px 0; font-weight: 600; font-size: 18px; display: flex; align-items: center; gap: 8px;">
                    Saviour
                    <div
                        style="width: 24px; height: 24px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <div
                            style="width: 12px; height: 16px; border-right: 3px solid {{ $secondaryColor }}; border-bottom: 3px solid {{ $secondaryColor }}; transform: rotate(45deg) translateY(-2px);">
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Spacer -->
    <div style="height: 100px;"></div>

    <!-- Client Info (Invoice To) -->
    <div id="invoice-client"
        style="position: absolute; {{ getPositionStyle('invoice-client', $positions) ?: 'top: 120px; left: 20px;' }} width: 250px; z-index: 10;">
        <div style="font-size: {{ $fontSize }}; line-height: 1.7; font-family: {{ $bodyFont }};">
            <strong style="font-size: 13px; display: block; margin-bottom: 8px;">{{ __('company.invoice_to') }}</strong>

            @if ($isCompanyModule)
                {{-- Company Module: Show Customer Data --}}
                <div>
                    <strong>{{ $invoiceToData->Legal_Name_Company_Name ?? ($invoiceToData->Business_Name ?? __('company.customer_name')) }}</strong>
                </div>

                @if (isset($invoiceToData->Contact_Person_Name) && $invoiceToData->Contact_Person_Name)
                    <div>{{ $invoiceToData->Contact_Person_Name }}</div>
                @endif

                @if (isset($invoiceToData->Street_Address) && $invoiceToData->Street_Address)
                    <div>{{ $invoiceToData->Street_Address }}</div>
                @endif

                @if (isset($invoiceToData->City) && $invoiceToData->City)
                    <div>{{ $invoiceToData->City }}</div>
                @endif

                @if (isset($invoiceToData->Province) && $invoiceToData->Province)
                    <div>{{ $invoiceToData->Province }}</div>
                @endif

                @if (isset($invoiceToData->Postal_Code) && $invoiceToData->Postal_Code)
                    <div>{{ $invoiceToData->Postal_Code }}</div>
                @endif

                @if (isset($invoiceToData->Country) && $invoiceToData->Country)
                    <div>{{ $invoiceToData->Country }}</div>
                @endif

                @if (isset($invoiceToData->Phone) && $invoiceToData->Phone)
                    <div>{{ __('company.phone_short') }}: {{ $invoiceToData->Phone }}</div>
                @endif

                @if (isset($invoiceToData->Email) && $invoiceToData->Email)
                    <div>{{ __('company.email_short') }}: {{ $invoiceToData->Email }}</div>
                @endif

                @if (isset($invoiceToData->Tax_ID_Number) && $invoiceToData->Tax_ID_Number)
                    <div>{{ __('company.vat_no') }}: {{ $invoiceToData->Tax_ID_Number }}</div>
                @endif
            @else
                {{-- Main App: Show File Data --}}
                @if (isset($fileData) && $fileData)
                    <div><strong>{{ $fileData->First_Name }} {{ $fileData->Last_Name }}</strong></div>
                    @if ($fileData->Matter)
                        <div>{{ __('company.matter') }}: {{ $fileData->Matter }}</div>
                    @endif
                    @if ($fileData->Address1)
                        <div>{{ $fileData->Address1 }}</div>
                    @endif
                    @if ($fileData->Town)
                        <div>{{ $fileData->Town }}</div>
                    @endif
                    @if ($fileData->Post_Code)
                        <div>{{ $fileData->Post_Code }}</div>
                    @endif
                    @if ($fileData->Phone)
                        <div>{{ __('company.phone_short') }}: {{ $fileData->Phone }}</div>
                    @endif
                    @if ($fileData->Email)
                        <div>{{ __('company.email_short') }}: {{ $fileData->Email }}</div>
                    @endif
                @else
                    <div><strong>{{ $client->Business_Name ?? __('company.client_name') }}</strong></div>
                @endif
            @endif
        </div>
    </div>

    <!-- Invoice Meta -->
    <div id="invoice-meta"
        style="position: absolute; {{ getPositionStyle('invoice-meta', $positions) ?: 'top: 120px; left: 300px;' }} width: 240px; z-index: 10;">
        <div style="font-size: {{ $fontSize }}; line-height: 1.9; font-family: {{ $bodyFont }};">
            <div><strong>{{ __('company.invoice_date') }}</strong>
                {{ isset($validated['Transaction_Date']) ? \Carbon\Carbon::parse($validated['Transaction_Date'])->format('d/m/Y') : date('d/m/Y') }}
            </div>
            <div><strong>{{ __('company.inv_due_date') }}</strong>
                {{ isset($validated['Inv_Due_Date']) ? \Carbon\Carbon::parse($validated['Inv_Due_Date'])->format('d/m/Y') : date('d/m/Y', strtotime('+30 days')) }}
            </div>
            <div><strong>{{ __('company.invoice_no') }}</strong>
                {{ $validated['invoice_no'] ?? ($validated['Transaction_Code'] ?? 'N/A') }}</div>
            <div><strong>{{ __('company.invoice_ref') }}</strong> {{ $validated['invoice_ref'] ?? 'N/A' }}</div>
        </div>
    </div>

    <!-- Company Info (Your Business) -->
    <div id="invoice-company"
        style="position: absolute; {{ getPositionStyle('invoice-company', $positions) ?: 'top: 120px; right: 20px;' }} width: 240px; z-index: 10;">
        <div style="font-size: {{ $fontSize }}; line-height: 1.7; font-family: {{ $bodyFont }};">

            <strong style="display: block; margin-bottom: 8px;">
                {{ $yourBusinessData->Company_Name ?? ($yourBusinessData->Business_Name ?? __('company.your_business')) }}
            </strong>

            @if (isset($yourBusinessData->Street_Address) && $yourBusinessData->Street_Address)
                <div>{{ $yourBusinessData->Street_Address }}</div>
            @elseif (isset($yourBusinessData->Address1) && $yourBusinessData->Address1)
                <div>{{ $yourBusinessData->Address1 }}</div>
            @endif

            @if (isset($yourBusinessData->City) && $yourBusinessData->City)
                <div>{{ $yourBusinessData->City }}</div>
            @elseif (isset($yourBusinessData->Town) && $yourBusinessData->Town)
                <div>{{ $yourBusinessData->Town }}</div>
            @endif

            @if (isset($yourBusinessData->State) && $yourBusinessData->State)
                <div>{{ $yourBusinessData->State }}</div>
            @endif

            @if (isset($yourBusinessData->Postal_Code) && $yourBusinessData->Postal_Code)
                <div>{{ $yourBusinessData->Postal_Code }}</div>
            @elseif (isset($yourBusinessData->Post_Code) && $yourBusinessData->Post_Code)
                <div>{{ $yourBusinessData->Post_Code }}</div>
            @endif

            @if (isset($yourBusinessData->Country) && $yourBusinessData->Country)
                <div>{{ $yourBusinessData->Country }}</div>
            @endif

            @if (isset($yourBusinessData->Contact_Phone) && $yourBusinessData->Contact_Phone)
                <div>{{ __('company.phone_short') }}: {{ $yourBusinessData->Contact_Phone }}</div>
            @elseif (isset($yourBusinessData->Phone) && $yourBusinessData->Phone)
                <div>{{ __('company.phone_short') }}: {{ $yourBusinessData->Phone }}</div>
            @endif

            @if (isset($yourBusinessData->Contact_Email) && $yourBusinessData->Contact_Email)
                <div>{{ __('company.email_short') }}: {{ $yourBusinessData->Contact_Email }}</div>
            @elseif (isset($yourBusinessData->Email) && $yourBusinessData->Email)
                <div>{{ __('company.email_short') }}: {{ $yourBusinessData->Email }}</div>
            @endif

            @if (isset($yourBusinessData->Tax_ID) && $yourBusinessData->Tax_ID)
                <div>{{ __('company.vat_no') }}: {{ $yourBusinessData->Tax_ID }}</div>
            @elseif (isset($yourBusinessData->VAT_Registration_No) && $yourBusinessData->VAT_Registration_No)
                <div>{{ __('company.vat_no') }}: {{ $yourBusinessData->VAT_Registration_No }}</div>
            @endif

            @if (isset($yourBusinessData->Company_Reg_No) && $yourBusinessData->Company_Reg_No)
                <div>{{ __('company.reg_no') }}: {{ $yourBusinessData->Company_Reg_No }}</div>
            @endif

            @if (isset($yourBusinessData->Website) && $yourBusinessData->Website)
                <div>{{ __('company.website_short') }}: {{ $yourBusinessData->Website }}</div>
            @endif
        </div>
    </div>

    <!-- Invoice Table -->
    <div id="invoice-table" class="invoice-table-wrapper" data-element="table"
        style="position: absolute; {{ getPositionStyle('invoice-table', $positions) ?: 'top: 300px; left: 20px; right: 20px;' }} z-index: 9; box-sizing: border-box; width: calc(100% - 40px);">
        <table class="invoice-table"
            style="width: 100%; box-sizing: border-box; table-layout: fixed; border-collapse: collapse; margin-bottom: 40px; font-family: {{ $bodyFont }};">

            <thead>
                <tr class="table-header-row" style="background-color: {{ $tableHeaderColor }} !important;">
                    <th class="table-header-cell"
                        style="border: 1px solid {{ $tableBorderColor }}; padding: {{ $tableRowHeight }}; text-align: center; font-size: 12px; font-weight: 600; width: 70px; color: {{ $tableHeaderTextColor }} !important; background-color: {{ $tableHeaderColor }} !important;">
                        {{ __('company.image') }}
                    </th>

                    <th class="table-header-cell"
                        style="border: 1px solid {{ $tableBorderColor }}; padding: {{ $tableRowHeight }}; text-align: left; font-size: 12px; font-weight: 600; color: {{ $tableHeaderTextColor }} !important; background-color: {{ $tableHeaderColor }} !important;">
                        {{ __('company.description') }}
                    </th>
                    <th class="table-header-cell"
                        style="border: 1px solid {{ $tableBorderColor }}; padding: {{ $tableRowHeight }}; text-align: center; font-size: 12px; font-weight: 600; width: 80px; color: {{ $tableHeaderTextColor }} !important; background-color: {{ $tableHeaderColor }} !important;">
                        {{ __('company.qty') }}
                    </th>
                    <th class="table-header-cell"
                        style="border: 1px solid {{ $tableBorderColor }}; padding: {{ $tableRowHeight }}; text-align: right; font-size: 12px; font-weight: 600; width: 120px; color: {{ $tableHeaderTextColor }} !important; background-color: {{ $tableHeaderColor }} !important;">
                        {{ __('company.unit_price') }}
                    </th>
                    <th class="table-header-cell"
                        style="border: 1px solid {{ $tableBorderColor }}; padding: {{ $tableRowHeight }}; text-align: right; font-size: 12px; font-weight: 600; width: 150px; color: {{ $tableHeaderTextColor }} !important; background-color: {{ $tableHeaderColor }} !important;">
                        {{ __('company.vat') }}
                    </th>
                    <th class="table-header-cell"
                        style="border: 1px solid {{ $tableBorderColor }}; padding: {{ $tableRowHeight }}; text-align: right; font-size: 12px; font-weight: 600; width: 130px; color: {{ $tableHeaderTextColor }} !important; background-color: {{ $tableHeaderColor }} !important;">
                        {{ __('company.total_amount') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @if (isset($validated['items']) && is_array($validated['items']) && count($validated['items']) > 0)
                    @foreach ($validated['items'] as $key => $item)
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
                        <tr class="table-body-row">
                            <td class="table-body-cell"
                                style="border: 1px solid {{ $tableBorderColor }}; padding: 4px; font-size: {{ $tableFontSize }}; text-align: center; vertical-align: middle;">
                                @if ($productImage)
                                    <img src="{{ $productImage }}" alt="{{ $item['item_code'] ?? __('company.product') }}"
                                        style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #dee2e6; cursor: pointer;"
                                        onclick="showImageModal('{{ $productImage }}')"
                                        title="{{ __('company.click_view_full_size') }}">
                                @else
                                    <div
                                        style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background-color: #f8f9fa; border: 1px dashed #dee2e6; border-radius: 4px; margin: 0 auto;">
                                        <i class="fas fa-image"
                                            style="font-size: 20px; opacity: 0.3; color: #6c757d;"></i>
                                    </div>
                                @endif
                            </td>

                            <td class="table-body-cell"
                                style="border: 1px solid {{ $tableBorderColor }}; padding: {{ $tableRowHeight }}; font-size: {{ $tableFontSize }};">
                                {{ $item['description'] ?? ($item['item_code'] ?? 'N/A') }}
                            </td>
                            <td class="table-body-cell"
                                style="border: 1px solid {{ $tableBorderColor }}; padding: {{ $tableRowHeight }}; font-size: {{ $tableFontSize }}; text-align: center;">
                                {{ $quantity }}
                            </td>
                            <td class="table-body-cell"
                                style="border: 1px solid {{ $tableBorderColor }}; padding: {{ $tableRowHeight }}; font-size: {{ $tableFontSize }}; text-align: right;">
                                £{{ number_format($lineSubtotal, 2) }}
                            </td>
                            <td class="table-body-cell"
                                style="border: 1px solid {{ $tableBorderColor }}; padding: {{ $tableRowHeight }}; font-size: {{ $tableFontSize }}; text-align: right;">
                                £{{ number_format($lineVatTotal, 2) }}
                            </td>
                            <td class="table-body-cell"
                                style="border: 1px solid {{ $tableBorderColor }}; padding: {{ $tableRowHeight }}; font-size: {{ $tableFontSize }}; text-align: right;">
                                £{{ number_format($lineTotal, 2) }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr class="table-body-row">
                        <td class="table-body-cell" style="border: 1px solid {{ $tableBorderColor }}; padding: 6px; text-align: center;"
                            colspan="6">
                            {{ __('company.no_items_found') }}
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Payment Details (Commented out in original) -->
    {{-- <div id="invoice-payment"
        style="position: absolute; {{ getPositionStyle('invoice-payment', $positions) ?: 'left: 20px;' }} width: 350px; z-index: 10;">
        <div style="font-size: {{ $fontSize }}; line-height: 1.7; font-family: {{ $bodyFont }};">
            <strong style="display: block; margin-bottom: 8px;">{{ __('company.electronic_payment_to') }}</strong>
            <div>{{ __('company.payment_name') }}: {{ $client->Business_Name ?? __('company.your_business') }}</div>
            @if (isset($bankAccount))
                <div>{{ __('company.sort_code') }}: {{ $bankAccount->Sort_Code ?? 'N/A' }}</div>
                <div>{{ __('company.account_no') }}: {{ $bankAccount->Account_No ?? 'N/A' }}</div>
            @else
                <div>{{ __('company.sort_code') }}: N/A</div>
                <div>{{ __('company.account_no') }}: N/A</div>
            @endif
            <div>{{ __('company.payment_ref') }}: {{ $validated['invoice_no'] ?? ($validated['Transaction_Code'] ?? 'N/A') }}</div>
        </div>
    </div> --}}

    <!-- Totals -->
    <div id="invoice-totals"
        style="position: absolute; {{ getPositionStyle('invoice-totals', $positions) ?: 'right: 20px;' }} z-index: 10;">
        <table style="width: 300px; font-size: 12px; font-family: {{ $bodyFont }}; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px; text-align: right;"><strong>{{ __('company.net') }}</strong></td>
                <td style="padding: 8px; text-align: right; font-weight: 600; width: 150px;">
                    £{{ number_format(floatval($validated['invoice_net_amount'] ?? 0), 2) }}
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; text-align: right;"><strong>{{ __('company.vat') }}</strong></td>
                <td style="padding: 8px; text-align: right; font-weight: 600; width: 150px;">
                    £{{ number_format(floatval($validated['invoice_vat_amount'] ?? 0), 2) }}
                </td>
            </tr>
            <tr style="border-top: 2px solid #000;">
                <td style="padding: 8px; text-align: right;"><strong>{{ __('company.total') }}</strong></td>
                <td style="padding: 8px; text-align: right; font-weight: bold; font-size: 14px; width: 150px;">
                    £{{ number_format(floatval($validated['invoice_total_amount'] ?? 0), 2) }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Invoice Notes Section -->
    @if (isset($invoiceNotes) && count($invoiceNotes) > 0)
        <div id="invoice-notes"
            style="position: absolute; {{ getPositionStyle('invoice-notes', $positions) ?: 'top: 680px; left: 20px; right: 20px;' }} z-index: 10; max-width: calc(100% - 40px);">

            <h6
                style="margin: 0 0 12px 0; font-weight: 600; color: {{ $primaryColor }}; font-family: {{ $titleFont }}; font-size: 14px;">
                {{ __('company.additional_notes') }}
            </h6>

            @foreach ($invoiceNotes as $note)
                @if ($note['has_table'] && $note['table_html'])
                    <div style="overflow-x: auto; margin-bottom: 15px;">
                        @php
                            $notesHeaderColor =
                                $templateData['notesTableHeaderColor'] ??
                                ($templateData['tableHeaderColor'] ?? $tableHeaderColor);
                            $notesHeaderTextColor =
                                $templateData['notesTableHeaderTextColor'] ??
                                ($templateData['tableHeaderTextColor'] ?? $tableHeaderTextColor);
                            $notesBorderColor =
                                $templateData['notesTableBorderColor'] ??
                                ($templateData['tableBorderColor'] ?? $tableBorderColor);
                            $notesRowHeight =
                                $templateData['notesTableRowHeight'] ??
                                ($templateData['tableRowHeight'] ?? $tableRowHeight);
                            $notesFontSize =
                                $templateData['notesTableFontSize'] ??
                                ($templateData['tableFontSize'] ?? $tableFontSize);

                            $styledTable = preg_replace(
                                ['/<table>/', '/<thead>/', '/<tbody>/', '/<tr>/', '/<th([^>]*)>/', '/<td([^>]*)>/'],
                                [
                                    '<table class="invoice-table notes-table" style="width: 100%; box-sizing: border-box; table-layout: fixed; border-collapse: collapse; margin-bottom: 20px; font-family: ' .
                                    $bodyFont .
                                    ';">',
                                    '<thead>',
                                    '<tbody>',
                                    '<tr class="table-body-row notes-table-row">',
                                    '<th class="table-header-cell notes-table-header-cell"$1 style="border: 1px solid ' .
                                    $notesBorderColor .
                                    ' !important; padding: ' .
                                    $notesRowHeight .
                                    '; text-align: center; font-size: 12px; font-weight: 600; color: ' .
                                    $notesHeaderTextColor .
                                    ' !important; background-color: ' .
                                    $notesHeaderColor .
                                    ' !important;">',
                                    '<td class="table-body-cell notes-table-body-cell"$1 style="border: 2px solid ' .
                                    $notesBorderColor .
                                    ' !important; padding: ' .
                                    $notesRowHeight .
                                    '; font-size: ' .
                                    $notesFontSize .
                                    '; text-align: center;">',
                                ],
                                $note['table_html'],
                            );
                        @endphp
                        {!! $styledTable !!}
                    </div>
                @endif

                @if (!empty($note['text']))
                    <p
                        style="margin: 8px 0 15px 0; font-size: {{ $fontSize }}; font-family: {{ $bodyFont }}; line-height: 1.6; color: #333;">
                        {{ $note['text'] }}
                    </p>
                @endif
            @endforeach

        </div>
    @endif

    <!-- Footer -->
    <div id="invoice-footer"
        style="position: absolute; {{ getPositionStyle('invoice-footer', $positions) ?: 'bottom: 20px; left: 20px;' }} right: 20px; text-align: center; border-top: 1px solid #ddd; padding-top: 15px; z-index: 10;">
        <p style="font-size: 9px; color: #666; margin: 0; font-family: {{ $bodyFont }};">
            @php
                $footerParts = [];

                if (isset($client->Company_Reg_No) && $client->Company_Reg_No) {
                    $footerParts[] = __('company.company_registration_no') . ': ' . $client->Company_Reg_No;
                }

                $address = __('company.registered_office') . ': ';
                $addressParts = [];

                if (isset($client->Address1) && $client->Address1) {
                    $addressParts[] = $client->Address1;
                }
                if (isset($client->Address2) && $client->Address2) {
                    $addressParts[] = $client->Address2;
                }
                if (isset($client->Town) && $client->Town) {
                    $addressParts[] = $client->Town;
                }
                if (isset($client->Post_Code) && $client->Post_Code) {
                    $addressParts[] = $client->Post_Code;
                }

                $address .= implode(', ', $addressParts);
                $footerParts[] = $address;

                echo implode(' | ', $footerParts);
            @endphp
        </p>
    </div>
</div>

{{-- Product Image Modal --}}
<div class="modal fade" id="productImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header border-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-image me-2"></i>{{ __('company.product_image') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="fullProductImage" src="" class="img-fluid"
                    style="max-height: 70vh; border-radius: 8px; box-shadow: 0 4px 20px rgba(255, 255, 255, 0.1);">
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
    function showImageModal(imageSrc) {
        document.getElementById('fullProductImage').src = imageSrc;
        const modal = new bootstrap.Modal(document.getElementById('productImageModal'));
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        adjustPaymentAndTotalsPosition();
        setTimeout(adjustPaymentAndTotalsPosition, 500);
        setTimeout(adjustPaymentAndTotalsPosition, 1000);
    });

    function adjustPaymentAndTotalsPosition() {
        const table = document.getElementById('invoice-table');
        const payment = document.getElementById('invoice-payment');
        const totals = document.getElementById('invoice-totals');

        if (!table || !totals) {
            return;
        }

        const positions = @json($positions);
        const paymentManual = payment && positions['invoice-payment']?.manuallyPositioned;
        const totalsManual = positions['invoice-totals']?.manuallyPositioned;

        if (paymentManual && totalsManual) {
            return;
        }

        const tableTop = parseInt(table.style.top) || 300;
        const tableHeight = table.offsetHeight;
        const newTop = tableTop + tableHeight + 20;

        if (payment && !paymentManual) {
            payment.style.top = newTop + 'px';
        }

        if (!totalsManual) {
            totals.style.top = newTop + 'px';
        }
    }

    const observer = new MutationObserver(function(mutations) {
        adjustPaymentAndTotalsPosition();
    });

    const table = document.getElementById('invoice-table');
    if (table) {
        observer.observe(table, {
            childList: true,
            subtree: true,
            attributes: true
        });
    }
</script>