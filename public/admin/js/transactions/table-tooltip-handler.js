/**
 * Smart Table Tooltip Handler
 * Handles ellipsis tooltips and scroll behavior for table cells
 */
class TableTooltipHandler {
    constructor() {
        this.tooltip = null;
        this.currentCell = null;
        this.hideTimeout = null;
    }

    initialize() {
        // Create tooltip element
        this.tooltip = document.createElement('div');
        this.tooltip.className = 'cell-tooltip';
        document.body.appendChild(this.tooltip);

        // Bind events
        this.bindEvents();
        
        console.log('✅ TableTooltipHandler initialized');
    }

    bindEvents() {
        document.addEventListener('mouseover', (e) => this.handleMouseOver(e));
        document.addEventListener('mouseout', (e) => this.handleMouseOut(e));
        document.addEventListener('focus', (e) => this.handleFocus(e), true);
        document.addEventListener('input', (e) => this.handleInput(e), true);
        document.addEventListener('keydown', (e) => this.handleKeydown(e), true);
        document.addEventListener('click', (e) => this.handleClick(e), true);
    }

    handleFocus(e) {
        const cell = e.target.closest(
            '.rich-text-editor table td[contenteditable], .rich-text-editor table th[contenteditable]');

        if (cell) {
            // Force reflow to remove ellipsis
            cell.style.overflow = 'visible';
            cell.style.textOverflow = 'clip';

            // Force browser to recalculate
            void cell.offsetHeight;

            // Then enable scroll
            cell.style.overflow = 'auto';
            cell.style.overflowY = 'hidden';

            // Scroll to end
            setTimeout(() => {
                cell.scrollLeft = cell.scrollWidth;
            }, 0);
        }
    }

    handleInput(e) {
        const cell = e.target.closest(
            '.rich-text-editor table td[contenteditable], .rich-text-editor table th[contenteditable]');

        if (cell) {
            // Keep scrolling to end as user types
            requestAnimationFrame(() => {
                cell.scrollLeft = cell.scrollWidth;
            });
        }
    }

    handleKeydown(e) {
        const cell = e.target.closest(
            '.rich-text-editor table td[contenteditable], .rich-text-editor table th[contenteditable]');

        if (cell && !e.ctrlKey && !e.metaKey) {
            // Delay scroll to after character is inserted
            setTimeout(() => {
                cell.scrollLeft = cell.scrollWidth;
            }, 0);
        }
    }

    handleClick(e) {
        const cell = e.target.closest(
            '.rich-text-editor table td[contenteditable], .rich-text-editor table th[contenteditable]');

        if (cell) {
            setTimeout(() => this.scrollToCursor(cell), 0);
        }
    }

    scrollToEnd(cell) {
        requestAnimationFrame(() => {
            cell.scrollLeft = cell.scrollWidth - cell.clientWidth + 50;
        });
    }

    scrollToCursor(cell) {
        try {
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                const rect = range.getBoundingClientRect();
                const cellRect = cell.getBoundingClientRect();

                // If cursor is not visible, scroll to it
                if (rect.left < cellRect.left || rect.right > cellRect.right) {
                    const scrollAmount = rect.left - cellRect.left - 20;
                    cell.scrollLeft += scrollAmount;
                }
            }
        } catch (e) {
            // Fallback to end
            this.scrollToEnd(cell);
        }
    }

    handleMouseOver(e) {
        const cell = e.target.closest(
            '.rich-text-editor table td, .rich-text-editor table th, .notes-history-content table td, .notes-history-content table th'
        );

        if (!cell) return;
        if (cell.matches(':focus')) return;

        const cellText = cell.textContent.trim();
        const isOverflowing = cell.scrollWidth > cell.clientWidth;

        if (isOverflowing && cellText.length > 0) {
            clearTimeout(this.hideTimeout);
            this.currentCell = cell;
            this.showTooltip(cell, cellText);
        }
    }

    handleMouseOut(e) {
        const cell = e.target.closest(
            '.rich-text-editor table td, .rich-text-editor table th, .notes-history-content table td, .notes-history-content table th'
        );

        if (cell === this.currentCell) {
            this.hideTimeout = setTimeout(() => {
                this.hideTooltip();
            }, 100);
        }
    }

    showTooltip(cell, text) {
        this.tooltip.textContent = text;
        this.tooltip.classList.add('show');

        const cellRect = cell.getBoundingClientRect();

        // Position below cell
        this.tooltip.style.left = cellRect.left + 'px';
        this.tooltip.style.top = (cellRect.bottom + 2) + 'px';

        // Adjust if off-screen
        setTimeout(() => {
            const tooltipRect = this.tooltip.getBoundingClientRect();

            if (tooltipRect.right > window.innerWidth) {
                this.tooltip.style.left = (window.innerWidth - tooltipRect.width - 10) + 'px';
            }

            if (tooltipRect.bottom > window.innerHeight) {
                this.tooltip.style.top = (cellRect.top - tooltipRect.height - 2) + 'px';
                this.tooltip.classList.add('tooltip-top');
            } else {
                this.tooltip.classList.remove('tooltip-top');
            }
        }, 0);
    }

    hideTooltip() {
        this.tooltip.classList.remove('show');
        this.currentCell = null;
    }
}

// Export to window for global access
if (typeof window !== 'undefined') {
    window.TableTooltipHandler = TableTooltipHandler;
}