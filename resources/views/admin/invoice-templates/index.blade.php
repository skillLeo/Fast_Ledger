@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <h4 class="page-title">TAX INVOICE</h4>
                            <div class="d-flex doc_button gap-2">
                                <!-- Template Selector -->
                                @if (isset($templates) && $templates->count() > 0)
                                    <div class="btn-group me-2">
                                        <select class="form-select form-select-sm" id="templateSelector"
                                            style="min-width: 200px;">
                                            <option value="">Default Template</option>
                                            @foreach ($templates as $template)
                                                <option value="{{ $template->id }}"
                                                    {{ $defaultTemplate && $defaultTemplate->id === $template->id ? 'selected' : '' }}>
                                                    {{ $template->name }} {{ $template->is_default ? '(Default)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <!-- Customize Button -->
                                <div class="btn-group me-2">
                                    <button type="button" class="btn btn-outline-primary" id="customizeInvoice">
                                        <i class="fas fa-edit"></i> Customize
                                    </button>
                                </div>

                                <!-- Download Button -->
                                <div class="btn-group me-2">
                                    <button type="button" class="btn downloadcsv dropdown-toggle" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#" id="downloadPDF">
                                                <i class="fas fa-file-pdf"></i> Download PDF
                                            </a>
                                        </li>
                                        @if (isset($templates) && $templates->count() > 0)
                                            <li>
                                                <a class="dropdown-item" href="#" id="downloadCustomizedPDF">
                                                    <i class="fas fa-palette"></i> Download with Custom Template
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Apply template styles if template exists -->
                            @if (isset($template) && $template)
                                <style id="templateStyles">
                                    /* Dynamic template styles will be applied here */
                                    @if ($template->template_data && isset($template->template_data['elements']))
                                        @foreach ($template->template_data['elements'] as $elementKey => $elementData)
                                            [data-element="{{ $elementKey }}"] {
                                                @if (isset($elementData['position']))
                                                    position: absolute;
                                                    left: {{ $elementData['position']['x'] }}px;
                                                    top: {{ $elementData['position']['y'] }}px;
                                                @endif
                                                @if (isset($elementData['size']))
                                                    width: {{ $elementData['size']['width'] }}px;
                                                    height: {{ $elementData['size']['height'] }}px;
                                                @endif
                                                @if (isset($elementData['styles']))
                                                    @foreach ($elementData['styles'] as $property => $value)
                                                        {{ $property }}: {{ $value }};
                                                    @endforeach
                                                @endif
                                            }
                                        @endforeach
                                    @endif
                                </style>
                            @endif

                            <style>
                                /* Main Container & Body */
                                body {
                                    background-color: #f8f9fa;
                                    font-family: Arial, sans-serif;
                                }

                                .invoice-container {
                                    background-color: white;
                                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                                    max-width: 900px;
                                    margin: 0 auto;
                                    padding: 30px;
                                }

                                /* Logo Styles */
                                .logo-container {
                                    display: flex;
                                    align-items: center;
                                    justify-content: flex-end;
                                }

                                .logo-energy {
                                    background-color: #1e3a8a;
                                    color: white;
                                    padding: 8px 16px;
                                    border-radius: 8px 0 0 8px;
                                    font-weight: 600;
                                    font-size: 18px;
                                }

                                .logo-saviour {
                                    background-color: #16a34a;
                                    color: white;
                                    padding: 8px 16px;
                                    border-radius: 0 8px 8px 0;
                                    font-weight: 600;
                                    font-size: 18px;
                                    display: flex;
                                    align-items: center;
                                }

                                .logo-icon {
                                    width: 24px;
                                    height: 24px;
                                    background-color: white;
                                    border-radius: 50%;
                                    margin-left: 8px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                }

                                .logo-check {
                                    width: 16px;
                                    height: 20px;
                                    border-right: 2px solid #16a34a;
                                    border-bottom: 2px solid #16a34a;
                                    transform: rotate(45deg);
                                    margin-top: -4px;
                                }

                                /* Header Section */
                                .invoice-header {
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    margin-bottom: 40px;
                                    border-bottom: 2px solid #e5e7eb;
                                    padding-bottom: 20px;
                                }

                                .invoice-title {
                                    font-size: 2.5rem;
                                    font-weight: bold;
                                    color: #1f2937;
                                    margin: 0;
                                }

                                /* Invoice Details Section */
                                .invoice-details {
                                    margin-bottom: 40px;
                                }

                                .invoice-to {
                                    font-size: 14px;
                                    font-weight: 600;
                                    color: #374151;
                                    margin-bottom: 10px;
                                }

                                .client-info {
                                    font-size: 13px;
                                    line-height: 1.5;
                                    color: #4b5563;
                                }

                                .client-info div {
                                    margin-bottom: 2px;
                                }

                                .company-info {
                                    font-size: 13px;
                                    line-height: 1.5;
                                    color: #4b5563;
                                }

                                .company-info div {
                                    margin-bottom: 2px;
                                }

                                .invoice-meta {
                                    font-size: 13px;
                                    color: #4b5563;
                                    margin-bottom: 15px;
                                }

                                .invoice-meta-label {
                                    font-weight: 600;
                                    color: #374151;
                                    display: block;
                                    margin-bottom: 3px;
                                }

                                /* Professional Table Styles */
                                .invoice-table {
                                    width: 100%;
                                    border-collapse: collapse;
                                    margin-bottom: 30px;
                                    background-color: white;
                                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                                }

                                .invoice-table thead th {
                                    background-color: #b3d9e8 !important;
                                    color: #ffffff !important;
                                    font-weight: 600 !important;
                                    font-size: 13px !important;
                                    padding: 15px 12px !important;
                                    text-align: center !important;
                                    border: 1px solid #a0c4d4 !important;
                                }

                                .invoice-table tbody td {
                                    padding: 12px;
                                    border: 1px solid #d1d5db;
                                    font-size: 13px;
                                    color: #374151;
                                    text-align: center;
                                    background-color: #ffffff;
                                }

                                .invoice-table tbody td:first-child {
                                    text-align: left;
                                }

                                .invoice-table tbody tr:nth-child(even) {
                                    background-color: #f9fafb;
                                }

                                .invoice-table tbody tr:hover {
                                    background-color: #f3f4f6;
                                }

                                /* Item Description Styling */
                                .item-description {
                                    font-weight: 600;
                                    color: #1f2937;
                                    margin-bottom: 4px;
                                }

                                .item-details {
                                    font-size: 12px;
                                    color: #6b7280;
                                    margin-bottom: 2px;
                                }

                                /* Payment and Totals Section */
                                .payment-section {
                                    display: flex;
                                    justify-content: space-between;
                                    margin-top: 30px;
                                }

                                .payment-section .row {
                                    display: flex;
                                    width: 100%;
                                }

                                .payment-section .col-md-8 {
                                    flex: 0 0 66.666667%;
                                    max-width: 66.666667%;
                                    padding-right: 30px;
                                }

                                .payment-section .col-md-4 {
                                    flex: 0 0 33.333333%;
                                    max-width: 33.333333%;
                                }

                                .payment-info {
                                    font-size: 13px;
                                    line-height: 1.6;
                                    color: #4b5563;
                                }

                                .payment-info div {
                                    margin-bottom: 3px;
                                }

                                .payment-title {
                                    font-weight: 600;
                                    color: #374151;
                                    margin-bottom: 10px;
                                    font-size: 14px;
                                }

                                /* Totals Styling */
                                .totals-section {
                                    background-color: #f9fafb;
                                    padding: 20px;
                                    border-radius: 8px;
                                    border: 1px solid #e5e7eb;
                                }

                                .total-row {
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    padding: 8px 0;
                                    font-size: 14px;
                                    border-bottom: 1px solid #e5e7eb;
                                }

                                .total-row:last-child {
                                    border-bottom: none;
                                }

                                .total-row.final {
                                    font-weight: bold;
                                    font-size: 16px;
                                    color: #1f2937;
                                    border-top: 2px solid #374151;
                                    margin-top: 8px;
                                    padding-top: 12px;
                                }

                                .total-row span:first-child {
                                    font-weight: 600;
                                    color: #374151;
                                }

                                .total-row span:last-child {
                                    font-weight: 600;
                                    color: #1f2937;
                                }

                                /* Footer */
                                .footer-text {
                                    text-align: center;
                                    font-size: 11px;
                                    color: #6b7280;
                                    margin-top: 40px;
                                    padding-top: 20px;
                                    border-top: 1px solid #e5e7eb;
                                }

                                /* Enhanced Button & UI Styling */
                                .btn-group .btn {
                                    border-radius: 0.375rem;
                                }

                                .form-select-sm {
                                    border-radius: 0.375rem;
                                }

                                .dropdown-menu {
                                    border-radius: 0.5rem;
                                    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
                                }

                                .dropdown-item {
                                    padding: 0.5rem 1rem;
                                    transition: all 0.2s ease;
                                }

                                .dropdown-item:hover {
                                    background-color: #f8f9fa;
                                    transform: translateX(2px);
                                }

                                .btn-outline-primary {
                                    transition: all 0.3s ease;
                                }

                                .btn-outline-primary:hover {
                                    transform: translateY(-1px);
                                    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
                                }

                                /* Responsive Design */
                                @media (max-width: 768px) {
                                    .invoice-container {
                                        margin: 10px;
                                        padding: 20px;
                                    }

                                    .invoice-header {
                                        flex-direction: column;
                                        align-items: flex-start;
                                    }

                                    .logo-container {
                                        justify-content: flex-start;
                                        margin-top: 20px;
                                    }

                                    .invoice-details>div[style*="display: flex"] {
                                        flex-direction: column !important;
                                    }

                                    .invoice-details>div[style*="display: flex"]>div {
                                        flex: none !important;
                                        padding: 0 !important;
                                        margin-bottom: 20px;
                                    }

                                    .payment-section .row {
                                        flex-direction: column;
                                    }

                                    .payment-section .col-md-8,
                                    .payment-section .col-md-4 {
                                        flex: none;
                                        max-width: 100%;
                                        padding-right: 0;
                                        margin-bottom: 20px;
                                    }

                                    .invoice-table {
                                        font-size: 11px;
                                    }

                                    .invoice-table thead th,
                                    .invoice-table tbody td {
                                        padding: 8px 6px;
                                    }
                                }

                                /* Print Styles */
                                @media print {
                                    body {
                                        background-color: white;
                                    }

                                    .invoice-container {
                                        box-shadow: none;
                                        max-width: none;
                                        margin: 0;
                                        padding: 0;
                                    }

                                    .btn-group,
                                    .dropdown-menu {
                                        display: none !important;
                                    }

                                    .invoice-table {
                                        box-shadow: none;
                                    }
                                }
                            </style>

                            <div class="invoice-container" id="invoicePreview">
                                <!-- Header -->
                                <div class="invoice-header">
                                    <h1 class="invoice-title" data-element="title">TAX INVOICE</h1>
                                    <div class="logo-container" data-element="logo">
                                        @if (isset($template) && $template && $template->logo_path)
                                            <img src="{{ Storage::url($template->logo_path) }}" alt="Logo"
                                                style="max-height: 60px; max-width: 200px;">
                                        @else
                                            <div class="logo-energy">Energy</div>
                                            <div class="logo-saviour">
                                                Saviour
                                                <div class="logo-icon">
                                                    <div class="logo-check"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Invoice Details -->
                                <div class="invoice-details">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 40px;">
                                        <div style="flex: 1; padding-right: 30px;">
                                            <div data-element="invoice-to-label" class="invoice-to">Invoice to:</div>
                                            <div class="client-info" data-element="client-info">
                                                @if (isset($fileData) && $fileData)
                                                    {{-- ✅ Display File Data --}}
                                                    <div><strong>{{ $fileData->First_Name }}
                                                            {{ $fileData->Last_Name }}</strong></div>

                                                    @if ($fileData->Matter)
                                                        <div><strong>Matter:</strong> {{ $fileData->Matter }}</div>
                                                    @endif

                                                    @if ($fileData->Ledger_Ref)
                                                        <div><strong>Ref:</strong> {{ $fileData->Ledger_Ref }}</div>
                                                    @endif

                                                    @if ($fileData->Address1)
                                                        <div>{{ $fileData->Address1 }}</div>
                                                    @endif

                                                    @if ($fileData->Address2)
                                                        <div>{{ $fileData->Address2 }}</div>
                                                    @endif

                                                    @if ($fileData->Town)
                                                        <div>{{ $fileData->Town }}</div>
                                                    @endif

                                                    @if ($fileData->Post_Code)
                                                        <div>{{ $fileData->Post_Code }}</div>
                                                    @endif

                                                    @if ($fileData->Phone)
                                                        <div>T: {{ $fileData->Phone }}</div>
                                                    @endif

                                                    @if ($fileData->Mobile)
                                                        <div>M: {{ $fileData->Mobile }}</div>
                                                    @endif

                                                    @if ($fileData->Email)
                                                        <div>E: {{ $fileData->Email }}</div>
                                                    @endif

                                                    @if ($fileData->NIC_No)
                                                        <div><strong>NIC:</strong> {{ $fileData->NIC_No }}</div>
                                                    @endif
                                                @else
                                                    {{-- ✅ Fallback to Client Data --}}
                                                    <div>{{ $client->name ?? 'ABC Company' }}</div>
                                                    <div>{{ $client->address ?? 'ABC Road Slough' }}</div>
                                                    <div>{{ $client->city ?? 'United Kingdom' }}</div>
                                                    <div>{{ $client->postcode ?? 'PI98 7HV' }}</div>
                                                    <div>T: {{ $client->phone ?? '07456764343' }}</div>
                                                    <div>E: {{ $client->email ?? 'abc@company.co.uk' }}</div>
                                                    <div>VAT No: {{ $client->vat_no ?? '15674537' }}</div>
                                                @endif
                                            </div>
                                        </div>

                                        <div style="flex: 1; padding: 0 15px;">
                                            <div data-element="invoice-meta">
                                                <div class="invoice-meta">
                                                    <span class="invoice-meta-label">Invoice Date</span><br>
                                                    {{ \Carbon\Carbon::parse($validated['Transaction_Date'])->format('d M Y') }}
                                                </div>
                                                <div class="invoice-meta">
                                                    <span class="invoice-meta-label">Invoice No</span><br>
                                                    {{ $validated['invoice_no'] }}
                                                </div>
                                                @if (isset($validated['Inv_Due_Date']))
                                                    <div class="invoice-meta">
                                                        <span class="invoice-meta-label">Inv Due Date</span><br>
                                                        {{ \Carbon\Carbon::parse($validated['Inv_Due_Date'])->format('d/M/Y') }}
                                                    </div>
                                                @endif
                                                @if (isset($validated['invoice_ref']))
                                                    <div class="invoice-meta">
                                                        <span class="invoice-meta-label">Invoice Ref</span><br>
                                                        {{ $validated['invoice_ref'] }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div style="flex: 1; padding-left: 30px;">
                                            <div class="company-info" data-element="company-info">
                                                <div style="font-weight: bold; margin-bottom: 8px; color: #1f2937;">Energy
                                                    Saviour Ltd</div>
                                                <div>First line of address</div>
                                                <div>Second line of address</div>
                                                <div>Town, County</div>
                                                <div>Postcode</div>
                                                <div>T: 07673767623</div>
                                                <div>E: office@energysaviourltd.co.uk</div>
                                                <div>VAT No: 157676554</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Invoice Table -->
                                <table class="invoice-table" data-element="invoice-table">
                                    <thead>
                                        <tr>
                                            <th data-column="description" style="width: 40%;">Description</th>
                                            <th data-column="qty" style="width: 10%;">Qty</th>
                                            <th data-column="unit-price" style="width: 15%;">Unit Price</th>
                                            <th data-column="vat" style="width: 15%;">VAT</th>
                                            <th data-column="total" style="width: 20%;">Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($validated['items']) && is_array($validated['items']))
                                            @foreach ($validated['items'] as $item)
                                                <tr>
                                                    <td>
                                                        <div class="item-description">{{ $item['item_code'] ?? '' }}</div>
                                                        <div class="item-details">{{ $item['description'] ?? '' }}</div>
                                                        @if (isset($item['ledger_ref']))
                                                            <div class="item-details">
                                                                {{ $item['ledger_ref'] }} -
                                                                {{ $item['account_ref'] ?? '' }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>1</td>
                                                    <td>£{{ number_format($item['unit_amount'] ?? 0, 2) }}</td>
                                                    <td>£{{ number_format($item['vat_amount'] ?? 0, 2) }}
                                                        ({{ $item['vat_rate'] ?? 0 }}%)</td>
                                                    <td>£{{ number_format($item['net_amount'] ?? 0, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        @endif

                                        @php
                                            $itemCount = isset($validated['items']) ? count($validated['items']) : 0;
                                            $emptyRows = 8 - $itemCount;
                                        @endphp

                                        @for ($i = 0; $i < $emptyRows; $i++)
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>

                                <!-- Payment and Totals -->
                                <div class="payment-section">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="payment-info" data-element="payment-info">
                                                <div class="payment-title">Please make electronic payment to</div>
                                                <div>Name: Energy Saviour Ltd</div>
                                                <div>Sort Code: 12-34-32</div>
                                                <div>Account No: 43456754</div>
                                                <div>Payment Ref: {{ $validated['invoice_no'] }}</div>
                                            </div>

                                            @if (isset($validated['invoice_notes']) && !empty($validated['invoice_notes']))
                                                <div style="margin-top: 20px;">
                                                    <div class="payment-title">Notes:</div>
                                                    <div class="payment-info">{{ $validated['invoice_notes'] }}</div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="col-md-4">
                                            <div class="totals-section" data-element="totals">
                                                <div class="total-row">
                                                    <span>NET</span>
                                                    <span>£{{ number_format($validated['invoice_net_amount'] ?? 0, 2) }}</span>
                                                </div>
                                                <div class="total-row">
                                                    <span>VAT</span>
                                                    <span>£{{ number_format($validated['invoice_vat_amount'] ?? 0, 2) }}</span>
                                                </div>
                                                <div class="total-row final">
                                                    <span>TOTAL</span>
                                                    <span>£{{ number_format($validated['invoice_total_amount'] ?? 0, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="footer-text" data-element="footer-text">
                                    Company Registration No: 76767554 &nbsp;&nbsp;&nbsp;
                                    Registered Office: Unit 30, Business Village, Wexham Road, Slough, SL1 5HF
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('downloadPDF').addEventListener('click', function(e) {
            e.preventDefault();

            // Create a form with all the validated data
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('preview.download.pdf') }}';
            form.style.display = 'none';

            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            // Add all the validated data as hidden fields
            const validatedData = @json($validated);

            function addFormField(name, value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            }

            function addFieldsRecursively(data, prefix = '') {
                for (const key in data) {
                    if (data.hasOwnProperty(key)) {
                        const fieldName = prefix ? `${prefix}[${key}]` : key;
                        const value = data[key];

                        if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
                            addFieldsRecursively(value, fieldName);
                        } else if (Array.isArray(value)) {
                            value.forEach((item, index) => {
                                if (typeof item === 'object' && item !== null) {
                                    addFieldsRecursively(item, `${fieldName}[${index}]`);
                                } else {
                                    addFormField(`${fieldName}[${index}]`, item);
                                }
                            });
                        } else {
                            addFormField(fieldName, value);
                        }
                    }
                }
            }

            addFieldsRecursively(validatedData);

            // Submit the form
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        });

        // Customize button functionality
        document.getElementById('customizeInvoice').addEventListener('click', function(e) {
            e.preventDefault();

            // Collect current form data and redirect to customize mode
            const formData = @json($validated);
            const params = new URLSearchParams();

            // Convert nested objects to proper URL parameters
            function addParamsRecursively(data, prefix = '') {
                for (const key in data) {
                    if (data.hasOwnProperty(key)) {
                        const fieldName = prefix ? `${prefix}[${key}]` : key;
                        const value = data[key];

                        if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
                            addParamsRecursively(value, fieldName);
                        } else if (Array.isArray(value)) {
                            value.forEach((item, index) => {
                                if (typeof item === 'object' && item !== null) {
                                    addParamsRecursively(item, `${fieldName}[${index}]`);
                                } else {
                                    params.append(`${fieldName}[${index}]`, item);
                                }
                            });
                        } else {
                            params.append(fieldName, value);
                        }
                    }
                }
            }

            addParamsRecursively(formData);

            window.location.href = '{{ route('invoicetemplates.preview.customize') }}?' + params.toString();
        });

        // Template selector change
        const templateSelector = document.getElementById('templateSelector');
        if (templateSelector) {
            templateSelector.addEventListener('change', function(e) {
                // Show loading state
                const currentText = this.options[this.selectedIndex].text;
                const loadingOption = document.createElement('option');
                loadingOption.value = '';
                loadingOption.text = 'Loading...';
                loadingOption.selected = true;
                this.appendChild(loadingOption);
                this.disabled = true;

                // Reload preview with selected template
                const currentUrl = new URL(window.location);
                if (e.target.value) {
                    currentUrl.searchParams.set('template_id', e.target.value);
                } else {
                    currentUrl.searchParams.delete('template_id');
                }
                window.location.href = currentUrl.toString();
            });
        }

        // Enhanced download with custom template
        const downloadCustomizedPDF = document.getElementById('downloadCustomizedPDF');
        if (downloadCustomizedPDF) {
            downloadCustomizedPDF.addEventListener('click', function(e) {
                e.preventDefault();

                const selectedTemplate = templateSelector?.value;
                if (!selectedTemplate) {
                    alert('Please select a template first.');
                    return;
                }

                // Create form similar to downloadPDF but with template_id
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('preview.download.pdf') }}';
                form.style.display = 'none';

                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                // Add template ID
                const templateInput = document.createElement('input');
                templateInput.type = 'hidden';
                templateInput.name = 'template_id';
                templateInput.value = selectedTemplate;
                form.appendChild(templateInput);

                // Add validated data
                const validatedData = @json($validated);

                function addFormField(name, value) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    form.appendChild(input);
                }

                function addFieldsRecursively(data, prefix = '') {
                    for (const key in data) {
                        if (data.hasOwnProperty(key)) {
                            const fieldName = prefix ? `${prefix}[${key}]` : key;
                            const value = data[key];

                            if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
                                addFieldsRecursively(value, fieldName);
                            } else if (Array.isArray(value)) {
                                value.forEach((item, index) => {
                                    if (typeof item === 'object' && item !== null) {
                                        addFieldsRecursively(item, `${fieldName}[${index}]`);
                                    } else {
                                        addFormField(`${fieldName}[${index}]`, item);
                                    }
                                });
                            } else {
                                addFormField(fieldName, value);
                            }
                        }
                    }
                }

                addFieldsRecursively(validatedData);
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            });
        }

        // Add smooth transitions for better UX
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading states to buttons
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    if (!this.disabled && !this.classList.contains('dropdown-toggle')) {
                        const originalText = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + originalText
                            .replace(/<i[^>]*><\/i>\s*/, '');
                        this.disabled = true;

                        // Re-enable after 3 seconds (fallback)
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.disabled = false;
                        }, 3000);
                    }
                });
            });
        });
    </script>
@endsection
