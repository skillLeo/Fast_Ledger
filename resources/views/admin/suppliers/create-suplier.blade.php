@extends('admin.layout.app')
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    @include('admin.partial.errors')
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="page-title mb-0">Agent Admin Setup</h4>
                            <div>
                                <button type="submit" form="agentAdminForm" class="btn btn-success btn-wave" id="saveAllBtn">
                                    <i class="fas fa-save"></i> Save All Sections
                                </button>
                                <a href="{{ route('clients.index') }}" class="btn btn-secondary btn-wave ms-2"
                                    role="button">
                                    Back to List
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="row g-0 main-row">
                                <!-- Sidebar Navigation -->
                                <div class="col-md-3 bg-light border-end sidebar-column">
                                    <div class="sidebar-nav p-3 sticky-sidebar">
                                        <nav class="nav flex-column" id="sectionNav">
                                            <a class="nav-link active" href="#contact-details"
                                                data-section="contact-details">
                                                <i class="fas fa-user me-2"></i> Contact details
                                            </a>
                                            <a class="nav-link" href="#addresses" data-section="addresses">
                                                <i class="fas fa-map-marker-alt me-2"></i> Addresses
                                            </a>
                                            <a class="nav-link" href="#financial-details" data-section="financial-details">
                                                <i class="fas fa-credit-card me-2"></i> Financial details
                                            </a>
                                            <a class="nav-link" href="#bank-accounts" data-section="bank-accounts">
                                                <i class="fas fa-university me-2"></i> Bank Accounts
                                            </a>
                                            <a class="nav-link" href="#company-deadlines" data-section="company-deadlines">
                                                <i class="fas fa-calendar-alt me-2"></i> Company Deadlines
                                            </a>
                                            <a class="nav-link" href="#accounts-package" data-section="accounts-package">
                                                <i class="fas fa-box me-2"></i> Accounts Package
                                            </a>
                                            <a class="nav-link" href="#engagement-details"
                                                data-section="engagement-details">
                                                <i class="fas fa-handshake me-2"></i> Engagement Details
                                            </a>
                                            <a class="nav-link" href="#login-details" data-section="login-details">
                                                <i class="fas fa-key me-2"></i> Login Details
                                            </a>
                                        </nav>

                                        <!-- Progress Indicator -->
                                        <div class="mt-4 p-3 bg-white rounded">
                                            <small class="text-muted d-block mb-2">Form Progress</small>
                                            <div class="progress mb-2" style="height: 6px;">
                                                <div class="progress-bar progress-bar-striped" role="progressbar"
                                                    style="width: 0%" id="formProgress"></div>
                                            </div>
                                            <small class="text-muted" id="progressText">0% Complete</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Main Content Area -->
                                <div class="col-md-9 main-content-column">
                                    <div class="container py-4" id="formContainer">
                                        <h1 class="mb-4 fw-bold">Agent Admin Setup</h1>
                                        <form id="agentAdminForm" method="POST">
                                            @csrf
                                            <div class="row">
                                                <!-- LEFT COLUMN -->
                                                <div class="col-lg-6">
                                                    <!-- Contact Details Section -->
                                                    <div class="section-content" id="contact-details">
                                                        <div class="card mb-4">
                                                            <div class="card-header fw-semibold">Company Information</div>
                                                            <div class="card-body">
                                                                <div class="mb-3">
                                                                    <label for="first-name" class="form-label">First
                                                                        Name</label>
                                                                    <input type="text" id="first-name" name="first_name"
                                                                        class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="last-name" class="form-label">Last
                                                                        Name</label>
                                                                    <input type="text" id="last-name" name="last_name"
                                                                        class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="email" class="form-label">Email</label>
                                                                    <input type="email" id="email" name="email"
                                                                        class="form-control" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="contact-name" class="form-label">Contact
                                                                        Name</label>
                                                                    <input type="text" id="contact-name"
                                                                        name="contact_name" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="account-number" class="form-label">Account
                                                                        Numnber</label>
                                                                    </label>
                                                                    <input type="text" id="account-number"
                                                                        name="account_number" class="form-control"
                                                                        required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="telephone" class="form-label">Phone
                                                                        Number</label>
                                                                    <input type="tel" id="phone-number"
                                                                        name="phone_number" class="form-control">
                                                                </div>
                                                                {{-- <div class="mb-3">
                                                                    <label for="mobile"
                                                                        class="form-label">Mobile</label>
                                                                    <input type="tel" id="mobile" name="mobile"
                                                                        class="form-control">
                                                                </div> --}}

                                                                <div class="mb-3">
                                                                    <label for="website"
                                                                        class="form-label">Website</label>
                                                                    <input type="url" id="website" name="website"
                                                                        class="form-control">
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="company-reg" class="form-label">Company
                                                                        Reg No</label>
                                                                    <input type="text" id="company-reg"
                                                                        name="company_reg" class="form-control">
                                                                </div>


                                                                <div class="mb-3">
                                                                    <label for="address-details" class="form-label">
                                                                        Notes</label>
                                                                    <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                                                                </div>



                                                                <div class="mb-3">
                                                                    <label for="address-details"
                                                                        class="form-label">Address
                                                                        Details</label>
                                                                    <input type="text" id="address" name="address"
                                                                        class="form-control">
                                                                </div>


                                                                <div class="mb-3">
                                                                    <label for="paye-ref" class="form-label">PAYE Ref
                                                                        No</label>
                                                                    <input type="text" id="paye-ref" name="paye_ref"
                                                                        class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="paye-acc" class="form-label">PAYE Acc
                                                                        Office Ref</label>
                                                                    <input type="text" id="paye-acc" name="paye_acc"
                                                                        class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="payroll-freq" class="form-label">Payroll
                                                                        Frequency</label>
                                                                    <select id="payroll-freq" name="payroll_freq"
                                                                        class="form-select">
                                                                        <option value="">Select</option>
                                                                        <option value="weekly">Weekly</option>
                                                                        <option value="monthly">Monthly</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="pension-details" class="form-label">
                                                                        Pension Details</label>
                                                                    <input type="text" id="pension-details"
                                                                        name="pension_details" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="pension-scheme" class="form-label">Pension
                                                                        Scheme</label>
                                                                    <input type="text" id="pension-scheme"
                                                                        name="pension_scheme" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="vat-number" class="form-label">VAT
                                                                        Number</label>
                                                                    <input type="text" id="vat-number"
                                                                        name="vat_number" class="form-control">
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="company-auth" class="form-label">Company
                                                                        Auth Code</label>
                                                                    <input type="text" id="company-auth"
                                                                        name="company_auth" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="company-utr" class="form-label">Company
                                                                        UTR No</label>
                                                                    <input type="text" id="company-utr"
                                                                        name="company_utr" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="client-referral" class="form-label">Client
                                                                        Referral</label>
                                                                    <input type="text" id="client-referral"
                                                                        name="client_referral" class="form-control">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Addresses Section -->
                                                    <div class="section-content" id="addresses">
                                                        <div class="card mb-4">
                                                            <div class="card-header fw-semibold">Addresses</div>
                                                            <div class="card-body">
                                                                <div class="mb-3">
                                                                    <label for="address-details"
                                                                        class="form-label">Address Details</label>
                                                                    <textarea id="address-details" name="address_details" class="form-control" rows="3"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Financial Details Section -->
                                                    <div class="section-content" id="financial-details">
                                                        <div class="card mb-4">
                                                            <div class="card-header fw-semibold">Financial Details</div>
                                                            <div class="card-body">
                                                                <div class="mb-3">
                                                                    <label for="financial-info"
                                                                        class="form-label">Financial Information</label>
                                                                    <textarea id="financial-info" name="financial_info" class="form-control" rows="3"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Engagement Details -->
                                                    <div class="section-content" id="engagement-details">
                                                        <div class="card mb-4">
                                                            <div class="card-header fw-semibold">Engagement Details</div>
                                                            <div class="card-body">
                                                                <div class="mb-3">
                                                                    <label for="status"
                                                                        class="form-label">Status</label>
                                                                    <input type="text" id="status" name="status"
                                                                        class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="engagement-letter"
                                                                        class="form-label">Engagement Letter</label>
                                                                    <input type="text" id="engagement-letter"
                                                                        name="engagement_letter" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="engagement-latest"
                                                                        class="form-label">Engagement Latest Date</label>
                                                                    <input type="date" id="engagement-latest"
                                                                        name="engagement_latest" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="client-lost" class="form-label">Date of
                                                                        Client Lost</label>
                                                                    <input type="date" id="client-lost"
                                                                        name="client_lost" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="authority-letter"
                                                                        class="form-label">Authority Letter(64-8)</label>
                                                                    <input type="text" id="authority-letter"
                                                                        name="authority_letter" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="authority-status"
                                                                        class="form-label">Authority Letter Status</label>
                                                                    <input type="text" id="authority-status"
                                                                        name="authority_status" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="authority-completion"
                                                                        class="form-label">Authority Completion
                                                                        Date</label>
                                                                    <input type="date" id="authority-completion"
                                                                        name="authority_completion" class="form-control">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- RIGHT COLUMN -->
                                                <div class="col-lg-6">
                                                    <!-- Bank Accounts -->
                                                    <div class="section-content" id="bank-accounts">
                                                        <div class="card mb-4">
                                                            <div class="card-header fw-semibold">Bank Accounts</div>
                                                            <div class="card-body">
                                                                <div id="bankAccountsContainer">
                                                                    <div class="bank-account-item border rounded p-3 mb-3">
                                                                        <div class="mb-3">
                                                                            <label for="bank-type" class="form-label">Bank
                                                                                Type</label>
                                                                            <select id="bank-type"
                                                                                name="bank_accounts[0][type]"
                                                                                class="form-select">
                                                                                <option value="">Select</option>
                                                                                <option value="office">Office Account
                                                                                </option>
                                                                                <option value="client">Client Account
                                                                                </option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="bank-name" class="form-label">Bank
                                                                                Name</label>
                                                                            <input type="text" id="bank-name"
                                                                                name="bank_accounts[0][name]"
                                                                                class="form-control">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="sort-code" class="form-label">Sort
                                                                                Code</label>
                                                                            <input type="text" id="sort-code"
                                                                                name="bank_accounts[0][sort_code]"
                                                                                class="form-control">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="account-no"
                                                                                class="form-label">Account No</label>
                                                                            <input type="text" id="account-no"
                                                                                name="bank_accounts[0][account_no]"
                                                                                class="form-control">
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label for="bank-address"
                                                                                class="form-label">Bank Address</label>
                                                                            <input type="text" id="bank-address"
                                                                                name="bank_accounts[0][address]"
                                                                                class="form-control">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="text-end">
                                                                    <button type="button" id="addBankAccount"
                                                                        class="btn btn-sm btn-outline-primary">
                                                                        <i class="fas fa-plus me-1"></i> Add Bank Account
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Company Deadlines -->
                                                    <div class="section-content" id="company-deadlines">
                                                        <div class="card mb-4">
                                                            <div class="card-header fw-semibold">Company Deadlines</div>
                                                            <div class="card-body">
                                                                <div class="mb-3">
                                                                    <label for="payroll-pension"
                                                                        class="form-label">Payroll & Pension</label>
                                                                    <input type="date" id="payroll-pension"
                                                                        name="payroll_pension" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="vat-returns" class="form-label">VAT
                                                                        Returns</label>
                                                                    <input type="date" id="vat-returns"
                                                                        name="vat_returns" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="confirmation-statement"
                                                                        class="form-label">Confirmation Statement</label>
                                                                    <input type="date" id="confirmation-statement"
                                                                        name="confirmation_statement"
                                                                        class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="annual-accounts" class="form-label">Annual
                                                                        Accounts</label>
                                                                    <input type="date" id="annual-accounts"
                                                                        name="annual_accounts" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="company-tax-returns"
                                                                        class="form-label">Company Tax Returns</label>
                                                                    <input type="date" id="company-tax-returns"
                                                                        name="company_tax_returns" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="sra-audit" class="form-label">SRA Audit
                                                                        Date</label>
                                                                    <input type="date" id="sra-audit" name="sra_audit"
                                                                        class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="statutory-audit"
                                                                        class="form-label">Statutory Audit Date</label>
                                                                    <input type="date" id="statutory-audit"
                                                                        name="statutory_audit" class="form-control">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Accounts Package -->
                                                    <div class="section-content" id="accounts-package">
                                                        <div class="card mb-4">
                                                            <div class="card-header fw-semibold">Accounts Package</div>
                                                            <div class="card-body">
                                                                <div class="mb-3">
                                                                    <label for="bookkeeping"
                                                                        class="form-label">Bookkeeping</label>
                                                                    <input type="text" id="bookkeeping"
                                                                        name="bookkeeping" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="payroll-pkg"
                                                                        class="form-label">Payroll</label>
                                                                    <input type="text" id="payroll-pkg"
                                                                        name="payroll_pkg" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="vat-returns-pkg" class="form-label">VAT
                                                                        Returns</label>
                                                                    <input type="text" id="vat-returns-pkg"
                                                                        name="vat_returns_pkg" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="confirmation-statement-pkg"
                                                                        class="form-label">Confirmation Statement</label>
                                                                    <input type="text" id="confirmation-statement-pkg"
                                                                        name="confirmation_statement_pkg"
                                                                        class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="annual-accounts-pkg"
                                                                        class="form-label">Annual Accounts</label>
                                                                    <input type="text" id="annual-accounts-pkg"
                                                                        name="annual_accounts_pkg" class="form-control">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="company-tax-returns-pkg"
                                                                        class="form-label">Company Tax Returns</label>
                                                                    <input type="text" id="company-tax-returns-pkg"
                                                                        name="company_tax_returns_pkg"
                                                                        class="form-control">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Login Details -->
                                                    <div class="section-content" id="login-details">
                                                        <div class="card mb-4">
                                                            <div class="card-header fw-semibold">Login Details</div>
                                                            <div class="card-body">
                                                                <div class="mb-3">
                                                                    <label for="username"
                                                                        class="form-label">Username</label>
                                                                    <input type="text" id="username" name="username"
                                                                        class="form-control" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="password"
                                                                        class="form-label">Password</label>
                                                                    <div class="input-group">
                                                                        <input type="password" id="password"
                                                                            name="password" class="form-control" required>
                                                                        <button type="button"
                                                                            class="btn btn-outline-secondary"
                                                                            id="togglePassword">
                                                                            <i class="fas fa-eye"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="confirm-password"
                                                                        class="form-label">Confirm Password</label>
                                                                    <div class="input-group">
                                                                        <input type="password" id="confirm-password"
                                                                            name="confirm_password" class="form-control"
                                                                            required>
                                                                        <button type="button"
                                                                            class="btn btn-outline-secondary"
                                                                            id="toggleConfirmPassword">
                                                                            <i class="fas fa-eye"></i>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Submit Buttons -->
                                            <div class="text-center mt-4">
                                                <button type="submit" class="btn btn-primary btn-lg me-2">Save
                                                    Setup</button>
                                                <button type="reset" class="btn btn-secondary btn-lg"
                                                    id="resetBtn">Reset Form</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Main layout with proper height management */
        .main-content {
            min-height: 100vh;
        }

        .main-row {
            min-height: calc(100vh - 200px);
        }

        /* Sticky Sidebar */
        .sidebar-column {
            position: relative;
        }

        .sticky-sidebar {
            position: sticky;
            top: 20px;
            height: calc(100vh - 40px);
            overflow-y: auto;
            background: #f8f9fa;
        }

        /* Main content area scrolling */
        .main-content-column {
            max-height: calc(100vh - 120px);
            overflow-y: auto;
        }

        #formContainer {
            scroll-behavior: smooth;
        }

        /* Sidebar styling */
        .sidebar-nav {
            background: #f8f9fa;
        }

        .sidebar-nav .nav-link {
            color: #495057;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 4px;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .sidebar-nav .nav-link:hover {
            background-color: #e9ecef;
            color: #007bff;
        }

        .sidebar-nav .nav-link.active {
            background-color: #007bff;
            color: white;
            font-weight: 500;
        }

        .sidebar-nav .nav-link i {
            width: 20px;
            font-size: 14px;
        }

        .section-content {
            min-height: 200px;
            scroll-margin-top: 20px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        .bank-account-item {
            background: #f8f9fa;
            position: relative;
        }

        .remove-item {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            color: #dc3545;
            font-size: 18px;
            cursor: pointer;
            z-index: 10;
        }

        .remove-item:hover {
            color: #c82333;
        }

        .border-end {
            border-right: 1px solid #dee2e6 !important;
        }

        .progress-bar {
            transition: width 0.3s ease;
        }

        .is-valid {
            border-color: #28a745;
        }

        .is-invalid {
            border-color: #dc3545;
        }

        /* Floating Save Button */
        #saveAllBtn {
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        @media (max-width: 768px) {
            .sticky-sidebar {
                position: static;
                height: auto;
            }

            .main-content-column {
                max-height: none;
                overflow-y: visible;
            }

            .sidebar-nav .nav {
                flex-direction: row;
                overflow-x: auto;
                white-space: nowrap;
            }

            .sidebar-nav .nav-link {
                white-space: nowrap;
                margin-right: 8px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let bankAccountCount = 1;

            const form = document.getElementById('agentAdminForm');
            const formContainer = document.getElementById('formContainer');
            const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
            const sections = document.querySelectorAll('.section-content');
            const progressBar = document.getElementById('formProgress');
            const progressText = document.getElementById('progressText');
            const saveAllBtn = document.getElementById('saveAllBtn');
            const resetBtn = document.getElementById('resetBtn');

            // Initialize scroll spy
            initScrollSpy();
            updateProgress();

            // Scroll spy functionality
            function initScrollSpy() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && entry.intersectionRatio > 0.3) {
                            const sectionId = entry.target.id;
                            updateActiveNavLink(sectionId);
                        }
                    });
                }, {
                    root: formContainer,
                    rootMargin: '-20% 0px -60% 0px',
                    threshold: [0.3]
                });

                sections.forEach(section => {
                    observer.observe(section);
                });
            }

            // Update active navigation link
            function updateActiveNavLink(sectionId) {
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('data-section') === sectionId) {
                        link.classList.add('active');
                    }
                });
            }

            // Smooth scroll to section
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetSection = this.getAttribute('data-section');
                    const targetElement = document.getElementById(targetSection);

                    if (targetElement) {
                        const containerRect = formContainer.getBoundingClientRect();
                        const targetRect = targetElement.getBoundingClientRect();
                        const offsetTop = targetRect.top - containerRect.top + formContainer
                            .scrollTop - 20;

                        formContainer.scrollTo({
                            top: offsetTop,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Calculate and update form progress
            function updateProgress() {
                const totalFields = form.querySelectorAll('input, select, textarea').length;
                const filledFields = Array.from(form.querySelectorAll('input, select, textarea'))
                    .filter(field => field.value.trim() !== '').length;

                const percentage = totalFields > 0 ? Math.round((filledFields / totalFields) * 100) : 0;

                progressBar.style.width = percentage + '%';
                progressText.textContent = percentage + '% Complete';

                // Update progress bar color based on completion
                progressBar.className = 'progress-bar progress-bar-striped';
                if (percentage < 25) {
                    progressBar.classList.add('bg-danger');
                } else if (percentage < 50) {
                    progressBar.classList.add('bg-warning');
                } else if (percentage < 75) {
                    progressBar.classList.add('bg-info');
                } else {
                    progressBar.classList.add('bg-success');
                }
            }

            // Add bank account functionality
            if (document.getElementById('addBankAccount')) {
                document.getElementById('addBankAccount').addEventListener('click', function() {
                    const container = document.getElementById('bankAccountsContainer');
                    const newBankAccount = document.querySelector('.bank-account-item').cloneNode(true);

                    // Update field names with new index
                    const inputs = newBankAccount.querySelectorAll('input, select, textarea');
                    inputs.forEach(input => {
                        const name = input.getAttribute('name');
                        if (name) {
                            input.setAttribute('name', name.replace('[0]',
                                `[${bankAccountCount}]`));
                            input.value = '';
                            input.id = input.id + '_' + bankAccountCount;
                        }
                    });

                    // Update labels
                    const labels = newBankAccount.querySelectorAll('label');
                    labels.forEach(label => {
                        const forAttr = label.getAttribute('for');
                        if (forAttr) {
                            label.setAttribute('for', forAttr + '_' + bankAccountCount);
                        }
                    });

                    // Add remove button
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'remove-item';
                    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                    removeBtn.onclick = function() {
                        this.parentElement.remove();
                        updateProgress();
                    };
                    newBankAccount.appendChild(removeBtn);

                    container.appendChild(newBankAccount);
                    bankAccountCount++;
                    updateProgress();
                });
            }

            // Password toggle functionality
            if (document.getElementById('togglePassword')) {
                document.getElementById('togglePassword').addEventListener('click', function() {
                    const password = document.getElementById('password');
                    const icon = this.querySelector('i');

                    if (password.type === 'password') {
                        password.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        password.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            }

            if (document.getElementById('toggleConfirmPassword')) {
                document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
                    const confirmPassword = document.getElementById('confirm-password');
                    const icon = this.querySelector('i');

                    if (confirmPassword.type === 'password') {
                        confirmPassword.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        confirmPassword.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            }

            // Form validation
            function validateForm() {
                let isValid = true;
                const requiredFields = form.querySelectorAll('[required]');

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        field.classList.remove('is-invalid');
                        field.classList.add('is-valid');
                    }
                });

                // Password confirmation validation
                const password = document.getElementById('password');
                const confirmPassword = document.getElementById('confirm-password');

                if (password && confirmPassword && password.value && confirmPassword.value) {
                    if (password.value !== confirmPassword.value) {
                        confirmPassword.classList.add('is-invalid');
                        isValid = false;
                    } else {
                        confirmPassword.classList.remove('is-invalid');
                        confirmPassword.classList.add('is-valid');
                    }
                }

                return isValid;
            }

            // Real-time validation and progress update
            form.addEventListener('input', function(e) {
                updateProgress();

                if (e.target.hasAttribute('required')) {
                    if (e.target.value.trim()) {
                        e.target.classList.remove('is-invalid');
                        e.target.classList.add('is-valid');
                    } else {
                        e.target.classList.remove('is-valid');
                        e.target.classList.add('is-invalid');
                    }
                }

                // Email validation
                if (e.target.type === 'email' && e.target.value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (emailRegex.test(e.target.value)) {
                        e.target.classList.remove('is-invalid');
                        e.target.classList.add('is-valid');
                    } else {
                        e.target.classList.remove('is-valid');
                        e.target.classList.add('is-invalid');
                    }
                }

                // Password confirmation check
                if (e.target.id === 'confirm-password' || e.target.id === 'password') {
                    const password = document.getElementById('password');
                    const confirmPassword = document.getElementById('confirm-password');

                    if (password && confirmPassword && password.value && confirmPassword.value) {
                        if (password.value === confirmPassword.value) {
                            confirmPassword.classList.remove('is-invalid');
                            confirmPassword.classList.add('is-valid');
                        } else {
                            confirmPassword.classList.remove('is-valid');
                            confirmPassword.classList.add('is-invalid');
                        }
                    }
                }
            });

            // Sort code formatting
            document.addEventListener('input', function(e) {
                if (e.target.name && e.target.name.includes('sort_code')) {
                    let value = e.target.value.replace(/\D/g, ''); // Remove non-digits
                    if (value.length >= 2) {
                        value = value.substring(0, 2) + '-' + value.substring(2);
                    }
                    if (value.length >= 5) {
                        value = value.substring(0, 5) + '-' + value.substring(5, 7);
                    }
                    e.target.value = value;
                }
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                if (validateForm()) {
                    // Show loading state
                    saveAllBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving All Sections...';
                    saveAllBtn.disabled = true;

                    // Simulate form submission (replace with actual submission)
                    setTimeout(() => {
                        alert('Agent Admin Setup saved successfully!');

                        // Reset button state
                        saveAllBtn.innerHTML = '<i class="fas fa-save"></i> Save All Sections';
                        saveAllBtn.disabled = false;

                        // Uncomment the line below to actually submit the form
                        // this.submit();
                    }, 2000);
                } else {
                    // Scroll to first invalid field
                    const firstInvalidField = form.querySelector('.is-invalid');
                    if (firstInvalidField) {
                        const containerRect = formContainer.getBoundingClientRect();
                        const fieldRect = firstInvalidField.getBoundingClientRect();
                        const offsetTop = fieldRect.top - containerRect.top + formContainer.scrollTop - 100;

                        formContainer.scrollTo({
                            top: offsetTop,
                            behavior: 'smooth'
                        });
                        firstInvalidField.focus();
                    }

                    // Show validation message
                    showToast('Please fill in all required fields before saving.', 'danger');
                }
            });

            // Reset form
            if (resetBtn) {
                resetBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to reset the form? All data will be lost.')) {
                        form.reset();

                        // Remove validation classes
                        form.querySelectorAll('.is-valid, .is-invalid').forEach(field => {
                            field.classList.remove('is-valid', 'is-invalid');
                        });

                        updateProgress();
                        localStorage.removeItem('agentAdminFormData');
                    }
                });
            }

            // Auto-save functionality
            let autoSaveTimeout;
            form.addEventListener('input', function() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(() => {
                    localStorage.setItem('agentAdminFormData', JSON.stringify(getFormData()));
                }, 3000);
            });

            // Get form data
            function getFormData() {
                const formData = new FormData(form);
                const data = {};
                for (let [key, value] of formData.entries()) {
                    data[key] = value;
                }
                return data;
            }

            // Show toast notification
            function showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
                toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                toast.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
                `;
                document.body.appendChild(toast);

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 5000);
            }

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    switch (e.key) {
                        case 's':
                            e.preventDefault();
                            saveAllBtn.click();
                            break;
                    }
                }
            });
        });
    </script>
@endsection
