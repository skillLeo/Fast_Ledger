{{-- ========================================================================
     NOTES EDITOR WITH EDIT/DELETE FUNCTIONALITY
     ======================================================================== --}}

<div class="mb-4">
    <h6><strong>Notes</strong></h6>

    {{-- Notes History (with edit/delete buttons) --}}
    <div class="notes-history-wrapper" id="notesHistoryWrapper">
        <div class="notes-history">
            <div class="notes-history-content" id="notesHistoryContent">
                @if (!empty(old('invoice_notes')))
                    {!! old('invoice_notes') !!}
                @elseif(!empty($transaction->invoice_notes ?? ''))
                    {!! $transaction->invoice_notes !!}
                @endif
            </div>
        </div>
    </div>

    {{-- Add Note Button --}}
    <button class="btn addbutton" id="addNoteToggleBtn" type="button">
        <span id="addNoteBtnText">
            <i class="fas fa-plus"></i> Add Note
        </span>
    </button>

    {{-- Add Note Section (Collapsible) --}}
    <div class="add-note-section" id="addNoteSection">
        {{-- Toolbar --}}
        <div class="notes-toolbar">
            {{-- Table Insert --}}
            <div class="table-insert-container">
                <button class="table-insert-btn" id="tableInsertBtn" type="button">
                    <i class="fas fa-table"></i>
                    Insert Table
                </button>

                {{-- Grid Selector Dropdown --}}
                <div class="grid-selector" id="gridSelector">
                    <div class="grid-title" id="gridTitle">1 x 1 Table</div>
                    <div class="grid-container" id="gridContainer"></div>
                    <div class="grid-info" id="gridInfo">Click to insert table</div>
                    <button class="custom-table-btn" id="customTableBtn" type="button">
                        <i class="fas fa-plus"></i> Insert Custom Table
                    </button>
                </div>
            </div>

            {{-- Formatting Buttons --}}
            <button class="format-btn" id="boldBtn" title="Bold" type="button">
                <i class="fas fa-bold"></i>
            </button>
            <button class="format-btn" id="italicBtn" title="Italic" type="button">
                <i class="fas fa-italic"></i>
            </button>
            <button class="format-btn" id="underlineBtn" title="Underline" type="button">
                <i class="fas fa-underline"></i>
            </button>
        </div>

        {{-- Rich Text Editor --}}
        <div class="rich-text-editor" id="richTextEditor" contenteditable="true"></div>

        {{-- Action Buttons --}}
        <div class="note-actions">
            <button class="btn teal-custom" id="saveNoteBtn" type="button">
                <i class="fas fa-save me-1"></i>Save Note
            </button>
            <button class="cancel-note-btn" id="cancelNoteBtn" type="button">
                <i class="fas fa-times me-1"></i>Cancel
            </button>
            <div style="margin-left: auto;">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Changes are automatically saved as you type
                </small>
            </div>
        </div>
    </div>

    {{-- Hidden textarea for form submission --}}
    <textarea name="invoice_notes" id="invoiceNotesHidden" style="display: none;">{{ old('invoice_notes', $transaction->invoice_notes ?? '') }}</textarea>

    @error('invoice_notes')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

{{-- Custom Table Modal --}}
<div class="custom-modal" id="customModal">
    <div class="modal-content">
        <div class="modal-header">
            <h6 class="mb-0">Insert Custom Table</h6>
        </div>
        <div class="modal-form">
            <div class="form-row">
                <label for="customRows">Rows:</label>
                <input type="number" id="customRows" min="1" max="50" value="3">
            </div>
            <div class="form-row">
                <label for="customCols">Columns:</label>
                <input type="number" id="customCols" min="1" max="20" value="3">
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="includeHeaders" checked>
                <label class="form-check-label" for="includeHeaders">
                    Include header row
                </label>
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn btn-secondary" id="cancelCustomBtn" type="button">Cancel</button>
            <button class="btn btn-primary" id="insertCustomBtn" type="button">Insert Table</button>
        </div>
    </div>
</div>

{{-- Success Message --}}
<div class="success-message" id="successMessage">
    <i class="fas fa-check-circle me-2"></i>
    Note saved successfully!
</div>
