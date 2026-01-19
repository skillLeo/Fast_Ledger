@extends('admin.layout.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/transactions/transaction-form.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/transactions/product-dropdown.css') }}">

    <style>
        .custom-border {
            border: 1px solid #000 !important;
        }

        .read-only-input {
            background-color: #f8f9fa !important;
            cursor: default !important;
            border: 1px solid #000 !important;
            pointer-events: none;
        }

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

        .view-mode-badge {
            background: #17a2b8;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-weight: 600;
        }

        .btn.addbutton {
            transition: all 0.3s ease;
        }

        .btn.addbutton:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

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
                                    {{ __('company.view_invoice') }}
                                </h4>
                            </div>

                            <div class="d-flex gap-2">
                                <span class="view-mode-badge">
                                    <i class="fas fa-eye me-1"></i>{{ __('company.read_only') }}
                                </span>

                                @if (isset($invoice) && method_exists($invoice, 'getAttribute'))
                                    @php
                                        $status = $invoice->status ?? ($invoiceData['status'] ?? null);
                                    @endphp

                                    @if ($status === 'draft')
                                        <a href="{{ route(($routePrefix ?? 'invoices') . '.edit', $invoice->id) }}"
                                            class="btn btn-warning">
                                            <i class="fas fa-edit me-1"></i>{{ __('company.edit') }}
                                        </a>
                                    @endif
                                @endif

                                <a href="{{ url()->previous() }}" class="btn teal-custom">
                                    <i class="fas fa-arrow-left me-1"></i>{{ __('company.back') }}
                                </a>
                            </div>
                        </div>

                        <div class="payment-type-selection d-flex justify-content-between align-items-center">
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                @php
                                    $paymentType = $invoiceData['current_payment_type'] ?? 'sales_invoice';
                                    $paymentTypeLabels = [
                                        'sales_invoice' => __('company.sales_invoice'),
                                        'sales_credit' => __('company.sales_credit'),
                                        'purchase' => __('company.purchase'),
                                        'purchase_credit' => __('company.purchase_credit'),
                                        'journal' => __('company.journal'),
                                    ];
                                @endphp

                                <button type="button" class="btn-simple active" disabled style="cursor: default;">
                                    {{ $paymentTypeLabels[$paymentType] ?? __('company.sales_invoice') }}
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="background-light">

                                @include('admin.invoices._partials._view-header', [
                                    'invoiceData' => $invoiceData,
                                ])

                                @include('admin.invoices._partials._view-items-table', [
                                    'items' => $invoiceData['items'] ?? [],
                                    'invoiceData' => $invoiceData,
                                ])

                                {{-- Notes Section --}}
                                @php
                                    // Decode notes if it's a JSON string
                                        $decodedNotes = $invoiceData['notes'] ?? null;
                                    if (is_string($decodedNotes)) {
                                        $decodedNotes = json_decode($decodedNotes, true);
                                    }
                                @endphp

                                @if (!empty($decodedNotes))
                                    @include('admin.invoices._partials._view-notes', [
                                        'notes' => $decodedNotes,
                                    ])
                                @endif

                                {{-- Activity Log Button --}}
                                <div class="mb-4">
                                    <h6><strong>{{ __('company.activity_history') }}</strong></h6>

                                    <button class="btn addbutton" id="viewActivityLogBtn" type="button"
                                        onclick="toggleActivityLogInline({{ $invoice->invoice_id ?? 0 }})">
                                        <span>
                                            <i class="fas fa-history"></i> {{ __('company.view_activity_log') }}
                                        </span>
                                    </button>

                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        {{ __('company.track_all_changes') }}
                                    </small>

                                    <div id="activityLogSection" style="display: none;">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-history me-2"></i>{{ __('company.activity_log') }}
                                            </h6>
                                            <button type="button" class="btn btn-sm btn-light"
                                                onclick="hideActivityLogInline()">
                                                <i class="fas fa-times"></i> {{ __('company.hide') }}
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div id="activityLogContent">
                                                <div class="text-center py-4">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">{{ __('company.loading') }}</span>
                                                    </div>
                                                    <p class="mt-2 text-muted">{{ __('company.loading_activity_log') }}</p>
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
                    <h5 class="modal-title">{{ __('company.product_image') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="{{ __('company.product_label') }}" class="img-fluid"
                        style="max-height: 500px;">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Localized messages for JavaScript
        const messages = {
            noActivityLog: "{{ __('company.no_activity_log_yet') }}",
            changes: "{{ __('company.changes') }}",
            justNow: "{{ __('company.just_now') }}",
            minutesAgo: "{{ __('company.minutes_ago') }}",
            hoursAgo: "{{ __('company.hours_ago') }}",
            daysAgo: "{{ __('company.days_ago') }}",
            system: "{{ __('company.system') }}",
            unknownDevice: "{{ __('company.unknown_device') }}",
            created: "{{ __('company.created') }}",
            edited: "{{ __('company.edited') }}",
            issued: "{{ __('company.issued') }}",
            statusUpdated: "{{ __('company.status_updated') }}",
            sent: "{{ __('company.sent') }}",
            cancelled: "{{ __('company.cancelled') }}",
            viewed: "{{ __('company.viewed') }}",
            paymentRecorded: "{{ __('company.payment_recorded') }}"
        };

        const routePrefix = '{{ $routePrefix ?? 'invoices' }}';

        class ActivityLogManager {
            constructor() {
                this.currentInvoiceId = null;
                this.activityLogSection = document.getElementById('activityLogSection');
                this.activityLogContent = document.getElementById('activityLogContent');
            }

            async showActivityLog(invoiceId) {
                if (!invoiceId) {
                    console.error('❌ Invoice ID is required');
                    return;
                }

                this.currentInvoiceId = invoiceId;
                this.activityLogSection.style.display = 'block';
                await this.loadActivityLog(invoiceId);
            }

            async loadActivityLog(invoiceId) {
                try {
                    this.showLoading();

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

            renderActivityLog(activities) {
                if (!activities || activities.length === 0) {
                    this.activityLogContent.innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">${messages.noActivityLog}</p>
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
                const item = document.createElement('div');
                item.className = 'activity-item d-flex mb-3';

                const metadata = this.getActionMetadata(activity.action);

                item.innerHTML = `
                    <div class="activity-icon-wrapper">
                        <div class="activity-icon bg-${metadata.color} text-white">
                            <i class="fas ${metadata.icon}"></i>
                        </div>
                        <div class="activity-line"></div>
                    </div>
                    <div class="activity-content flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong class="text-${metadata.color}">${metadata.label}</strong>
                                
                                <div class="mt-2">
                                    <div class="text-muted small">
                                        <i class="fas fa-user me-1"></i>
                                        <strong>${activity.user_name || messages.system}</strong>
                                        ${activity.user_email ? `<span class="ms-2 text-primary">${activity.user_email}</span>` : ''}
                                    </div>
                                    
                                    ${activity.ip_address ? `
                                                <div class="text-muted small mt-1">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    IP: <code>${activity.ip_address}</code>
                                                </div>
                                            ` : ''}
                                    
                                    ${activity.user_agent ? `
                                                <div class="text-muted small mt-1">
                                                    <i class="fas fa-desktop me-1"></i>
                                                    ${this.getUserAgentInfo(activity.user_agent)}
                                                </div>
                                            ` : ''}
                                </div>
                            </div>
                            <small class="text-muted">${this.formatTime(activity.created_at)}</small>
                        </div>
                        
                        ${activity.notes ? `
                                    <div class="alert alert-info py-2 px-3 mb-2 small">
                                        <i class="fas fa-sticky-note me-1"></i>
                                        ${activity.notes}
                                    </div>
                                ` : ''}
                        
                        ${this.renderChanges(activity)}
                    </div>
                `;

                return item;
            }

            getUserAgentInfo(userAgent) {
                if (!userAgent) return messages.unknownDevice;

                let browser = 'Unknown';
                let os = 'Unknown';

                if (userAgent.includes('Chrome')) browser = 'Chrome';
                else if (userAgent.includes('Firefox')) browser = 'Firefox';
                else if (userAgent.includes('Safari')) browser = 'Safari';
                else if (userAgent.includes('Edge')) browser = 'Edge';

                if (userAgent.includes('Windows')) os = 'Windows';
                else if (userAgent.includes('Mac')) os = 'macOS';
                else if (userAgent.includes('Linux')) os = 'Linux';
                else if (userAgent.includes('Android')) os = 'Android';
                else if (userAgent.includes('iOS')) os = 'iOS';

                return `${browser} on ${os}`;
            }

            getActionMetadata(action) {
                const metadata = {
                    'created': {
                        color: 'success',
                        icon: 'fa-plus',
                        label: messages.created
                    },
                    'edited': {
                        color: 'info',
                        icon: 'fa-edit',
                        label: messages.edited
                    },
                    'issued': {
                        color: 'primary',
                        icon: 'fa-check',
                        label: messages.issued
                    },
                    'status_updated': {
                        color: 'warning',
                        icon: 'fa-sync',
                        label: messages.statusUpdated
                    },
                    'sent': {
                        color: 'info',
                        icon: 'fa-envelope',
                        label: messages.sent
                    },
                    'cancelled': {
                        color: 'danger',
                        icon: 'fa-ban',
                        label: messages.cancelled
                    },
                    'viewed': {
                        color: 'secondary',
                        icon: 'fa-eye',
                        label: messages.viewed
                    },
                    'payment_recorded': {
                        color: 'success',
                        icon: 'fa-money-bill',
                        label: messages.paymentRecorded
                    },
                };

                return metadata[action] || {
                    color: 'secondary',
                    icon: 'fa-circle',
                    label: action.charAt(0).toUpperCase() + action.slice(1).replace(/_/g, ' ')
                };
            }

            renderChanges(activity) {
                if (!activity.old_values || !activity.new_values) {
                    return '';
                }

                const changes = [];
                let oldValues, newValues;

                try {
                    oldValues = typeof activity.old_values === 'string' ?
                        JSON.parse(activity.old_values) : activity.old_values;
                    newValues = typeof activity.new_values === 'string' ?
                        JSON.parse(activity.new_values) : activity.new_values;
                } catch (e) {
                    console.error('Error parsing values:', e);
                    return '';
                }

                for (const key in newValues) {
                    if (oldValues[key] != newValues[key]) {
                        const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        changes.push(`
                            <li>
                                <strong>${label}:</strong> 
                                <span class="text-danger">${oldValues[key] || 'N/A'}</span> 
                                → 
                                <span class="text-success">${newValues[key]}</span>
                            </li>
                        `);
                    }
                }

                if (changes.length === 0) return '';

                return `
                    <div class="activity-changes bg-white border rounded p-2 mt-2">
                        <strong class="d-block mb-2">${messages.changes}:</strong>
                        <ul class="mb-0">${changes.join('')}</ul>
                    </div>
                `;
            }

            formatTime(timestamp) {
                const date = new Date(timestamp);
                const now = new Date();
                const diffMs = now - date;
                const diffMins = Math.floor(diffMs / 60000);
                const diffHours = Math.floor(diffMs / 3600000);
                const diffDays = Math.floor(diffMs / 86400000);

                if (diffMins < 1) return messages.justNow;
                if (diffMins < 60) return messages.minutesAgo.replace(':count', diffMins);
                if (diffHours < 24) return messages.hoursAgo.replace(':count', diffHours);
                if (diffDays < 7) return messages.daysAgo.replace(':count', diffDays);

                return date.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            showLoading() {
                this.activityLogContent.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">{{ __('company.loading') }}</span>
                        </div>
                        <p class="mt-2 text-muted">{{ __('company.loading_activity_log') }}</p>
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

    {{-- Activity Log Styles --}}
    <style>
        .activity-timeline {
            position: relative;
            padding-left: 10px;
        }

        .activity-item {
            position: relative;
            display: flex;
            margin-bottom: 20px;
        }

        .activity-icon-wrapper {
            position: relative;
            flex-shrink: 0;
            margin-right: 20px;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            position: relative;
            z-index: 2;
        }

        .activity-line {
            position: absolute;
            left: 50%;
            top: 40px;
            width: 2px;
            height: calc(100% + 20px);
            background: #e9ecef;
            transform: translateX(-50%);
            z-index: 1;
        }

        .activity-item:last-child .activity-line {
            display: none;
        }

        .activity-content {
            flex-grow: 1;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border-left: 3px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .activity-content:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateX(5px);
        }

        .activity-changes ul {
            margin: 0;
            padding-left: 20px;
        }

        .activity-changes li {
            margin-bottom: 5px;
            font-size: 13px;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .activity-item {
            animation: slideIn 0.3s ease forwards;
        }

        .activity-item:nth-child(1) {
            animation-delay: 0.05s;
        }

        .activity-item:nth-child(2) {
            animation-delay: 0.1s;
        }

        .activity-item:nth-child(3) {
            animation-delay: 0.15s;
        }

        .activity-item:nth-child(4) {
            animation-delay: 0.2s;
        }

        .activity-item:nth-child(5) {
            animation-delay: 0.25s;
        }
    </style>
@endsection
