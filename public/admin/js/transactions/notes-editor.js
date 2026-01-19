/**
 * Collapsible Notes Editor
 * Handles rich text notes with table insertion and edit/delete functionality
 */
class CollapsibleNotesEditor {
    constructor() {
        this.maxRows = 8;
        this.maxCols = 10;
        this.selectedRows = 0;
        this.selectedCols = 0;
        this.isAddNoteSectionVisible = false;
        this.editingNoteId = null;
        this.notes = [];

        this.isResizing = false;
        this.currentResizeCell = null;
        this.startX = 0;
        this.startWidth = 0;
    }

    initialize() {
        this.initializeElements();
        this.createGrid();
        this.bindEvents();
        this.initializeContent();
        this.loadExistingNotes();
        console.log('✅ CollapsibleNotesEditor initialized');
    }

    initializeElements() {
        // Toggle button
        this.addNoteToggleBtn = document.getElementById('addNoteToggleBtn');
        this.addNoteBtnText = document.getElementById('addNoteBtnText');
        this.addNoteSection = document.getElementById('addNoteSection');

        // History section
        this.notesHistoryWrapper = document.getElementById('notesHistoryWrapper');
        this.notesHistoryContent = document.getElementById('notesHistoryContent');

        // Table functionality
        this.tableInsertBtn = document.getElementById('tableInsertBtn');
        this.gridSelector = document.getElementById('gridSelector');
        this.gridContainer = document.getElementById('gridContainer');
        this.gridTitle = document.getElementById('gridTitle');
        this.gridInfo = document.getElementById('gridInfo');
        this.customTableBtn = document.getElementById('customTableBtn');

        // Modal
        this.customModal = document.getElementById('customModal');
        this.customRows = document.getElementById('customRows');
        this.customCols = document.getElementById('customCols');
        this.includeHeaders = document.getElementById('includeHeaders');
        this.cancelCustomBtn = document.getElementById('cancelCustomBtn');
        this.insertCustomBtn = document.getElementById('insertCustomBtn');

        // Editor
        this.richTextEditor = document.getElementById('richTextEditor');
        this.hiddenTextarea = document.getElementById('invoiceNotesHidden');

        // Action buttons
        this.saveNoteBtn = document.getElementById('saveNoteBtn');
        this.cancelNoteBtn = document.getElementById('cancelNoteBtn');

        // Format buttons
        this.boldBtn = document.getElementById('boldBtn');
        this.italicBtn = document.getElementById('italicBtn');
        this.underlineBtn = document.getElementById('underlineBtn');

        // Messages
        this.successMessage = document.getElementById('successMessage');
    }

    initializeContent() {
        if (this.hiddenTextarea && this.hiddenTextarea.value.trim()) {
            this.loadExistingNotes();
        }
    }

    loadExistingNotes() {
        if (!this.hiddenTextarea || !this.hiddenTextarea.value.trim()) {
            this.updateHistoryVisibility();
            return;
        }

        try {
            // Try to parse as JSON (new format)
            this.notes = JSON.parse(this.hiddenTextarea.value);
            this.renderAllNotes();
        } catch (e) {
            // Fallback: treat as single HTML content (old format)
            const existingContent = this.hiddenTextarea.value;
            if (existingContent.trim()) {
                this.notes = [{
                    id: Date.now(),
                    content: existingContent,
                    timestamp: new Date().toISOString()
                }];
                this.renderAllNotes();
            }
        }

        this.updateHistoryVisibility();
    }

    renderAllNotes() {
        if (!this.notesHistoryContent) return;

        this.notesHistoryContent.innerHTML = '';

        this.notes.forEach(note => {
            this.renderNote(note);
        });

        this.updateHiddenTextarea();
    }

    renderNote(note) {
        const noteElement = document.createElement('div');
        noteElement.className = 'note-item';
        noteElement.dataset.noteId = note.id;
        noteElement.style.cssText = `
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background-color: #f8f9fa;
            position: relative;
        `;

        const timestamp = note.timestamp ? new Date(note.timestamp).toLocaleString() : new Date().toLocaleString();

        noteElement.innerHTML = `
            <div class="note-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <strong style="color: #495057;">Note from ${timestamp}</strong>
                <div class="note-actions" style="display: flex; gap: 8px;">
                    <button type="button" class="btn btn-sm btn-outline-primary edit-note-btn" data-note-id="${note.id}" title="Edit Note">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-note-btn" data-note-id="${note.id}" title="Delete Note">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
            <div class="note-content" style="padding: 10px; background-color: white; border-radius: 4px;">
                ${note.content}
            </div>
        `;

        this.notesHistoryContent.appendChild(noteElement);

        // Bind edit and delete events
        noteElement.querySelector('.edit-note-btn').addEventListener('click', () => this.editNote(note.id));
        noteElement.querySelector('.delete-note-btn').addEventListener('click', () => this.deleteNote(note.id));
    }

    createGrid() {
        if (!this.gridContainer) return;

        this.gridContainer.innerHTML = '';

        for (let row = 0; row < this.maxRows; row++) {
            for (let col = 0; col < this.maxCols; col++) {
                const cell = document.createElement('div');
                cell.className = 'grid-cell';
                cell.dataset.row = row;
                cell.dataset.col = col;
                this.gridContainer.appendChild(cell);
            }
        }
    }

    bindEvents() {
        if (!this.addNoteToggleBtn) return;

        // Toggle add note section
        this.addNoteToggleBtn.addEventListener('click', () => {
            this.toggleAddNoteSection();
        });

        if (this.saveNoteBtn) {
            this.saveNoteBtn.addEventListener('click', () => {
                this.saveNote();
            });
        }

        if (this.cancelNoteBtn) {
            this.cancelNoteBtn.addEventListener('click', () => {
                this.cancelNote();
            });
        }

        if (this.tableInsertBtn) {
            this.tableInsertBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleGridSelector();
            });
        }

        if (this.gridContainer) {
            this.gridContainer.addEventListener('mouseover', (e) => {
                if (e.target.classList.contains('grid-cell')) {
                    this.highlightCells(e.target);
                }
            });

            this.gridContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('grid-cell')) {
                    this.selectTable(e.target);
                }
            });
        }

        if (this.customTableBtn) {
            this.customTableBtn.addEventListener('click', () => {
                this.showCustomModal();
            });
        }

        if (this.cancelCustomBtn) {
            this.cancelCustomBtn.addEventListener('click', () => {
                this.hideCustomModal();
            });
        }

        if (this.insertCustomBtn) {
            this.insertCustomBtn.addEventListener('click', () => {
                this.insertCustomTable();
            });
        }

        if (this.boldBtn) this.boldBtn.addEventListener('click', () => this.toggleFormat('bold'));
        if (this.italicBtn) this.italicBtn.addEventListener('click', () => this.toggleFormat('italic'));
        if (this.underlineBtn) this.underlineBtn.addEventListener('click', () => this.toggleFormat('underline'));

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (this.tableInsertBtn && this.gridSelector &&
                !this.tableInsertBtn.contains(e.target) && !this.gridSelector.contains(e.target)) {
                this.hideGridSelector();
            }
        });

        if (this.customModal) {
            this.customModal.addEventListener('click', (e) => {
                if (e.target === this.customModal) {
                    this.hideCustomModal();
                }
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideGridSelector();
                this.hideCustomModal();
            }
        });
    }

    toggleAddNoteSection() {
        this.isAddNoteSectionVisible = !this.isAddNoteSectionVisible;

        if (this.isAddNoteSectionVisible) {
            if (this.addNoteSection) this.addNoteSection.classList.add('show');
            if (this.addNoteToggleBtn) this.addNoteToggleBtn.classList.add('active');
            if (this.addNoteBtnText) this.addNoteBtnText.textContent = '× Cancel';
            if (this.richTextEditor) {
                this.richTextEditor.focus();
            }
        } else {
            if (this.addNoteSection) this.addNoteSection.classList.remove('show');
            if (this.addNoteToggleBtn) this.addNoteToggleBtn.classList.remove('active');
            if (this.addNoteBtnText) {
                this.addNoteBtnText.innerHTML = '<i class="fas fa-plus"></i> Add Note';
            }
            this.editingNoteId = null;
            this.saveNoteBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Note';
        }
    }

    saveNote() {
        if (!this.richTextEditor) return;

        const noteContent = this.richTextEditor.innerHTML.trim();

        if (!noteContent || noteContent === '') {
            alert('Please add some content before saving.');
            return;
        }

        if (this.editingNoteId !== null) {
            // Update existing note
            const noteIndex = this.notes.findIndex(n => n.id === this.editingNoteId);
            if (noteIndex !== -1) {
                this.notes[noteIndex].content = noteContent;
                this.notes[noteIndex].timestamp = new Date().toISOString();
                this.showSuccessMessage('Note updated successfully!');
            }
        } else {
            // Create new note
            const newNote = {
                id: Date.now(),
                content: noteContent,
                timestamp: new Date().toISOString()
            };
            this.notes.push(newNote);
            this.showSuccessMessage('Note saved successfully!');
        }

        this.renderAllNotes();
        this.richTextEditor.innerHTML = '';
        this.editingNoteId = null;
        this.toggleAddNoteSection();
        this.updateHistoryVisibility();
    }

    editNote(noteId) {
        const note = this.notes.find(n => n.id === noteId);
        if (!note) return;

        this.richTextEditor.innerHTML = note.content;
        this.editingNoteId = noteId;
        this.saveNoteBtn.innerHTML = '<i class="fas fa-save me-1"></i>Update Note';

        if (!this.isAddNoteSectionVisible) {
            this.toggleAddNoteSection();
        }

        this.richTextEditor.focus();
    }

    deleteNote(noteId) {
        if (!confirm('Are you sure you want to delete this note?')) {
            return;
        }

        this.notes = this.notes.filter(n => n.id !== noteId);
        this.renderAllNotes();
        this.showSuccessMessage('Note deleted successfully!');
        this.updateHistoryVisibility();
    }

    cancelNote() {
        if (!this.richTextEditor) return;

        if (this.richTextEditor.innerHTML.trim() !== '') {
            if (!confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
                return;
            }
        }

        this.richTextEditor.innerHTML = '';
        this.editingNoteId = null;
        this.saveNoteBtn.innerHTML = '<i class="fas fa-save me-1"></i>Save Note';
        this.toggleAddNoteSection();
    }

    updateHistoryVisibility() {
        if (this.notesHistoryWrapper) {
            if (this.notes.length > 0) {
                this.notesHistoryWrapper.style.display = 'block';
            } else {
                this.notesHistoryWrapper.style.display = 'none';
            }
        }
    }

    updateHiddenTextarea() {
        if (this.hiddenTextarea) {
            this.hiddenTextarea.value = JSON.stringify(this.notes);
        }
    }

    toggleGridSelector() {
        if (this.gridSelector) {
            this.gridSelector.classList.toggle('show');
        }
    }

    hideGridSelector() {
        if (this.gridSelector) {
            this.gridSelector.classList.remove('show');
        }
    }

    highlightCells(targetCell) {
        const row = parseInt(targetCell.dataset.row);
        const col = parseInt(targetCell.dataset.col);

        this.selectedRows = row + 1;
        this.selectedCols = col + 1;

        if (this.gridContainer) {
            this.gridContainer.querySelectorAll('.grid-cell').forEach(cell => {
                cell.classList.remove('highlight');
            });

            for (let r = 0; r <= row; r++) {
                for (let c = 0; c <= col; c++) {
                    const cell = this.gridContainer.querySelector(`[data-row="${r}"][data-col="${c}"]`);
                    if (cell) {
                        cell.classList.add('highlight');
                    }
                }
            }
        }

        if (this.gridTitle) {
            this.gridTitle.textContent = `${this.selectedRows} x ${this.selectedCols} Table`;
        }
        if (this.gridInfo) {
            this.gridInfo.textContent = 'Click to insert table';
        }
    }

    selectTable(targetCell) {
        const row = parseInt(targetCell.dataset.row);
        const col = parseInt(targetCell.dataset.col);

        this.insertTable(row + 1, col + 1, true);
        this.hideGridSelector();
    }

    showCustomModal() {
        if (this.customModal) {
            this.customModal.classList.add('show');
        }
        this.hideGridSelector();
    }

    hideCustomModal() {
        if (this.customModal) {
            this.customModal.classList.remove('show');
        }
    }

    insertCustomTable() {
        if (!this.customRows || !this.customCols || !this.includeHeaders) return;

        const rows = parseInt(this.customRows.value) || 3;
        const cols = parseInt(this.customCols.value) || 3;
        const hasHeaders = this.includeHeaders.checked;

        if (rows < 1 || rows > 50 || cols < 1 || cols > 20) {
            alert('Please enter valid numbers (1-50 rows, 1-20 columns)');
            return;
        }

        this.insertTable(rows, cols, hasHeaders);
        this.hideCustomModal();
    }

    insertTable(rows, cols, includeHeaders = true) {
        if (!this.richTextEditor) return;

        const table = document.createElement('table');

        for (let r = 0; r < rows; r++) {
            const row = document.createElement('tr');

            for (let c = 0; c < cols; c++) {
                const cellElement = r === 0 && includeHeaders ?
                    document.createElement('th') :
                    document.createElement('td');

                cellElement.contentEditable = true;
                cellElement.textContent = '';

                cellElement.style.cssText = `
                    height: 40px;
                    padding: 8px 10px;
                    box-sizing: border-box;
                `;

                row.appendChild(cellElement);
            }

            table.appendChild(row);
        }

        this.richTextEditor.appendChild(table);
        this.richTextEditor.appendChild(document.createElement('br'));

        this.showSuccessMessage('Table inserted successfully!');

        setTimeout(() => {
            const firstCell = table.querySelector('th, td');
            if (firstCell) {
                firstCell.focus();
            }
        }, 100);
    }

    toggleFormat(format) {
        document.execCommand(format, false, null);
        const formatBtn = document.getElementById(format + 'Btn');
        if (formatBtn) {
            formatBtn.classList.toggle('active');
        }
    }

    showSuccessMessage(message) {
        if (this.successMessage) {
            this.successMessage.textContent = message;
            this.successMessage.classList.add('show');
            setTimeout(() => {
                this.successMessage.classList.remove('show');
            }, 3000);
        }
    }
}

// Export to window for global access
if (typeof window !== 'undefined') {
    window.CollapsibleNotesEditor = CollapsibleNotesEditor;
}