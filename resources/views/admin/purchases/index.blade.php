@extends('admin.layout.app')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.partial.errors')

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        {{-- ✅ CHANGED: Use dynamic type name --}}
                        <span class="page-title">{{ $typeName ?? 'Purchases' }}s </span>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <div class="d-flex flex-wrap gap-2">
                                {{-- ✅ CHANGED: Use dynamic route prefix --}}
                                <a href="{{ route(($routePrefix ?? 'invoices') . '.index', ['tab' => 'issued']) }}"
                                    class="nav-link-btn {{ $activeTab === 'issued' ? 'active' : '' }}">
                                    {{ $typeName ?? 'Invoice' }}s
                                </a>

                                <a href="{{ route(($routePrefix ?? 'invoices') . '.index', ['tab' => 'drafts']) }}"
                                    class="nav-link-btn {{ $activeTab === 'drafts' ? 'active' : '' }}">
                                    </i>Draft {{ $typeName ?? 'Invoice' }}s
                                </a>
                            </div>
                            <div>
                                {{-- ✅ CHANGED: Determine payment type based on route --}}
                                @php
                                    $paymentType =
                                        ($routePrefix ?? 'invoices') === 'purchases' ? 'purchase' : 'sales_invoice';
                                @endphp
                                <a href="{{ route('transactions.create', ['type' => 'office', 'payment_type' => $paymentType]) }}"
                                    class="btn btn-primary">
                                    <i class="fa fa-plus me-2"></i>Create New {{ $typeName ?? 'Invoice' }}
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            {{-- ✅ CHANGED: Use dynamic type name --}}
                                            <th>{{ $typeName ?? 'Invoice' }} No</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Due Date</th>
                                            <th class="text-end">Total</th>
                                            <th class="text-end">Paid</th>
                                            <th class="text-end">Balance</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Documents</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($invoices as $invoice)
                                            <tr>
                                                <td><span class="fw-bold">{{ $invoice->invoice_no ?: '-' }}</span></td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="fw-medium">{{ $invoice->customer_name }}</span>
                                                        <small class="text-muted">{{ $invoice->customer_ref }}</small>
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
                                                <td class="text-end text-success">£{{ number_format($invoice->paid, 2) }}
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
                                                            title="Click to change status">
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
                                                            title="View {{ $invoice->documents->count() }} document(s)">
                                                            <span class="badge bg-info">
                                                                <i class="fa fa-paperclip"></i>
                                                                {{ $invoice->documents->count() }}
                                                            </span>
                                                        </a>
                                                    @else
                                                        <span class="text-muted">
                                                            <i class="fa fa-file-o"></i> No docs
                                                        </span>
                                                    @endif
                                                </td>

                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        @if ($activeTab === 'drafts')
                                                            {{-- ✅ CHANGED: Use dynamic route --}}
                                                            <a href="{{ route(($routePrefix ?? 'invoices') . '.edit', $invoice->id) }}"
                                                                class="btn btn-sm btn-warning" title="Edit Draft">
                                                                <i class="fa fa-edit"></i>
                                                            </a>

                                                            <form
                                                                action="{{ route(($routePrefix ?? 'invoices') . '.destroy', $invoice->id) }}"
                                                                method="POST" class="d-inline"
                                                                onsubmit="return confirm('Delete this draft?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger"
                                                                    title="Delete">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <a href="{{ route(($routePrefix ?? 'invoices') . '.view', $invoice->id) }}"
                                                                class="btn btn-sm btn-primary"
                                                                title="View {{ $typeName ?? 'Invoice' }}">
                                                                <i class="fa fa-eye"></i>
                                                            </a>

                                                            <button class="btn btn-sm btn-secondary" title="Download PDF"
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
                                                    <p class="mb-0">No {{ $activeTab }}
                                                        {{ strtolower($typeName ?? 'invoice') }}s found</p>
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

    {{-- ✅ STATUS CHANGE MODAL --}}
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-edit me-2"></i>Change Invoice Status
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Loading State --}}
                    <div id="statusLoading" class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2 text-muted">Loading invoice details...</p>
                    </div>

                    {{-- Status Form --}}
                    <div id="statusForm" style="display: none;">
                        {{-- Invoice Summary --}}
                        <div class="alert alert-info">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Invoice #:</strong> <span id="modal_invoice_no"></span></p>
                                    <p class="mb-1"><strong>Customer:</strong> <span id="modal_customer"></span></p>
                                    <p class="mb-0"><strong>Due Date:</strong> <span id="modal_due_date"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1">Total: <strong>£<span id="modal_total"></span></strong></p>
                                    <p class="mb-1">Paid: <strong class="text-success">£<span
                                                id="modal_paid"></span></strong></p>
                                    <p class="mb-0">Balance: <strong class="text-danger">£<span
                                                id="modal_balance"></span></strong></p>
                                </div>
                            </div>
                        </div>

                        {{-- Current Status --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Current Status:</label>
                            <span id="current_status_badge"></span>
                        </div>

                        {{-- Status Change Form --}}
                        <form id="statusChangeForm">
                            <div class="mb-3">
                                <label class="form-label">New Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="new_status" required>
                                    <option value="">Select Status</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- ✅ Payment Amount (Only for Partially Paid) --}}
                            <div class="mb-3" id="payment_amount_section" style="display: none;">
                                <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">£</span>
                                    <input type="number" class="form-control" id="payment_amount" step="0.01"
                                        min="0.01">
                                </div>
                                <small class="text-muted">
                                    Current Balance: £<span id="balance_hint"></span>
                                </small>
                                <div class="invalid-feedback"></div>
                            </div>

                            {{-- ✅ Info Messages --}}
                            <div class="alert alert-success" id="paid_info" style="display: none;">
                                <i class="fa fa-check-circle"></i>
                                This will mark the invoice as <strong>Fully Paid</strong>.
                                Balance will become £0.00
                            </div>

                            <div class="alert alert-warning" id="overdue_info" style="display: none;">
                                <i class="fa fa-exclamation-triangle"></i>
                                This invoice is overdue (Due date has passed)
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitStatus">
                        <i class="fa fa-check me-1"></i> Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ DOCUMENTS MODAL --}}
    <div class="modal fade" id="documentsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-paperclip me-2"></i>Invoice Documents
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Loading State --}}
                    <div id="documentsLoading" class="text-center py-4">
                        <div class="spinner-border text-info"></div>
                        <p class="mt-2 text-muted">Loading documents...</p>
                    </div>

                    {{-- Documents List --}}
                    <div id="documentsList" style="display: none;">
                        <div class="mb-3">
                            <p class="mb-1"><strong>Invoice #:</strong> <span id="doc_modal_invoice_no"></span></p>
                            <p class="mb-0"><strong>Total Documents:</strong> <span id="doc_modal_count"></span></p>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">Type</th>
                                        <th>Document Name</th>
                                        <th width="100">Size</th>
                                        <th width="120">Uploaded</th>
                                        <th width="100" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="documentsTableBody"></tbody>
                            </table>
                        </div>
                    </div>

                    {{-- No Documents --}}
                    <div id="noDocuments" style="display: none;" class="text-center py-4">
                        <i class="fa fa-file-o fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No documents attached to this invoice</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                let currentInvoiceId = null;
                let maxBalance = 0;
                let isOverdue = false;

                // ✅ Open modal when clicking status badge
                $(document).on('click', '.status-change-btn', function() {
                    currentInvoiceId = $(this).data('invoice-id');
                    $('#statusModal').modal('show');
                    loadStatusDetails(currentInvoiceId);
                });

                // ✅ Load invoice status details
                function loadStatusDetails(invoiceId) {
                    $('#statusLoading').show();
                    $('#statusForm').hide();

                    $.ajax({
                        url: `/invoices/${invoiceId}/status-details`,
                        method: 'GET',
                        success: function(response) {
                            if (response.success) {
                                const data = response.data;

                                // Fill invoice details
                                $('#modal_invoice_no').text(data.invoice_no);
                                $('#modal_customer').text(data.customer_name);
                                $('#modal_due_date').text(data.due_date);
                                $('#modal_total').text(data.total_amount);
                                $('#modal_paid').text(data.paid);
                                $('#modal_balance').text(data.balance);
                                $('#balance_hint').text(data.balance);

                                maxBalance = parseFloat(data.balance_raw);
                                isOverdue = data.is_overdue;

                                // Show current status
                                $('#current_status_badge').html(
                                    `<span class="badge bg-info">${data.current_status_label}</span>`);

                                // Fill status dropdown
                                $('#new_status').empty().append('<option value="">Select Status</option>');
                                $.each(data.status_options, function(value, label) {
                                    // Don't show current status as an option
                                    if (value !== data.current_status) {
                                        $('#new_status').append(
                                            `<option value="${value}">${label}</option>`);
                                    }
                                });

                                $('#statusLoading').hide();
                                $('#statusForm').show();
                            }
                        },
                        error: function() {
                            alert('Failed to load invoice details');
                            $('#statusModal').modal('hide');
                        }
                    });
                }

                // ✅ Handle status change
                $('#new_status').change(function() {
                    const selectedStatus = $(this).val();

                    // Hide all conditional sections
                    $('#payment_amount_section').hide();
                    $('#paid_info').hide();
                    $('#overdue_info').hide();
                    $('#payment_amount').prop('required', false);

                    // Show relevant sections based on status
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
                            alert('This invoice is not overdue yet. Due date has not passed.');
                            $(this).val('');
                        }
                    }
                });

                // ✅ Submit status change
                $('#submitStatus').click(function() {
                    const btn = $(this);
                    const newStatus = $('#new_status').val();

                    if (!newStatus) {
                        alert('Please select a status');
                        return;
                    }

                    // Validate payment amount for partial payment
                    if (newStatus === 'partially_paid') {
                        const amount = parseFloat($('#payment_amount').val());
                        if (!amount || amount <= 0) {
                            alert('Please enter a valid payment amount');
                            return;
                        }
                        if (amount > maxBalance) {
                            alert(`Payment amount cannot exceed balance of £${maxBalance.toFixed(2)}`);
                            return;
                        }
                    }

                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

                    // Clear previous errors
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').text('');

                    $.ajax({
                        url: `/invoices/${currentInvoiceId}/update-status`,
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: newStatus,
                            payment_amount: $('#payment_amount').val()
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('Status updated successfully!');
                                $('#statusModal').modal('hide');
                                location.reload(); // Reload to show updated values
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                // Validation errors
                                const errors = xhr.responseJSON.errors;
                                Object.keys(errors).forEach(function(field) {
                                    const input = $(`#${field}`);
                                    input.addClass('is-invalid');
                                    input.next('.invalid-feedback').text(errors[field][0]);
                                });
                            } else {
                                alert(xhr.responseJSON.message || 'Failed to update status');
                            }
                        },
                        complete: function() {
                            btn.prop('disabled', false).html(
                                '<i class="fa fa-check me-1"></i> Update Status');
                        }
                    });
                });

                // Reset form when modal closes
                $('#statusModal').on('hidden.bs.modal', function() {
                    $('#statusChangeForm')[0].reset();
                    $('#payment_amount_section').hide();
                    $('#paid_info').hide();
                    $('#overdue_info').hide();
                    $('.is-invalid').removeClass('is-invalid');
                });
            });

            // ========================================
            // ✅ DOCUMENTS MODAL FUNCTIONALITY
            // ========================================
            $(document).on('click', '.view-documents-btn', function() {
                const invoiceId = $(this).data('invoice-id');
                $('#documentsModal').modal('show');
                loadInvoiceDocuments(invoiceId);
            });

            function loadInvoiceDocuments(invoiceId) {
                $('#documentsLoading').show();
                $('#documentsList').hide();
                $('#noDocuments').hide();

                $.ajax({
                    url: `/invoices/${invoiceId}/documents`,
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
                                    <strong>${doc.document_name}</strong>
                                    <br>
                                    <small class="text-muted">${doc.file_type.toUpperCase()}</small>
                                </td>
                                <td>${doc.formatted_size}</td>
                                <td>
                                    <small>${doc.created_at}</small>
                                </td>
                                <td class="text-center">
                                    <a href="${doc.document_url}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-primary me-1" 
                                       title="View/Download">
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
                    error: function() {
                        alert('Failed to load documents');
                        $('#documentsModal').modal('hide');
                    }
                });
            }

            // ✅ Get file icon based on type
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

            // ✅ Get file icon color based on type
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

            window.downloadInvoicePDF = function(invoiceId) {
                const btn = event.target.closest('button');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
                btn.disabled = true;

                // ✅ Detect route prefix (purchases vs invoices)
                const routePrefix = '{{ $routePrefix ?? 'invoices' }}';

                $.ajax({
                    url: `/${routePrefix}/${invoiceId}/get-invoice-data`,
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

                                        if (typeof value === 'object' && value !== null && !Array.isArray(
                                            value)) {
                                            addFieldsRecursively(value, fieldName);
                                        } else if (Array.isArray(value)) {
                                            value.forEach((item, index) => {
                                                if (typeof item === 'object' && item !== null) {
                                                    addFieldsRecursively(item,
                                                    `${fieldName}[${index}]`);
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
                        console.error('PDF download error:', xhr);
                        alert('Failed to load invoice data');
                        btn.innerHTML = originalHTML;
                        btn.disabled = false;
                    }
                });
            };
        </script>
    @endpush
@endsection
