/**
 * Sortable Manager
 * Handles drag-and-drop sorting for journal entries and invoice items
 */
class SortableManager {
    constructor() {
        this.journalSortable = null;
    }

    initialize() {
        console.log('✅ SortableManager initialized');
    }

    /**
     * Initialize sortable functionality for journal entries
     */
    initializeJournalSortable() {
        const journalTableBody = document.getElementById('journalRows');
        if (journalTableBody && !this.journalSortable) {
            this.journalSortable = Sortable.create(journalTableBody, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                filter: '[data-template-row]',
                onStart: (evt) => {
                    evt.item.style.opacity = '0.5';
                },
                onEnd: (evt) => {
                    evt.item.style.opacity = '1';
                    this.updateJournalTotals();
                    this.updateJournalIndices();
                    console.log('Journal entry moved from index', evt.oldIndex, 'to', evt.newIndex);
                }
            });
            console.log('Journal sortable initialized');
        }
    }

    /**
     * Update journal indices in form field names after reordering
     */
    updateJournalIndices() {
        const rows = document.querySelectorAll('#journalRows tr:not([data-template-row])');
        rows.forEach((row, index) => {
            const actualIndex = index + 1;

            const inputs = row.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.name && input.name.includes('journal_items[')) {
                    const newName = input.name.replace(/journal_items\[\d+\]/, `journal_items[${actualIndex}]`);
                    input.name = newName;
                }
            });

            row.dataset.journalRow = actualIndex;

            const selects = row.querySelectorAll('select[data-row]');
            selects.forEach(select => {
                select.dataset.row = actualIndex;
            });
        });
    }

    /**
     * Update journal totals after reordering
     */
    updateJournalTotals() {
        if (window.journalHandler && typeof window.journalHandler.updateJournalTotals === 'function') {
            window.journalHandler.updateJournalTotals();
        }
    }

    /**
     * Destroy sortable instances (useful when switching between forms)
     */
    destroySortableInstances() {
        if (window.invoiceHandler) {
            window.invoiceHandler.destroySortable();
        }
        if (this.journalSortable) {
            this.journalSortable.destroy();
            this.journalSortable = null;
            console.log('Journal sortable destroyed');
        }
    }
}

// Export to window for global access
if (typeof window !== 'undefined') {
    window.SortableManager = SortableManager;
}