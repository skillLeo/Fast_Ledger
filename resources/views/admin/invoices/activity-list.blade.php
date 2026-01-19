@extends('admin.layout.app')

<style>
    /* Activity Log Table Styles */
    .badge-action {
        font-size: 11px;
        padding: 4px 8px;
        font-weight: 600;
    }

    .badge-created {
        background-color: #198754;
    }

    .badge-edited {
        background-color: #0dcaf0;
    }

    .badge-issued {
        background-color: #0d6efd;
    }

    .badge-sent {
        background-color: #ffc107;
        color: #000;
    }

    .badge-cancelled {
        background-color: #dc3545;
    }

    .badge-viewed {
        background-color: #6c757d;
    }

    .badge-status_updated {
        background-color: #6610f2;
    }

    /* Status Badges */
    .badge-status {
        font-size: 11px;
        padding: 4px 8px;
        font-weight: 600;
    }

    .badge-draft {
        background-color: #6c757d;
    }

    .badge-paid {
        background-color: #198754;
    }

    .badge-partially_paid {
        background-color: #ffc107;
        color: #000;
    }

    .badge-overdue {
        background-color: #dc3545;
    }

    .changes-summary {
        font-size: 13px;
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .view-details-btn {
        font-size: 12px;
        padding: 2px 8px;
    }

    #activityLogTable tbody tr:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }

    .card.bg-primary-transparent,
    .card.bg-success-transparent,
    .card.bg-info-transparent,
    .card.bg-warning-transparent {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card.bg-primary-transparent:hover,
    .card.bg-success-transparent:hover,
    .card.bg-info-transparent:hover,
    .card.bg-warning-transparent:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
</style>

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.partial.errors')

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h1 class="page-title fw-medium fs-18 mb-0">{{ __('company.invoice_activity_history') }}</h1>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-info" id="refreshLogsBtn">
                                    <i class="fas fa-sync-alt"></i> {{ __('company.refresh') }}
                                </button>
                                <button class="teal-custom-btn" id="exportLogsBtn">
                                    <i class="fas fa-download"></i> {{ __('company.export') }}
                                </button>
                            </div>
                        </div>

                        <div class="card-body" style="padding:40px;">

                            {{-- Filters Section --}}
                            <div class="row g-3 mb-4">
                                <div class="col-md-2">
                                    <label class="form-label">{{ __('company.status') }}</label>
                                    <select class="form-select rounded-0 p-1" id="filterStatus">
                                        <option value="">{{ __('company.all_statuses') }}</option>
                                        <option value="draft">{{ __('company.draft') }}</option>
                                        <option value="sent">{{ __('company.sent') }}</option>
                                        <option value="paid">{{ __('company.paid_status') }}</option>
                                        <option value="partially_paid">{{ __('company.partially_paid') }}</option>
                                        <option value="overdue">{{ __('company.overdue') }}</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">{{ __('company.user') }}</label>
                                    <select class="form-select rounded-0 p-1" id="filterUser">
                                        <option value="">{{ __('company.all_users') }}</option>
                                        {{-- Populated dynamically --}}
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">{{ __('company.invoice_no') }}</label>
                                    <input type="text" class="form-control" id="filterInvoiceNo"
                                        placeholder="e.g., SIN000036">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">{{ __('company.date_from') }}</label>
                                    <input type="date" class="form-control" id="filterDateFrom">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">{{ __('company.date_to') }}</label>
                                    <input type="date" class="form-control" id="filterDateTo">
                                </div>

                                <div class="col-md-2 d-flex align-items-end gap-2">
                                    <button type="button" class="teal-custom-btn p-1 w-100 mb-1" onclick="applyFilters()">
                                        <i class="fas fa-filter me-1"></i>{{ __('company.filter') }}
                                    </button>
                                    <button type="button" class="teal-custom-btn mb-1" style="padding:7px"
                                        onclick="clearFilters()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Statistics Cards --}}
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card bg-primary-transparent">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="avatar avatar-md bg-primary">
                                                        <i class="fas fa-list"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-fill">
                                                    <h6 class="mb-1">{{ __('company.total_activities') }}</h6>
                                                    <h4 class="mb-0" id="statTotal">0</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card bg-success-transparent">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="avatar avatar-md bg-success">
                                                        <i class="fas fa-file-invoice"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-fill">
                                                    <h6 class="mb-1">{{ __('company.unique_invoices') }}</h6>
                                                    <h4 class="mb-0" id="statInvoices">0</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card bg-info-transparent">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="avatar avatar-md bg-info">
                                                        <i class="fas fa-users"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-fill">
                                                    <h6 class="mb-1">{{ __('company.active_users') }}</h6>
                                                    <h4 class="mb-0" id="statUsers">0</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card bg-warning-transparent">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <span class="avatar avatar-md bg-warning">
                                                        <i class="fas fa-clock"></i>
                                                    </span>
                                                </div>
                                                <div class="flex-fill">
                                                    <h6 class="mb-1">{{ __('company.today_activities') }}</h6>
                                                    <h4 class="mb-0" id="statToday">0</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Activity Log Table --}}
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover text-nowrap" id="activityLogTable">
                                    <thead class="table-primary">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="10%">{{ __('company.action') }}</th>
                                            <th width="12%">{{ __('company.invoice_no') }}</th>
                                            <th width="10%">{{ __('company.status') }}</th>
                                            <th width="13%">{{ __('company.user') }}</th>
                                            <th width="20%">{{ __('company.changes') }}</th>
                                            <th width="10%">{{ __('company.ip_address') }}</th>
                                            <th width="8%">{{ __('company.browser') }}</th>
                                            <th width="12%">{{ __('company.date_time') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="activityLogTableBody">
                                        <tr>
                                            <td colspan="9" class="text-center py-5">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">{{ __('company.loading') }}</span>
                                                </div>
                                                <p class="mt-2 text-muted">{{ __('company.loading_activity_log') }}</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <span class="text-muted">{{ __('company.showing') }} <strong id="showingFrom">0</strong> {{ __('company.to') }}
                                        <strong id="showingTo">0</strong> {{ __('company.of') }} <strong id="totalRecords">0</strong>
                                        {{ __('company.activities') }}</span>
                                </div>
                                <nav>
                                    <ul class="pagination mb-0" id="pagination">
                                        {{-- Pagination buttons generated dynamically --}}
                                    </ul>
                                </nav>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

            {{-- Activity Details Modal --}}
            <div class="modal fade" id="activityDetailsModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-info-circle me-2"></i>{{ __('company.activity_details') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="activityDetailsContent">
                            {{-- Populated dynamically --}}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('company.close') }}</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Localized messages for JavaScript
        const messages = {
            noActivityLogs: "{{ __('company.no_activity_logs_found') }}",
            noChanges: "{{ __('company.no_changes') }}",
            system: "{{ __('company.system') }}",
            created: "{{ __('company.created') }}",
            edited: "{{ __('company.edited') }}",
            issued: "{{ __('company.issued') }}",
            statusUpdated: "{{ __('company.status_updated') }}",
            sent: "{{ __('company.sent') }}",
            cancelled: "{{ __('company.cancelled') }}",
            viewed: "{{ __('company.viewed') }}",
            paymentRecorded: "{{ __('company.payment_recorded') }}",
            draft: "{{ __('company.draft') }}",
            paid: "{{ __('company.paid_status') }}",
            partiallyPaid: "{{ __('company.partially_paid') }}",
            overdue: "{{ __('company.overdue') }}",
            loading: "{{ __('company.loading') }}",
            action: "{{ __('company.action') }}",
            invoiceNo: "{{ __('company.invoice_no') }}",
            status: "{{ __('company.status') }}",
            user: "{{ __('company.user') }}",
            ipAddress: "{{ __('company.ip_address') }}",
            browser: "{{ __('company.browser') }}",
            dateTime: "{{ __('company.date_time') }}",
            notes: "{{ __('company.notes') }}",
            changes: "{{ __('company.changes') }}",
            allUsers: "{{ __('company.all_users') }}",
            previous: "{{ __('company.previous') }}",
            next: "{{ __('company.next') }}"
        };

        const isCompanyModule = {{ $isCompanyModule ? 'true' : 'false' }};
        const routePrefix = isCompanyModule ? '/company/invoices' : '/invoices';

        function buildUrl(path) {
            return routePrefix + path;
        }

        let allActivities = [];
        let filteredActivities = [];
        let currentPage = 1;
        let perPage = 25;

        document.addEventListener('DOMContentLoaded', function() {
            console.log('📋 Activity Log Index Page Initialized');
            loadAllActivityLogs();

            document.getElementById('refreshLogsBtn')?.addEventListener('click', function() {
                loadAllActivityLogs();
            });

            document.getElementById('exportLogsBtn')?.addEventListener('click', function() {
                exportActivityLogs();
            });
        });

        async function loadAllActivityLogs() {
            try {
                showTableLoading();

                const response = await fetch(buildUrl('/all-activity-logs'));

                if (!response.ok) {
                    throw new Error('Failed to load activity logs');
                }

                const data = await response.json();

                if (data.success) {
                    console.log('✅ Loaded', data.activities.length, 'activity logs');
                    allActivities = data.activities;
                    filteredActivities = data.activities;

                    updateStatistics();
                    populateUserFilter();
                    renderTable();
                } else {
                    showTableError(data.message || 'Failed to load activity logs');
                }

            } catch (error) {
                console.error('❌ Error loading activity logs:', error);
                showTableError('Failed to load activity logs. Please try again.');
            }
        }

        function renderTable() {
            const tbody = document.getElementById('activityLogTableBody');

            if (!filteredActivities || filteredActivities.length === 0) {
                tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">${messages.noActivityLogs}</p>
                </td>
            </tr>
        `;
                updatePaginationInfo(0, 0, 0);
                return;
            }

            const start = (currentPage - 1) * perPage;
            const end = Math.min(start + perPage, filteredActivities.length);
            const pageActivities = filteredActivities.slice(start, end);

            tbody.innerHTML = pageActivities.map((activity, index) => {
                const actualIndex = start + index + 1;
                return createTableRow(activity, actualIndex);
            }).join('');

            updatePaginationInfo(start + 1, end, filteredActivities.length);
            renderPagination();
        }

        function createTableRow(activity, index) {
            const actionBadge = getActionBadge(activity.action);
            const statusBadge = getStatusBadge(activity.invoice_status);
            const changesSummary = getChangesSummary(activity);
            const userAgent = formatUserAgent(activity.user_agent);
            const formattedDate = formatDateTime(activity.created_at);

            return `
        <tr onclick="showActivityDetails(${activity.id})" style="cursor: pointer;">
            <td>${index}</td>
            <td>${actionBadge}</td>
            <td>
                <span class="badge bg-secondary">
                    <i class="fas fa-file-invoice me-1"></i>${activity.invoice_no || 'N/A'}
                </span>
            </td>
            <td>${statusBadge}</td>
            <td>
                <div>
                    <strong>${activity.user_name || messages.system}</strong>
                    ${activity.user_email ? `<br><small class="text-muted">${activity.user_email}</small>` : ''}
                </div>
            </td>
            <td>
                <div class="changes-summary" title="${changesSummary}">
                    ${changesSummary || `<em class="text-muted">${messages.noChanges}</em>`}
                </div>
            </td>
            <td>
                <small>${activity.ip_address || 'N/A'}</small>
            </td>
            <td>
                <small>${userAgent}</small>
            </td>
            <td>
                <small>${formattedDate}</small>
            </td>
        </tr>
    `;
        }

        function getActionBadge(action) {
            const badges = {
                'created': `<span class="badge badge-action badge-created"><i class="fas fa-plus me-1"></i>${messages.created}</span>`,
                'edited': `<span class="badge badge-action badge-edited"><i class="fas fa-edit me-1"></i>${messages.edited}</span>`,
                'issued': `<span class="badge badge-action badge-issued"><i class="fas fa-check me-1"></i>${messages.issued}</span>`,
                'sent': `<span class="badge badge-action badge-sent"><i class="fas fa-envelope me-1"></i>${messages.sent}</span>`,
                'cancelled': `<span class="badge badge-action badge-cancelled"><i class="fas fa-ban me-1"></i>${messages.cancelled}</span>`,
                'viewed': `<span class="badge badge-action badge-viewed"><i class="fas fa-eye me-1"></i>${messages.viewed}</span>`,
                'status_updated': `<span class="badge badge-action badge-status_updated"><i class="fas fa-sync me-1"></i>${messages.statusUpdated}</span>`,
            };

            return badges[action] || `<span class="badge bg-secondary">${action}</span>`;
        }

        function getStatusBadge(status) {
            if (!status) return '<span class="badge bg-secondary">N/A</span>';

            const badges = {
                'draft': `<span class="badge badge-status badge-draft"><i class="fas fa-file me-1"></i>${messages.draft}</span>`,
                'sent': `<span class="badge badge-status badge-sent"><i class="fas fa-paper-plane me-1"></i>${messages.sent}</span>`,
                'paid': `<span class="badge badge-status badge-paid"><i class="fas fa-check-circle me-1"></i>${messages.paid}</span>`,
                'partially_paid': `<span class="badge badge-status badge-partially_paid"><i class="fas fa-coins me-1"></i>${messages.partiallyPaid}</span>`,
                'overdue': `<span class="badge badge-status badge-overdue"><i class="fas fa-exclamation-triangle me-1"></i>${messages.overdue}</span>`,
            };

            return badges[status] || `<span class="badge bg-secondary">${status}</span>`;
        }

        function getChangesSummary(activity) {
            if (!activity.old_values || !activity.new_values) {
                return activity.notes || '';
            }

            const oldValues = typeof activity.old_values === 'string' ?
                JSON.parse(activity.old_values) :
                activity.old_values;

            const newValues = typeof activity.new_values === 'string' ?
                JSON.parse(activity.new_values) :
                activity.new_values;

            const changes = [];
            for (const key in newValues) {
                if (oldValues[key] != newValues[key]) {
                    changes.push(`${key}: ${oldValues[key]} → ${newValues[key]}`);
                }
            }

            return changes.length > 0 ? changes.join(', ') : (activity.notes || '');
        }

        function formatUserAgent(userAgent) {
            if (!userAgent) return 'Unknown';

            if (userAgent.includes('Chrome')) return 'Chrome';
            if (userAgent.includes('Firefox')) return 'Firefox';
            if (userAgent.includes('Safari')) return 'Safari';
            if (userAgent.includes('Edge')) return 'Edge';

            return 'Browser';
        }

        function formatDateTime(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function applyFilters() {
            const status = document.getElementById('filterStatus').value;
            const userId = document.getElementById('filterUser').value;
            const invoiceNo = document.getElementById('filterInvoiceNo').value.toLowerCase();
            const dateFrom = document.getElementById('filterDateFrom').value;
            const dateTo = document.getElementById('filterDateTo').value;

            filteredActivities = allActivities.filter(activity => {
                const matchStatus = !status || activity.invoice_status === status;
                const matchUser = !userId || activity.user_id == userId;
                const matchInvoice = !invoiceNo ||
                    (activity.invoice_no && activity.invoice_no.toLowerCase().includes(invoiceNo));

                let matchDate = true;
                if (dateFrom || dateTo) {
                    const activityDate = new Date(activity.created_at).toISOString().split('T')[0];
                    if (dateFrom) matchDate = matchDate && activityDate >= dateFrom;
                    if (dateTo) matchDate = matchDate && activityDate <= dateTo;
                }

                return matchStatus && matchUser && matchInvoice && matchDate;
            });

            currentPage = 1;
            updateStatistics();
            renderTable();
        }

        function clearFilters() {
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterUser').value = '';
            document.getElementById('filterInvoiceNo').value = '';
            document.getElementById('filterDateFrom').value = '';
            document.getElementById('filterDateTo').value = '';

            filteredActivities = allActivities;
            currentPage = 1;
            updateStatistics();
            renderTable();
        }

        function populateUserFilter() {
            const filterUser = document.getElementById('filterUser');

            const users = [...new Set(allActivities
                .filter(a => a.user_name)
                .map(a => JSON.stringify({
                    id: a.user_id,
                    name: a.user_name
                }))
            )].map(s => JSON.parse(s));

            filterUser.innerHTML = `<option value="">${messages.allUsers}</option>`;
            users.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = user.name;
                filterUser.appendChild(option);
            });
        }

        function updateStatistics() {
            document.getElementById('statTotal').textContent = filteredActivities.length;

            const uniqueInvoices = [...new Set(filteredActivities.map(a => a.invoice_id))].length;
            document.getElementById('statInvoices').textContent = uniqueInvoices;

            const uniqueUsers = [...new Set(filteredActivities.map(a => a.user_id))].length;
            document.getElementById('statUsers').textContent = uniqueUsers;

            const today = new Date().toISOString().split('T')[0];
            const todayActivities = filteredActivities.filter(a =>
                a.created_at.startsWith(today)
            ).length;
            document.getElementById('statToday').textContent = todayActivities;
        }

        function updatePaginationInfo(from, to, total) {
            document.getElementById('showingFrom').textContent = from;
            document.getElementById('showingTo').textContent = to;
            document.getElementById('totalRecords').textContent = total;
        }

        function renderPagination() {
            const totalPages = Math.ceil(filteredActivities.length / perPage);
            const pagination = document.getElementById('pagination');

            if (totalPages <= 1) {
                pagination.innerHTML = '';
                return;
            }

            let html = '';

            html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1}); return false;">${messages.previous}</a>
        </li>
    `;

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>
            `;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1}); return false;">${messages.next}</a>
        </li>
    `;

            pagination.innerHTML = html;
        }

        function changePage(page) {
            const totalPages = Math.ceil(filteredActivities.length / perPage);
            if (page < 1 || page > totalPages) return;

            currentPage = page;
            renderTable();
        }

        function showActivityDetails(activityId) {
            const activity = allActivities.find(a => a.id === activityId);
            if (!activity) return;

            const modalContent = document.getElementById('activityDetailsContent');

            const oldValues = activity.old_values ?
                (typeof activity.old_values === 'string' ? JSON.parse(activity.old_values) : activity.old_values) : null;

            const newValues = activity.new_values ?
                (typeof activity.new_values === 'string' ? JSON.parse(activity.new_values) : activity.new_values) : null;

            let changesHtml = '';
            if (oldValues && newValues) {
                changesHtml = `<h6>${messages.changes}:</h6><ul class="list-group">`;
                for (const key in newValues) {
                    if (oldValues[key] != newValues[key]) {
                        changesHtml += `
                    <li class="list-group-item">
                        <strong>${key.replace(/_/g, ' ').toUpperCase()}:</strong><br>
                        <span class="text-danger">${oldValues[key] || 'N/A'}</span> 
                        <i class="fas fa-arrow-right mx-2"></i> 
                        <span class="text-success">${newValues[key]}</span>
                    </li>
                `;
                    }
                }
                changesHtml += '</ul>';
            }

            modalContent.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <p><strong>${messages.action}:</strong> ${getActionBadge(activity.action)}</p>
                <p><strong>${messages.invoiceNo}:</strong> ${activity.invoice_no || 'N/A'}</p>
                <p><strong>${messages.status}:</strong> ${getStatusBadge(activity.invoice_status)}</p>
                <p><strong>${messages.user}:</strong> ${activity.user_name || messages.system} ${activity.user_email ? `(${activity.user_email})` : ''}</p>
            </div>
            <div class="col-md-6">
                <p><strong>${messages.dateTime}:</strong> ${formatDateTime(activity.created_at)}</p>
                <p><strong>${messages.ipAddress}:</strong> ${activity.ip_address || 'N/A'}</p>
                <p><strong>${messages.browser}:</strong> ${formatUserAgent(activity.user_agent)}</p>
            </div>
        </div>
        ${activity.notes ? `<div class="alert alert-info mt-3"><strong>${messages.notes}:</strong> ${activity.notes}</div>` : ''}
        ${changesHtml ? `<div class="mt-3">${changesHtml}</div>` : ''}
    `;

            const modal = new bootstrap.Modal(document.getElementById('activityDetailsModal'));
            modal.show();
        }

        function exportActivityLogs() {
            let csv = `ID,${messages.action},${messages.invoiceNo},${messages.status},${messages.user},User Email,${messages.ipAddress},${messages.browser},${messages.dateTime},${messages.notes}\n`;

            filteredActivities.forEach(activity => {
                csv +=
                    `${activity.id},${activity.action},"${activity.invoice_no || 'N/A'}","${activity.invoice_status || 'N/A'}","${activity.user_name || messages.system}","${activity.user_email || ''}","${activity.ip_address || 'N/A'}","${formatUserAgent(activity.user_agent)}","${formatDateTime(activity.created_at)}","${activity.notes || ''}"\n`;
            });

            const blob = new Blob([csv], {
                type: 'text/csv'
            });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `activity_logs_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
        }

        function showTableLoading() {
            document.getElementById('activityLogTableBody').innerHTML = `
        <tr>
            <td colspan="9" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">${messages.loading}</span>
                </div>
                <p class="mt-2 text-muted">${messages.loading}...</p>
            </td>
        </tr>
    `;
        }

        function showTableError(message) {
            document.getElementById('activityLogTableBody').innerHTML = `
        <tr>
            <td colspan="9" class="text-center py-5">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <p class="text-danger mb-0">${message}</p>
            </td>
        </tr>
    `;
        }
    </script>
@endsection