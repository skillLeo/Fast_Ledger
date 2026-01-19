@extends('admin.layout.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/transactions/transaction-form.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/transactions/product-dropdown.css') }}">

    <style>
        .custom-border {
            border: 1px solid #000 !important;
        }

        /* Read-only field styling */
        .read-only-input {
            background-color: #f8f9fa !important;
            cursor: default !important;
            border: 1px solid #000 !important;
            pointer-events: none;
        }

        /* Product thumbnail */
        .product-thumbnail {
            max-width: 50px;
            max-height: 50px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #dee2e6;
        }

        .no-image-placeholder {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        /* Invoice table styles */
        .invoice-table {
            width: 100%;
            margin-bottom: 1rem;
        }

        .invoice-table thead th {
            background-color: #e9ecef;
            font-weight: 600;
            padding: 0.75rem;
            border: 1px solid #000;
            vertical-align: middle;
        }

        .invoice-table tbody td {
            padding: 0.5rem;
            border: 1px solid #dee2e6;
            background-color: #ffffff;
            vertical-align: middle;
        }


        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 1rem;
        }

        /* Notes display */
        .notes-history {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 1rem;
            border-radius: 0.25rem;
            min-height: 100px;
        }

        .notes-history table {
            width: 100%;
            border-collapse: collapse;
        }

        .notes-history table td,
        .notes-history table th {
            border: 1px solid #dee2e6;
            padding: 8px;
        }

        /* View mode badge */
        .view-mode-badge {
            background: #17a2b8;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-weight: 600;
        }

        /* Activity log button style */
        .btn.addbutton {
            transition: all 0.3s ease;
        }

        .btn.addbutton:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Activity log section within same card */
        #activityLogSection {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #e9ecef;
        }

        #activityLogSection .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
        }

        #activityLogSection .card-body {
            padding: 1rem;
        }
    </style>
@endpush

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">

                        {{-- Header --}}
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="page-title">
                                <h4 class="page-title">
                                    <i class="fas fa-file-invoice me-2"></i>
                                    {{-- ✅ CHANGED: Use dynamic variable --}}
                                    View {{ $typeName ?? 'Invoice' }}
                                </h4>
                            </div>

                            <div class="d-flex gap-2">
                                <span class="view-mode-badge">
                                    <i class="fas fa-eye me-1"></i>READ-ONLY
                                </span>

                                @if (isset($invoice) && method_exists($invoice, 'getAttribute'))
                                    @php
                                        $status = $invoice->status ?? ($invoiceData['status'] ?? null);
                                    @endphp

                                    @if ($status === 'draft')
                                        {{-- ✅ CHANGED: Use dynamic route prefix --}}
                                        <a href="{{ route(($routePrefix ?? 'invoices') . '.edit', $invoice->id) }}"
                                            class="btn btn-warning">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                    @endif
                                @endif

                                <a href="{{ url()->previous() }}" class="btn teal-custom">
                                    <i class="fas fa-arrow-left me-1"></i>Back
                                </a>
                            </div>
                        </div>

                        {{-- Rest of content stays the same --}}
                        <div class="payment-type-selection d-flex justify-content-between align-items-center">
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @php
                                    $paymentType = $invoiceData['current_payment_type'] ?? 'sales_invoice';
                                    $paymentTypeLabels = [
                                        'sales_invoice' => 'Sales Invoice',
                                        'sales_credit' => 'Sales Credit',
                                        'purchase' => 'Purchase',
                                        'purchase_credit' => 'Purchase Credit',
                                        'journal' => 'Journal',
                                    ];
                                @endphp

                                <button type="button" class="btn-simple active" disabled style="cursor: default;">
                                    {{ $paymentTypeLabels[$paymentType] ?? 'Sales Invoice' }}
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="background-light">

                                {{-- ✅ SHARED PARTIALS (same includes for both) --}}
                                @include('admin.invoices._partials._view-header', [
                                    'invoiceData' => $invoiceData,
                                ])

                                @include('admin.invoices._partials._view-items-table', [
                                    'items' => $invoiceData['items'] ?? [],
                                    'invoiceData' => $invoiceData,
                                ])

                                @if (!empty($invoiceData['invoice_notes']))
                                    @include('admin.invoices._partials._view-notes', [
                                        'notes' => $invoiceData['invoice_notes'],
                                    ])
                                @endif

                                {{-- Activity Log Button --}}
                                <div class="mb-4">
                                    <h6><strong>Activity History</strong></h6>

                                    <button class="btn addbutton" id="viewActivityLogBtn" type="button"
                                        onclick="toggleActivityLogInline({{ $invoice->invoice_id ?? 0 }})">
                                        <span>
                                            <i class="fas fa-history"></i> View Activity Log
                                        </span>
                                    </button>

                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        {{-- ✅ CHANGED: Use dynamic type name --}}
                                        Track all changes and actions performed on this
                                        {{ strtolower($typeName ?? 'invoice') }}
                                    </small>

                                    <div id="activityLogSection" style="display: none;">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-history me-2"></i>Activity Log
                                            </h6>
                                            <button type="button" class="btn btn-sm btn-light"
                                                onclick="hideActivityLogInline()">
                                                <i class="fas fa-times"></i> Hide
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div id="activityLogContent">
                                                <div class="text-center py-4">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <p class="mt-2 text-muted">Loading activity log...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Image Preview Modal --}}
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Product Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Product" class="img-fluid" style="max-height: 500px;">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // ✅ CHANGED: Use dynamic route prefix
        const routePrefix = '{{ $routePrefix ?? 'invoices' }}';

        class ActivityLogManager {
            constructor() {
                this.currentInvoiceId = null;
                this.activityLogSection = document.getElementById('activityLogSection');
                this.activityLogContent = document.getElementById('activityLogContent');
            }

            async showActivityLog(invoiceId) {
                if (!invoiceId) {
                    console.error('❌ ID is required');
                    return;
                }

                this.currentInvoiceId = invoiceId;
                this.activityLogSection.style.display = 'block';
                await this.loadActivityLog(invoiceId);
            }

            async loadActivityLog(invoiceId) {
                try {
                    this.showLoading();

                    // ✅ CHANGED: Use dynamic route
                    const response = await fetch(`/${routePrefix}/${invoiceId}/activity-log`);

                    if (!response.ok) {
                        throw new Error('Failed to load activity log');
                    }

                    const data = await response.json();

                    if (data.success) {
                        this.renderActivityLog(data.activities);
                    } else {
                        this.showError(data.message || 'Failed to load activity log');
                    }

                } catch (error) {
                    console.error('❌ Activity log error:', error);
                    this.showError('Failed to load activity log. Please try again.');
                }
            }

            // ✅ KEEP ALL OTHER METHODS THE SAME
            renderActivityLog(activities) {
                if (!activities || activities.length === 0) {
                    this.activityLogContent.innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No activity log yet</p>
                        </div>
                    `;
                    return;
                }

                const timeline = document.createElement('div');
                timeline.className = 'activity-timeline';

                activities.forEach(activity => {
                    const item = this.createActivityItem(activity);
                    timeline.appendChild(item);
                });

                this.activityLogContent.innerHTML = '';
                this.activityLogContent.appendChild(timeline);
            }

            createActivityItem(activity) {
                // ... KEEP YOUR EXISTING CODE
            }

            getUserAgentInfo(userAgent) {
                // ... KEEP YOUR EXISTING CODE
            }

            getActionMetadata(action) {
                // ... KEEP YOUR EXISTING CODE
            }

            renderChanges(activity) {
                // ... KEEP YOUR EXISTING CODE
            }

            formatTime(timestamp) {
                // ... KEEP YOUR EXISTING CODE
            }

            showLoading() {
                this.activityLogContent.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading activity log...</p>
                    </div>
                `;
            }

            showError(message) {
                this.activityLogContent.innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${message}
                    </div>
                `;
            }

            hide() {
                this.activityLogSection.style.display = 'none';
            }
        }

        // Initialize
        let activityLogManager;

        document.addEventListener('DOMContentLoaded', function() {
            activityLogManager = new ActivityLogManager();
        });

        function toggleActivityLogInline(invoiceId) {
            const section = document.getElementById('activityLogSection');

            if (section.style.display === 'none') {
                activityLogManager.showActivityLog(invoiceId);
            } else {
                activityLogManager.hide();
            }
        }

        function hideActivityLogInline() {
            activityLogManager.hide();
        }

        function showImagePreview(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            new bootstrap.Modal(document.getElementById('imagePreviewModal')).show();
        }
    </script>

    {{-- Keep all your existing styles --}}
@endsection
