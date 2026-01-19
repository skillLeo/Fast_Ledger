@extends('admin.layout.app')

@section('content')
<style>
    .bank-feed-card {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .bank-feed-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-connected {
        background-color: #d4edda;
        color: #155724;
    }

    .status-not-connected {
        background-color: #f8d7da;
        color: #721c24;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #f8f9fa;
        border-radius: 12px;
        margin: 40px 0;
    }

    .empty-state-icon {
        font-size: 64px;
        color: #01677d;
        margin-bottom: 20px;
    }

    .bank-type-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        margin-left: 8px;
    }

    .bank-type-client {
        background-color: #cfe2ff;
        color: #084298;
    }

    .bank-type-office {
        background-color: #d1e7dd;
        color: #0f5132;
    }

    .sync-info {
        font-size: 12px;
        color: #6c757d;
        margin-top: 5px;
    }

    .btn-connect-bankfeed {
        background-color: #01677d;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-connect-bankfeed:hover {
        background-color: #014d5c;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(1,103,125,0.3);
    }

    /* 🆕 NEW: Toggle Switch Styles */
    .import-toggle-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 16px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 6px;
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .toggle-slider {
        background-color: #01677d;
    }

    input:checked + .toggle-slider:before {
        transform: translateX(20px);
    }

    .toggle-label {
        font-size: 13px;
        font-weight: 500;
        color: #333;
        white-space: nowrap;
    }
</style>

<div class="main-content app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="card custom-card">
                    
                    <!-- Header -->
                    <div class="card-header justify-content-between d-flex">
                        <h4 class="page-title">Bank Feed Settings</h4>
                        <a href="{{ route('bulk-transactions.dashboard') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>

                    <div class="card-body">
                        
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($bankAccounts->isEmpty())
                            <!-- Empty State -->
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-university"></i>
                                </div>
                                <h3 class="mb-3">No Bank Accounts Found</h3>
                                <p class="text-muted mb-4">
                                    You need to create a bank account first before connecting to Bank Feed.
                                </p>
                                <a href="{{ route('banks.create', auth()->id()) }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus-circle"></i> Create Bank Account
                                </a>
                            </div>

                        @elseif($connectedBanks->isEmpty())
                            <!-- No Connected Banks Yet -->
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-link"></i>
                                </div>
                                <h3 class="mb-3">Connect Your Bank Feed</h3>
                                <p class="text-muted mb-4">
                                    Get automatic transaction updates from your bank - no more manual uploads!<br>
                                    Connect your bank account below to start receiving real-time transactions.
                                </p>
                                <button class="btn btn-connect-bankfeed btn-lg" data-bs-toggle="modal" data-bs-target="#bankTypeModal">
                                    <i class="fas fa-plug"></i> Connect Bank Feed
                                </button>
                            </div>

                            <!-- Show existing banks that can be connected -->
                            <div class="mt-5">
                                <h5 class="mb-3">Your Bank Accounts</h5>
                                @foreach($bankAccounts as $account)
                                    <div class="bank-feed-card">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="bank-logo me-3" 
                                                     style="width:48px;height:48px;border-radius:6px;background:#eef6fb;display:flex;align-items:center;justify-content:center;font-weight:700;color:#01677d;">
                                                    {{ strtoupper(substr($account->Bank_Name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold">
                                                        {{ $account->Bank_Name }}
                                                        @if($account->Bank_Type_ID == 1)
                                                            <span class="bank-type-badge bank-type-client">Client</span>
                                                        @elseif($account->Bank_Type_ID == 2)
                                                            <span class="bank-type-badge bank-type-office">Office</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-muted" style="font-size: 13px;">
                                                        Sort Code: {{ $account->Sort_Code ?? '—' }} | 
                                                        Account: {{ $account->Account_No ?? '—' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="status-badge status-not-connected">
                                                    <i class="fas fa-times-circle"></i> Not Connected
                                                </span>
                                                <button class="btn btn-sm btn-primary ms-2" 
                                                        onclick="connectBankFeed({{ $account->Bank_Account_ID }}, '{{ $account->Bank_Name }}', {{ $account->Bank_Type_ID }})">
                                                    <i class="fas fa-plug"></i> Connect
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        @else
                            <!-- 🆕 UPDATED: Connected Banks Header with Toggle -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">Connected Bank Accounts</h5>
                                
                                <div class="d-flex gap-2 align-items-center">
                                    {{-- 🆕 NEW: Import Button Toggle --}}
                                    <div class="import-toggle-wrapper">
                                        <label class="toggle-switch">
                                            <input type="checkbox" 
                                                   id="importButtonToggle" 
                                                   {{ session('show_import_button', false) ? 'checked' : '' }}
                                                   onchange="toggleImportButton(this)">
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="toggle-label">
                                            <i class="fas fa-file-import"></i> Show Import Button
                                        </span>
                                    </div>

                                    <button class="btn btn-connect-bankfeed" data-bs-toggle="modal" data-bs-target="#bankTypeModal">
                                        <i class="fas fa-plus"></i> Connect Another Bank
                                    </button>
                                </div>
                            </div>

                            @foreach($connectedBanks as $account)
                                <div class="bank-feed-card">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-start">
                                            <div class="bank-logo me-3" 
                                                 style="width:48px;height:48px;border-radius:6px;background:#d4edda;display:flex;align-items:center;justify-content:center;font-weight:700;color:#155724;">
                                                {{ strtoupper(substr($account->Bank_Name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">
                                                    {{ $account->Bank_Name }}
                                                    @if($account->Bank_Type_ID == 1)
                                                        <span class="bank-type-badge bank-type-client">Client</span>
                                                    @elseif($account->Bank_Type_ID == 2)
                                                        <span class="bank-type-badge bank-type-office">Office</span>
                                                    @endif
                                                </div>
                                                <div class="text-muted" style="font-size: 13px;">
                                                    Sort Code: {{ $account->Sort_Code ?? '—' }} | 
                                                    Account: {{ $account->Account_No ?? '—' }}
                                                </div>
                                                <div class="sync-info">
                                                    <i class="fas fa-check-circle text-success"></i>
                                                    Last synced: {{ $account->bank_feed_last_synced_at ? \Carbon\Carbon::parse($account->bank_feed_last_synced_at)->diffForHumans() : 'Never' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="status-badge status-connected mb-2 d-block">
                                                <i class="fas fa-check-circle"></i> Connected
                                            </span>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        onclick="syncNow({{ $account->Bank_Account_ID }})">
                                                    <i class="fas fa-sync"></i> Sync Now
                                                </button>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="disconnectBank({{ $account->Bank_Account_ID }}, '{{ $account->Bank_Name }}')">
                                                    <i class="fas fa-unlink"></i> Disconnect
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <!-- Not Connected Banks -->
                            @if($notConnectedBanks->isNotEmpty())
                                <h5 class="mt-5 mb-3">Available to Connect</h5>
                                @foreach($notConnectedBanks as $account)
                                    <div class="bank-feed-card">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <div class="bank-logo me-3" 
                                                     style="width:48px;height:48px;border-radius:6px;background:#eef6fb;display:flex;align-items:center;justify-content:center;font-weight:700;color:#01677d;">
                                                    {{ strtoupper(substr($account->Bank_Name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold">
                                                        {{ $account->Bank_Name }}
                                                        @if($account->Bank_Type_ID == 1)
                                                            <span class="bank-type-badge bank-type-client">Client</span>
                                                        @elseif($account->Bank_Type_ID == 2)
                                                            <span class="bank-type-badge bank-type-office">Office</span>
                                                        @endif
                                                    </div>
                                                    <div class="text-muted" style="font-size: 13px;">
                                                        Sort Code: {{ $account->Sort_Code ?? '—' }} | 
                                                        Account: {{ $account->Account_No ?? '—' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="status-badge status-not-connected">
                                                    <i class="fas fa-times-circle"></i> Not Connected
                                                </span>
                                                <button class="btn btn-sm btn-primary ms-2" 
                                                        onclick="connectBankFeed({{ $account->Bank_Account_ID }}, '{{ $account->Bank_Name }}', {{ $account->Bank_Type_ID }})">
                                                    <i class="fas fa-plug"></i> Connect
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bank Type Selection Modal (for new connections) -->
<div class="modal fade" id="bankTypeModal" tabindex="-1" aria-labelledby="bankTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bankTypeModalLabel">
                    <i class="fas fa-university"></i> Select Bank Account Type
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4">This bank feed connection is for:</p>
                
                <div class="row g-3">
                    <div class="col-6">
                        <div class="card text-center h-100 bank-type-card" onclick="selectBankType(1)" style="cursor: pointer;">
                            <div class="card-body">
                                <i class="fas fa-user-tie fa-3x mb-3" style="color: #084298;"></i>
                                <h5>Client Bank</h5>
                                <p class="text-muted small">For client transactions</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card text-center h-100 bank-type-card" onclick="selectBankType(2)" style="cursor: pointer;">
                            <div class="card-body">
                                <i class="fas fa-building fa-3x mb-3" style="color: #0f5132;"></i>
                                <h5>Office Bank</h5>
                                <p class="text-muted small">For office transactions</p>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="selectedBankType" value="">
            </div>
        </div>
    </div>
</div>

<style>
    .bank-type-card {
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .bank-type-card:hover {
        border-color: #01677d;
        box-shadow: 0 4px 12px rgba(1,103,125,0.2);
        transform: translateY(-4px);
    }

    .bank-type-card.selected {
        border-color: #01677d;
        background-color: #f0f8ff;
    }
</style>

@endsection

@section('scripts')
<script>
{{-- 🆕 NEW: Toggle Import Button --}}
function toggleImportButton(checkbox) {
    const isChecked = checkbox.checked;
    
    fetch('/finexer/toggle-import-button', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            show_import_button: isChecked
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success toast
            const toast = document.createElement('div');
            toast.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
            toast.style.zIndex = '9999';
            toast.innerHTML = `
                <i class="fas fa-check-circle"></i> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => toast.remove(), 3000);
        } else {
            alert('❌ Error: ' + data.message);
            checkbox.checked = !isChecked;
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
        checkbox.checked = !isChecked;
    });
}

function selectBankType(bankTypeId) {
    document.getElementById('selectedBankType').value = bankTypeId;
    
    // Visual feedback
    document.querySelectorAll('.bank-type-card').forEach(card => {
        card.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
    
    // Redirect to connect with bank type
    setTimeout(() => {
        window.location.href = `/finexer/connect?bank_type_id=${bankTypeId}`;
    }, 300);
}

function connectBankFeed(bankAccountId, bankName, existingBankTypeId) {
    if (confirm(`Connect ${bankName} to Bank Feed?\n\nThis will enable automatic transaction sync.`)) {
        // Bank already has Bank_Type_ID, so we can connect directly
        window.location.href = `/finexer/connect?bank_account_id=${bankAccountId}&bank_type_id=${existingBankTypeId}`;
    }
}

function syncNow(bankAccountId) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
    btn.disabled = true;
    
    fetch(`/finexer/sync/${bankAccountId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✅ Synced successfully!\n\n${data.message}`);
            location.reload();
        } else {
            alert('❌ Sync failed: ' + data.message);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error.message);
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

function disconnectBank(bankAccountId, bankName) {
    if (confirm(`Disconnect ${bankName} from Bank Feed?\n\nYou will need to reconnect to receive automatic updates.`)) {
        fetch(`/finexer/disconnect/${bankAccountId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Bank disconnected successfully');
                location.reload();
            } else {
                alert('❌ Disconnect failed: ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Error: ' + error.message);
        });
    }
}
</script>
@endsection