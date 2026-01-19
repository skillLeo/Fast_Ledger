/**
 * Scrollable Table Utilities
 * Reusable functions for table scroll handling
 */

const ScrollableTable = {
    /**
     * Initialize scrollable table with shadow effects
     * @param {string} wrapperSelector - CSS selector for table wrapper
     */
    init: function (wrapperSelector = '.table-sticky-wrapper') {
        const $wrapper = $(wrapperSelector);

        if ($wrapper.length === 0) {
            console.warn('ScrollableTable: No wrapper found with selector:', wrapperSelector);
            return;
        }

        // Get the tbody element for scroll detection
        const $tbody = $wrapper.find('table tbody');

        if ($tbody.length === 0) {
            console.warn('ScrollableTable: No tbody found');
            return;
        }

        // Add scroll shadow effect - listen to tbody scroll
        $tbody.on('scroll', function () {
            if ($(this).scrollTop() > 0) {
                $wrapper.addClass('scrolled');
            } else {
                $wrapper.removeClass('scrolled');
            }
        });

        // Optional: Add scroll position indicator
        this.addScrollIndicator($wrapper);

        // Sync column widths between thead and tbody
        this.syncColumnWidths($wrapper);
         // Initialize truncate for all tables with truncate class
        this.initTruncate();
    },

    /**
     * Initialize text truncation with tooltip for tables
     * Auto-adds title attributes to cells for hover tooltips
     * Usage: Add 'table-truncate' class to any table
     */
    initTruncate: function () {
        $('.table-truncate td, .table-truncate th').each(function () {
            const $cell = $(this);
            
            // Only add title if it doesn't already exist
            if (!$cell.attr('title')) {
                const text = $cell.text().trim();
                if (text) {
                    $cell.attr('title', text);
                }
            }
        });

        // Watch for dynamically added tables
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver((mutations) => {
                let shouldUpdate = false;
                
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) { // Element node
                            if ($(node).hasClass('table-truncate') || 
                                $(node).find('.table-truncate').length > 0) {
                                shouldUpdate = true;
                            }
                        }
                    });
                });

                if (shouldUpdate) {
                    this.initTruncate();
                }
            });

            observer.observe(document.body, { 
                childList: true, 
                subtree: true 
            });
        }
    },

    /**
     * Sync column widths between thead and tbody
     * @param {jQuery} $wrapper - Table wrapper element
     */
    syncColumnWidths: function ($wrapper) {
        const $table = $wrapper.find('table');
        const $thead = $table.find('thead');
        const $tbody = $table.find('tbody');

        // Force table layout
        $table.css('table-layout', 'fixed');

        // Function to sync widths
        function syncWidths() {
            const scrollbarWidth = $tbody[0].offsetWidth - $tbody[0].clientWidth;

            // Add padding to thead to compensate for scrollbar
            $thead.css('padding-right', scrollbarWidth + 'px');

            const theadCells = $thead.find('th');
            const firstRow = $tbody.find('tr:first td');

            theadCells.each(function (index) {
                const width = $(this).outerWidth();
                firstRow.eq(index).css('width', width + 'px');
            });
        }

        // Match widths on window resize and initial load
        $(window).on('resize', syncWidths);

        // Initial sync with a small delay to ensure tbody is rendered
        setTimeout(syncWidths, 100);
    },
    /**
     * Add visual scroll indicators (optional)
     * @param {jQuery} $wrapper - Table wrapper element
     */
    addScrollIndicator: function ($wrapper) {
        const $tbody = $wrapper.find('table tbody');

        if ($tbody.length === 0) return;

        const hasVerticalScroll = $tbody[0].scrollHeight > $tbody[0].clientHeight;

        if (hasVerticalScroll) {
            $wrapper.addClass('has-vertical-scroll');
        }
    },

    /**
     * Format numbers with proper negative styling
     * @param {number} value - Number to format
     * @returns {string} Formatted HTML string
     */
    formatNumber: function (value) {
        const n = Number(value || 0);
        const abs = Math.abs(n).toFixed(2);
        const formatted = Number(abs).toLocaleString();

        if (n < 0) {
            return `<span class="text-danger fw-semibold">(${formatted})</span>`;
        }
        return formatted;
    },

    /**
     * Apply negative number styling to existing table cells
     * @param {string} tableSelector - CSS selector for table
     */
    colorNegatives: function (tableSelector = '#profit-loss-table') {
        $(tableSelector + ' td').each(function () {
            const txt = $(this).text().trim();
            if (txt.startsWith('(') || txt.startsWith('-')) {
                const $span = $(this).find('span');
                if ($span.length) {
                    $span.addClass('text-danger fw-semibold');
                } else {
                    $(this).wrapInner('<span class="text-danger fw-semibold"></span>');
                }
            }
        });
    },

    /**
     * Toggle table columns visibility
     * @param {string} columnClass - CSS class of column to toggle
     * @param {boolean} show - Show or hide
     */
    toggleColumn: function (columnClass, show) {
        const selector = `.${columnClass}`;
        $(selector).toggle(show);
    },

    /**
     * Scroll to a specific row
     * @param {string} wrapperSelector - Table wrapper selector
     * @param {string} rowSelector - Row selector
     */
    scrollToRow: function (wrapperSelector, rowSelector) {
        const $wrapper = $(wrapperSelector);
        const $tbody = $wrapper.find('table tbody');
        const $row = $(rowSelector);

        if ($row.length && $tbody.length) {
            const rowTop = $row.position().top;
            $tbody.animate({
                scrollTop: rowTop + $tbody.scrollTop()
            }, 300);
        }
    }
};

// Auto-initialize on document ready
$(document).ready(function () {
    ScrollableTable.init();
});