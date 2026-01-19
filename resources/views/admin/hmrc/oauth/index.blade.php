@extends('admin.layout.app')

@section('content')
<div class="main-content app-content">
    <div class="container-fluid">
        <div class="row justify-content-center" style="margin-top: 50px;">
            <div class="col-md-8">
                <div class="card custom-card text-center" style="padding: 40px;">
                    <div class="card-body">
                        <div style="font-size: 60px; margin-bottom: 20px;">🔐</div>
                        <h2 class="mb-3">Connect to HMRC VAT MTD</h2>
                        <p class="text-muted mb-4">
                            Connect your application to HMRC to automatically submit VAT returns and manage your tax obligations.
                        </p>
                        
                        <a href="{{ route('hmrc.connect') }}" class="btn btn-primary btn-lg" style="padding: 15px 40px; font-size: 18px;">
                            <i class="bi bi-link-45deg"></i> Connect to HMRC
                        </a>

                        <div class="mt-5">
                            <h5>What you can do after connecting:</h5>
                            <div class="row mt-3">
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 border rounded">
                                        <strong>✅ Submit VAT Returns</strong>
                                        <p class="text-muted small mb-0">Submit returns directly from your reports</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 border rounded">
                                        <strong>📊 View Obligations</strong>
                                        <p class="text-muted small mb-0">See upcoming and overdue returns</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 border rounded">
                                        <strong>💰 Check Payments</strong>
                                        <p class="text-muted small mb-0">View payment history</p>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="p-3 border rounded">
                                        <strong>📈 View Liabilities</strong>
                                        <p class="text-muted small mb-0">Track what you owe</p>
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
@endsection