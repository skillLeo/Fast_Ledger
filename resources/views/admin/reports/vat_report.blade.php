@extends('admin.layout.app')

<style>
    /* Add these new styles */
    .hmrc-status {
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .hmrc-status.connected {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }

    .hmrc-status.disconnected {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    .btn-hmrc-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 12px 30px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 5px;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .btn-hmrc-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-hmrc-submit:disabled {
        background: #6c757d;
        cursor: not-allowed;
        transform: none;
    }

    /* Readonly date inputs */
    input[readonly].form-control {
        background-color: #e9ecef !important;
        cursor: not-allowed;
        opacity: 0.8;
    }

    input[readonly].form-control:focus {
        box-shadow: none;
        border-color: #ced4da;
    }

    /* above new  styel  */
    #tbl_exporttable_to_xls {
        margin: 20px auto;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #fff;
        border: 1px solid #ddd;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    .main-header-container {
        left: 175px
    }

    #tabletop {
        background-color: #e6e6e6;
        color: #555;
        text-align: center;
        padding: 12px;
        font-size: 20px;
        font-weight: bold;
        border-bottom: 2px solid #e6e6e6;
    }

    #tbl_exporttable_to_xls table {
        width: 100%;
        border-collapse: collapse;
    }

    #tbl_exporttable_to_xls th,
    #tbl_exporttable_to_xls td {
        padding: 12px;
        border-bottom: 1px solid #cdcaca;
    }

    #tbl_exporttable_to_xls th {
        background-color: #f4f6f8;
        color: #333;
        text-align: left;
        font-weight: 600;
    }

    #tbl_exporttable_to_xls td {
        color: #555;
        vertical-align: top;
    }

    #tbl_exporttable_to_xls tr:nth-child(even) td {
        background-color: #fafafa;
    }

    #tbl_exporttable_to_xls td:nth-child(3),
    #tbl_exporttable_to_xls th:nth-child(3) {
        text-align: center;
    }

    /* Optional: Hover effect */
    #tbl_exporttable_to_xls tr:hover td {
        background-color: #eef6ff;
    }

    #tbl_exporttable_to_xls tfoot {
        background-color: #c3bfbf;
        /* light grey background */
        color: #555;
        /* dark grey text */
        font-weight: bold;
    }


    @media (min-width: 1440px) {
        .main-header-container {
            left: 145px;
        }
    }

    @media (min-width: 2250px) {
        .main-header-container {
            left: 170px;
        }
    }

    #tbl_exporttable_to_xls tfoot td {
        padding: 12px;
        border-top: 2px solid #ccc;
        text-align: center;
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
                            <h4 class="page-title mb-4">VAT Report</h4>
                            @if (request()->has('period_key'))
                                <a href="{{ route('hmrc.vat.dashboard') }}" class="teal-custom-btn "
                                    style="margin-top: -14px; margin-right: 20px;">
                                    ← Back to Vat Returns
                                </a>
                            @endif
                        </div>

                        {{-- HMRC Connection Status --}}
                        @if (isset($isConnectedToHmrc))
                            <div class="hmrc-status {{ $isConnectedToHmrc ? 'connected' : 'disconnected' }} "
                                style="margin-right: 20px">
                                @if ($isConnectedToHmrc)
                                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path
                                            d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                    </svg>
                                    <strong>Connected to HMRC</strong>
                                    <span>(Token expires: {{ $hmrcToken->expires_at->format('d M Y H:i') }})</span>
                                    <form action="{{ route('hmrc.disconnect') }}" method="POST" class="ms-auto d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger ms-auto rounded-0">
                                            Disconnect
                                        </button>
                                    </form>
                                @else
                                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                        <path
                                            d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z" />
                                    </svg>
                                    <strong>Not Connected to HMRC</strong>
                                    <a href="{{ route('hmrc.connect') }}" class="btn btn-sm btn-primary"
                                        style="margin-left: auto;">
                                        Connect Now
                                    </a>
                                @endif
                            </div>
                        @endif

                        {{-- Display errors from HMRC submission --}}
                        @if ($errors->has('submission'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                {{ $errors->first('submission') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- Display success message --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif



                        <div class="card-body">
                            <!-- Filter Form -->
                            <form method="GET" id="filter-form">
                                <div class="mb-4 row">
                                    <div class="col-md-2">
                                        <label for="from_date">
                                            From Date:
                                            @if (request()->has('period_key'))
                                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16"
                                                    class="ms-1" style="color: #6c757d;">
                                                    <path
                                                        d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z" />
                                                </svg>
                                            @endif
                                        </label>
                                        <input type="date" id="from_date" name="from_date"
                                            class="form-control {{ request()->has('period_key') ? 'bg-light' : '' }}"
                                            value="{{ old('from_date', $fromDate ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}"
                                            @if (request()->has('period_key')) readonly style="cursor: not-allowed;" @endif>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="to_date">
                                            To Date:
                                            @if (request()->has('period_key'))
                                                <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16"
                                                    class="ms-1" style="color: #6c757d;">
                                                    <path
                                                        d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z" />
                                                </svg>
                                            @endif
                                        </label>
                                        <input type="date" id="to_date" name="to_date"
                                            class="form-control {{ request()->has('period_key') ? 'bg-light' : '' }}"
                                            value="{{ old('to_date', $toDate ?? \Carbon\Carbon::now()->format('Y-m-d')) }}"
                                            @if (request()->has('period_key')) readonly style="cursor: not-allowed;" @endif>
                                    </div>

                                    @if (!request()->has('period_key'))
                                        <div class="col-md-4 d-flex align-items-end">
                                            <div class="ms-2">
                                                <button type="submit" id="filter-btn" class="btn teal-custom">
                                                    View Report
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-md-8 d-flex align-items-end">
                                            <div class="mb-0 d-flex mt-3 gap-2" style="font-size: 14px;">
                                                {{-- <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path
                                                        d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z" />
                                                </svg> --}}
                                                <span>
                                                    {{-- <strong>Locked to Period {{ $periodKey }}</strong> -
                                                    Date range is fixed for this obligation. --}}
                                                    <a href="{{ route('vat.report') }}"
                                                        class="alert-link text-blue text-decoration-underline">View custom
                                                        date range</a>
                                                </span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </form>

                            {{-- ✅ ENHANCED HMRC VAT SUBMISSION FORM WITH FULFILLMENT CHECK --}}
                            @if (isset($isConnectedToHmrc) && $isConnectedToHmrc && request()->has('period_key'))
                                {{-- ✅ CHECK IF OBLIGATION IS ALREADY FULFILLED --}}
                                @if (isset($isObligationFulfilled) && $isObligationFulfilled)
                                    @php
                                        // Get the submission record
                                        $submission = \App\Models\VatSubmission::where('period_key', $periodKey)
                                            ->where('vrn', config('hmrc.vat.vrn'))
                                            ->where('successful', true)
                                            ->with('submittedBy')
                                            ->first();
                                    @endphp

                                    {{-- 📌 Show "Already Submitted" Message --}}
                                    <div class="alert alert-success mt-4 mb-4"
                                        style="background: #e6e6e6; border-left: 4px solid #000; border-radius: 8px;">
                                        <div style="display: flex; align-items: start; gap: 15px;">
                                            <svg width="32" height="32" fill="currentColor" viewBox="0 0 16 16"
                                                style="color: #000; flex-shrink: 0;">
                                                <path
                                                    d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                            </svg>
                                            <div style="flex: 1;">
                                                <h5 style="margin: 0 0 8px 0;font-size: 18px;">
                                                    VAT Return Submitted to HMRC
                                                </h5>

                                                {{-- <div
                                                    style="display: grid; grid-template-columns: auto 1fr; gap: 8px 15px; font-size: 14px; color: #000; margin-bottom: 15px;">
                                                    <strong>Period:</strong>
                                                    <span>{{ $periodKey }}</span>

                                                    @if ($obligation && $obligation->received_date)
                                                        <strong>Submitted On:</strong>
                                                        <span>{{ \Carbon\Carbon::parse($obligation->received_date)->format('d M Y, H:i') }}</span>
                                                    @endif

                                                    @if ($submission && $submission->submittedBy)
                                                        <strong>Submitted By:</strong>
                                                        <span>{{ $submission->submittedBy->name }}</span>
                                                    @endif

                                                    @if ($submission && $submission->processing_date)
                                                        <strong>HMRC Processing Date:</strong>
                                                        <span>{{ \Carbon\Carbon::parse($submission->processing_date)->format('d M Y') }}</span>
                                                    @endif
                                                </div> --}}

                                                {{-- <div
                                                    style="background: white; padding: 12px; border-radius: 6px; border: 1px solid #c3e6cb;">
                                                    <div
                                                        style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; font-size: 13px;">
                                                        <div>
                                                            <span
                                                                style="color: #6c757d; display: block; font-size: 11px;">BOX
                                                                1 - VAT DUE SALES</span>
                                                            <strong
                                                                style="color: #000;">£{{ number_format($_box1Amount, 2) }}</strong>
                                                        </div>
                                                        <div>
                                                            <span
                                                                style="color: #6c757d; display: block; font-size: 11px;">BOX
                                                                4 - VAT RECLAIMED</span>
                                                            <strong
                                                                style="color: #000;">£{{ number_format($_box4Amount, 2) }}</strong>
                                                        </div>
                                                        <div>
                                                            <span
                                                                style="color: #6c757d; display: block; font-size: 11px;">BOX
                                                                5 - NET VAT DUE</span>
                                                            <strong
                                                                style="color: #000; font-size: 16px;">£{{ number_format($_box5Amount, 2) }}</strong>
                                                        </div>
                                                    </div>
                                                </div> --}}

                                                <div
                                                    style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #c3e6cb; font-size: 13px; color: #000;">
                                                    <svg width="14" height="14" fill="currentColor"
                                                        viewBox="0 0 16 16" style="vertical-align: text-bottom;">
                                                        <path
                                                            d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                                        <path
                                                            d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" />
                                                    </svg>
                                                    This VAT return cannot be modified or resubmitted. For corrections,
                                                    contact HMRC or submit an adjustment.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                @endif
                            @elseif(isset($isConnectedToHmrc) && $isConnectedToHmrc && !request()->has('period_key'))
                                {{-- ℹ️ Show Info Message When Viewing Report Directly (No period_key) --}}
                                <div class="alert alert-info mt-4 mb-4" style="border-left: 4px solid #0dcaf0;">
                                    <div style="display: flex; align-items: center; gap: 15px;">
                                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 16 16"
                                            style="color: #0dcaf0; flex-shrink: 0;">
                                            <path
                                                d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                            <path
                                                d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" />
                                        </svg>
                                        <div style="flex: 1;">

                                            <p style="margin: 5px 0 0 0; font-size: 14px;">
                                                To submit VAT return to HMRC, Please go to
                                                <a href="{{ route('hmrc.vat.dashboard') }}" class="alert-link"
                                                    style="color: #0d6efd; text-decoration: underline;"
                                                    onmouseover="this.style.color='#0a58ca'"
                                                    onmouseout="this.style.color='#0d6efd'">
                                                    VAT Return
                                                </a>.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @elseif(!isset($isConnectedToHmrc) || !$isConnectedToHmrc)
                                {{-- ⚠️ Not Connected to HMRC --}}
                                <div class="alert alert-warning mt-4 mb-4">
                                    ⚠️ Please <a href="{{ route('hmrc.connect') }}">connect to HMRC</a> first to submit
                                    VAT returns.
                                </div>
                            @endif

                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex gap-2">
                                    <a href="#" class="nav-link-btn active" data-section="complete-report">Complete
                                        Report</a>
                                    <a href="#" class="nav-link-btn" data-section="in-put">Input VAT</a>
                                    <a href="#" class="nav-link-btn" data-section="out-put">Output VAT</a>
                                </div>
                                <form method="POST" action="{{ route('hmrc.vat.returns.store') }}" 
                                    id="hmrc-submit-form">
                                    @csrf

                                    {{-- Hidden fields for HMRC API --}}
                                    <input type="hidden" name="periodKey" value="{{ $periodKey ?? '#001' }}">
                                    <input type="hidden" name="vatDueSales" value="{{ $_box1Amount }}">
                                    <input type="hidden" name="vatDueAcquisitions" value="{{ $_box2Amount ?? 0 }}">
                                    <input type="hidden" name="totalVatDue" value="{{ $_box3Amount }}">
                                    <input type="hidden" name="vatReclaimedCurrPeriod" value="{{ $_box4Amount }}">
                                    <input type="hidden" name="netVatDue" value="{{ $_box5Amount }}">
                                    <input type="hidden" name="totalValueSalesExVAT" value="{{ (int) $_box6Amount }}">
                                    <input type="hidden" name="totalValuePurchasesExVAT"
                                        value="{{ (int) $_box7Amount }}">
                                    <input type="hidden" name="totalValueGoodsSuppliedExVAT"
                                        value="{{ (int) ($_box8Amount ?? 0) }}">
                                    <input type="hidden" name="totalAcquisitionsExVAT"
                                        value="{{ (int) ($_box9Amount ?? 0) }}">
                                    <input type="hidden" name="finalised" value="1">

                                    <div {{-- style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 2px solid #e9ecef;" --}}>
                                        <div style="display: flex; justify-content: end; align-items: center;">
                                            {{-- <div>
                                                    <h5 style="margin: 0 0 10px 0;">📊 Ready to Submit to HMRC</h5>
                                                    <div
                                                        style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; font-size: 14px;">
                                                        <div>
                                                            <strong>Period:</strong> {{ $periodKey ?? '#001' }}
                                                        </div>
                                                        <div>
                                                            <strong>Total VAT Due:</strong>
                                                            £{{ number_format($_box3Amount, 2) }}
                                                        </div>
                                                        <div>
                                                            <strong>Net VAT Due:</strong>
                                                            £{{ number_format($_box5Amount, 2) }}
                                                        </div>
                                                    </div>
                                                </div> --}}
                                            <div>
                                                <button type="button" class="teal-custom-btn p-2"
                                                    onclick="confirmSubmission()">
                                                    📤 Submit to HMRC
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            {{-- Your existing table sections --}}
                            <div id="tbl_exporttable_to_xls">
                                <!-- Summary Section -->
                                <div id="summary-section">
                                    <div id="tabletop">Summary</div>
                                    <table class="table table-border table-striped">
                                        <tbody>
                                            <tr>
                                                <th>Item</th>
                                                <th width="90">Box</th>
                                                <th width="60" style="text-align:center">Amount</th>
                                            </tr>
                                            <tr>
                                                <td>VAT due in this period on sales and other output.</td>
                                                <td width="90">1</td>
                                                <td width="60" style="text-align:center">
                                                    {{ number_format($_box1Amount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td width="90"></td>
                                                <td width="60" style="text-align:center"></td>
                                            </tr>
                                            <tr>
                                                <td>Vat due in this period on acquisitions from other</td>
                                                <td width="90">2</td>
                                                <td width="60" style="text-align:center">
                                                    {{ number_format($_box2Amount ?? 0, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>EC Member state</td>
                                                <td width="90"></td>
                                                <td width="60" style="text-align:center"></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td width="90"></td>
                                                <td width="60" style="text-align:center"></td>
                                            </tr>
                                            <tr>
                                                <td>Total VAT due</td>
                                                <td width="90">3</td>
                                                <td width="60" style="text-align:center">
                                                    {{ number_format($_box3Amount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td width="90"></td>
                                                <td width="60" style="text-align:center"></td>
                                            </tr>
                                            <tr>
                                                <td>VAT reclaimed in this period on purchases and other inputs
                                                    (including acquisitions from EC)</td>
                                                <td width="90">4</td>
                                                <td width="60" style="text-align:center">
                                                    {{ number_format($_box4Amount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td width="90"></td>
                                                <td width="60" style="text-align:center"></td>
                                            </tr>
                                            <tr>
                                                <td>Net vat to be paid to customs</td>
                                                <td width="90">5</td>
                                                <td width="60" style="text-align:center">
                                                    {{ number_format($_box5Amount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td width="90"></td>
                                                <td width="60" style="text-align:center"></td>
                                            </tr>
                                            <tr>
                                                <td>Total value of sales and all other outputs excluding any VAT.</td>
                                                <td width="90">6</td>
                                                <td width="60" style="text-align:center">
                                                    {{ number_format($_box6Amount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td width="90"></td>
                                                <td width="60" style="text-align:center"></td>
                                            </tr>
                                            <tr>
                                                <td>Total value of purchases and all other inputs excluding any VAT.</td>
                                                <td width="90">7</td>
                                                <td width="60" style="text-align:center">
                                                    {{ number_format($_box7Amount, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td width="90"></td>
                                                <td width="60" style="text-align:center"></td>
                                            </tr>
                                            <tr>
                                                <td>Total value of all supplies of goods and related costs,
                                                    excluding any VAT, to other EC Member States</td>
                                                <td width="90">8</td>
                                                <td width="60" style="text-align:center">
                                                    {{ number_format($_box8Amount ?? 0, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td width="90"></td>
                                                <td width="60" style="text-align:center"></td>
                                            </tr>
                                            <tr>
                                                <td>Total value of all acquisitions of goods and related costs.</td>
                                                <td width="90">9</td>
                                                <td width="60" style="text-align:center">
                                                    {{ number_format($_box9Amount ?? 0, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table><br>
                                </div>

                                <!-- Output VAT Section -->
                                <div id="output-section">
                                    <div id="tabletop">Vat Return - Output VAT</div>
                                    <table class="table table-border table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Ledger Ref</th>
                                                <th>Account Ref</th>
                                                <th>Description</th>
                                                <th style="text-align:center">Net</th>
                                                <th style="text-align:center">VAT</th>
                                                <th style="text-align:center;">Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($outputVatDetails as $value)
                                                <tr>
                                                    <td>
                                                        {{ \Carbon\Carbon::parse($value['date'])->format('d/m/Y') }}
                                                    </td>
                                                    <td style="padding: 0 0 0 8px;">
                                                        <a class="ledger-link"
                                                            href="javascript:void(0);">{{ $value['ledger_ref'] }}</a>
                                                    </td>
                                                    <td>{{ $value['account_ref'] }}</td>
                                                    <td>{{ $value['description'] }}</td>
                                                    <td align="center">{{ number_format((float) $value['net'], 2) }}</td>
                                                    <td align="center">{{ number_format((float) $value['vat'], 2) }}</td>
                                                    <td align="center">{{ $value['rate'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" align="right"><strong>Total</strong></td>
                                                <td align="center"><strong>{{ number_format($_box6Amount, 2) }}</strong>
                                                </td>
                                                <td align="center"><strong>{{ number_format($_box1Amount, 2) }}</strong>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table><br>
                                </div>

                                <!-- Input VAT Section -->
                                <div id="input-section">
                                    <div id="tabletop">Vat Return - Input VAT</div>
                                    <table class="table table-border table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Ledger Ref</th>
                                                <th>Account Ref</th>
                                                <th>Description</th>
                                                <th style="text-align:center">Net</th>
                                                <th style="text-align:center">VAT</th>
                                                <th style="text-align:center;">Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($inputVatDetails as $value)
                                                <tr>
                                                    <td>
                                                        {{ \Carbon\Carbon::parse($value['date'])->format('d/m/Y') }}
                                                    </td>
                                                    <td style="padding: 0 0 0 8px;">
                                                        <a class="ledger-link"
                                                            href="javascript:void(0);">{{ $value['ledger_ref'] }}</a>
                                                    </td>
                                                    <td>{{ $value['account_ref'] }}</td>
                                                    <td>{{ $value['description'] }}</td>
                                                    <td align="center">{{ number_format((float) $value['net'], 2) }}</td>
                                                    <td align="center">{{ number_format((float) $value['vat'], 2) }}</td>
                                                    <td align="center">{{ $value['rate'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" align="right"><strong>Total</strong></td>
                                                <td align="center"><strong>{{ number_format($_box7Amount, 2) }}</strong>
                                                </td>
                                                <td align="center"><strong>{{ number_format($_box4Amount, 2) }}</strong>
                                                </td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td colspan="2">Net VAT to be paid to Customs</td>
                                                <td align="center"><strong></strong></td>
                                                <td></td>
                                                <td align="center">
                                                    <strong>{{ number_format($_box5Amount, 2) }}</strong>
                                                </td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab navigation
        const navButtons = document.querySelectorAll('.nav-link-btn');
        const summarySection = document.getElementById('summary-section');
        const outputSection = document.getElementById('output-section');
        const inputSection = document.getElementById('input-section');

        navButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                navButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                const section = this.getAttribute('data-section');
                summarySection.style.display = 'block';
                outputSection.style.display = 'none';
                inputSection.style.display = 'none';

                if (section === 'complete-report') {
                    outputSection.style.display = 'block';
                    inputSection.style.display = 'block';
                } else if (section === 'in-put') {
                    inputSection.style.display = 'block';
                } else if (section === 'out-put') {
                    outputSection.style.display = 'block';
                }
            });
        });
    });

    // Confirmation before submission
    function confirmSubmission() {
        if (confirm('⚠️ Are you sure you want to submit this VAT return to HMRC?\n\nThis action cannot be undone.')) {
            document.getElementById('hmrc-submit-form').submit();
        }
    }
</script>
