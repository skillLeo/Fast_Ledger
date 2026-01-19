@extends('admin.layout.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/file-opening-book/file-opening-book.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/employee-form.css') }}">
@endpush

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between d-flex">
                            <span class="page-title">Clients</span>

                        </div>

                        <!-- Filters -->
                        @include('admin.file_opening_book._partials._filters')

                    </div>

                    <!-- Matter Book Content -->
                    <div class="content-section mx-custom" id="matters-content">
                        <!-- Left Side - Consolidated Table -->
                        <div class="left-side" style="border: 1px solid #dee2e6 !important;">
                            <div class="ledger-table-container rounded-0 p-4">
                                <!-- Fixed Header Table -->
                                <table class="table account-table">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left; padding-left:4px !important; width: auto;">Ledger
                                                Name</th>
                                            <th style="width: 91px;">BAL (office)</th>
                                            <th style="width: 97px;">BAL (client)</th>
                                        </tr>
                                    </thead>
                                </table>

                                <!-- Scrollable Body Wrapper -->
                                <div class="ledger-tbody-wrapper">
                                    <table class="table account-table">
                                        <colgroup>
                                            <col style="width: auto;">
                                            <col style="width: 90px;">
                                            <col style="width: 90px;">
                                        </colgroup>
                                        <tbody id="ledger-table-body">
                                            @forelse($files as $file)
                                                <tr class="ledger-row" data-ledger-ref="{{ $file->Ledger_Ref }}"
                                                    data-office-balance="{{ $file->office_balance ?? 0 }}"
                                                    data-client-balance="{{ $file->client_balance ?? 0 }}">
                                                    <td data-column="ledgerref" style="text-align: left">
                                                        {{ $file->Ledger_Ref }} - {{ $file->First_Name }}
                                                        {{ $file->Last_Name }}
                                                    </td>
                                                    <td data-column="office-balance">
                                                        {{ number_format($file->office_balance ?? 0, 2) }}
                                                    </td>
                                                    <td data-column="client-balance">
                                                        {{ number_format($file->client_balance ?? 0, 2) }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center">No files found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side - Details -->
                        <div class="right-side  p-4" style="border: 1px solid #dee2e6 !important;">

                            <!-- Client Information Section -->
                            <div class="client-info-section" id="client-info" style="display: none;">
                                <div class="align-items-center mb-2">
                                    <!-- Row 1: Name - Ledger Ref -->
                                    <div class="d-flex align-items-center">
                                        <h5 class="mb-0" id="client-name">-</h5>
                                        <span class="mx-2">-</span>
                                        <h5 class="mb-0" id="ledger-ref-badge" style="font-weight: 500;">-</h5>
                                    </div>

                                    <!-- Row 2: Address and Contact -->
                                    <div>
                                        <small class="me-3" id="client-address">-</small>
                                        <small class="" id="client-contact">-</small>
                                    </div>

                                    <!-- Row 3: Email, Matter, Fee Earner, Status -->
                                    <div>
                                        <small class="me-3" id="email">-</small>
                                        <small class="me-3" id="matter">-</small>
                                        <small class="me-3" id="fee-earner">-</small>
                                        <small class="" id="status">-</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Tables Container -->
                            <div id="tables-container-wrapper">
                                <div class="table-container" style="width: 100%;">
                                    <!-- Fixed Header Table -->
                                    <table class="table account-table mb-0"
                                        style="width: 100%; table-layout: fixed !important;">
                                        <colgroup>
                                            <col style="width: 10%;">
                                            <col style="width: 25%;">
                                            <col style="width: 10%;">
                                            <col style="width: 10%;">
                                            <col style="width: 10%;">
                                            <col style="width: 10%;">
                                            <col style="width: 10%;">
                                            <col style="width: 15%;">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th colspan="2" class="details-head text-center">Details</th>
                                                <th colspan="3" class="office-head text-center">Office Account</th>
                                                <th colspan="3" class="client-head text-center">Client Account</th>
                                            </tr>
                                            <tr>
                                                <th class="details-head text-center">Date</th>
                                                <th class="details-head text-center">Description</th>
                                                <th class="office-head text-end">Debit</th>
                                                <th class="office-head text-end">Credit</th>
                                                <th class="office-head text-end">Balance</th>
                                                <th class="client-head text-end">Debit</th>
                                                <th class="client-head text-end">Credit</th>
                                                <th class="client-head text-end">Balance</th>
                                            </tr>
                                        </thead>
                                    </table>

                                    <!-- Scrollable Body Wrapper -->
                                    <div id="tables-container">
                                        <table class="table account-table mb-0"
                                            style="width: 100%; table-layout: fixed !important;">
                                            <colgroup>
                                                <col style="width: 10%;">
                                                <col style="width: 25%;">
                                                <col style="width: 10%;">
                                                <col style="width: 10%;">
                                                <col style="width: 10%;">
                                                <col style="width: 10%;">
                                                <col style="width: 10%;">
                                                <col style="width: 15%;">
                                            </colgroup>
                                            <tbody id="combined-table-body">
                                                <!-- Dynamic content -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Suppliers Content -->
                    <div class="content-section mx-custom" id="suppliers-content" style="display: none;">
                        @include('admin.file_opening_book._partials._suppliers._supplier-list')
                        <div class="right-side p-4" style="border: 1px solid #dee2e6 !important;">
                            @include('admin.file_opening_book._partials._suppliers._supplier-info')
                            @include('admin.file_opening_book._partials._suppliers._supplier-transactions')

                            {{-- ✅ Add Supplier Details Form --}}
                            @include('admin.file_opening_book._partials._suppliers._supplier-details-form')
                        </div>
                    </div>

                    <!-- Employees Content -->
                    <div class="content-section mx-custom" id="employees-content" style="display: none;">
                        @include('admin.file_opening_book._partials._employees._employee-list')

                        <div class="right-side p-4" style="border: 1px solid #dee2e6 !important;">
                            @include('admin.file_opening_book._partials._employees._employee-info')
                            @include('admin.file_opening_book._partials._employees._employee-transactions')

                            {{-- Employee Details Form (NEW) --}}
                            @include('admin.file_opening_book._partials._employees._employee-details-form')

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('admin/js/file-opening-book.js') }}"></script>
@endsection
