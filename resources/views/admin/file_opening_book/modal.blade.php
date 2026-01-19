<div class="modal-header">
    <h5 class="modal-title" id="viewModalLabel">View File Details</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <input type="hidden" name="File_ID" id="modalFileId" value="{{ $fileData->File_ID }}">
    <div class="mb-3">
        <label for="fileID" class="form-label">File ID</label>
        <input type="text" id="fileID" class="form-control" value="{{ $fileData->File_ID }}" readonly>
    </div>
    <div class="mb-3">
        <label for="fileDate" class="form-label">File Date</label>
        <input type="text" id="fileDate" class="form-control" value="{{ $fileData->File_Date }}" readonly>
    </div>
    <div class="mb-3">
        <label for="ledgerRef" class="form-label">Ledger Reference</label>
        <input type="text" id="ledgerRef" class="form-control" value="{{ $fileData->Ledger_Ref }}" readonly>
    </div>
    <div class="mb-3">
        <label for="matter" class="form-label">Matter</label>
        <input type="text" id="matter" class="form-control" value="{{ $fileData->Matter }}" readonly>
    </div>
    <!-- Add additional fields as needed -->
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
</div>
