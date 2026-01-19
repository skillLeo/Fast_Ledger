@extends('admin.layout.app')

@section('title', 'Bulk Transaction Import')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Upload Bank Statement Card -->
                    <div class="card">
                        <div class="card-header text-center py-4">
                            <i class="fas fa-file-upload fa-3x text-primary mb-3"></i>
                            <h4 class="mb-2">Upload Bank Statement</h4>
                            <p class="text-muted mb-0">Upload your bank statement file (CSV or Excel) to import transactions
                                into your account.</p>
                        </div>

                        <div class="card-body p-4">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form action="{{ route('bulk-transactions.upload.post') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf

                                <!-- File Upload Section -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Bank Statement File</label>

                                    <div class="file-upload-area" onclick="document.getElementById('file').click()">
                                        <div id="file-upload-content">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                            <h6>Choose a CSV or Excel file containing your bank statement data</h6>
                                            <p class="text-muted mb-0">Maximum file size is 10MB.</p>
                                            <button type="button" class="btn btn-outline-primary mt-3">
                                                <i class="fas fa-folder-open me-2"></i>Choose File
                                            </button>
                                        </div>
                                        <div id="file-selected-content" style="display: none;">
                                            <i class="fas fa-file-alt fa-3x text-success mb-3"></i>
                                            <h6 id="selected-file-name">File selected</h6>
                                            <p id="selected-file-size" class="text-muted mb-0"></p>
                                            <button type="button" class="btn btn-outline-danger mt-2"
                                                onclick="clearFile()">
                                                <i class="fas fa-times me-2"></i>Remove
                                            </button>
                                        </div>
                                    </div>

                                    <input type="file" name="file" id="file" class="d-none"
                                        accept=".csv,.xlsx,.xls" required>

                                    <div class="file-info mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Supported formats: CSV, Excel (.xlsx, .xls) - Any format with transaction data
                                        </small>
                                    </div>

                                    @error('file')
                                        <div class="text-danger mt-2">
                                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back
                                    </a>

                                    <button type="submit" class="btn btn-primary btn-lg" id="uploadBtn" disabled>
                                        <i class="fas fa-upload me-2"></i>Upload & Continue
                                    </button>
                                </div>
                            </form>

                            <!-- Download Sample Templates -->
                            {{-- <div class="text-center mt-4 pt-4 border-top">
                            <h6 class="text-muted mb-3">Need a sample template?</h6>
                            <div class="btn-group">
                                <a href="{{ route('bulk-transactions.download-template', 'csv') }}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-download me-1"></i>CSV Template
                                </a>
                                <a href="{{ route('bulk-transactions.download-template', 'excel') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-download me-1"></i>Excel Template
                                </a>
                            </div>
                        </div> --}}
                        </div>
                    </div>

                    <!-- What happens next? -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-question-circle text-primary me-2"></i>What happens next?
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="rounded-circle bg-light p-3 mx-auto mb-3"
                                            style="width: 60px; height: 60px; line-height: 34px;">
                                            <i class="fas fa-cogs text-primary fa-lg"></i>
                                        </div>
                                        <h6>File Processing</h6>
                                        <small class="text-muted">We'll analyze your file and extract the column
                                            headers.</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="rounded-circle bg-light p-3 mx-auto mb-3"
                                            style="width: 60px; height: 60px; line-height: 34px;">
                                            <i class="fas fa-table text-primary fa-lg"></i>
                                        </div>
                                        <h6>Column Mapping</h6>
                                        <small class="text-muted">Map your file's columns to our standard format (Date,
                                            Description, Amount, Balance).</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="rounded-circle bg-light p-3 mx-auto mb-3"
                                            style="width: 60px; height: 60px; line-height: 34px;">
                                            <i class="fas fa-eye text-primary fa-lg"></i>
                                        </div>
                                        <h6>Preview & Import</h6>
                                        <small class="text-muted">Review the processed data before importing it into your
                                            account.</small>
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
        .file-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .file-upload-area:hover {
            border-color: #0d6efd;
            background: #f0f8ff;
        }

        .file-upload-area.dragover {
            border-color: #0d6efd;
            background: #e6f3ff;
        }

        @media (max-width: 768px) {
            .file-upload-area {
                padding: 30px 20px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('file');
            const uploadBtn = document.getElementById('uploadBtn');
            const fileUploadArea = document.querySelector('.file-upload-area');

            // Enable upload button when file is selected
            function checkFormValidity() {
                const fileSelected = fileInput.files.length > 0;
                uploadBtn.disabled = !fileSelected;
            }

            // File input change
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    // Validate file size (10MB)
                    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
                    if (file.size > maxSize) {
                        alert('File size exceeds 10MB limit. Please choose a smaller file.');
                        clearFile();
                        return;
                    }

                    // Validate file type
                    const allowedTypes = ['text/csv', 'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Please select a valid CSV or Excel file');
                        clearFile();
                        return;
                    }

                    // Show selected file info
                    showSelectedFile(file);
                }
                checkFormValidity();
            });

            // Drag and drop functionality
            fileUploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.add('dragover');
            });

            fileUploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('dragover');
            });

            fileUploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('dragover');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });

            function showSelectedFile(file) {
                const defaultContent = document.getElementById('file-upload-content');
                const selectedContent = document.getElementById('file-selected-content');
                const fileName = document.getElementById('selected-file-name');
                const fileSize = document.getElementById('selected-file-size');

                defaultContent.style.display = 'none';
                selectedContent.style.display = 'block';

                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size) + ' • ' + getFileType(file.name);
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            function getFileType(filename) {
                const extension = filename.split('.').pop().toLowerCase();
                return extension.toUpperCase();
            }

            window.clearFile = function() {
                fileInput.value = '';
                const defaultContent = document.getElementById('file-upload-content');
                const selectedContent = document.getElementById('file-selected-content');

                defaultContent.style.display = 'block';
                selectedContent.style.display = 'none';

                checkFormValidity();
            };

            // Form submission with loading state
            document.querySelector('form').addEventListener('submit', function() {
                uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                uploadBtn.disabled = true;
            });
        });
    </script>
@endsection
