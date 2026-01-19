@extends('admin.layout.app')

@section('title', 'Map Columns')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-1">Map Your Columns</h4>
                            <p class="mb-0 small">Map the 4 essential columns from your file</p>
                        </div>

                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Errors:</h6>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <!-- File Info -->
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>File:</strong> {{ $uploadedFile->original_filename }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Total Rows:</strong> {{ number_format($fileData['total_rows']) }}
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('bulk-transactions.mapping.save', $uploadedFile->id) }}" method="POST">
                                @csrf

                                <div class="row">
                                    <!-- Column Mapping -->
                                    <div class="col-md-6">
                                        <div class="card border-primary mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Column Mapping</h6>
                                            </div>
                                            <div class="card-body">
                                                <!-- Bank Account -->
                                                <div class="mb-3">
                                                    <label for="bank_account_id" class="form-label fw-bold">
                                                        Bank Account <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="bank_account_id" id="bank_account_id" class="form-select"
                                                        required>
                                                        <option value="">-- Select Bank Account --</option>
                                                        @foreach ($bankAccounts as $bank)
                                                            <option value="{{ $bank->Bank_Account_ID }}">
                                                                {{ $bank->Bank_Name }} -
                                                                {{ $bank->bankAccountType->Bank_Type ?? 'N/A' }}

                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <hr>

                                                <!-- Date Column -->
                                                <div class="mb-3">
                                                    <label for="date_column" class="form-label fw-bold">
                                                        Date <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="date_column" id="date_column" class="form-select"
                                                        required>
                                                        <option value="">-- Select Date Column --</option>
                                                        @foreach ($fileData['headers'] as $header)
                                                            <option value="{{ $header }}"
                                                                {{ ($fileData['auto_detected']['date'] ?? '') == $header ? 'selected' : '' }}>
                                                                {{ $header }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Amount Column -->
                                                <div class="mb-3">
                                                    <label for="amount_column" class="form-label fw-bold">
                                                        Amount <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="amount_column" id="amount_column" class="form-select"
                                                        required>
                                                        <option value="">-- Select Amount Column --</option>
                                                        @foreach ($fileData['headers'] as $header)
                                                            <option value="{{ $header }}"
                                                                {{ ($fileData['auto_detected']['amount'] ?? '') == $header ? 'selected' : '' }}>
                                                                {{ $header }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Description Column -->
                                                <div class="mb-3">
                                                    <label for="description_column" class="form-label fw-bold">
                                                        Description <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="description_column" id="description_column"
                                                        class="form-select" required>
                                                        <option value="">-- Select Description Column --</option>
                                                        @foreach ($fileData['headers'] as $header)
                                                            <option value="{{ $header }}"
                                                                {{ ($fileData['auto_detected']['description'] ?? '') == $header ? 'selected' : '' }}>
                                                                {{ $header }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Balance Column (Optional) -->
                                                <div class="mb-3">
                                                    <label for="balance_column" class="form-label fw-bold">
                                                        Balance <span class="badge bg-secondary">Optional</span>
                                                    </label>
                                                    <select name="balance_column" id="balance_column" class="form-select">
                                                        <option value="">-- Auto Calculate --</option>
                                                        @foreach ($fileData['headers'] as $header)
                                                            <option value="{{ $header }}"
                                                                {{ ($fileData['auto_detected']['balance'] ?? '') == $header ? 'selected' : '' }}>
                                                                {{ $header }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Preview -->
                                    <div class="col-md-6">
                                        <div class="card border-info mb-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">Preview</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered">
                                                        <thead class="table-dark">
                                                            <tr>
                                                                <th>Date</th>
                                                                <th>Amount</th>
                                                                <th>Description</th>
                                                                <th>Balance</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if (isset($fileData['essential_preview']))
                                                                @foreach ($fileData['essential_preview'] as $row)
                                                                    <tr>
                                                                        <td><small>{{ $row['Date'] ?? 'N/A' }}</small></td>
                                                                        <td class="text-end">
                                                                            @if (is_numeric($row['Amount'] ?? ''))
                                                                                @php $amt = (float)$row['Amount']; @endphp
                                                                                <small
                                                                                    class="{{ $amt < 0 ? 'text-danger' : 'text-success' }}">
                                                                                    {{ number_format($amt, 2) }}
                                                                                </small>
                                                                            @else
                                                                                <small>{{ $row['Amount'] ?? 'N/A' }}</small>
                                                                            @endif
                                                                        </td>
                                                                        <td><small>{{ Str::limit($row['Description'] ?? 'N/A', 30) }}</small>
                                                                        </td>
                                                                        <td class="text-end">
                                                                            @if (isset($row['calculated_balance']))
                                                                                <small
                                                                                    class="{{ $row['calculated_balance'] < 0 ? 'text-danger' : 'text-success' }}">
                                                                                    {{ number_format($row['calculated_balance'], 2) }}
                                                                                </small>
                                                                            @else
                                                                                <small>-</small>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @else
                                                                <tr>
                                                                    <td colspan="4" class="text-center">No preview
                                                                        available</td>
                                                                </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- How it Works -->
                                <div class="card border-info mb-3">
                                    <div class="card-body">
                                        <h6 class="text-info"><i class="fas fa-question-circle me-2"></i>How it works</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <p class="small mb-0">
                                                    <strong>1. Map Essential Fields:</strong><br>
                                                    Only 4 columns need mapping - we'll handle the rest automatically.
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="small mb-0">
                                                    <strong>2. All Data Preserved:</strong><br>
                                                    Every column from your file is stored in our database.
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <p class="small mb-0">
                                                    <strong>3. Review & Approve:</strong><br>
                                                    After processing, you'll review each transaction before final approval.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('bulk-transactions.upload') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        Process & Continue <i class="fas fa-arrow-right ms-2"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pass PHP data to JavaScript
            const fullPreviewData = @json($fullPreviewData ?? []);

            // Get all dropdown elements
            const dateDropdown = document.getElementById('date_column');
            const amountDropdown = document.getElementById('amount_column');
            const descriptionDropdown = document.getElementById('description_column');
            const balanceDropdown = document.getElementById('balance_column');

            // Update preview table
            function updatePreview() {
                const selectedDate = dateDropdown.value;
                const selectedAmount = amountDropdown.value;
                const selectedDescription = descriptionDropdown.value;
                const selectedBalance = balanceDropdown.value;

                // Get all preview table rows (skip header)
                const previewRows = document.querySelectorAll('.table tbody tr');

                previewRows.forEach((row, index) => {
                    if (fullPreviewData[index]) {
                        const rowData = fullPreviewData[index];
                        const cells = row.querySelectorAll('td');

                        // Update Date cell
                        if (cells[0] && selectedDate) {
                            const dateValue = rowData[selectedDate] || 'N/A';
                            cells[0].querySelector('small').textContent = formatDate(dateValue);
                        }

                        // Update Amount cell
                        if (cells[1] && selectedAmount) {
                            const amountValue = cleanAmount(rowData[selectedAmount] || 0);
                            const amountSmall = cells[1].querySelector('small');
                            amountSmall.textContent = formatNumber(amountValue);
                            amountSmall.className = amountValue < 0 ? 'text-danger' : 'text-success';
                        }

                        // Update Description cell
                        if (cells[2] && selectedDescription) {
                            const descValue = rowData[selectedDescription] || 'N/A';
                            cells[2].querySelector('small').textContent = truncate(descValue, 30);
                        }

                        // Update Balance cell (if balance column selected)
                        if (cells[3]) {
                            if (selectedBalance) {
                                const balanceValue = cleanAmount(rowData[selectedBalance] || 0);
                                const balanceSmall = cells[3].querySelector('small');
                                balanceSmall.textContent = formatNumber(balanceValue);
                                balanceSmall.className = balanceValue < 0 ? 'text-danger' : 'text-success';
                            } else {
                                // Show calculated balance if no balance column selected
                                cells[3].querySelector('small').textContent = '-';
                            }
                        }
                    }
                });
            }

            // Helper: Clean amount (remove currency symbols)
            function cleanAmount(value) {
                if (typeof value === 'number') return value;
                const cleaned = String(value).replace(/[£$€,\s]/g, '');
                if (/^\((.+)\)$/.test(cleaned)) {
                    return -1 * parseFloat(RegExp.$1);
                }
                return parseFloat(cleaned) || 0;
            }

            // Helper: Format number
            function formatNumber(num) {
                return new Intl.NumberFormat('en-GB', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(num);
            }

            // Helper: Format date
            function formatDate(dateStr) {
                if (!dateStr || dateStr === 'N/A') return dateStr;
                try {
                    const date = new Date(dateStr);
                    return date.toLocaleDateString('en-GB');
                } catch (e) {
                    return dateStr;
                }
            }

            // Helper: Truncate string
            function truncate(str, length) {
                return str.length > length ? str.substring(0, length) + '...' : str;
            }

            // Attach event listeners
            dateDropdown.addEventListener('change', updatePreview);
            amountDropdown.addEventListener('change', updatePreview);
            descriptionDropdown.addEventListener('change', updatePreview);
            balanceDropdown.addEventListener('change', updatePreview);

            // Initial update on page load
            updatePreview();
        });
    </script>
@endsection
