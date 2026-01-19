/**
 * Invoice Document Upload Handler
 * Handles multiple file uploads for invoice documents
 */
class FileUploadHandler {
    constructor() {
        this.fileInput = null;
        this.uploadBtn = null;
        this.addFileBtn = null;
        this.modal = null;
        this.selectedFiles = [];
        this.uploadedDocuments = [];
    }

    initialize() {
        this.fileInput = document.getElementById('invoiceDocuments');
        this.uploadBtn = document.getElementById('uploadDocumentBtn');
        this.addFileBtn = document.getElementById('addFileBtn');

        if (document.getElementById('invoiceFileUploadModal')) {
            this.modal = new bootstrap.Modal(document.getElementById('invoiceFileUploadModal'));
        }

        this.bindEvents();
        console.log('✅ FileUploadHandler initialized');
    }

    bindEvents() {
        // Open modal when "Add File" button clicked
        if (this.addFileBtn) {
            this.addFileBtn.addEventListener('click', () => {
                this.resetUploadModal();
                this.modal.show();
            });
        }

        // File input change - show preview of multiple files
        if (this.fileInput) {
            this.fileInput.addEventListener('change', () => {
                this.selectedFiles = Array.from(this.fileInput.files);
                this.showFilesPreview(this.selectedFiles);
            });
        }

        // Upload button click
        if (this.uploadBtn) {
            this.uploadBtn.addEventListener('click', () => {
                this.uploadMultipleDocuments();
            });
        }
    }

    showFilesPreview(files) {
        const previewList = document.getElementById('filesPreviewList');
        const previewContainer = document.getElementById('filesPreviewContainer');

        if (files.length === 0) {
            previewList.classList.add('d-none');
            return;
        }

        let html = '';
        files.forEach((file, index) => {
            const icon = this.getFileIcon(file.type);
            html += `
                <div class="d-flex align-items-center justify-content-between p-2 border-bottom">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-${icon} me-2 text-primary"></i>
                        <div>
                            <div class="fw-bold">${file.name}</div>
                            <small class="text-muted">${this.formatFileSize(file.size)}</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="window.fileUploadHandler.removeFile(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        });

        previewContainer.innerHTML = html;
        previewList.classList.remove('d-none');
    }

    async uploadMultipleDocuments() {
        if (this.selectedFiles.length === 0) {
            this.showError('Please select at least one file to upload');
            return;
        }

        // Validate all files
        for (let file of this.selectedFiles) {
            if (file.size > 5 * 1024 * 1024) {
                this.showError(`File "${file.name}" is larger than 5MB`);
                return;
            }

            const allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'image/png', 'image/jpeg', 'image/jpg'
            ];

            if (!allowedTypes.includes(file.type)) {
                this.showError(`File "${file.name}" has invalid type`);
                return;
            }
        }

        // Prepare form data
        const formData = new FormData();
        this.selectedFiles.forEach((file) => {
            formData.append(`documents[]`, file);
        });
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        // Show progress
        this.showProgress(0);

        try {
            const response = await fetch('/invoice/upload-documents', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.handleUploadSuccess(data);
            } else {
                this.showError(data.message || 'Upload failed');
            }
        } catch (error) {
            console.error('Upload error:', error);
            this.showError('An error occurred during upload');
        }
    }

    handleUploadSuccess(data) {
        // Append new documents to existing array
        this.uploadedDocuments = this.uploadedDocuments.concat(data.documents);

        // Update hidden input with JSON array
        document.getElementById('invoiceDocuments_data').value = JSON.stringify(this.uploadedDocuments);

        // Show success message
        document.getElementById('uploadProgress').classList.add('d-none');
        const successDiv = document.getElementById('uploadSuccess');
        const successMessage = document.getElementById('uploadSuccessMessage');

        this.showUploadedFilesList();
        successMessage.textContent = `${data.documents.length} document(s) uploaded successfully! (Total: ${this.uploadedDocuments.length})`;
        successDiv.classList.remove('d-none');

        this.uploadBtn.disabled = true;
        this.uploadBtn.innerHTML = '<i class="fas fa-check me-1"></i>Uploaded';

        // Auto-close modal after 2 seconds
        setTimeout(() => {
            this.modal.hide();
            this.resetUploadModal();
        }, 2000);

        // Update "Add File" button with TOTAL count
        if (this.addFileBtn) {
            this.addFileBtn.innerHTML = `<i class="fas fa-check-circle me-1"></i>${this.uploadedDocuments.length} File(s) Attached`;
            this.addFileBtn.classList.remove('teal-custom');
            this.addFileBtn.classList.add('btn-success');
        }
    }

    showUploadedFilesList() {
        if (this.uploadedDocuments.length === 0) return;

        const previewContainer = document.getElementById('filesPreviewContainer');
        const previewList = document.getElementById('filesPreviewList');

        let html = '<div class="mb-2"><strong>Files ready to attach:</strong></div>';

        this.uploadedDocuments.forEach((doc, index) => {
            const icon = this.getFileIcon(doc.file_type);
            html += `
                <div class="d-flex align-items-center justify-content-between p-2 border-bottom">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-${icon} me-2 text-primary"></i>
                        <div>
                            <div class="fw-bold">${doc.file_name}</div>
                            <small class="text-muted">${this.formatFileSize(doc.file_size)}</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="window.fileUploadHandler.removeUploadedFile(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        });

        previewContainer.innerHTML = html;
        previewList.classList.remove('d-none');
    }

    removeUploadedFile(index) {
        this.uploadedDocuments.splice(index, 1);

        // Update hidden input
        document.getElementById('invoiceDocuments_data').value = JSON.stringify(this.uploadedDocuments);

        // Update UI
        if (this.uploadedDocuments.length === 0) {
            this.addFileBtn.innerHTML = '<i class="fas fa-plus me-1"></i> Add File';
            this.addFileBtn.classList.remove('btn-success');
            this.addFileBtn.classList.add('teal-custom');
            document.getElementById('filesPreviewList').classList.add('d-none');
        } else {
            this.addFileBtn.innerHTML = `<i class="fas fa-check-circle me-1"></i>${this.uploadedDocuments.length} File(s) Attached`;
            this.showUploadedFilesList();
        }
    }

    removeFile(index) {
        this.selectedFiles.splice(index, 1);
        this.showFilesPreview(this.selectedFiles);
    }

    showProgress(progress) {
        document.getElementById('uploadProgress').classList.remove('d-none');
        document.getElementById('uploadError').classList.add('d-none');
        const progressBar = document.getElementById('uploadProgressBar');
        progressBar.style.width = progress + '%';
        this.uploadBtn.disabled = true;
    }

    showError(message) {
        const errorDiv = document.getElementById('uploadError');
        errorDiv.textContent = message;
        errorDiv.classList.remove('d-none');
        document.getElementById('uploadProgress').classList.add('d-none');
        this.uploadBtn.disabled = false;
    }

    resetUploadModal() {
        this.fileInput.value = '';
        this.selectedFiles = [];
        document.getElementById('filesPreviewList').classList.add('d-none');
        document.getElementById('uploadProgress').classList.add('d-none');
        document.getElementById('uploadSuccess').classList.add('d-none');
        document.getElementById('uploadError').classList.add('d-none');
        this.uploadBtn.disabled = false;
        this.uploadBtn.innerHTML = '<i class="fas fa-upload me-1"></i>Upload';
    }

    getFileIcon(fileType) {
        if (fileType.includes('pdf')) return 'file-pdf';
        if (fileType.includes('word') || fileType.includes('document')) return 'file-word';
        if (fileType.includes('excel') || fileType.includes('spreadsheet')) return 'file-excel';
        if (fileType.includes('image')) return 'file-image';
        return 'file';
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
}

// Export to window for global access
if (typeof window !== 'undefined') {
    window.FileUploadHandler = FileUploadHandler;
}