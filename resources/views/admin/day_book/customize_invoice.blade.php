@extends('admin.layout.app')

@section('content')
    @php
        // Decode template data from JSON string to array
        $templateData = [];
        $isCompanyModule = $isCompanyModule ?? false;
        if ($isCompanyModule) {
            // Company Module: Customer in "Invoice To", Company in "Your Business"
            $invoiceToData = $customerData ?? $client;
            $yourBusinessData = $companyData ?? $client;
        } else {
            // Main App: File in "Invoice To", Client in "Your Business"
            $invoiceToData = $fileData ?? $client;
            $yourBusinessData = $client;
        }
    @endphp
    <div class="main-content app-content">
        <div class="container-fluid p-4">

            <!-- Template Selector -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <label class="form-label fw-bold mb-0">
                                        <i class="fas fa-layer-group me-2"></i>{{ __('company.template_selector_label') }}
                                    </label>
                                </div>
                                <div class="col-md-8">
                                    <select id="templateSelector" class="form-select">
                                        <option value="">{{ __('company.default_layout') }}</option>
                                        @foreach ($templates as $t)
                                            <option value="{{ $t->id }}"
                                                {{ isset($template) && $template && isset($template->id) && $template->id === $t->id ? 'selected' : '' }}>
                                                {{ $t->name }} {{ $t->is_default ? '⭐' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100" id="loadTemplateBtn">
                                        <i class="fas fa-sync me-2"></i>{{ __('company.load_template') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Live Preview with Drag & Drop -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-lg">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>{{ __('company.live_preview') }}</h5>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="dragModeToggle">
                                    <label class="form-check-label text-white" for="dragModeToggle">
                                        <i class="fas fa-arrows-alt me-1"></i>{{ __('company.drag_mode') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body bg-light p-4">

                            <!-- INVOICE PREVIEW - ALL ELEMENTS ABSOLUTELY POSITIONED -->
                            <div class="invoice-preview-wrapper"
                                style="max-width: 850px; margin: 0 auto; background: white; padding: 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); position: relative; min-height: 1100px; overflow: hidden;">
                                
                                <!-- Draggable Title -->
                                <div id="invoice-title" class="draggable-element" data-element="title"
                                    style="position: absolute; top: 20px; left: 20px; cursor: move; z-index: 10;">
                                    <h1 class="invoice-title"
                                        style="font-size: 36px; font-weight: bold; margin: 0; font-family: Arial; user-select: none;">
                                        {{ __('company.tax_invoice') }}
                                    </h1>
                                </div>

                                <!-- Draggable Logo -->
                                <div id="invoice-logo" class="draggable-element" data-element="logo"
                                    style="position: absolute; top: 20px; right: 20px; cursor: move; z-index: 10;">
                                    <div class="logo-container d-inline-flex">
                                        @if (isset($template) && $template && $template->logo_path)
                                            @php
                                                $logoPath = $template->logo_path ?? null;
                                                $logoFolder = 'invoice_logos';
                                                $logoFilename = $logoPath ? basename($logoPath) : null;
                                            @endphp

                                            @if ($logoFilename)
                                                <img src="{{ route('uploadfiles.show', ['folder' => $logoFolder, 'filename' => $logoFilename]) }}"
                                                    alt="{{ __('company.logo') }}"
                                                    style="max-height: 60px; max-width: 200px; object-fit: contain; pointer-events: none;">
                                            @endif
                                        @else
                                            <div class="logo-energy"
                                                style="background-color: #1e3a8a; color: white; padding: 10px 20px; border-radius: 8px 0 0 8px; font-weight: 600; font-size: 18px; pointer-events: none;">
                                                Energy
                                            </div>
                                            <div class="logo-saviour"
                                                style="background-color: #16a34a; color: white; padding: 10px 20px; border-radius: 0 8px 8px 0; font-weight: 600; font-size: 18px; display: flex; align-items: center; gap: 8px; pointer-events: none;">
                                                Saviour
                                                <div
                                                    style="width: 24px; height: 24px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                    <div
                                                        style="width: 12px; height: 16px; border-right: 3px solid #16a34a; border-bottom: 3px solid #16a34a; transform: rotate(45deg) translateY(-2px);">
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Draggable Client Info -->
                                <div id="invoice-client" class="draggable-element" data-element="client"
                                    style="position: absolute; top: 120px; left: 20px; width: 250px; cursor: move; z-index: 10;">
                                    <div class="client-info"
                                        style="font-size: 11px; line-height: 1.7; font-family: Arial; user-select: none;">
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

                                            @if (isset($invoiceToData->Postal_Code) && $invoiceToData->Postal_Code)
                                                <div>{{ $invoiceToData->Postal_Code }}</div>
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
                                                @if ($fileData->Address1)
                                                    <div>{{ $fileData->Address1 }}</div>
                                                @endif
                                                @if ($fileData->Town)
                                                    <div>{{ $fileData->Town }}</div>
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

                                <!-- Draggable Invoice Meta -->
                                <div id="invoice-meta" class="draggable-element" data-element="meta"
                                    style="position: absolute; top: 120px; left: 300px; width: 240px; cursor: move; z-index: 10;">
                                    <div class="invoice-meta"
                                        style="font-size: 11px; line-height: 1.9; font-family: Arial; user-select: none;">
                                        <div><strong>{{ __('company.invoice_date') }}</strong>
                                            {{ isset($validated['Transaction_Date']) ? \Carbon\Carbon::parse($validated['Transaction_Date'])->format('d/m/Y') : date('d/m/Y') }}
                                        </div>
                                        <div><strong>{{ __('company.inv_due_date') }}</strong>
                                            {{ isset($validated['Inv_Due_Date']) ? \Carbon\Carbon::parse($validated['Inv_Due_Date'])->format('d/m/Y') : date('d/m/Y', strtotime('+30 days')) }}
                                        </div>
                                        <div><strong>{{ __('company.invoice_no') }}</strong>
                                            {{ $validated['invoice_no'] ?? ($validated['Transaction_Code'] ?? 'N/A') }}
                                        </div>
                                        <div><strong>{{ __('company.invoice_ref') }}</strong> {{ $validated['invoice_ref'] ?? 'N/A' }}</div>
                                    </div>
                                </div>

                                <!-- Draggable Company Info -->
                                <div id="invoice-company" class="draggable-element" data-element="company"
                                    style="position: absolute; top: 120px; right: 20px; width: 240px; cursor: move; z-index: 10;">
                                    <div class="company-info"
                                        style="font-size: 11px; line-height: 1.7; font-family: Arial; user-select: none;">

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

                                        @if (isset($yourBusinessData->Postal_Code) && $yourBusinessData->Postal_Code)
                                            <div>{{ $yourBusinessData->Postal_Code }}</div>
                                        @elseif (isset($yourBusinessData->Post_Code) && $yourBusinessData->Post_Code)
                                            <div>{{ $yourBusinessData->Post_Code }}</div>
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

                                        @if (isset($yourBusinessData->Website) && $yourBusinessData->Website)
                                            <div>{{ __('company.website_short') }}: {{ $yourBusinessData->Website }}</div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Invoice Table (draggable inside preview) -->
                                <div id="invoice-table" class="draggable-element preview-fullwidth" data-element="table"
                                    style="position: absolute; top: 300px; left: 20px; right: 20px; z-index: 9; box-sizing: border-box; width: calc(100% - 40px);">

                                    <table class="invoice-table"
                                        style="width: 100%; box-sizing: border-box; border-collapse: collapse; margin-bottom: 20px; font-family: Arial;">

                                        <thead>
                                            <tr class="table-header-row">
                                                <th class="table-header-cell"
                                                    style="border: 1px solid #6c757d; padding: 6px; text-align: center; font-size: 12px; font-weight: 600; width: 70px;">
                                                    {{ __('company.image') }}</th>

                                                <th class="table-header-cell"
                                                    style="border: 1px solid #6c757d; padding: 6px; text-align: left; font-size: 12px; font-weight: 600;">
                                                    {{ __('company.description') }}</th>
                                                <th class="table-header-cell"
                                                    style="border: 1px solid #6c757d; padding: 6px; text-align: center; font-size: 12px; font-weight: 600; width: 80px;">
                                                    {{ __('company.qty') }}</th>
                                                <th class="table-header-cell"
                                                    style="border: 1px solid #6c757d; padding: 6px; text-align: right; font-size: 12px; font-weight: 600; width: 120px;">
                                                    {{ __('company.unit_price') }}</th>
                                                <th class="table-header-cell"
                                                    style="border: 1px solid #6c757d; padding: 6px; text-align: right; font-size: 12px; font-weight: 600; width: 150px;">
                                                    {{ __('company.vat') }}</th>
                                                <th class="table-header-cell"
                                                    style="border: 1px solid #6c757d; padding: 6px; text-align: right; font-size: 12px; font-weight: 600; width: 130px;">
                                                    {{ __('company.total_amount') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (isset($validated['items']) && is_array($validated['items']) && count($validated['items']) > 0)
                                                @foreach ($validated['items'] as $key => $item)
                                                    @php
                                                        $quantity = 1;
                                                        $unitAmount = floatval($item['unit_amount'] ?? 0);
                                                        $vatAmount = floatval($item['vat_amount'] ?? 0);
                                                        $vatRate = intval($item['vat_rate'] ?? 20);
                                                        $lineTotal = $unitAmount + $vatAmount;
                                                        $productImage = $item['product_image'] ?? null;
                                                    @endphp
                                                    <tr class="table-body-row">
                                                        <td class="table-body-cell"
                                                            style="border: 1px solid #6c757d; padding: 4px; font-size: 11px; text-align: center; vertical-align: middle;">
                                                            @if ($productImage)
                                                                <img src="{{ $productImage }}"
                                                                    alt="{{ $item['item_code'] ?? __('company.product') }}"
                                                                    class="product-thumbnail-preview"
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
                                                            style="border: 1px solid #6c757d; padding: 4px; font-size: 11px;">
                                                            {{ $item['description'] ?? ($item['item_code'] ?? 'N/A') }}
                                                        </td>
                                                        <td class="table-body-cell"
                                                            style="border: 1px solid #6c757d; padding: 4px; font-size: 11px; text-align: center;">
                                                            {{ $quantity }}
                                                        </td>
                                                        <td class="table-body-cell"
                                                            style="border: 1px solid #6c757d; padding: 4px; font-size: 11px; text-align: right;">
                                                            £{{ number_format($unitAmount, 2) }}
                                                        </td>
                                                        <td class="table-body-cell"
                                                            style="border: 1px solid #6c757d; padding: 4px; font-size: 11px; text-align: right;">
                                                            £{{ number_format($vatAmount, 2) }}
                                                        </td>
                                                        <td class="table-body-cell"
                                                            style="border: 1px solid #6c757d; padding: 4px; font-size: 11px; text-align: right;">
                                                            £{{ number_format($lineTotal, 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr class="table-body-row">
                                                    <td class="table-body-cell"
                                                        style="border: 1px solid #6c757d; padding: 4px; text-align: center;"
                                                        colspan="6">
                                                        {{ __('company.no_items_found') }}
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Draggable Payment Details -->
                                <div id="invoice-payment" class="draggable-element" data-element="payment"
                                    style="position: absolute; left: 20px; width: 350px; cursor: move; z-index: 10;">
                                </div>

                                <!-- Draggable Totals -->
                                <div id="invoice-totals" class="draggable-element" data-element="totals"
                                    style="position: absolute; right: 20px; width: 300px; cursor: move; z-index: 10;">
                                    <table style="width: 100%; font-size: 12px; font-family: Arial;">
                                        <tr>
                                            <td style="padding: 8px; text-align: right;"><strong>{{ __('company.net') }}</strong></td>
                                            <td style="padding: 8px; text-align: right; font-weight: 600;">
                                                £{{ number_format($validated['invoice_net_amount'] ?? 300, 2) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding: 8px; text-align: right;"><strong>{{ __('company.vat') }}</strong></td>
                                            <td style="padding: 8px; text-align: right; font-weight: 600;">
                                                £{{ number_format($validated['invoice_vat_amount'] ?? 60, 2) }}
                                            </td>
                                        </tr>
                                        <tr style="border-top: 2px solid #000;">
                                            <td style="padding: 8px; text-align: right;"><strong>{{ __('company.total') }}</strong></td>
                                            <td
                                                style="padding: 8px; text-align: right; font-weight: bold; font-size: 14px;">
                                                £{{ number_format($validated['invoice_total_amount'] ?? 360, 2) }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <!-- Draggable Invoice Notes -->
                                @if (isset($invoiceNotes) && count($invoiceNotes) > 0)
                                    <div id="invoice-notes" class="draggable-element preview-fullwidth"
                                        data-element="notes"
                                        style="position: absolute; top: 680px; left: 20px; right: 20px; cursor: move; z-index: 10; box-sizing: border-box; width: calc(100% - 40px);">

                                        <h6
                                            style="margin: 0 0 12px 0; font-weight: 600; color: #1e3a8a; font-family: Arial; font-size: 14px; pointer-events: none;">
                                            {{ __('company.additional_notes') }}
                                        </h6>

                                        @foreach ($invoiceNotes as $note)
                                            @if ($note['has_table'] && $note['table_html'])
                                                <div style="overflow-x: auto; margin-bottom: 15px; pointer-events: none; width: 100%;">
                                                    {!! $note['table_html'] !!}
                                                </div>
                                            @endif

                                            @if (!empty($note['text']))
                                                <p style="margin: 8px 0 15px 0; font-size: 11px; font-family: Arial; line-height: 1.6; color: #333; pointer-events: none;">
                                                    {{ $note['text'] }}
                                                </p>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Draggable Footer -->
                                <div id="invoice-footer" class="draggable-element" data-element="footer"
                                    style="position: absolute; bottom: 20px; left: 20px; right: 20px; text-align: center; border-top: 1px solid #ddd; padding-top: 15px; cursor: move; z-index: 10;">
                                    <p style="font-size: 9px; color: #666; margin: 0; font-family: Arial; user-select: none;">
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

                            <!-- Drag Instructions -->
                            <div class="alert alert-info mt-3" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>{{ __('company.tip') }}:</strong> {{ __('company.drag_tip') }}
                                <hr class="my-2">
                                <small><i class="fas fa-magic me-1"></i><strong>{{ __('company.auto_spacing') }}:</strong> {{ __('company.auto_spacing_tip') }}</small>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Enhanced Controls -->
                <div class="col-lg-4">
                    <div class="sticky-controls">
                        <div class="card border-0 shadow-lg">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>{{ __('company.styling_controls') }}</h5>
                            </div>
                            <div class="card-body" style="max-height: 80vh; overflow-y: auto;">

                                <!-- Template Name -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-tag me-2"></i>{{ __('company.template_name') }}
                                    </label>
                                    <input id="templateName" class="form-control"
                                        value="{{ $template->name ?? __('company.my_custom_template') }}">
                                </div>

                                <!-- Set as Default -->
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="setAsDefault"
                                            {{ isset($template) && $template && $template->is_default ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold">
                                            <i class="fas fa-star text-warning me-1"></i>{{ __('company.set_as_default') }}
                                        </label>
                                    </div>
                                </div>

                                <hr>

                                <!-- Element Positioning -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-arrows-alt me-2"></i>{{ __('company.element_positioning') }}
                                    </h6>
                                    <div class="alert alert-light border p-2">
                                        <small class="text-muted">
                                            <i class="fas fa-hand-pointer me-1"></i>
                                            {{ __('company.click_element_position') }}
                                        </small>
                                    </div>

                                    <div id="positionControls" style="display: none;">
                                        <label class="form-label small fw-bold">{{ __('company.selected_element') }} <span
                                                id="selectedElement">{{ __('company.none') }}</span></label>
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <label class="form-label small">{{ __('company.left_px') }}</label>
                                                <input type="number" id="posLeft" class="form-control form-control-sm"
                                                    value="0">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small">{{ __('company.top_px') }}</label>
                                                <input type="number" id="posTop" class="form-control form-control-sm"
                                                    value="0">
                                            </div>
                                        </div>
                                        <div class="btn-group w-100 mb-2" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="alignElement('left')">
                                                <i class="fas fa-align-left"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="alignElement('center')">
                                                <i class="fas fa-align-center"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="alignElement('right')">
                                                <i class="fas fa-align-right"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <button class="btn btn-outline-secondary btn-sm w-100 mt-2" id="resetPositions">
                                        <i class="fas fa-undo me-1"></i>{{ __('company.reset_all_positions') }}
                                    </button>

                                    <button class="btn btn-outline-info btn-sm w-100 mt-2" id="recalculatePositions">
                                        <i class="fas fa-sync me-1"></i>{{ __('company.recalculate_table_spacing') }}
                                    </button>
                                </div>

                                <hr>

                                <!-- Logo Colors -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-palette me-2"></i>{{ __('company.logo_colors') }}
                                    </h6>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="form-label small">{{ __('company.primary') }}</label>
                                            <input type="color" class="form-control form-control-color w-100"
                                                id="primaryColor"
                                                value="{{ $template->template_data['primaryColor'] ?? '#1e3a8a' }}">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">{{ __('company.secondary') }}</label>
                                            <input type="color" class="form-control form-control-color w-100"
                                                id="secondaryColor"
                                                value="{{ $template->template_data['secondaryColor'] ?? '#16a34a' }}">
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- Quick Themes -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-swatchbook me-2"></i>{{ __('company.quick_themes') }}
                                    </h6>
                                    <div class="row g-2">
                                        @php
                                            $themes = [
                                                ['name' => 'theme_default', 'primary' => '#1e3a8a', 'secondary' => '#16a34a'],
                                                ['name' => 'theme_blue', 'primary' => '#3b82f6', 'secondary' => '#60a5fa'],
                                                ['name' => 'theme_green', 'primary' => '#10b981', 'secondary' => '#34d399'],
                                                ['name' => 'theme_purple', 'primary' => '#8b5cf6', 'secondary' => '#a78bfa'],
                                                ['name' => 'theme_red', 'primary' => '#ef4444', 'secondary' => '#f87171'],
                                                ['name' => 'theme_dark', 'primary' => '#1f2937', 'secondary' => '#374151'],
                                            ];
                                        @endphp
                                        @foreach ($themes as $theme)
                                            <div class="col-4">
                                                <button class="theme-btn w-100" data-primary="{{ $theme['primary'] }}"
                                                    data-secondary="{{ $theme['secondary'] }}"
                                                    style="background: linear-gradient(135deg, {{ $theme['primary'] }} 50%, {{ $theme['secondary'] }} 50%); height: 50px; border: 2px solid #dee2e6; border-radius: 8px; cursor: pointer;">
                                                    <small class="text-white fw-bold">{{ __('company.' . $theme['name']) }}</small>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <hr><!-- Typography -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-font me-2"></i>{{ __('company.typography') }}
                                    </h6>

                                    <div class="mb-3">
                                        <label class="form-label small">{{ __('company.title_font') }}</label>
                                        <select id="titleFont" class="form-select">
                                            @foreach (['Arial', 'Times New Roman', 'Verdana', 'Georgia', 'Courier New'] as $font)
                                                <option value="{{ $font }}">{{ $font }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small">{{ __('company.body_font') }}</label>
                                        <select id="bodyFont" class="form-select">
                                            @foreach (['Arial', 'Times New Roman', 'Verdana', 'Georgia'] as $font)
                                                <option value="{{ $font }}">{{ $font }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small">{{ __('company.font_size') }}</label>
                                        <select id="fontSize" class="form-select">
                                            <option value="9px">{{ __('company.font_size_extra_small') }}</option>
                                            <option value="10px">{{ __('company.font_size_small') }}</option>
                                            <option value="11px" selected>{{ __('company.font_size_medium') }}</option>
                                            <option value="12px">{{ __('company.font_size_large') }}</option>
                                            <option value="13px">{{ __('company.font_size_extra_large') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <hr>

                                <!-- Table Customization -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-table me-2"></i>{{ __('company.table_customization') }}
                                    </h6>

                                    <div class="mb-3">
                                        <label class="form-label small">{{ __('company.header_background_color') }}</label>
                                        <input type="color" class="form-control form-control-color w-100"
                                            id="tableHeaderColor"
                                            value="{{ $template->template_data['tableHeaderColor'] ?? '#b3d9ff' }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small">{{ __('company.header_text_color') }}</label>
                                        <input type="color" class="form-control form-control-color w-100"
                                            id="tableHeaderTextColor"
                                            value="{{ $template->template_data['tableHeaderTextColor'] ?? '#000000' }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small">{{ __('company.border_color') }}</label>
                                        <input type="color" class="form-control form-control-color w-100"
                                            id="tableBorderColor"
                                            value="{{ $template->template_data['tableBorderColor'] ?? '#6c757d' }}">
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small">{{ __('company.row_height') }}</label>
                                            <select id="tableRowHeight" class="form-select">
                                                <option value="8px">{{ __('company.row_height_compact') }}</option>
                                                <option value="12px" selected>{{ __('company.row_height_default') }}</option>
                                                <option value="16px">{{ __('company.row_height_comfortable') }}</option>
                                                <option value="20px">{{ __('company.row_height_spacious') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">{{ __('company.font_size') }}</label>
                                            <select id="tableFontSize" class="form-select">
                                                <option value="10px">{{ __('company.font_size_small') }}</option>
                                                <option value="11px" selected>{{ __('company.row_height_default') }}</option>
                                                <option value="12px">{{ __('company.font_size_medium') }}</option>
                                                <option value="13px">{{ __('company.font_size_large') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <label class="form-label small">{{ __('company.column_width_preset') }}</label>
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="setColumnWidths('auto')">
                                                {{ __('company.column_auto') }}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="setColumnWidths('equal')">
                                                {{ __('company.column_equal') }}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="setColumnWidths('balanced')">
                                                {{ __('company.column_balanced') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- Notes Table Customization -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-sticky-note me-2"></i>{{ __('company.notes_table_customization') }}
                                    </h6>

                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="useItemsTableStyling"
                                            checked>
                                        <label class="form-check-label small">
                                            {{ __('company.use_items_table_styling') }}
                                        </label>
                                    </div>

                                    <div id="notesTableControls" style="display: none;">
                                        <div class="mb-3">
                                            <label class="form-label small">{{ __('company.header_background_color') }}</label>
                                            <input type="color" class="form-control form-control-color w-100"
                                                id="notesTableHeaderColor"
                                                value="{{ $template->template_data['notesTableHeaderColor'] ?? ($template->template_data['tableHeaderColor'] ?? '#b3d9ff') }}">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small">{{ __('company.header_text_color') }}</label>
                                            <input type="color" class="form-control form-control-color w-100"
                                                id="notesTableHeaderTextColor"
                                                value="{{ $template->template_data['notesTableHeaderTextColor'] ?? ($template->template_data['tableHeaderTextColor'] ?? '#000000') }}">
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small">{{ __('company.border_color') }}</label>
                                            <input type="color" class="form-control form-control-color w-100"
                                                id="notesTableBorderColor"
                                                value="{{ $template->template_data['notesTableBorderColor'] ?? ($template->template_data['tableBorderColor'] ?? '#6c757d') }}">
                                        </div>

                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label small">{{ __('company.row_height') }}</label>
                                                <select id="notesTableRowHeight" class="form-select">
                                                    <option value="8px">{{ __('company.row_height_compact') }}</option>
                                                    <option value="12px" selected>{{ __('company.row_height_default') }}</option>
                                                    <option value="16px">{{ __('company.row_height_comfortable') }}</option>
                                                    <option value="20px">{{ __('company.row_height_spacious') }}</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small">{{ __('company.font_size') }}</label>
                                                <select id="notesTableFontSize" class="form-select">
                                                    <option value="10px">{{ __('company.font_size_small') }}</option>
                                                    <option value="11px" selected>{{ __('company.row_height_default') }}</option>
                                                    <option value="12px">{{ __('company.font_size_medium') }}</option>
                                                    <option value="13px">{{ __('company.font_size_large') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- Logo Upload -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-image me-2"></i>{{ __('company.logo_management') }}
                                    </h6>
                                    <input id="logoUpload" type="file" accept="image/*" class="form-control mb-2">
                                    <small class="text-muted d-block mb-2">{{ __('company.png_recommended') }}</small>
                                    @if (isset($template) && $template && $template->logo_path)
                                        <div class="alert alert-success p-2 small">
                                            <i class="fas fa-check-circle me-1"></i>{{ __('company.custom_logo_active') }}
                                        </div>
                                    @endif
                                </div>

                                <hr>

                                <!-- Layout Presets -->
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-th-large me-2"></i>{{ __('company.layout_presets') }}
                                    </h6>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-primary btn-sm" onclick="applyLayout('default')">
                                            <i class="fas fa-file-invoice me-1"></i>{{ __('company.standard_layout') }}
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" onclick="applyLayout('centered')">
                                            <i class="fas fa-align-center me-1"></i>{{ __('company.centered_layout') }}
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" onclick="applyLayout('modern')">
                                            <i class="fas fa-file-alt me-1"></i>{{ __('company.modern_layout') }}
                                        </button>
                                    </div>
                                </div>

                                <hr>

                                <!-- Action Buttons -->
                                <div class="d-grid gap-2">
                                    <button id="saveTemplate" class="btn btn-success btn-lg">
                                        <i class="fas fa-save me-2"></i>{{ __('company.save_template') }}
                                    </button>
                                    <button id="previewTemplate" class="btn btn-primary">
                                        <i class="fas fa-eye me-2"></i>{{ __('company.preview_full_page') }}
                                    </button>
                                    <button id="downloadPDF" class="btn btn-outline-primary">
                                        <i class="fas fa-file-pdf me-2"></i>{{ __('company.download_pdf') }}
                                    </button>
                                    <button id="resetStyles" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo me-2"></i>{{ __('company.reset_everything') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" id="draftKey" value="{{ $draft->draft_key }}">
        <input type="hidden" id="currentTemplateData" value='@json($template->template_data ?? [])'>
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
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <script>
        (function() {
            const draftKey = document.getElementById('draftKey').value;
            let currentStyles = JSON.parse(document.getElementById('currentTemplateData').value || '{}');
            let dragMode = false;
            let selectedElement = null;
            let draggedElement = null;
            let offsetX = 0,
                offsetY = 0;

            if (!currentStyles.positions) {
                currentStyles.positions = {};
            }

            const defaultPositions = {
                'invoice-title': { top: 20, left: 20 },
                'invoice-logo': { top: 20, right: 20 },
                'invoice-client': { top: 120, left: 20 },
                'invoice-meta': { top: 120, left: 300 },
                'invoice-company': { top: 120, right: 20 },
                'invoice-payment': { left: 20 },
                'invoice-totals': { right: 20 },
                'invoice-notes': { top: 680, left: 20 },
                'invoice-footer': { bottom: 20, left: 20 },
                'invoice-table': { top: 300, left: 20 }
            };

            function adjustElementPositionsBasedOnTable() {
                const table = document.getElementById('invoice-table');
                const payment = document.getElementById('invoice-payment');
                const totals = document.getElementById('invoice-totals');

                if (table && payment && totals) {
                    setTimeout(() => {
                        const tableTop = parseInt(table.style.top) || 300;
                        const tableHeight = table.offsetHeight;
                        const newTop = tableTop + tableHeight + 20;

                        if (!currentStyles.positions['invoice-payment']?.manuallyPositioned) {
                            payment.style.top = newTop + 'px';
                            currentStyles.positions['invoice-payment'] = currentStyles.positions['invoice-payment'] || {};
                            currentStyles.positions['invoice-payment'].top = newTop;
                        }

                        if (!currentStyles.positions['invoice-totals']?.manuallyPositioned) {
                            totals.style.top = newTop + 'px';
                            currentStyles.positions['invoice-totals'] = currentStyles.positions['invoice-totals'] || {};
                            currentStyles.positions['invoice-totals'].top = newTop;
                        }
                    }, 200);
                }
            }

            function collectAllPositions() {
                const positions = {};
                const elements = [
                    'invoice-title', 'invoice-logo', 'invoice-client', 'invoice-meta',
                    'invoice-company', 'invoice-payment', 'invoice-totals', 'invoice-notes',
                    'invoice-footer', 'invoice-table'
                ];

                elements.forEach(elementId => {
                    const el = document.getElementById(elementId);
                    if (el) {
                        const position = {};

                        if (el.style.left && el.style.left !== 'auto' && el.style.left !== '') {
                            position.left = parseInt(el.style.left);
                        }
                        if (el.style.top && el.style.top !== 'auto' && el.style.top !== '') {
                            position.top = parseInt(el.style.top);
                        }
                        if (el.style.right && el.style.right !== 'auto' && el.style.right !== '') {
                            position.right = parseInt(el.style.right);
                        }
                        if (el.style.bottom && el.style.bottom !== 'auto' && el.style.bottom !== '') {
                            position.bottom = parseInt(el.style.bottom);
                        }

                        if (Object.keys(position).length === 0) {
                            position.left = el.offsetLeft;
                            position.top = el.offsetTop;
                        }

                        if (currentStyles.positions[elementId]?.manuallyPositioned) {
                            position.manuallyPositioned = true;
                        }

                        positions[elementId] = position;
                    }
                });

                return positions;
            }

            function init() {
                initializeStyles();
                initializeDragAndDrop();
                loadSavedPositions();

                setTimeout(adjustElementPositionsBasedOnTable, 100);
                setTimeout(adjustElementPositionsBasedOnTable, 500);
                setTimeout(adjustElementPositionsBasedOnTable, 1000);
            }

            window.addEventListener('load', function() {
                setTimeout(adjustElementPositionsBasedOnTable, 200);
            });

            function watchTableChanges() {
                const table = document.getElementById('invoice-table');
                if (!table) return;

                const observer = new MutationObserver(function(mutations) {
                    adjustElementPositionsBasedOnTable();
                });

                observer.observe(table, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    characterData: true
                });
            }

            setTimeout(watchTableChanges, 1000);

            function loadSavedPositions() {
                if (currentStyles.positions && Object.keys(currentStyles.positions).length > 0) {
                    Object.keys(currentStyles.positions).forEach(elementId => {
                        const el = document.getElementById(elementId);
                        if (el && currentStyles.positions[elementId]) {
                            const pos = currentStyles.positions[elementId];

                            el.style.left = 'auto';
                            el.style.right = 'auto';
                            el.style.top = 'auto';
                            el.style.bottom = 'auto';

                            if (pos.left !== undefined) el.style.left = pos.left + 'px';
                            if (pos.top !== undefined) el.style.top = pos.top + 'px';
                            if (pos.right !== undefined) {
                                el.style.right = pos.right + 'px';
                                el.style.left = 'auto';
                            }
                            if (pos.bottom !== undefined) {
                                el.style.bottom = pos.bottom + 'px';
                                el.style.top = 'auto';
                            }
                        }
                    });

                    setTimeout(adjustElementPositionsBasedOnTable, 300);
                } else {
                    setTimeout(adjustElementPositionsBasedOnTable, 300);
                }
            }

            function initializeDragAndDrop() {
                const draggables = document.querySelectorAll('.draggable-element');

                draggables.forEach(el => {
                    el.addEventListener('mousedown', handleMouseDown);
                    el.addEventListener('click', handleElementClick);
                });

                document.addEventListener('mousemove', handleMouseMove);
                document.addEventListener('mouseup', handleMouseUp);
            }

            function handleElementClick(e) {
                if (!dragMode) {
                    e.stopPropagation();
                    selectElement(e.currentTarget);
                }
            }

            function selectElement(el) {
                document.querySelectorAll('.draggable-element').forEach(elem => {
                    elem.style.outline = 'none';
                    elem.style.boxShadow = 'none';
                });

                el.style.outline = '2px dashed #0d6efd';
                el.style.boxShadow = '0 0 10px rgba(13, 110, 253, 0.3)';
                selectedElement = el;

                document.getElementById('positionControls').style.display = 'block';
                const elementName = el.dataset.element || 'Element';
                document.getElementById('selectedElement').textContent = elementName.charAt(0).toUpperCase() +
                    elementName.slice(1);

                const parentRect = el.offsetParent ? el.offsetParent.getBoundingClientRect() : { left: 0, top: 0 };
                const elRect = el.getBoundingClientRect();
                const computedLeft = Math.round(elRect.left - parentRect.left);
                const computedTop = Math.round(elRect.top - parentRect.top);

                document.getElementById('posLeft').value = isNaN(computedLeft) ? 0 : computedLeft;
                document.getElementById('posTop').value = isNaN(computedTop) ? 0 : computedTop;
            }

            function handleMouseDown(e) {
                if (!dragMode) return;

                draggedElement = e.currentTarget;
                const rect = draggedElement.getBoundingClientRect();

                offsetX = e.clientX - rect.left;
                offsetY = e.clientY - rect.top;

                draggedElement.style.zIndex = '1000';
                e.preventDefault();
            }

            function handleMouseMove(e) {
                if (!draggedElement) return;

                const parentRect = draggedElement.offsetParent.getBoundingClientRect();
                let newLeft = e.clientX - parentRect.left - offsetX;
                let newTop = e.clientY - parentRect.top - offsetY;

                const elementWidth = draggedElement.offsetWidth;
                const elementHeight = draggedElement.offsetHeight;
                const containerWidth = draggedElement.offsetParent.offsetWidth;
                const containerHeight = draggedElement.offsetParent.offsetHeight;

                const usesRight = draggedElement.style.right && draggedElement.style.right !== 'auto';

                if (usesRight) {
                    let newRight = containerWidth - (newLeft + elementWidth);
                    newRight = Math.max(0, Math.min(newRight, containerWidth - elementWidth));

                    draggedElement.style.right = newRight + 'px';
                    draggedElement.style.left = 'auto';
                } else {
                    newLeft = Math.max(0, Math.min(newLeft, containerWidth - elementWidth));
                    draggedElement.style.left = newLeft + 'px';
                    draggedElement.style.right = 'auto';
                }

                newTop = Math.max(0, Math.min(newTop, containerHeight - elementHeight));
                draggedElement.style.top = newTop + 'px';
                draggedElement.style.bottom = 'auto';
            }

            function handleMouseUp() {
                if (draggedElement) {
                    const elementId = draggedElement.id;
                    const position = {};

                    if (draggedElement.style.left && draggedElement.style.left !== 'auto') {
                        position.left = parseInt(draggedElement.style.left);
                    }
                    if (draggedElement.style.right && draggedElement.style.right !== 'auto') {
                        position.right = parseInt(draggedElement.style.right);
                    }
                    if (draggedElement.style.top && draggedElement.style.top !== 'auto') {
                        position.top = parseInt(draggedElement.style.top);
                    }
                    if (draggedElement.style.bottom && draggedElement.style.bottom !== 'auto') {
                        position.bottom = parseInt(draggedElement.style.bottom);
                    }

                    position.manuallyPositioned = true;
                    currentStyles.positions[elementId] = position;

                    draggedElement.style.zIndex = '10';
                    draggedElement = null;

                    showNotification('{{ __('company.position_updated') }}');
                }
            }

            document.getElementById('dragModeToggle').addEventListener('change', function() {
                dragMode = this.checked;
                const draggables = document.querySelectorAll('.draggable-element');

                if (dragMode) {
                    draggables.forEach(el => {
                        el.style.border = '2px dashed rgba(13, 110, 253, 0.3)';
                        el.style.backgroundColor = 'rgba(13, 110, 253, 0.02)';
                    });
                    showNotification('{{ __('company.drag_mode_enabled') }}');
                } else {
                    draggables.forEach(el => {
                        el.style.border = 'none';
                        el.style.outline = 'none';
                        el.style.backgroundColor = 'transparent';
                    });
                    showNotification('{{ __('company.drag_mode_disabled') }}');
                }
            });

            document.getElementById('posLeft').addEventListener('change', function() {
                if (selectedElement) {
                    selectedElement.style.left = this.value + 'px';
                    selectedElement.style.right = 'auto';
                    currentStyles.positions[selectedElement.id] = currentStyles.positions[selectedElement.id] || {};
                    currentStyles.positions[selectedElement.id].left = parseInt(this.value);
                    currentStyles.positions[selectedElement.id].manuallyPositioned = true;
                    delete currentStyles.positions[selectedElement.id].right;
                }
            });

            document.getElementById('posTop').addEventListener('change', function() {
                if (selectedElement) {
                    selectedElement.style.top = this.value + 'px';
                    selectedElement.style.bottom = 'auto';
                    currentStyles.positions[selectedElement.id] = currentStyles.positions[selectedElement.id] || {};
                    currentStyles.positions[selectedElement.id].top = parseInt(this.value);
                    currentStyles.positions[selectedElement.id].manuallyPositioned = true;
                    delete currentStyles.positions[selectedElement.id].bottom;
                }
            });

            window.alignElement = function(alignment) {
                if (!selectedElement) {
                    alert('{{ __('company.select_element_first') }}');
                    return;
                }

                const parent = selectedElement.offsetParent;
                const parentWidth = parent.offsetWidth;
                const elementWidth = selectedElement.offsetWidth;

                switch (alignment) {
                    case 'left':
                        selectedElement.style.left = '20px';
                        selectedElement.style.right = 'auto';
                        currentStyles.positions[selectedElement.id] = currentStyles.positions[selectedElement.id] || {};
                        currentStyles.positions[selectedElement.id].left = 20;
                        currentStyles.positions[selectedElement.id].manuallyPositioned = true;
                        delete currentStyles.positions[selectedElement.id].right;
                        break;
                    case 'center':
                        const centerPos = (parentWidth - elementWidth) / 2;
                        selectedElement.style.left = centerPos + 'px';
                        selectedElement.style.right = 'auto';
                        currentStyles.positions[selectedElement.id] = currentStyles.positions[selectedElement.id] || {};
                        currentStyles.positions[selectedElement.id].left = Math.round(centerPos);
                        currentStyles.positions[selectedElement.id].manuallyPositioned = true;
                        delete currentStyles.positions[selectedElement.id].right;
                        break;
                    case 'right':
                        selectedElement.style.left = 'auto';
                        selectedElement.style.right = '20px';
                        currentStyles.positions[selectedElement.id] = currentStyles.positions[selectedElement.id] || {};
                        currentStyles.positions[selectedElement.id].right = 20;
                        currentStyles.positions[selectedElement.id].manuallyPositioned = true;
                        delete currentStyles.positions[selectedElement.id].left;
                        break;
                }

                document.getElementById('posLeft').value = parseInt(selectedElement.style.left) || 0;
                showNotification('{{ __('company.element_aligned') }} ' + alignment);
            };

            window.applyLayout = function(layout) {
                const layouts = {
                    'default': {
                        'invoice-title': { left: 20, top: 20 },
                        'invoice-logo': { right: 20, top: 20 },
                        'invoice-client': { left: 20, top: 120 },
                        'invoice-meta': { left: 300, top: 120 },
                        'invoice-company': { right: 20, top: 120 },
                        'invoice-notes': { left: 20, top: 680 },
                        'invoice-footer': { left: 20, bottom: 20 },
                        'invoice-table': { left: 20, top: 300 }
                    },
                    'centered': {
                        'invoice-title': { left: 300, top: 20 },
                        'invoice-logo': { left: 300, top: 80 },
                        'invoice-client': { left: 20, top: 160 },
                        'invoice-meta': { left: 300, top: 160 },
                        'invoice-company': { right: 20, top: 160 },
                        'invoice-notes': { left: 20, top: 640 },
                        'invoice-footer': { left: 20, bottom: 20 },
                        'invoice-table': { left: 20, top: 220 }
                    },
                    'modern': {
                        'invoice-title': { left: 20, top: 20 },
                        'invoice-logo': { right: 20, top: 20 },
                        'invoice-client': { left: 20, top: 100 },
                        'invoice-meta': { right: 20, top: 100 },
                        'invoice-company': { left: 20, top: 220 },
                        'invoice-notes': { left: 20, top: 700 },
                        'invoice-footer': { left: 20, bottom: 20 },
                        'invoice-table': { left: 20, top: 260 }
                    }
                };

                const preset = layouts[layout];
                if (!preset) return;

                Object.keys(preset).forEach(elementId => {
                    const el = document.getElementById(elementId);
                    if (el) {
                        el.style.left = 'auto';
                        el.style.right = 'auto';
                        el.style.top = 'auto';
                        el.style.bottom = 'auto';

                        if (preset[elementId].left !== undefined) {
                            el.style.left = preset[elementId].left + 'px';
                        }
                        if (preset[elementId].right !== undefined) {
                            el.style.right = preset[elementId].right + 'px';
                        }
                        if (preset[elementId].top !== undefined) {
                            el.style.top = preset[elementId].top + 'px';
                        }
                        if (preset[elementId].bottom !== undefined) {
                            el.style.bottom = preset[elementId].bottom + 'px';
                        }

                        currentStyles.positions[elementId] = preset[elementId];
                        currentStyles.positions[elementId].manuallyPositioned = true;
                    }
                });

                setTimeout(adjustElementPositionsBasedOnTable, 100);
                showNotification('{{ __('company.layout_preset_applied') }}');
            };

            document.getElementById('resetPositions').addEventListener('click', function() {
                if (confirm('{{ __('company.confirm_reset_positions') }}')) {
                    currentStyles.positions = {};

                    Object.keys(defaultPositions).forEach(elementId => {
                        const el = document.getElementById(elementId);
                        if (el) {
                            el.style.left = 'auto';
                            el.style.right = 'auto';
                            el.style.top = 'auto';
                            el.style.bottom = 'auto';

                            Object.keys(defaultPositions[elementId]).forEach(prop => {
                                el.style[prop] = defaultPositions[elementId][prop] + 'px';
                            });
                        }
                    });

                    setTimeout(adjustElementPositionsBasedOnTable, 100);
                    showNotification('{{ __('company.positions_reset') }}');
                }
            });

            document.getElementById('recalculatePositions').addEventListener('click', function() {
                if (currentStyles.positions['invoice-payment']) {
                    delete currentStyles.positions['invoice-payment'].manuallyPositioned;
                }
                if (currentStyles.positions['invoice-totals']) {
                    delete currentStyles.positions['invoice-totals'].manuallyPositioned;
                }

                adjustElementPositionsBasedOnTable();
                showNotification('{{ __('company.table_spacing_recalculated') }}');
            });

            function initializeStyles() {
                if (currentStyles.primaryColor) {
                    document.getElementById('primaryColor').value = currentStyles.primaryColor;
                    applyColor('.logo-energy', currentStyles.primaryColor);
                }
                if (currentStyles.secondaryColor) {
                    document.getElementById('secondaryColor').value = currentStyles.secondaryColor;
                    applyColor('.logo-saviour', currentStyles.secondaryColor);
                }
                if (currentStyles.titleFont) {
                    document.getElementById('titleFont').value = currentStyles.titleFont;
                    applyTitleFont(currentStyles.titleFont);
                }
                if (currentStyles.bodyFont) {
                    document.getElementById('bodyFont').value = currentStyles.bodyFont;
                    applyBodyFont(currentStyles.bodyFont);
                }
                if (currentStyles.fontSize) {
                    document.getElementById('fontSize').value = currentStyles.fontSize;
                    applyFontSize(currentStyles.fontSize);
                }

                if (currentStyles.tableHeaderColor) {
                    document.getElementById('tableHeaderColor').value = currentStyles.tableHeaderColor;
                    applyTableHeaderColor(currentStyles.tableHeaderColor);
                }
                if (currentStyles.tableHeaderTextColor) {
                    document.getElementById('tableHeaderTextColor').value = currentStyles.tableHeaderTextColor;
                    applyTableHeaderTextColor(currentStyles.tableHeaderTextColor);
                }
                if (currentStyles.tableBorderColor) {
                    document.getElementById('tableBorderColor').value = currentStyles.tableBorderColor;
                    applyTableBorderColor(currentStyles.tableBorderColor);
                }
                if (currentStyles.tableRowHeight) {
                    document.getElementById('tableRowHeight').value = currentStyles.tableRowHeight;
                    applyTableRowHeight(currentStyles.tableRowHeight);
                }
                if (currentStyles.tableFontSize) {
                    document.getElementById('tableFontSize').value = currentStyles.tableFontSize;
                    applyTableFontSize(currentStyles.tableFontSize);
                }

                const useItemsStyling = currentStyles.notesUseSeparateStyling !== true;
                document.getElementById('useItemsTableStyling').checked = useItemsStyling;

                if (!useItemsStyling && currentStyles.notesTableHeaderColor) {
                    document.getElementById('notesTableControls').style.display = 'block';
                    document.getElementById('notesTableHeaderColor').value = currentStyles.notesTableHeaderColor;
                    document.getElementById('notesTableHeaderTextColor').value = currentStyles.notesTableHeaderTextColor || '#000000';
                    document.getElementById('notesTableBorderColor').value = currentStyles.notesTableBorderColor || '#6c757d';
                    document.getElementById('notesTableRowHeight').value = currentStyles.notesTableRowHeight || '6px';
                    document.getElementById('notesTableFontSize').value = currentStyles.notesTableFontSize || '11px';

                    applyNotesTableHeaderColor(currentStyles.notesTableHeaderColor);
                    applyNotesTableHeaderTextColor(currentStyles.notesTableHeaderTextColor || '#000000');
                    applyNotesTableBorderColor(currentStyles.notesTableBorderColor || '#6c757d');
                    applyNotesTableRowHeight(currentStyles.notesTableRowHeight || '6px');
                    applyNotesTableFontSize(currentStyles.notesTableFontSize || '11px');
                } else {
                    applyNotesTableHeaderColor(currentStyles.tableHeaderColor || '#b3d9ff');
                    applyNotesTableHeaderTextColor(currentStyles.tableHeaderTextColor || '#000000');
                    applyNotesTableBorderColor(currentStyles.tableBorderColor || '#6c757d');
                    applyNotesTableRowHeight(currentStyles.tableRowHeight || '6px');
                    applyNotesTableFontSize(currentStyles.tableFontSize || '11px');
                }
            }

            function applyColor(selector, color) {
                const el = document.querySelector(selector);
                if (el) el.style.backgroundColor = color;
            }

            function applyTitleFont(font) {
                const title = document.querySelector('.invoice-title');
                if (title) title.style.fontFamily = font;
            }

            function applyBodyFont(font) {
                document.querySelectorAll('.client-info, .company-info, .invoice-meta, .invoice-table')
                    .forEach(el => el.style.fontFamily = font);
            }

            function applyFontSize(size) {
                document.querySelectorAll('.client-info, .company-info, .invoice-meta')
                    .forEach(el => el.style.fontSize = size);
            }

            function applyTableHeaderColor(color) {
                document.querySelectorAll('.table-header-row').forEach(row => {
                    row.style.setProperty('background-color', color, 'important');
                });

                document.querySelectorAll('.table-header-cell').forEach(cell => {
                    cell.style.setProperty('background-color', color, 'important');
                });
            }

            function applyTableHeaderTextColor(color) {
                document.querySelectorAll('.table-header-cell').forEach(cell => {
                    cell.style.color = color;
                });
            }

            function applyTableBorderColor(color) {
                document.querySelectorAll('.table-header-cell, .table-body-cell').forEach(cell => {
                    cell.style.borderColor = color;
                });
            }

            function applyTableRowHeight(height) {
                document.querySelectorAll('.table-header-cell, .table-body-cell').forEach(cell => {
                    cell.style.padding = height;
                });

                setTimeout(adjustElementPositionsBasedOnTable, 100);
            }

            function applyTableFontSize(size) {
                document.querySelectorAll('.table-body-cell').forEach(cell => {
                    cell.style.fontSize = size;
                });

                setTimeout(adjustElementPositionsBasedOnTable, 100);
            }

            function applyNotesTableHeaderColor(color) {
                document.querySelectorAll('.notes-table-header-cell').forEach(cell => {
                    cell.style.setProperty('background-color', color, 'important');
                });
            }

            function applyNotesTableHeaderTextColor(color) {
                document.querySelectorAll('.notes-table-header-cell').forEach(cell => {
                    cell.style.setProperty('color', color, 'important');
                });
            }

            function applyNotesTableBorderColor(color) {
                document.querySelectorAll('.notes-table-header-cell, .notes-table-body-cell').forEach(cell => {
                    cell.style.borderColor = color;
                    cell.style.borderWidth = '2px';
                });
            }

            function applyNotesTableRowHeight(height) {
                document.querySelectorAll('.notes-table-header-cell, .notes-table-body-cell').forEach(cell => {
                    cell.style.padding = height;
                });
            }

            function applyNotesTableFontSize(size) {
                document.querySelectorAll('.notes-table-body-cell').forEach(cell => {
                    cell.style.fontSize = size;
                });
            }

            window.setColumnWidths = function(preset) {
                const table = document.querySelector('.invoice-table');
                if (!table) return;

                const headers = table.querySelectorAll('th');

                switch (preset) {
                    case 'auto':
                        headers[0].style.width = '70px';
                        headers[1].style.width = 'auto';
                        headers[2].style.width = '80px';
                        headers[3].style.width = '120px';
                        headers[4].style.width = '150px';
                        headers[5].style.width = '130px';
                        break;
                    case 'equal':
                        headers.forEach(h => h.style.width = '16.66%');
                        break;
                    case 'balanced':
                        headers[0].style.width = '10%';
                        headers[1].style.width = '35%';
                        headers[2].style.width = '10%';
                        headers[3].style.width = '15%';
                        headers[4].style.width = '15%';
                        headers[5].style.width = '15%';
                        break;
                }

                currentStyles.columnWidthPreset = preset;
                showNotification('{{ __('company.column_widths_updated') }}');
            };

            document.getElementById('primaryColor').addEventListener('change', function() {
                applyColor('.logo-energy', this.value);
                currentStyles.primaryColor = this.value;
                showNotification('{{ __('company.primary_color_updated') }}');
            });

            document.getElementById('secondaryColor').addEventListener('change', function() {
                applyColor('.logo-saviour', this.value);
                currentStyles.secondaryColor = this.value;
                showNotification('{{ __('company.secondary_color_updated') }}');
            });

            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const primary = this.dataset.primary;
                    const secondary = this.dataset.secondary;

                    document.getElementById('primaryColor').value = primary;
                    document.getElementById('secondaryColor').value = secondary;

                    applyColor('.logo-energy', primary);
                    applyColor('.logo-saviour', secondary);

                    currentStyles.primaryColor = primary;
                    currentStyles.secondaryColor = secondary;

                    document.querySelectorAll('.theme-btn').forEach(b => b.style.border = '2px solid #dee2e6');
                    this.style.border = '3px solid #0d6efd';

                    showNotification('{{ __('company.theme_applied') }}');
                });
            });

            document.getElementById('titleFont').addEventListener('change', function() {
                applyTitleFont(this.value);
                currentStyles.titleFont = this.value;
                showNotification('{{ __('company.title_font_updated') }}');
            });

            document.getElementById('bodyFont').addEventListener('change', function() {
                applyBodyFont(this.value);
                currentStyles.bodyFont = this.value;
                showNotification('{{ __('company.body_font_updated') }}');
            });

            document.getElementById('fontSize').addEventListener('change', function() {
                applyFontSize(this.value);
                currentStyles.fontSize = this.value;
                showNotification('{{ __('company.font_size_updated') }}');
            });

            document.getElementById('tableHeaderColor').addEventListener('change', function() {
                applyTableHeaderColor(this.value);
                currentStyles.tableHeaderColor = this.value;
                showNotification('{{ __('company.table_header_color_updated') }}');
            });

            document.getElementById('tableHeaderTextColor').addEventListener('change', function() {
                applyTableHeaderTextColor(this.value);
                currentStyles.tableHeaderTextColor = this.value;
                showNotification('{{ __('company.header_text_color_updated') }}');
            });

            document.getElementById('tableBorderColor').addEventListener('change', function() {
                applyTableBorderColor(this.value);
                currentStyles.tableBorderColor = this.value;
                showNotification('{{ __('company.border_color_updated') }}');
            });

            document.getElementById('tableRowHeight').addEventListener('change', function() {
                applyTableRowHeight(this.value);
                currentStyles.tableRowHeight = this.value;
                showNotification('{{ __('company.row_height_updated') }}');
            });

            document.getElementById('tableFontSize').addEventListener('change', function() {
                applyTableFontSize(this.value);
                currentStyles.tableFontSize = this.value;
                showNotification('{{ __('company.table_font_size_updated') }}');
            });

            document.getElementById('useItemsTableStyling').addEventListener('change', function() {
                const notesControls = document.getElementById('notesTableControls');

                if (this.checked) {
                    notesControls.style.display = 'none';
                    currentStyles.notesUseSeparateStyling = false;

                    applyNotesTableHeaderColor(currentStyles.tableHeaderColor || '#b3d9ff');
                    applyNotesTableHeaderTextColor(currentStyles.tableHeaderTextColor || '#000000');
                    applyNotesTableBorderColor(currentStyles.tableBorderColor || '#6c757d');
                    applyNotesTableRowHeight(currentStyles.tableRowHeight || '6px');
                    applyNotesTableFontSize(currentStyles.tableFontSize || '11px');

                    showNotification('{{ __('company.notes_using_items_styling') }}');
                } else {
                    notesControls.style.display = 'block';
                    currentStyles.notesUseSeparateStyling = true;
                    showNotification('{{ __('company.notes_styling_independent') }}');
                }
            });

            document.getElementById('notesTableHeaderColor').addEventListener('change', function() {
                applyNotesTableHeaderColor(this.value);
                currentStyles.notesTableHeaderColor = this.value;
                showNotification('{{ __('company.notes_header_color_updated') }}');
            });

            document.getElementById('notesTableHeaderTextColor').addEventListener('change', function() {
                applyNotesTableHeaderTextColor(this.value);
                currentStyles.notesTableHeaderTextColor = this.value;
                showNotification('{{ __('company.notes_header_text_color_updated') }}');
            });

            document.getElementById('notesTableBorderColor').addEventListener('change', function() {
                applyNotesTableBorderColor(this.value);
                currentStyles.notesTableBorderColor = this.value;
                showNotification('{{ __('company.notes_border_color_updated') }}');
            });

            document.getElementById('notesTableRowHeight').addEventListener('change', function() {
                applyNotesTableRowHeight(this.value);
                currentStyles.notesTableRowHeight = this.value;
                showNotification('{{ __('company.notes_row_height_updated') }}');
            });

            document.getElementById('notesTableFontSize').addEventListener('change', function() {
                applyNotesTableFontSize(this.value);
                currentStyles.notesTableFontSize = this.value;
                showNotification('{{ __('company.notes_font_size_updated') }}');
            });

            document.getElementById('logoUpload').addEventListener('change', function(e) {
                const file = this.files[0];
                if (!file) return;

                if (file.size > 2 * 1024 * 1024) {
                    alert('{{ __('company.file_too_large') }}');
                    return;
                }

                const fd = new FormData();
                fd.append('logo', file);

                axios.post('{{ route('invoicetemplates.uploadLogo') }}', fd, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'multipart/form-data'
                    }
                }).then(res => {
                    if (res.data.success) {
                        const logoContainer = document.querySelector('.logo-container');
                        if (logoContainer) {
                            logoContainer.innerHTML =
                                `<img src="${res.data.url}" alt="{{ __('company.logo') }}" style="max-height: 60px; max-width: 200px; object-fit: contain; pointer-events: none;">`;
                        }
                        currentStyles.logoPath = res.data.path;
                        showNotification('{{ __('company.logo_uploaded_successfully') }}');
                    }
                }).catch(err => {
                    alert('{{ __('company.upload_failed') }}');
                });
            });

            document.getElementById('loadTemplateBtn').addEventListener('click', function() {
                const templateId = document.getElementById('templateSelector').value;

                @if ($isCompanyModule ?? false)
                    let url = '{{ route('company.invoices.templates.preview.customize', ['draft' => ':draft:']) }}'
                        .replace(':draft:', draftKey);
                @else
                    let url = '{{ route('invoicetemplates.preview.customize', ['draft' => ':draft:']) }}'
                        .replace(':draft:', draftKey);
                @endif

                if (templateId) url += '?template_id=' + templateId;
                window.location.href = url;
            });

            document.getElementById('saveTemplate').addEventListener('click', function() {
                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __('company.saving') }}';

                currentStyles.positions = collectAllPositions();

                currentStyles.tableHeaderColor = document.getElementById('tableHeaderColor').value;
                currentStyles.tableHeaderTextColor = document.getElementById('tableHeaderTextColor').value;
                currentStyles.tableBorderColor = document.getElementById('tableBorderColor').value;
                currentStyles.tableRowHeight = document.getElementById('tableRowHeight').value;
                currentStyles.tableFontSize = document.getElementById('tableFontSize').value;
                currentStyles.primaryColor = document.getElementById('primaryColor').value;
                currentStyles.secondaryColor = document.getElementById('secondaryColor').value;
                currentStyles.titleFont = document.getElementById('titleFont').value;
                currentStyles.bodyFont = document.getElementById('bodyFont').value;
                currentStyles.fontSize = document.getElementById('fontSize').value;

                currentStyles.notesUseSeparateStyling = !document.getElementById('useItemsTableStyling').checked;

                if (currentStyles.notesUseSeparateStyling) {
                    currentStyles.notesTableHeaderColor = document.getElementById('notesTableHeaderColor').value;
                    currentStyles.notesTableHeaderTextColor = document.getElementById('notesTableHeaderTextColor').value;
                    currentStyles.notesTableBorderColor = document.getElementById('notesTableBorderColor').value;
                    currentStyles.notesTableRowHeight = document.getElementById('notesTableRowHeight').value;
                    currentStyles.notesTableFontSize = document.getElementById('notesTableFontSize').value;
                }

                const payload = {
                    name: document.getElementById('templateName').value || '{{ __('company.my_template') }}',
                    template_data: JSON.stringify(currentStyles),
                    logo_path: currentStyles.logoPath || null,
                    is_default: document.getElementById('setAsDefault').checked ? 1 : 0,
                    draft_key: draftKey
                };

                @if ($isCompanyModule ?? false)
                    const saveUrl = '{{ route('company.invoices.templates.save') }}';
                    const redirectUrl = '{{ route('company.invoices.templates.preview.show', ['draft' => ':draft:']) }}'
                        .replace(':draft:', draftKey);
                @else
                    const saveUrl = '{{ route('invoicetemplates.save') }}';
                    const redirectUrl = '{{ route('invoicetemplates.preview.show', ['draft' => ':draft:']) }}'
                        .replace(':draft:', draftKey);
                @endif

                axios.post(saveUrl, payload, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).then(res => {
                    if (res.data.success) {
                        showNotification('{{ __('company.template_saved_successfully') }}');

                        setTimeout(() => {
                            window.location.href = redirectUrl + '?template_id=' + res.data.template_id;
                        }, 1000);
                    }
                }).catch(err => {
                    alert('{{ __('company.save_failed') }}: ' + (err.response?.data?.message || err.message));
                }).finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save me-2"></i>{{ __('company.save_template') }}';
                });
            });

            document.getElementById('previewTemplate').addEventListener('click', function() {
                @if ($isCompanyModule ?? false)
                    const previewUrl = '{{ route('company.invoices.templates.preview.show', ['draft' => $draft->draft_key]) }}';
                @else
                    const previewUrl = '{{ route('invoicetemplates.preview.show', ['draft' => $draft->draft_key]) }}';
                @endif
                window.open(previewUrl, '_blank');
            });

            document.getElementById('downloadPDF').addEventListener('click', function() {
                @if ($isCompanyModule ?? false)
                    const downloadUrl = '{{ route('company.invoices.templates.preview.show', ['draft' => $draft->draft_key]) }}';
                @else
                    const downloadUrl = '{{ route('invoicetemplates.preview.show', ['draft' => $draft->draft_key]) }}';
                @endif
                window.open(downloadUrl, '_blank');
            });

            document.getElementById('resetStyles').addEventListener('click', function() {
                if (confirm('{{ __('company.confirm_reset_all') }}')) {
                    location.reload();
                }
            });

            function showNotification(message) {
                const notification = document.createElement('div');
                notification.style.cssText = `
                    position: fixed; top: 20px; right: 20px; z-index: 9999;
                    background: #d4edda; color: #155724; border: 1px solid #c3e6cb;
                    border-radius: 8px; padding: 12px 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                `;
                notification.innerHTML = `<i class="fas fa-check-circle me-2"></i>${message}`;
                document.body.appendChild(notification);
                setTimeout(() => notification.remove(), 2000);
            }

            init();

        })();

        function showImageModal(imageSrc) {
            document.getElementById('fullProductImage').src = imageSrc;
            const modal = new bootstrap.Modal(document.getElementById('productImageModal'));
            modal.show();
        }
    </script>

    <style>
        .product-thumbnail-preview {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .product-thumbnail-preview:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        #productImageModal .modal-content {
            background-color: rgba(0, 0, 0, 0.95) !important;
        }

        #productImageModal img {
            box-shadow: 0 4px 20px rgba(255, 255, 255, 0.1);
        }

        .table-body-cell:has(.product-thumbnail-preview) {
            vertical-align: middle !important;
        }

        .table-body-cell .fa-image {
            pointer-events: none;
        }

        @media print {
            .product-thumbnail-preview {
                max-width: 50px;
                max-height: 50px;
            }
        }

        .invoice-preview-wrapper {
            overflow: hidden !important;
            box-sizing: border-box;
        }

        .draggable-element {
            max-width: 100%;
            box-sizing: border-box;
        }

        #invoice-logo {
            max-width: 250px;
        }

        #invoice-company,
        #invoice-totals {
            max-width: 300px;
        }

        .sticky-controls {
            position: sticky;
            top: 20px;
        }

        .theme-btn {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .theme-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2) !important;
        }

        .draggable-element {
            transition: all 0.2s ease;
        }

        .invoice-preview-wrapper .table-header-row {
            background-color: #b3d9ff !important;
        }
    </style>
@endsection