@extends('admin.layout.app')

<style>
    .hmrc-dashboard {
        padding: 20px;
        background: #f8f9fa;
    }

    /* HMRC Header */
    .hmrc-header {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .hmrc-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
    }

    .view-summary-toggle {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .toggle-switch {
        position: relative;
        width: 50px;
        height: 24px;
        background: #ccc;
        border-radius: 12px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .toggle-switch.active {
        background: #1d70b8;
    }

    .toggle-switch::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: white;
        top: 2px;
        left: 2px;
        transition: left 0.3s;
    }

    .toggle-switch.active::after {
        left: 28px;
    }

    /* VAT Summary Card */
    .vat-summary-card {
        background: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 30px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }

    .summary-item {
        text-align: center;
        padding: 0px;
        border-right: 1px solid #dee2e6;
    }

    .summary-item:last-child {
        border-right: none;
    }

    .summary-label {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .summary-value {
        font-size: 18px;
        font-weight: 700;
        color: #0b0c0c;
    }

    .summary-value.positive {
        color: #00703c;
    }

    .summary-value.negative {
        color: #d4351c;
    }

    .summary-date {
        font-size: 24px;
        font-weight: 600;
        color: #0b0c0c;
    }

    .summary-link {
        font-size: 14px;
        color: #1d70b8;
        text-decoration: underline;
        margin-top: 5px;
        display: inline-block;
    }

    /* Section Headers */
    .section-header {
        font-size: 20px;
        font-weight: 600;
        color: #0b0c0c;
    }

    /* Obligation Card */
    .obligation-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-left: 5px solid;
    }

    .obligation-card.needs-attention {
        border-left-color: #1d70b8;
    }

    .obligation-card.completed {
        border-left-color: #00703c;
    }

    .obligation-info {
        flex: 1;
    }

    .obligation-period {
        font-size: 18px;
        font-weight: 600;
        color: #0b0c0c;
        margin-bottom: 5px;
    }

    .obligation-meta {
        font-size: 14px;
        color: #6c757d;
    }

    .obligation-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 10px;
    }

    .badge-due {
        background: #ffe5cc;
        color: #663500;
    }

    .badge-filed {
        background: #d4edda;
        color: #155724;
    }

    .obligation-amount {
        font-size: 16px;
        font-weight: 600;
        margin-right: 20px;
    }


    /* Empty State */
    .empty-state {
        background: white;
        padding: 60px 20px;
        text-align: center;
        border-radius: 8px;
        color: #6c757d;
    }

    /* Connection Status */
    .connection-status {
        background: #d4edda;
        border: 2px solid #c3e6cb;
        color: #155724;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .connection-status.disconnected {
        background: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .stats-summary {
        display: none;
        /* Hidden by default */
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }

    .stats-summary.show {
        display: grid;
    }

    .stat-box {
        background: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border-top: 4px solid;
    }

    .stat-box.open {
        border-top-color: #1d70b8;
    }

    .stat-box.overdue {
        border-top-color: #d4351c;
    }

    .stat-box.completed {
        border-top-color: #00703c;
    }

    .stat-box.total {
        border-top-color: #6c757d;
    }

    .stat-number {
        font-size: 36px;
        font-weight: 700;
        color: #0b0c0c;
    }

    .stat-label {
        font-size: 14px;
        color: #6c757d;
        margin-top: 5px;
    }
</style>

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between">
                            <h4 class="page-title mb-4">VAT Return</h4>
                            <!-- Connection Status -->
                            @if ($isConnected)
                                <form action="{{ route('hmrc.disconnect') }}" method="POST" class="d-inline  ms-auto">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-0">
                                        Disconnect
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('hmrc.connect') }}" class="teal-custom-btn" style="margin-top: -20px">Not
                                    connected
                                    to HMRC</a>
                            @endif
                        </div>
                        <!-- Header -->
                        <div class="hmrc-header">
                            <div>
                                <h3>VAT information from HMRC as of {{ now()->format('d M Y') }}</h3>
                                <small class="text-muted">VRN: {{ $vrn }}</small>
                            </div>
                            <div class="view-summary-toggle">
                                <span>View summary on dashboard</span>
                                <div class="toggle-switch" id="summaryToggle"></div>
                            </div>
                        </div>



                        {{-- Flash Messages --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <strong>Error:</strong>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- VAT Summary Card -->
                        @php
                            $nextDueObligation = collect($obligations['open'])->sortBy('due_date')->first();
                            $latestSubmission = $recentSubmissions->first();
                        @endphp

                        <div class="vat-summary-card">
                            <div class="summary-item">
                                <div class="summary-label pt-2">VAT amount due</div>
                                @if ($latestSubmission && $latestSubmission->net_vat_due > 0)
                                    <div class="summary-value negative">
                                        £{{ number_format($latestSubmission->net_vat_due, 2) }}
                                    </div>
                                @else
                                    <div class="summary-value">Nothing to pay</div>
                                @endif
                            </div>

                            <div class="summary-item">
                                <div class="summary-label pt-2">Payment due</div>
                                @if ($nextDueObligation)
                                    <div class="summary-date">
                                        {{ \Carbon\Carbon::parse($nextDueObligation['due_date'])->format('d M Y') }}
                                    </div>
                                    <a href="#" class="summary-link">View previous payments</a>
                                @else
                                    <div class="summary-value">No upcoming</div>
                                @endif
                            </div>

                            <div class="summary-item">
                                <div class="summary-label pt-2">Payment method</div>
                                <div class="summary-value" style="font-size: 18px;">
                                    Not supplied from HMRC
                                </div>
                                <a href="https://www.gov.uk/find-hmrc-contacts/technical-support-with-vat-online-services" 
                                   target="_blank" 
                                   rel="noopener noreferrer" 
                                   class="summary-link">
                                    Contact HMRC <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16" style="margin-left: 4px; vertical-align: baseline;">
                                        <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/>
                                        <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="stats-summary" id="vatSummary">
                            <div class="stat-box open">
                                <div class="stat-number">{{ count($obligations['open']) }}</div>
                                <div class="stat-label">Open</div>
                            </div>
                            <div class="stat-box overdue">
                                <div class="stat-number">{{ count($obligations['overdue']) }}</div>
                                <div class="stat-label">Overdue</div>
                            </div>
                            <div class="stat-box completed">
                                <div class="stat-number">{{ count($obligations['fulfilled']) }}</div>
                                <div class="stat-label">Completed</div>
                            </div>
                            <div class="stat-box total">
                                <div class="stat-number">{{ $recentSubmissions->count() }}</div>
                                <div class="stat-label">Total Submissions</div>
                            </div>
                        </div>

                        <!-- Needs Attention -->
                        @if (!empty($obligations['overdue']) || !empty($obligations['open']))
                            <h2 class="section-header">Upcoming VAT Return</h2>

                            {{-- ========================================
                                FOR OVERDUE OBLIGATIONS 
                                ======================================== --}}
                            @foreach ($obligations['overdue'] as $obligation)
                                <div class="obligation-card needs-attention">
                                    <div class="obligation-info">
                                        <div class="obligation-period">
                                            {{ \Carbon\Carbon::parse($obligation['start_date'])->format('d M Y') }} -
                                            {{ \Carbon\Carbon::parse($obligation['end_date'])->format('d M Y') }}
                                            <span class="obligation-badge badge-due">
                                                Overdue
                                                {{ \Carbon\Carbon::parse($obligation['due_date'])->diffInDays(now()) }}
                                                days
                                            </span>
                                        </div>
                                        <div class="obligation-meta">
                                            Due {{ \Carbon\Carbon::parse($obligation['due_date'])->format('d M Y') }}
                                        </div>
                                    </div>
                                    {{-- ✅ CORRECTED: Use hmrc.vat.review --}}
                                    <a href="{{ route('hmrc.vat.review', ['periodKey' => $obligation['period_key']]) }}"
                                        class="teal-custom-btn">
                                        Submit Now
                                    </a>
                                </div>
                            @endforeach

                            {{-- ========================================
                        FOR OPEN OBLIGATIONS 
                        ======================================== --}}

                            @foreach ($obligations['open'] as $obligation)
                                <div class="obligation-card needs-attention">
                                    <div class="obligation-info">
                                        <div class="obligation-period">
                                            {{ \Carbon\Carbon::parse($obligation['start_date'])->format('d M Y') }} -
                                            {{ \Carbon\Carbon::parse($obligation['end_date'])->format('d M Y') }}
                                            <span class="obligation-badge badge-due">
                                                Due in
                                                {{ \Carbon\Carbon::parse($obligation['due_date'])->diffInDays(now()) }}
                                                days
                                            </span>
                                        </div>
                                        <div class="obligation-meta">
                                            Due {{ \Carbon\Carbon::parse($obligation['due_date'])->format('d M Y') }}
                                        </div>
                                    </div>
                                    {{-- ✅ CORRECTED: Use hmrc.vat.review --}}
                                    <a href="{{ route('hmrc.vat.review', ['periodKey' => $obligation['period_key']]) }}"
                                        class="teal-custom-btn">
                                        Review
                                    </a>
                                </div>
                            @endforeach
                        @endif

                        <!-- Completed -->
                        @if (!empty($obligations['fulfilled']) || $recentSubmissions->count() > 0)
                            <h2 class="section-header">
                                Completed VAT Return
                                <small class="text-muted" style="font-size: 14px; font-weight: normal; margin-left: 10px;">
                                    Looking to see VAT Transactions for your own date range?
                                    <a href="{{ route('vat.report') }}" style="color: #1d70b8;">View</a>
                                </small>
                            </h2>

                            {{-- ========================================
                                FOR FULFILLED/COMPLETED OBLIGATIONS 
                                ======================================== --}}
                            @foreach ($obligations['fulfilled'] as $obligation)
                                @php
                                    $submission = $recentSubmissions->firstWhere(
                                        'period_key',
                                        $obligation['period_key'],
                                    );
                                @endphp
                                <div class="obligation-card completed">
                                    <div class="obligation-info">
                                        <div class="obligation-period">
                                            {{ \Carbon\Carbon::parse($obligation['start_date'])->format('d M Y') }} -
                                            {{ \Carbon\Carbon::parse($obligation['end_date'])->format('d M Y') }}
                                            <span class="obligation-badge badge-filed">Filed</span>
                                        </div>
                                        <div class="obligation-meta">
                                            @if ($obligation['received_date'])
                                                Submitted
                                                {{ \Carbon\Carbon::parse($obligation['received_date'])->format('d M Y') }}
                                                @if ($submission)
                                                    by {{ $submission->submittedBy->name ?? 'System' }}
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    @if ($submission)
                                        <div class="obligation-amount">
                                            VAT amount due: £{{ number_format($submission->net_vat_due, 2) }}
                                        </div>
                                    @endif
                                    {{-- ✅ CORRECTED: Use hmrc.vat.review --}}
                                    <a href="{{ route('hmrc.vat.review', ['periodKey' => $obligation['period_key']]) }}"
                                        class="teal-custom-btn">
                                        Review
                                    </a>
                                </div>
                            @endforeach
                            {{-- ========================================
                        FOR RECENT SUBMISSIONS WITHOUT OBLIGATIONS 
                        ======================================== --}}

                            @foreach ($recentSubmissions as $submission)
                                @if (!collect($obligations['fulfilled'])->contains('period_key', $submission->period_key))
                                    <div class="obligation-card completed">
                                        <div class="obligation-info">
                                            <div class="obligation-period">
                                                {{ $submission->period_key }}
                                                <span class="obligation-badge badge-filed">Filed</span>
                                            </div>
                                            <div class="obligation-meta">
                                                Submitted {{ $submission->submitted_at->format('d M Y') }} by
                                                {{ $submission->submittedBy->name ?? 'System' }}
                                            </div>
                                        </div>
                                        <div class="obligation-amount">
                                            VAT amount due: £{{ number_format($submission->net_vat_due, 2) }}
                                        </div>
                                        {{-- ✅ CORRECTED: Use hmrc.vat.review --}}
                                        <a href="{{ route('hmrc.vat.review', ['periodKey' => $submission->period_key]) }}"
                                            class="teal-custom-btn">
                                            Review
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        <!-- Empty State -->
                        @if (empty($obligations['open']) &&
                                empty($obligations['overdue']) &&
                                empty($obligations['fulfilled']) &&
                                $recentSubmissions->count() === 0)
                            <div class="empty-state">
                                <h4>No VAT obligations found</h4>
                                <p>
                                    @if ($isConnected)
                                        Your VAT obligations will appear here once they are available from HMRC.
                                    @else
                                        Please connect to HMRC to view your VAT obligations.
                                    @endif
                                </p>
                                @if (!$isConnected)
                                    <a href="{{ route('hmrc.connect') }}" class="btn btn-primary mt-3">Connect to
                                        HMRC</a>
                                @endif
                            </div>
                        @endif



                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle summary visibility
        const toggle = document.getElementById('summaryToggle');
        const summary = document.getElementById('vatSummary');

        if (toggle && summary) {
            toggle.addEventListener('click', function() {
                // Toggle the active class on the switch
                toggle.classList.toggle('active');

                // Toggle the show class on the summary
                summary.classList.toggle('show');
            });
        }
    });
</script>

@endsection
