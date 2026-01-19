{{-- ========================================================================
     INVOICE DOCUMENT UPLOAD MODAL - MULTIPLE FILES
     ======================================================================== --}}

<div class="modal fade" id="invoiceFileUploadModal" tabindex="-1" aria-labelledby="invoiceFileUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceFileUploadModalLabel">
                    <i class="fas fa-file-upload me-2"></i>Upload Invoice Documents
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <form id="invoiceDocumentUploadForm" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="invoiceDocuments" class="form-label">
                            Select Documents <span class="text-danger">*</span>
                        </label>
                        <input type="file" 
                               class="form-control" 
                               id="invoiceDocuments" 
                               name="documents[]"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg"
                               multiple
                               required>
                        <div class="form-text">
                            Allowed: PDF, DOC, DOCX, XLS, XLSX, PNG, JPG (Max: 5MB per file)
                        </div>
                    </div>

                    {{-- ✅ Files Preview List --}}
                    <div id="filesPreviewList" class="d-none">
                        <label class="form-label fw-bold">Selected Files:</label>
                        <div id="filesPreviewContainer" class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                            <!-- Files will be listed here -->
                        </div>
                    </div>

                    {{-- Upload Progress --}}
                    <div id="uploadProgress" class="d-none mt-3">
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: 0%"
                                 id="uploadProgressBar"></div>
                        </div>
                        <small class="text-muted d-block mt-2" id="uploadStatus">Uploading...</small>
                    </div>

                    {{-- Success Message --}}
                    <div id="uploadSuccess" class="alert alert-success d-none mt-3">
                        <i class="fas fa-check-circle me-2"></i>
                        <span id="uploadSuccessMessage"></span>
                    </div>

                    {{-- Error Message --}}
                    <div id="uploadError" class="alert alert-danger d-none mt-3"></div>

                </form>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="uploadDocumentBtn">
                    <i class="fas fa-upload me-1"></i>Upload
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ✅ Hidden input to store uploaded files (JSON array) --}}
{{-- <input type="hidden" name="invoice_documents" id="invoiceDocuments_data" value="[]"> --}}