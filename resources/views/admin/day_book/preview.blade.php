@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <h4 class="page-title">{{ __('company.tax_invoice') }}</h4>
                            <div class="d-flex doc_button gap-2">
                                <!-- Template Selector -->
                                @if (isset($templates) && $templates->count() > 0)
                                    <div class="btn-group me-2">
                                        <select class="form-select form-select-sm" id="templateSelector"
                                            style="min-width: 200px;">
                                            <option value="">{{ __('company.default_template') }}</option>
                                            @foreach ($templates as $templateOption)
                                                <option value="{{ $templateOption->id }}"
                                                    {{ (isset($template) && $template && isset($template->id) && $template->id === $templateOption->id) ||
                                                    (!isset($template) && $templateOption->is_default)
                                                        ? 'selected'
                                                        : '' }}>
                                                    {{ $templateOption->name }}
                                                    {{ $templateOption->is_default ? '(' . __('company.default_template') . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                                <!-- Customize Button -->
                                <div class="me-2">
                                    <button type="button" class="btn teal-custom" id="customizeInvoice">
                                        <i class="fas fa-edit"></i> {{ __('company.customize_button') }}
                                    </button>
                                </div>

                                <!-- Download Button -->
                                <div class="btn-group me-2">
                                    <button type="button" class="btn downloadcsv dropdown-toggle" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fas fa-download"></i> {{ __('company.download_button') }}
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#" id="downloadPDF">
                                                <i class="fas fa-file-pdf"></i> {{ __('company.download_pdf') }}
                                            </a>
                                        </li>
                                        @if (isset($templates) && $templates->count() > 0)
                                            <li>
                                                <a class="dropdown-item" href="#" id="downloadCustomizedPDF">
                                                    <i class="fas fa-palette"></i> {{ __('company.download_custom_pdf') }}
                                                </a>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body mt-4">
                            <style>
                                body {
                                    background-color: #f8f9fa;
                                    font-family: Arial, sans-serif;
                                }

                                .invoice-container {
                                    background-color: white;
                                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                                    max-width: 800px;
                                    margin: 0 auto;
                                }

                                .logo-container {
                                    display: flex;
                                    align-items: center;
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

                                .invoice-th {
                                    background-color: #bebebf !important;
                                    border: 1px solid #afb0b1 !important;
                                    padding: 12px !important;
                                    font-weight: 600 !important;
                                    font-size: 12px !important;
                                    color: #212529 !important;
                                }

                                .invoice-container .invoice-table thead th {
                                    background-color: #f8f9fa;
                                    border: 1px solid #e3e3d9;
                                    padding: 12px;
                                    font-weight: 600;
                                    font-size: 12px;
                                }

                                .invoice-table td {
                                    border: 1px solid #6c757d;
                                    padding: 12px;
                                    height: 35px;
                                    font-size: 11px;
                                }

                                .invoice-table {
                                    border-collapse: collapse;
                                }

                                .totals-section {
                                    border-bottom: 1px solid #6c757d;
                                    margin-bottom: 8px;
                                }

                                .totals-section.final {
                                    border-bottom: 2px solid #000;
                                    font-weight: bold;
                                }

                                .additional-table td {
                                    border: 1px solid #6c757d;
                                    padding: 12px;
                                    height: 25px;
                                }

                                .custom-table-font {
                                    font-size: 11px;
                                }

                                .custom-small-font {
                                    font-size: 10px;
                                }

                                .custom-tiny-font {
                                    font-size: 9px;
                                }

                                .client-info {
                                    font-size: 11px;
                                    line-height: 1.4;
                                }

                                .company-info {
                                    font-size: 11px;
                                    line-height: 1.4;
                                }

                                .invoice-meta {
                                    font-size: 11px;
                                }

                                .invoice-meta-label {
                                    font-size: 12px;
                                    font-weight: 600;
                                    color: #333;
                                }

                                /* Enhanced styling for customization features */
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

                                /* Loading States */
                                .loading-spinner {
                                    border: 4px solid #f3f3f3;
                                    border-top: 4px solid #3498db;
                                    border-radius: 50%;
                                    width: 40px;
                                    height: 40px;
                                    animation: spin 1s linear infinite;
                                    margin: 0 auto;
                                }

                                @keyframes spin {
                                    0% {
                                        transform: rotate(0deg);
                                    }

                                    100% {
                                        transform: rotate(360deg);
                                    }
                                }

                                .loading-overlay {
                                    min-height: 400px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    flex-direction: column;
                                }

                                .success-message {
                                    position: fixed;
                                    top: 20px;
                                    right: 20px;
                                    z-index: 9999;
                                    min-width: 300px;
                                    background-color: #d4edda;
                                    color: #155724;
                                    border: 1px solid #c3e6cb;
                                    border-radius: 0.375rem;
                                    padding: 0.75rem 1rem;
                                    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
                                }

                                /* Product Image in Invoice Table */
                                .invoice-table .table-body-cell img {
                                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                                }

                                .invoice-table .table-body-cell img:hover {
                                    transform: scale(1.1);
                                    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
                                }

                                .invoice-table .image-placeholder {
                                    width: 50px;
                                    height: 50px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    background-color: #f8f9fa;
                                    border: 1px dashed #dee2e6;
                                    border-radius: 4px;
                                    margin: 0 auto;
                                }

                                .invoice-table .image-placeholder i {
                                    font-size: 20px;
                                    opacity: 0.3;
                                    color: #6c757d;
                                }

                                #productImageModal .modal-content {
                                    background-color: rgba(0, 0, 0, 0.95) !important;
                                }

                                #productImageModal img {
                                    box-shadow: 0 4px 20px rgba(255, 255, 255, 0.1);
                                }

                                @media print {
                                    .invoice-table .table-body-cell img {
                                        max-width: 50px;
                                        max-height: 50px;
                                    }
                                }
                            </style>

                            <!-- Invoice Preview Container -->
                            <div id="invoicePreviewContainer">
                                @include('admin.day_book.preview_content', [
                                    'validated' => $validated,
                                    'client' => $client,
                                    'template' => $template ?? null,
                                    'templates' => $templates ?? [],
                                    'customerData' => $customerData ?? null,
                                    'companyData' => $companyData ?? null,
                                    'fileData' => $fileData ?? null,
                                    'bankAccount' => $bankAccount ?? null,
                                    'isCompanyModule' => $isCompanyModule ?? false,
                                    'invoiceNotes' => $invoiceNotes ?? [],
                                ])
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
        // AJAX Template Switching
        const templateSelector = document.getElementById('templateSelector');
        if (templateSelector) {
            templateSelector.addEventListener('change', function(e) {
                const templateId = e.target.value;
                const container = document.getElementById('invoicePreviewContainer');

                // Show loading state
                this.disabled = true;
                const originalHTML = container.innerHTML;
                container.innerHTML = `
                    <div class="loading-overlay text-center p-5">   
                        <div class="loading-spinner mb-3"></div>
                        <p class="text-muted">{{ __('company.loading_template') }}</p>
                    </div>
                `;

                // Make AJAX request
                fetch('{{ route('invoicetemplates.preview.ajax') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            template_id: templateId,
                            invoice_data: @json($validated)
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            container.innerHTML = data.html;

                            // Show success message
                            const successMsg = document.createElement('div');
                            successMsg.className = 'success-message';
                            successMsg.innerHTML = `
                            <i class="fas fa-check-circle me-2"></i>{{ __('company.template_applied_successfully') }}
                            <button type="button" class="btn-close float-end" onclick="this.parentElement.remove()"></button>
                        `;
                            document.body.appendChild(successMsg);

                            setTimeout(() => {
                                if (successMsg.parentNode) {
                                    successMsg.remove();
                                }
                            }, 3000);
                        } else {
                            container.innerHTML = originalHTML;
                            alert('{{ __('company.failed_load_template') }}: ' + (data.message || '{{ __('company.unknown_error') }}'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        container.innerHTML = originalHTML;
                        alert('{{ __('company.failed_load_template') }}');
                    })
                    .finally(() => {
                        this.disabled = false;
                    });
            });
        }

        document.getElementById('downloadPDF').addEventListener('click', function(e) {
            e.preventDefault();

            const templateSelector = document.getElementById('templateSelector');
            const selectedTemplateId = templateSelector ? templateSelector.value : '';

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('invoicetemplates.preview.download.pdf') }}';
            form.style.display = 'none';

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            if (selectedTemplateId) {
                const templateInput = document.createElement('input');
                templateInput.type = 'hidden';
                templateInput.name = 'template_id';
                templateInput.value = selectedTemplateId;
                form.appendChild(templateInput);
            }

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

        // Customize button functionality
        document.getElementById('customizeInvoice').addEventListener('click', function(e) {
            e.preventDefault();

            const draftKey = '{{ $draft->draft_key }}';
            const templateSelector = document.getElementById('templateSelector');
            const templateId = templateSelector ? templateSelector.value : '';

            @if ($isCompanyModule ?? false)
                let url = '{{ route('company.invoices.templates.preview.customize', ['draft' => ':draft:']) }}'
                    .replace(':draft:', draftKey);
            @else
                let url = '{{ route('invoicetemplates.preview.customize', ['draft' => ':draft:']) }}'
                    .replace(':draft:', draftKey);
            @endif

            if (templateId) {
                url += '?template_id=' + templateId;
            }

            window.location.href = url;
        });

        // Enhanced download with custom template
        const downloadCustomizedPDF = document.getElementById('downloadCustomizedPDF');
        if (downloadCustomizedPDF) {
            downloadCustomizedPDF.addEventListener('click', function(e) {
                e.preventDefault();

                const selectedTemplate = templateSelector?.value;
                if (!selectedTemplate) {
                    alert('{{ __('company.select_template_first') }}');
                    return;
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('invoicetemplates.preview.download.pdf') }}';
                form.style.display = 'none';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                const templateInput = document.createElement('input');
                templateInput.type = 'hidden';
                templateInput.name = 'template_id';
                templateInput.value = selectedTemplate;
                form.appendChild(templateInput);

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
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    if (!this.disabled && !this.classList.contains('dropdown-toggle') && this.id !==
                        'templateSelector') {
                        const originalText = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + originalText
                            .replace(/<i[^>]*><\/i>\s*/, '');
                        this.disabled = true;

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