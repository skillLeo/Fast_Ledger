class ResizableDraggableTable {
    constructor(tableElement) {
        this.table = tableElement;
        if (!this.table) return;

        // Find or create resize line
        this.resizeLine = this.table.parentElement.querySelector('.resize-line');
        if (!this.resizeLine) {
            this.resizeLine = document.createElement('div');
            this.resizeLine.className = 'resize-line';
            this.table.parentElement.appendChild(this.resizeLine);
        }

        this.headers = null;

        // Resize variables
        this.isResizing = false;
        this.currentHeader = null;
        this.currentColumnIndex = null;
        this.startX = 0;
        this.startWidth = 0;

        // Drag variables
        this.isDragging = false;
        this.draggedColumn = null;
        this.dragGhost = null;
        this.dragStartX = 0;
        this.dragStartY = 0;
        this.hasMoved = false;

        this.init();
    }

    init() {
        // ✅ Wait for table to render fully before setting widths
        requestAnimationFrame(() => {
            this.initializeHeaders();

            // ✅ Small delay to ensure accurate width calculation
            setTimeout(() => {
                this.setInitialColumnWidths();
            }, 50);

            this.attachGlobalListeners();
        });
    }

    initializeHeaders() {
        this.headers = this.table.querySelectorAll('th');

        this.headers.forEach((header, index) => {
            // Remove existing resize handle if any
            const existingHandle = header.querySelector('.resize-handle');
            if (existingHandle) {
                existingHandle.remove();
            }

            // Add resize handle
            const handle = document.createElement('div');
            handle.className = 'resize-handle';
            header.appendChild(handle);

            // Resize: Mouse down on handle
            handle.addEventListener('mousedown', (e) => {
                e.stopPropagation();
                e.preventDefault();
                this.startResize(e, header, index);
            });

            // Resize: Double-click to auto-fit
            handle.addEventListener('dblclick', (e) => {
                e.stopPropagation();
                e.preventDefault();
                this.autoFitColumn(index);
            });

            // Drag: Mouse down on header
            header.addEventListener('mousedown', (e) => {
                if (e.target.classList.contains('resize-handle')) return;
                // Don't start drag if clicking on input/select inside header
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') return;
                this.startDrag(e, index);
            });
        });
    }

    startResize(e, header, index) {
        this.isResizing = true;
        this.currentHeader = header;
        this.currentColumnIndex = index;
        this.startX = e.pageX;
        this.startWidth = header.offsetWidth;

        header.classList.add('resizing');
        this.resizeLine.style.left = e.pageX + 'px';
        this.resizeLine.style.display = 'block';
        document.body.style.cursor = 'col-resize';
    }

    startDrag(e, index) {
        this.isDragging = true;
        this.draggedColumn = index;
        this.dragStartX = e.pageX;
        this.dragStartY = e.pageY;
        this.hasMoved = false;
    }

    attachGlobalListeners() {
        const mouseMoveHandler = (e) => this.onMouseMove(e);
        const mouseUpHandler = (e) => this.onMouseUp(e);

        document.addEventListener('mousemove', mouseMoveHandler);
        document.addEventListener('mouseup', mouseUpHandler);
    }

    onMouseMove(e) {
        if (this.isResizing) {
            this.handleResize(e);
        } else if (this.isDragging && this.draggedColumn !== null) {
            this.handleDrag(e);
        }
    }

    handleResize(e) {
        const diff = e.pageX - this.startX;
        const newWidth = Math.max(50, this.startWidth + diff);

        this.resizeLine.style.left = e.pageX + 'px';

        // Apply width to header
        this.currentHeader.style.setProperty('width', newWidth + 'px', 'important');
        this.currentHeader.style.setProperty('min-width', newWidth + 'px', 'important');
        this.currentHeader.style.setProperty('max-width', newWidth + 'px', 'important');

        // Apply width to all cells in this column
        const rows = this.table.querySelectorAll('tr');
        rows.forEach(row => {
            const cell = row.cells[this.currentColumnIndex];
            if (cell) {
                cell.style.setProperty('width', newWidth + 'px', 'important');
                cell.style.setProperty('min-width', newWidth + 'px', 'important');
                cell.style.setProperty('max-width', newWidth + 'px', 'important');
            }
        });

        this.updateTableWidth();
    }

    handleDrag(e) {
        const moveX = Math.abs(e.pageX - this.dragStartX);
        const moveY = Math.abs(e.pageY - this.dragStartY);

        if (!this.hasMoved && (moveX > 5 || moveY > 5)) {
            this.hasMoved = true;
            this.headers[this.draggedColumn].classList.add('dragging');

            this.dragGhost = document.createElement('div');
            this.dragGhost.className = 'drag-ghost';
            this.dragGhost.textContent = this.headers[this.draggedColumn].textContent;
            document.body.appendChild(this.dragGhost);
        }

        if (this.hasMoved && this.dragGhost) {
            // Position ghost offset from cursor (20px right, 5px down) for better visibility
            this.dragGhost.style.left = (e.clientX + 1) + 'px';
            this.dragGhost.style.top = (e.clientY - 15) + 'px';
            const target = document.elementFromPoint(e.clientX, e.clientY);
            const targetHeader = target?.closest('th');

            this.headers.forEach(h => {
                h.classList.remove('drag-over-left', 'drag-over-right');
            });

            if (targetHeader && targetHeader !== this.headers[this.draggedColumn]) {
                const targetIndex = Array.from(this.headers).indexOf(targetHeader);
                const rect = targetHeader.getBoundingClientRect();
                const midpoint = rect.left + rect.width / 2;

                if (e.clientX < midpoint) {
                    targetHeader.classList.add('drag-over-left');
                } else {
                    targetHeader.classList.add('drag-over-right');
                }
            }
        }
    }

    onMouseUp(e) {
        if (this.isResizing) {
            this.endResize();
        } else if (this.isDragging && this.hasMoved) {
            this.endDrag(e);
        }

        this.isDragging = false;
        this.draggedColumn = null;
        this.hasMoved = false;
    }

    endResize() {
        this.isResizing = false;
        this.resizeLine.style.display = 'none';
        document.body.style.cursor = 'default';

        if (this.currentHeader) {
            this.currentHeader.classList.remove('resizing');
            this.currentHeader = null;
            this.currentColumnIndex = null;
        }
    }

    endDrag(e) {
        const target = document.elementFromPoint(e.clientX, e.clientY);
        const targetHeader = target?.closest('th');

        if (targetHeader && targetHeader !== this.headers[this.draggedColumn]) {
            const targetIndex = Array.from(this.headers).indexOf(targetHeader);
            const rect = targetHeader.getBoundingClientRect();
            const midpoint = rect.left + rect.width / 2;

            let insertIndex = targetIndex;
            if (e.clientX > midpoint) {
                insertIndex = targetIndex + 1;
            }

            this.swapColumns(this.draggedColumn, insertIndex);
        }

        if (this.dragGhost) {
            this.dragGhost.remove();
            this.dragGhost = null;
        }

        if (this.headers[this.draggedColumn]) {
            this.headers[this.draggedColumn].classList.remove('dragging');
        }

        this.headers.forEach(h => {
            h.classList.remove('drag-over-left', 'drag-over-right');
        });
    }

    swapColumns(fromIndex, toIndex) {
        if (fromIndex === toIndex) return;

        const rows = this.table.querySelectorAll('tr');

        rows.forEach(row => {
            const cells = Array.from(row.cells);
            const fromCell = cells[fromIndex];

            if (toIndex > fromIndex) {
                if (toIndex < cells.length) {
                    row.insertBefore(fromCell, cells[toIndex].nextSibling);
                } else {
                    row.appendChild(fromCell);
                }
            } else {
                row.insertBefore(fromCell, cells[toIndex]);
            }
        });

        this.initializeHeaders();
        this.setInitialColumnWidths();
    }

    autoFitColumn(columnIndex) {
        const rows = this.table.querySelectorAll('tr');
        let maxWidth = 0;

        const temp = document.createElement('span');
        temp.style.visibility = 'hidden';
        temp.style.position = 'absolute';
        temp.style.whiteSpace = 'nowrap';
        document.body.appendChild(temp);

        rows.forEach(row => {
            const cell = row.cells[columnIndex];
            if (cell) {
                temp.textContent = cell.textContent;
                temp.style.font = window.getComputedStyle(cell).font;
                maxWidth = Math.max(maxWidth, temp.offsetWidth);
            }
        });

        document.body.removeChild(temp);

        const finalWidth = maxWidth + 24;

        rows.forEach(row => {
            const cell = row.cells[columnIndex];
            if (cell) {
                cell.style.setProperty('width', finalWidth + 'px', 'important');
                cell.style.setProperty('min-width', finalWidth + 'px', 'important');
                cell.style.setProperty('max-width', finalWidth + 'px', 'important');
            }
        });

        this.updateTableWidth();
    }

    updateTableWidth() {
        let totalWidth = 0;
        this.headers.forEach(header => {
            totalWidth += header.offsetWidth;
        });
        this.table.style.width = totalWidth + 'px';
    }

    setInitialColumnWidths() {
        const headerRow = this.table.querySelector('thead tr');
        const bodyRow = this.table.querySelector('tbody tr');

        if (!headerRow || !bodyRow) return;

        // ✅ Force browser reflow to get accurate widths
        this.table.style.width = 'auto';
        void this.table.offsetWidth; // Force reflow

        this.headers.forEach((header, index) => {
            // ✅ Get computed width (more accurate)
            const computedStyle = window.getComputedStyle(header);
            const width = parseFloat(computedStyle.width) || header.offsetWidth;

            // ✅ Set explicit widths on headers
            header.style.setProperty('width', width + 'px', 'important');
            header.style.setProperty('min-width', width + 'px', 'important');
            header.style.setProperty('max-width', width + 'px', 'important');

            // ✅ Set explicit widths on all cells in this column
            const rows = this.table.querySelectorAll('tr');
            rows.forEach(row => {
                const cell = row.cells[index];
                if (cell) {
                    cell.style.setProperty('width', width + 'px', 'important');
                    cell.style.setProperty('min-width', width + 'px', 'important');
                    cell.style.setProperty('max-width', width + 'px', 'important');
                }
            });
        });

        this.updateTableWidth();
    }
}

// Initialize function that can be called manually
window.initResizableTable = function (tableIdOrElement) {
    let table;
    if (typeof tableIdOrElement === 'string') {
        table = document.getElementById(tableIdOrElement);
    } else {
        table = tableIdOrElement;
    }

    if (table) {
        table.classList.add('resizable-draggable-table');
        new ResizableDraggableTable(table);
    }
};

// Auto-initialize all tables with class 'resizable-draggable-table'
document.addEventListener('DOMContentLoaded', function () {
    const tables = document.querySelectorAll('.resizable-draggable-table');
    tables.forEach(table => {
        new ResizableDraggableTable(table);
    });
});