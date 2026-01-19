    @extends('admin.layout.app')

    @section('content')
        <div class="main-content app-content">
            <div class="container-fluid">
                @include('admin.partial.errors')

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card custom-card">

                            <span class="page-title">
                                {{ isset($type) && $type === 'purchase' ? __('company.purchasing_management') : __('company.invoices_management') }}

                            </span>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                {{-- ✅ NEW CODE - Tabs preserve type parameter --}}
                                <div class="d-flex flex-wrap gap-2">
                                    {{-- Build route parameters that preserve 'type' --}}
                                    @php
                                        $issuedParams = ['tab' => 'issued'];
                                        $draftsParams = ['tab' => 'drafts'];

                                        // ✅ Preserve 'type' parameter if present
                                        if (isset($type) && $type === 'purchase') {
                                            $issuedParams['type'] = 'purchase';
                                            $draftsParams['type'] = 'purchase';
                                        }
                                    @endphp

                                    {{-- Issued Tab --}}
                                    <a href="{{ route(($routePrefix ?? 'invoices') . '.index', $issuedParams) }}"
                                        class="nav-link-btn {{ $activeTab === 'issued' ? 'active' : '' }}">

                                        {{ isset($type) && $type === 'purchase' ? __('company.issued_purchases') : __('company.issued_invoices') }}
                                    </a>

                                    {{-- Drafts Tab --}}
                                    <a href="{{ route(($routePrefix ?? 'invoices') . '.index', $draftsParams) }}"
                                        class="nav-link-btn {{ $activeTab === 'drafts' ? 'active' : '' }}">

                                        {{ isset($type) && $type === 'purchase' ? __('company.draft_purchases') : __('company.draft_invoices') }}
                                    </a>
                                </div>
                                {{-- ✅ NEW CODE - Dynamic create button based on type --}}
                                <div>
                                    @if (isset($isCompanyModule) && $isCompanyModule)
                                        @if (isset($type) && $type === 'purchase')
                                            {{-- Purchasing Tab: Link to purchase invoice --}}
                                            <a href="{{ route('company.invoices.create', ['payment_type' => 'purchase']) }}"
                                                class="teal-custom-btn p-2">
                                                <i class="fa fa-plus me-2"></i>Create Purchase
                                            </a>
                                        @else
                                            {{-- Invoices Tab: Link to sales invoice --}}
                                            <a href="{{ route('company.invoices.create', ['payment_type' => 'sales_invoice']) }}"
                                                class="teal-custom-btn p-2">
                                                <i class="fa fa-plus me-2"></i>{{ __('company.create_new_invoice') }}
                                            </a>
                                        @endif
                                    @else
                                        @php
                                            $paymentType =
                                                isset($type) && $type === 'purchase' ? 'purchase' : 'sales_invoice';
                                        @endphp
                                        <a href="{{ route('transactions.create', ['type' => 'office', 'payment_type' => $paymentType]) }}"
                                            class="teal-custom-btn p-2">
                                            <i class="fa fa-plus me-2"></i>{{ __('company.create_new_invoice') }}
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ __('company.invoice_no') }}</th>
                                                <th>{{ __('company.customer') }}</th>
                                                <th>{{ __('company.date') }}</th>
                                                <th>{{ __('company.due_date') }}</th>
                                                <th class="text-end">{{ __('company.total') }}</th>
                                                <th class="text-end">{{ __('company.paid') }}</th>
                                                <th class="text-end">{{ __('company.balance') }}</th>
                                                <th class="text-center">{{ __('company.status') }}</th>
                                                <th class="text-center">{{ __('company.documents') }}</th>
                                                <th class="text-center">{{ __('company.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($invoices as $invoice)
                                                <tr>
                                                    <td><span class="fw-bold">{{ $invoice->invoice_no ?: '-' }}</span></td>
                                                    <td>
                                                        <div class="d-flex flex-column">
                                                            <span class="fw-medium">{{ $invoice->customer_name }}</span>
                                                            {{-- <small class="text-muted">{{ $invoice->customer_ref }}</small> --}}
                                                        </div>
                                                    </td>
                                                    <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                                                    <td>
                                                        {{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}
                                                        @if ($invoice->isOverdue())
                                                            <span class="badge bg-danger ms-1">
                                                                <i class="fa fa-exclamation-triangle"></i>
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end fw-bold">
                                                        £{{ number_format($invoice->total_amount, 2) }}</td>
                                                    <td class="text-end text-success">
                                                        £{{ number_format($invoice->paid, 2) }}
                                                    </td>
                                                    <td
                                                        class="text-end {{ $invoice->balance > 0 ? 'text-danger fw-bold' : 'text-muted' }}">
                                                        £{{ number_format($invoice->balance, 2) }}
                                                    </td>

                                                    <td class="text-center">
                                                        @if ($activeTab !== 'drafts')
                                                            <a href="javascript:void(0)"
                                                                class="text-decoration-none status-change-btn"
                                                                data-invoice-id="{{ $invoice->id }}"
                                                                title="{{ __('company.click_to_change_status') }}">
                                                                <span class="{{ $invoice->status_badge }}">
                                                                    <i class="fa fa-edit"></i> {{ $invoice->status_label }}
                                                                </span>
                                                            </a>
                                                        @else
                                                            <span class="{{ $invoice->status_badge }}">
                                                                {{ $invoice->status_label }}
                                                            </span>
                                                        @endif
                                                    </td>

                                                    <td class="text-center">
                                                        @if ($invoice->documents->count() > 0)
                                                            <a href="javascript:void(0)"
                                                                class="text-decoration-none view-documents-btn"
                                                                data-invoice-id="{{ $invoice->id }}"
                                                                title="{{ __('company.view_document') }}">
                                                                <span class="badge bg-info">
                                                                    <i class="fa fa-paperclip"></i>
                                                                    {{ $invoice->documents->count() }}
                                                                </span>
                                                            </a>
                                                        @else
                                                            <span class="text-muted">
                                                                <i class="fa fa-file-o"></i> {{ __('company.no_docs') }}
                                                            </span>
                                                        @endif
                                                    </td>

                                                    <td class="text-center">
                                                        <div class="btn-group" role="group">
                                                            @if ($activeTab === 'drafts')
                                                                <a href="{{ route(($routePrefix ?? 'invoices') . '.edit', $invoice->id) }}"
                                                                    class="btn btn-sm btn-warning"
                                                                    title="{{ __('company.edit_draft') }}">
                                                                    <i class="fa fa-edit"></i>
                                                                </a>

                                                                <form
                                                                    action="{{ route(($routePrefix ?? 'invoices') . '.destroy', $invoice->id) }}"
                                                                    method="POST" class="d-inline"
                                                                    onsubmit="return confirm('{{ __('company.confirm_delete_invoice') }}')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                                        title="{{ __('company.delete') }}">
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <a href="{{ route(($routePrefix ?? 'invoices') . '.view', $invoice->id) }}"
                                                                    class="btn btn-sm btn-primary"
                                                                    title="{{ __('company.view_invoice') }}">
                                                                    <i class="fa fa-eye"></i>
                                                                </a>

                                                                <button class="btn btn-sm btn-secondary"
                                                                    title="{{ __('company.download_pdf') }}"
                                                                    onclick="downloadInvoicePDF({{ $invoice->id }})">
                                                                    <i class="fa fa-file-pdf"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="10" class="text-center text-muted py-5">
                                                        <i class="fa fa-inbox fa-3x mb-3 d-block"></i>
                                                        <p class="mb-0">
                                                            @if ($activeTab === 'drafts')
                                                                @if (isset($type) && $type === 'purchase')
                                                                    {{ __('company.no_draft_purchases_found') }}
                                                                @else
                                                                    {{ __('company.no_drafts_found') }}
                                                                @endif
                                                            @else
                                                                @if (isset($type) && $type === 'purchase')
                                                                    {{ __('company.no_issued_purchases_found') }}
                                                                @else
                                                                    {{ __('company.no_issued_found') }}
                                                                @endif
                                                            @endif
                                                        </p>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="mt-3">
                                    {{ $invoices->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STATUS CHANGE MODAL --}}
        <div class="modal fade" id="statusModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-edit me-2"></i>{{ __('company.change_invoice_status') }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="statusLoading" class="text-center py-4">
                            <div class="spinner-border text-primary"></div>
                            <p class="mt-2 text-muted">{{ __('company.loading_invoice_details') }}</p>
                        </div>

                        <div id="statusForm" style="display: none;">
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>{{ __('company.invoice_no') }}:</strong> <span
                                                id="modal_invoice_no"></span></p>
                                        <p class="mb-1"><strong>{{ __('company.customer') }}:</strong> <span
                                                id="modal_customer"></span></p>
                                        <p class="mb-0"><strong>{{ __('company.due_date') }}:</strong> <span
                                                id="modal_due_date"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1">{{ __('company.total') }}: <strong>£<span
                                                    id="modal_total"></span></strong></p>
                                        <p class="mb-1">{{ __('company.paid') }}: <strong class="text-success">£<span
                                                    id="modal_paid"></span></strong></p>
                                        <p class="mb-0">{{ __('company.balance') }}: <strong class="text-danger">£<span
                                                    id="modal_balance"></span></strong></p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ __('company.current_status') }}</label>
                                <span id="current_status_badge"></span>
                            </div>

                            <form id="statusChangeForm">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('company.new_status') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="new_status" required>
                                        <option value="">{{ __('company.select_status') }}</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="mb-3" id="payment_amount_section" style="display: none;">
                                    <label class="form-label">{{ __('company.payment_amount') }} <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">£</span>
                                        <input type="number" class="form-control" id="payment_amount" step="0.01"
                                            min="0.01">
                                    </div>
                                    <small class="text-muted">
                                        {{ __('company.current_balance') }} £<span id="balance_hint"></span>
                                    </small>
                                    <div class="invalid-feedback"></div>
                                </div>

                                <div class="alert alert-success" id="paid_info" style="display: none;">
                                    <i class="fa fa-check-circle"></i>
                                    {{ __('company.mark_fully_paid') }}
                                </div>

                                <div class="alert alert-warning" id="overdue_info" style="display: none;">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    {{ __('company.invoice_overdue') }}
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('company.cancel') }}</button>
                        <button type="button" class="teal-custom-btn" id="submitStatus">
                            <i class="fa fa-check me-1"></i> {{ __('company.update_status') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- DOCUMENTS MODAL --}}
        <div class="modal fade" id="documentsModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="fa fa-paperclip me-2"></i>{{ __('company.invoice_documents') }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="documentsLoading" class="text-center py-4">
                            <div class="spinner-border text-info"></div>
                            <p class="mt-2 text-muted">{{ __('company.loading_documents') }}</p>
                        </div>

                        <div id="documentsList" style="display: none;">
                            <div class="mb-3">
                                <p class="mb-1"><strong>{{ __('company.invoice_no') }}:</strong> <span
                                        id="doc_modal_invoice_no"></span></p>
                                <p class="mb-0"><strong>{{ __('company.total_documents') }}</strong> <span
                                        id="doc_modal_count"></span></p>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">{{ __('company.type') }}</th>
                                            <th>{{ __('company.document_name') }}</th>
                                            <th width="100">{{ __('company.size') }}</th>
                                            <th width="120">{{ __('company.uploaded') }}</th>
                                            <th width="100" class="text-center">{{ __('company.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="documentsTableBody"></tbody>
                                </table>
                            </div>
                        </div>

                        <div id="noDocuments" style="display: none;" class="text-center py-4">
                            <i class="fa fa-file-o fa-3x text-muted mb-3"></i>
                            <p class="text-muted">{{ __('company.no_documents_attached') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('scripts')
    <script>
        // Localized messages
        const messages = {
            statusUpdated: "{{ __('company.status_updated_successfully') }}",
            failedUpdate: "{{ __('company.failed_to_update_status') }}",
            loadFailed: "{{ __('company.failed_to_load_documents') }}",
            selectStatus: "{{ __('company.select_a_status') }}",
            enterAmount: "{{ __('company.enter_payment_amount') }}",
            exceedsBalance: "{{ __('company.payment_exceeds_balance') }}",
            notOverdue: "{{ __('company.invoice_not_overdue_yet') }}",
            updating: "{{ __('company.updating') }}",
            updateStatus: "{{ __('company.update_status') }}",
            viewDownload: "{{ __('company.view_download') }}"
        };

        const isCompanyModule = {{ isset($isCompanyModule) && $isCompanyModule ? 'true' : 'false' }};
        const routePrefix = '{{ $routePrefix ?? 'invoices' }}';

        // ✅ FIX: Extract only the last segment (invoices/purchases) from route name
        const urlSegment = routePrefix.includes('.') ? routePrefix.split('.').pop() : routePrefix;
        const routeBase = isCompanyModule ? `/company/${urlSegment}` : `/${urlSegment}`;

        // ✅ CRITICAL FIX: Define downloadInvoicePDF OUTSIDE $(document).ready()
        window.downloadInvoicePDF = function(invoiceId) {
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
            btn.disabled = true;

            $.ajax({
                url: `${routeBase}/${invoiceId}/get-invoice-data`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('invoicetemplates.preview.download.pdf') }}';
                        form.style.display = 'none';

                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';
                        form.appendChild(csrfToken);

                        if (response.template_id) {
                            const templateInput = document.createElement('input');
                            templateInput.type = 'hidden';
                            templateInput.name = 'template_id';
                            templateInput.value = response.template_id;
                            form.appendChild(templateInput);
                        }

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

                        addFieldsRecursively(response.invoice_data);

                        document.body.appendChild(form);
                        form.submit();
                        document.body.removeChild(form);

                        setTimeout(() => {
                            btn.innerHTML = originalHTML;
                            btn.disabled = false;
                        }, 2000);
                    }
                },
                error: function(xhr) {
                    alert('{{ __('company.failed_to_load_invoice_data') }}');
                    btn.innerHTML = originalHTML;
                    btn.disabled = false;
                }
            });
        };

        // ✅ Helper functions also need to be outside
        function getFileIcon(fileType) {
            const icons = {
                'pdf': 'fa-file-pdf-o',
                'doc': 'fa-file-word-o',
                'docx': 'fa-file-word-o',
                'xls': 'fa-file-excel-o',
                'xlsx': 'fa-file-excel-o',
                'png': 'fa-file-image-o',
                'jpg': 'fa-file-image-o',
                'jpeg': 'fa-file-image-o',
                'gif': 'fa-file-image-o',
                'txt': 'fa-file-text-o'
            };
            return icons[fileType.toLowerCase()] || 'fa-file-o';
        }

        function getFileIconColor(fileType) {
            const colors = {
                'pdf': '#dc3545',
                'doc': '#0d6efd',
                'docx': '#0d6efd',
                'xls': '#198754',
                'xlsx': '#198754',
                'png': '#6f42c1',
                'jpg': '#6f42c1',
                'jpeg': '#6f42c1',
                'gif': '#6f42c1',
                'txt': '#6c757d'
            };
            return colors[fileType.toLowerCase()] || '#6c757d';
        }

        $(document).ready(function() {
            let currentInvoiceId = null;
            let maxBalance = 0;
            let isOverdue = false;

            $(document).on('click', '.status-change-btn', function() {
                currentInvoiceId = $(this).data('invoice-id');
                $('#statusModal').modal('show');
                loadStatusDetails(currentInvoiceId);
            });

            function loadStatusDetails(invoiceId) {
                $('#statusLoading').show();
                $('#statusForm').hide();

                $.ajax({
                    url: `${routeBase}/${invoiceId}/status-details`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            $('#modal_invoice_no').text(data.invoice_no);
                            $('#modal_customer').text(data.customer_name);
                            $('#modal_due_date').text(data.due_date);
                            $('#modal_total').text(data.total_amount);
                            $('#modal_paid').text(data.paid);
                            $('#modal_balance').text(data.balance);
                            $('#balance_hint').text(data.balance);

                            maxBalance = parseFloat(data.balance_raw);
                            isOverdue = data.is_overdue;

                            $('#current_status_badge').html(
                                `<span class="badge bg-info">${data.current_status_label}</span>`);

                            $('#new_status').empty().append(
                                `<option value="">${messages.selectStatus}</option>`);
                            $.each(data.status_options, function(value, label) {
                                if (value !== data.current_status) {
                                    $('#new_status').append(
                                        `<option value="${value}">${label}</option>`);
                                }
                            });

                            $('#statusLoading').hide();
                            $('#statusForm').show();
                        }
                    },
                    error: function(xhr) {
                        alert(messages.loadFailed);
                        $('#statusModal').modal('hide');
                    }
                });
            }

            $('#new_status').change(function() {
                const selectedStatus = $(this).val();
                $('#payment_amount_section').hide();
                $('#paid_info').hide();
                $('#overdue_info').hide();
                $('#payment_amount').prop('required', false);

                if (selectedStatus === 'partially_paid') {
                    $('#payment_amount_section').show();
                    $('#payment_amount').prop('required', true);
                    $('#payment_amount').attr('max', maxBalance);
                } else if (selectedStatus === 'paid') {
                    $('#paid_info').show();
                } else if (selectedStatus === 'overdue') {
                    if (isOverdue) {
                        $('#overdue_info').show();
                    } else {
                        alert(messages.notOverdue);
                        $(this).val('');
                    }
                }
            });

            $('#submitStatus').click(function() {
                const btn = $(this);
                const newStatus = $('#new_status').val();

                if (!newStatus) {
                    alert(messages.selectStatus);
                    return;
                }

                if (newStatus === 'partially_paid') {
                    const amount = parseFloat($('#payment_amount').val());
                    if (!amount || amount <= 0) {
                        alert(messages.enterAmount);
                        return;
                    }
                    if (amount > maxBalance) {
                        alert(messages.exceedsBalance.replace(':balance', maxBalance.toFixed(2)));
                        return;
                    }
                }

                btn.prop('disabled', true).html(
                    `<i class="fa fa-spinner fa-spin"></i> ${messages.updating}`);

                $.ajax({
                    url: `${routeBase}/${currentInvoiceId}/update-status`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: newStatus,
                        payment_amount: $('#payment_amount').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(messages.statusUpdated);
                            $('#statusModal').modal('hide');
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(function(field) {
                                $(`#${field}`).addClass('is-invalid').next(
                                    '.invalid-feedback').text(errors[field][0]);
                            });
                        } else {
                            alert(xhr.responseJSON.message || messages.failedUpdate);
                        }
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(
                            `<i class="fa fa-check me-1"></i> ${messages.updateStatus}`);
                    }
                });
            });

            $('#statusModal').on('hidden.bs.modal', function() {
                $('#statusChangeForm')[0].reset();
                $('#payment_amount_section, #paid_info, #overdue_info').hide();
                $('.is-invalid').removeClass('is-invalid');
            });

            // DOCUMENTS MODAL
            $(document).on('click', '.view-documents-btn', function() {
                const invoiceId = $(this).data('invoice-id');
                $('#documentsModal').modal('show');
                loadInvoiceDocuments(invoiceId);
            });

            function loadInvoiceDocuments(invoiceId) {
                $('#documentsLoading').show();
                $('#documentsList, #noDocuments').hide();

                $.ajax({
                    url: `${routeBase}/${invoiceId}/documents`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;
                            $('#doc_modal_invoice_no').text(data.invoice_no);
                            $('#doc_modal_count').text(data.documents.length);

                            if (data.documents.length > 0) {
                                let html = '';
                                data.documents.forEach(function(doc) {
                                    const icon = getFileIcon(doc.file_type);
                                    const iconColor = getFileIconColor(doc.file_type);
                                    html += `
                                        <tr>
                                            <td class="text-center">
                                                <i class="fa ${icon} fa-2x" style="color: ${iconColor}"></i>
                                            </td>
                                            <td>
                                                <strong>${doc.document_name}</strong><br>
                                                <small class="text-muted">${doc.file_type.toUpperCase()}</small>
                                            </td>
                                            <td>${doc.formatted_size}</td>
                                            <td><small>${doc.created_at}</small></td>
                                            <td class="text-center">
                                                <a href="${doc.document_url}" target="_blank" 
                                                class="btn btn-sm btn-primary" title="${messages.viewDownload}">
                                                    <i class="fa fa-external-link"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    `;
                                });
                                $('#documentsTableBody').html(html);
                                $('#documentsList').show();
                            } else {
                                $('#noDocuments').show();
                            }
                            $('#documentsLoading').hide();
                        }
                    },
                    error: function(xhr) {
                        alert(messages.loadFailed);
                        $('#documentsModal').modal('hide');
                    }
                });
            }
        });
    </script>
@endsection
