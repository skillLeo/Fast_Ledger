<!-- resources/views/auth/verify-email.blade.php -->

<x-layout>
    <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
        <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
            <!-- Logo Section -->
            <div class="my-5 d-flex justify-content-center">
                <x-logo src="admin/assets/images/brand-logos/desktop-dark.png" alt="logo" href="/" />
            </div>

            <!-- Verification Card -->
            <x-form-card title="Verify Your Email Address" subtitle="Please check your inbox">
                
                <!-- Success Message -->
                @if (session('status') == 'verification-link-sent')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ri-mail-check-line me-2"></i>
                        <strong>Email Sent!</strong> A new verification link has been sent to your email address.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Warning Message -->
                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="ri-alert-line me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Instructions -->
                <div class="mb-4">
                    <div class="text-center mb-3">
                        <i class="ri-mail-send-line" style="font-size: 64px; color: #845adf;"></i>
                    </div>
                    <p class="text-muted text-center">
                        Thanks for signing up! Before you can start using Fast Ledger, please verify your email address 
                        by clicking the link we just sent to:
                    </p>
                    <p class="text-center">
                        <strong>{{ auth()->user()->email }}</strong>
                    </p>
                    <p class="text-muted text-center">
                        If you didn't receive the email, click the button below to request a new one.
                    </p>
                </div>

                <!-- Resend Verification Email Form -->
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-mail-send-line me-2"></i>
                            Resend Verification Email
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="text-center my-3">
                    <span class="text-muted">or</span>
                </div>

                <!-- Logout Form -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <div class="d-grid">
                        <button type="submit" class="btn btn-light">
                            <i class="ri-logout-box-line me-2"></i>
                            Logout
                        </button>
                    </div>
                </form>

            </x-form-card>

            <!-- Help Text -->
            <div class="text-center mt-4">
                <p class="text-muted">
                    <small>
                        Having trouble? Contact support at 
                        <a href="mailto:support@fastledger.com">support@fastledger.com</a>
                    </small>
                </p>
            </div>
        </div>
    </div>
</x-layout>

<style>
    .alert {
        border-radius: 8px;
    }
</style>