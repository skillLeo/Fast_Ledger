@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="d-flex align-items-center mb-4">
        <div class="me-3">
            <div class="hmrc-icon-wrapper">
                <i class="fas fa-landmark"></i>
            </div>
        </div>
        <div>
            <h4 class="mb-1 page-title">Connect to HMRC</h4>
            <p class="text-muted mb-0 small">Securely connect your account to HMRC services</p>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="alert alert-info alert-modern mb-4" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <span>You'll be redirected to HMRC's secure website to authorize this connection. Make sure you have your Government Gateway credentials ready.</span>
    </div>

    <!-- Main Content Cards -->
    <div class="row mb-4">
        <!-- Left Card: OAuth Connection -->
        <div class="col-md-6 mb-4 mb-md-0">
            <div class="card hmrc-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="hmrc-card-icon me-3">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-1">Secure OAuth Connection</h5>
                            <p class="text-muted small mb-0">Enterprise-grade security</p>
                        </div>
                    </div>

                    <p class="card-text mb-4">
                        Connect your {{ config('app.name') }} account to HMRC using secure OAuth 2.0 authentication.
                        This allows us to submit returns and retrieve information on your behalf.
                    </p>

                    <a href="{{ route('hmrc.auth.redirect') }}"
                       class="btn btn-hmrc-primary w-100"
                       id="connect-btn">
                        Connect to HMRC
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>

                    @if(config('hmrc.environment') === 'sandbox')
                    <div class="alert alert-warning mt-3 mb-0 py-2 px-3">
                        <i class="fas fa-flask me-1"></i>
                        <small><strong>Sandbox Mode:</strong> Test environment</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Card: What you can do -->
        <div class="col-md-6">
            <div class="card hmrc-card h-100">
                <div class="card-body">
                    <h5 class="card-title mb-4">What you can do</h5>

                    <ul class="hmrc-feature-list">
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>Submit VAT returns directly to HMRC</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>Retrieve VAT obligations and liabilities</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>Access Making Tax Digital services</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span>View and manage your tax information</span>
                        </li>
                    </ul>

                    <div class="mt-4 pt-3 border-top">
                        <a href="{{ route('hmrc.auth.index') }}" class="text-muted small">
                            <i class="fas fa-arrow-left me-1"></i> Back to status
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Connection Process -->
    <div class="card hmrc-card">
        <div class="card-body">
            <h5 class="card-title mb-4">Connection Process</h5>

            <div class="row text-center">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="process-step">
                        <div class="process-number">1</div>
                        <h6 class="process-title">Authorize</h6>
                        <p class="process-desc">Log in with your Government Gateway credentials on HMRC's website</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="process-step">
                        <div class="process-number">2</div>
                        <h6 class="process-title">Grant Access</h6>
                        <p class="process-desc">Approve {{ config('app.name') }} to access your HMRC information</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="process-step">
                        <div class="process-number">3</div>
                        <h6 class="process-title">Complete</h6>
                        <p class="process-desc">Return to {{ config('app.name') }} and start managing your tax affairs</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Important Information -->
    <div class="card hmrc-card mt-4">
        <div class="card-body">
            <h6 class="card-title d-flex align-items-center mb-3">
                <i class="fas fa-info-circle text-muted me-2"></i>
                Important Information
            </h6>

            <ul class="hmrc-info-list mb-0">
                <li>Your credentials are never stored on our servers</li>
                <li>The connection is secured using OAuth 2.0 industry standard</li>
                <li>You can revoke access at any time through your HMRC account</li>
                <li>For production use, ensure you're using the correct HMRC environment (test or live)</li>
            </ul>
        </div>
    </div>
    </div>
</div>

@push('styles')
<style>
/* Page Header */
.page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
}

.hmrc-icon-wrapper {
    width: 48px;
    height: 48px;
    background: #f0f4f8;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1d687d;
    font-size: 1.25rem;
}

/* Modern Alert */
.alert-modern {
    border: none;
    border-left: 4px solid #17a2b8;
    background-color: #d1ecf1;
    color: #0c5460;
    border-radius: 4px;
    padding: 1rem 1.25rem;
}

/* HMRC Cards */
.hmrc-card {
    border: 1px solid #e3e6ea;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    transition: box-shadow 0.2s ease;
}

.hmrc-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.hmrc-card .card-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #2c3e50;
}

.hmrc-card .card-text {
    color: #5a6c7d;
    line-height: 1.6;
}

/* Card Icon */
.hmrc-card-icon {
    width: 40px;
    height: 40px;
    background: #e8f4f8;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #17a2b8;
    font-size: 1.125rem;
    flex-shrink: 0;
}

/* HMRC Primary Button */
.btn-hmrc-primary {
    background-color: #17848e;
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

.btn-hmrc-primary:hover {
    background-color: #136770;
    color: white;
}

.btn-hmrc-primary:active,
.btn-hmrc-primary:focus {
    background-color: #136770;
    color: white;
    box-shadow: none;
}

/* Feature List */
.hmrc-feature-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.hmrc-feature-list li {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
    color: #5a6c7d;
}

.hmrc-feature-list li:last-child {
    margin-bottom: 0;
}

.hmrc-feature-list li i {
    color: #28a745;
    font-size: 1.125rem;
    margin-right: 0.75rem;
    margin-top: 2px;
    flex-shrink: 0;
}

/* Process Steps */
.process-step {
    position: relative;
    padding: 0 1rem;
}

.process-number {
    width: 48px;
    height: 48px;
    background-color: #17848e;
    color: white;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

.process-title {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.process-desc {
    color: #5a6c7d;
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 0;
}

/* Info List */
.hmrc-info-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.hmrc-info-list li {
    position: relative;
    padding-left: 1.5rem;
    margin-bottom: 0.75rem;
    color: #5a6c7d;
    font-size: 0.9rem;
}

.hmrc-info-list li:last-child {
    margin-bottom: 0;
}

.hmrc-info-list li:before {
    content: "•";
    position: absolute;
    left: 0;
    color: #17a2b8;
    font-weight: 700;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .page-title {
        font-size: 1.25rem;
    }

    .hmrc-icon-wrapper {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }

    .process-step {
        padding: 0 0.5rem;
        margin-bottom: 2rem;
    }

    .process-number {
        width: 40px;
        height: 40px;
        font-size: 1.125rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Show toast notification for error messages
@if(session('error'))
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'error',
        title: '{{ session("error") }}',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        customClass: {
            popup: 'colored-toast'
        }
    });
@endif
</script>
@endpush
@endsection

