@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="hmrc-page-header">
            <div class="d-flex align-items-center">
                <div class="hmrc-icon-wrapper">
                    <i class="fas fa-building"></i>
                </div>
                <div>
                    <h4 class="page-title mb-1">HMRC Connection Status</h4>
                    <p class="text-muted mb-0" style="font-size: 0.875rem;">Manage your HMRC integration</p>
                </div>
            </div>
        </div>

        <div class="max-w-container mx-auto">
            @if($hasConnection && $token)
                <!-- Connection Status Card -->
                <div class="connection-status-card mb-4 border-start-success">
                    <div class="d-flex align-items-start gap-4">
                        <div class="status-icon-wrapper bg-success-light">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <h2 class="status-title text-success mb-0">Successfully Connected</h2>
                                <span class="badge bg-success">Active</span>
                            </div>
                            <p class="text-gray-600 mb-0">
                                Your Fast Ledger account is successfully connected to HMRC services. You can now submit returns and retrieve information.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Connection Details -->
                <div class="row g-4 mb-4">
                    <!-- Connected Since -->
                    <div class="col-md-6">
                        <div class="detail-card">
                            <div class="d-flex align-items-start gap-3">
                                <div class="detail-icon-wrapper bg-blue-light">
                                    <i class="fas fa-calendar text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="detail-label mb-1">Connected Since</p>
                                    <p class="detail-value mb-0">{{ $token->created_at->format('F d, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Token Expiry -->
                    <div class="col-md-6">
                        <div class="detail-card">
                            <div class="d-flex align-items-start gap-3">
                                <div class="detail-icon-wrapper bg-orange-light">
                                    <i class="fas fa-clock text-warning"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="detail-label mb-1">Token Expires At</p>
                                    <p class="detail-value mb-0">{{ $token->expires_at->format('F d, Y') }}</p>
                                    <p class="detail-hint mb-0">Token will auto-refresh before expiry</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permissions Card -->
                <div class="permissions-card mb-4">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="detail-icon-wrapper bg-purple-light">
                            <i class="fas fa-shield-alt text-purple"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="permissions-title mb-1">Permissions Granted</h3>
                            <p class="permissions-subtitle mb-0">The following permissions have been authorized for this connection</p>
                        </div>
                    </div>
                    <div class="row g-3 ms-5">
                        <div class="col-md-6">
                            <div class="permission-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Read self-assessment information</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="permission-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Submit VAT returns</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="permission-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Read tax obligations</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="permission-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Read liabilities information</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="permission-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Access Making Tax Digital services</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="permission-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Write self-assessment data</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Result Alert -->
                <div id="test-result-alert" class="d-none mb-4"></div>

                <!-- Quick Actions -->
                <div class="quick-actions-card">
                    <h3 class="quick-actions-title mb-4">Quick Actions</h3>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <button type="button" class="action-button" id="test-connection">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas fa-sync action-icon"></i>
                                    <span class="action-title">Test Connection</span>
                                </div>
                                <p class="action-description mb-0">
                                    Verify that your HMRC connection is working properly
                                </p>
                            </button>
                        </div>

                        <div class="col-md-6">
                            <a href="{{ route('hmrc.businesses.index') }}" class="action-button text-decoration-none">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas fa-eye action-icon"></i>
                                    <span class="action-title">View Businesses</span>
                                </div>
                                <p class="action-description mb-0">
                                    Access your linked HMRC businesses and their details
                                </p>
                            </a>
                        </div>
                    </div>

                    <!-- Disconnect Button -->
                    <div class="mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-outline-danger" id="disconnect-btn">
                            <i class="fas fa-unlink me-1"></i> Disconnect from HMRC
                        </button>
                        <form id="disconnect-form" action="{{ route('hmrc.auth.disconnect') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>

            @else
                <!-- Connection Status Card - Not Connected -->
                <div class="connection-status-card mb-4 border-start-danger">
                    <div class="d-flex align-items-start gap-4">
                        <div class="status-icon-wrapper bg-danger-light">
                            <i class="fas fa-times-circle text-danger"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <h2 class="status-title text-danger mb-0">Not Connected</h2>
                                <span class="badge bg-secondary">Inactive</span>
                            </div>
                            <p class="text-gray-600 mb-0">
                                Connect your account to HMRC to enable tax filing and reporting capabilities. You'll be able to submit returns and retrieve your tax information.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- What You Can Do Card -->
                <div class="permissions-card mb-4">
                    <h3 class="permissions-title mb-4">What you can do after connecting</h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="permission-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>View your business details</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="permission-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Check your tax obligations</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="permission-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Submit periodic updates</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="permission-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Submit annual summaries</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="permission-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Calculate tax liability</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="permission-item">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                <span>Submit final declarations</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Connect Action -->
                <div class="quick-actions-card text-center">
                    <a href="{{ route('hmrc.auth.connect') }}" class="btn btn-hmrc-primary btn-lg">
                        <i class="fas fa-link me-2"></i> Connect to HMRC
                    </a>
                </div>

                <!-- Security Information -->
                <div class="permissions-card mt-4">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="detail-icon-wrapper bg-success-light">
                            <i class="fas fa-shield-alt text-success"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h3 class="permissions-title mb-1">Security & Privacy</h3>
                        </div>
                    </div>
                    <ul class="security-list ms-5">
                        <li>All communication with HMRC is encrypted using industry-standard protocols</li>
                        <li>Your access tokens are securely encrypted in our database</li>
                        <li>We never see or store your HMRC Government Gateway password</li>
                        <li>You can revoke access at any time from your account settings</li>
                        <li>OAuth 2.0 secure authorization ensures your data privacy</li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Page Header */
.hmrc-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.hmrc-icon-wrapper {
    width: 48px;
    height: 48px;
    background: #e8f4f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.hmrc-icon-wrapper i {
    color: #17848e;
    font-size: 1.5rem;
}

.page-title {
    color: #13667d;
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

/* Container */
.max-w-container {
    max-width: 1024px;
}

/* Connection Status Card */
.connection-status-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    border: 1px solid #e5e7eb;
    border-left: 4px solid;
}

.border-start-success {
    border-left-color: #28a745 !important;
}

.border-start-danger {
    border-left-color: #dc3545 !important;
}

.status-icon-wrapper {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.status-icon-wrapper i {
    font-size: 2rem;
}

.bg-success-light {
    background-color: #d1e7dd;
}

.bg-danger-light {
    background-color: #f8d7da;
}

.status-title {
    font-size: 1.5rem;
    font-weight: 600;
}

.text-gray-600 {
    color: #6c757d;
    line-height: 1.6;
}

/* Detail Cards */
.detail-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    border: 1px solid #e5e7eb;
    height: 100%;
}

.detail-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.detail-icon-wrapper i {
    font-size: 1.5rem;
}

.bg-blue-light {
    background-color: #cfe2ff;
}

.bg-orange-light {
    background-color: #fff3cd;
}

.bg-purple-light {
    background-color: #e7d6f5;
}

.text-purple {
    color: #7c3aed;
}

.detail-label {
    font-size: 0.875rem;
    color: #6c757d;
}

.detail-value {
    font-size: 1rem;
    color: #212529;
    font-weight: 500;
}

.detail-hint {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

/* Permissions Card */
.permissions-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    border: 1px solid #e5e7eb;
}

.permissions-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #13667d;
}

.permissions-subtitle {
    font-size: 0.875rem;
    color: #6c757d;
}

.permission-item {
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    color: #495057;
}

.permission-item i {
    flex-shrink: 0;
}

/* Test Result Alert */
.test-alert {
    border-radius: 8px;
    padding: 1rem;
    border: 1px solid;
    border-left: 4px solid;
}

.test-alert-success {
    background-color: #d1e7dd;
    border-color: #28a745;
    border-left-color: #28a745;
}

.test-alert-error {
    background-color: #f8d7da;
    border-color: #dc3545;
    border-left-color: #dc3545;
}

.test-alert-success .alert-icon {
    color: #28a745;
}

.test-alert-error .alert-icon {
    color: #dc3545;
}

/* Quick Actions Card */
.quick-actions-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    border: 1px solid #e5e7eb;
}

.quick-actions-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #13667d;
}

.action-button {
    display: block;
    width: 100%;
    padding: 1.5rem;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    text-align: left;
    transition: all 0.2s ease;
    cursor: pointer;
}

.action-button:hover {
    border-color: #13667d;
    background-color: rgba(19, 102, 125, 0.03);
}

.action-icon {
    color: #13667d;
    font-size: 1.25rem;
}

.action-title {
    color: #13667d;
    font-weight: 600;
    font-size: 1rem;
}

.action-description {
    font-size: 0.75rem;
    color: #6c757d;
}

/* Security List */
.security-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.security-list li {
    position: relative;
    padding-left: 1.5rem;
    margin-bottom: 0.75rem;
    color: #495057;
    font-size: 0.875rem;
}

.security-list li:last-child {
    margin-bottom: 0;
}

.security-list li:before {
    content: "•";
    position: absolute;
    left: 0;
    color: #17848e;
    font-weight: 700;
}

/* Button Styles */
.btn-hmrc-primary {
    background-color: #17848e;
    border-color: #17848e;
    color: white;
}

.btn-hmrc-primary:hover {
    background-color: #13667d;
    border-color: #13667d;
    color: white;
}

/* Responsive */
@media (max-width: 767px) {
    .hmrc-icon-wrapper {
        width: 40px;
        height: 40px;
    }

    .hmrc-icon-wrapper i {
        font-size: 1.25rem;
    }

    .page-title {
        font-size: 1.25rem;
    }

    .status-icon-wrapper {
        width: 48px;
        height: 48px;
    }

    .status-icon-wrapper i {
        font-size: 1.5rem;
    }

    .status-title {
        font-size: 1.25rem;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Show toast notifications for success/error messages
@if(session('success'))
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: '{{ session("success") }}',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
    });
@endif

@if(session('error'))
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: '{{ session("error") }}',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true
    });
@endif

// Test Connection Button
document.getElementById('test-connection')?.addEventListener('click', async function() {
    const btn = this;
    const originalHtml = btn.innerHTML;
    const alertDiv = document.getElementById('test-result-alert');

    btn.disabled = true;
    btn.innerHTML = '<div class="d-flex align-items-center gap-2 mb-2"><i class="fas fa-sync fa-spin action-icon"></i><span class="action-title">Testing Connection...</span></div><p class="action-description mb-0">Verify that your HMRC connection is working properly</p>';

    // Hide previous alert
    alertDiv.classList.add('d-none');

    try {
        const response = await fetch('{{ route("hmrc.auth.test") }}');
        const data = await response.json();

        // Show inline alert
        if (data.status === 'success') {
            alertDiv.className = 'test-alert test-alert-success';
            alertDiv.innerHTML = `
                <div class="d-flex align-items-center gap-3">
                    <i class="fas fa-check-circle alert-icon" style="font-size: 1.25rem;"></i>
                    <div>
                        <p class="mb-1 fw-semibold" style="color: #155724;">Connection Test Successful</p>
                        <p class="mb-0" style="font-size: 0.875rem; color: #155724;">Your connection to HMRC is working correctly.</p>
                    </div>
                </div>
            `;
        } else {
            alertDiv.className = 'test-alert test-alert-error';
            alertDiv.innerHTML = `
                <div class="d-flex align-items-center gap-3">
                    <i class="fas fa-exclamation-circle alert-icon" style="font-size: 1.25rem;"></i>
                    <div>
                        <p class="mb-1 fw-semibold" style="color: #721c24;">Connection Test Failed</p>
                        <p class="mb-0" style="font-size: 0.875rem; color: #721c24;">Unable to reach HMRC services. Please try again.</p>
                    </div>
                </div>
            `;
        }

        // Auto-hide alert after 3 seconds
        setTimeout(() => {
            alertDiv.classList.add('d-none');
        }, 3000);

    } catch (error) {
        alertDiv.className = 'test-alert test-alert-error';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-exclamation-circle alert-icon" style="font-size: 1.25rem;"></i>
                <div>
                    <p class="mb-1 fw-semibold" style="color: #721c24;">Connection Test Failed</p>
                    <p class="mb-0" style="font-size: 0.875rem; color: #721c24;">An error occurred while testing the connection.</p>
                </div>
            </div>
        `;
        setTimeout(() => {
            alertDiv.classList.add('d-none');
        }, 3000);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
});

// Disconnect Button with SweetAlert Confirmation
document.getElementById('disconnect-btn')?.addEventListener('click', function() {
    Swal.fire({
        title: 'Disconnect from HMRC?',
        text: "You will need to reconnect to access your HMRC data again.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-unlink me-1"></i> Yes, disconnect',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('disconnect-form').submit();
        }
    });
});
</script>
@endpush
