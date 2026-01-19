const TableSearch = (function () {
    const activeFilters = {};

    function openSearch(col) {
        closeAllSearches();
        document.querySelectorAll('.filter-dropdown').forEach(d => d.classList.remove('show'));

        const container = document.querySelector(`.search-input-container[data-column="${col}"]`);
        const text = document.querySelector(`.header-text[data-column="${col}"]`);
        const icon = text.nextElementSibling;

        container.classList.add('active');
        text.classList.add('hidden');
        icon.style.display = 'none';

        setTimeout(() => container.querySelector('input').focus(), 100);
    }

    function closeSearch(col) {
        const container = document.querySelector(`.search-input-container[data-column="${col}"]`);
        const text = document.querySelector(`.header-text[data-column="${col}"]`);
        const icon = text.nextElementSibling;
        const input = document.getElementById(`search-${col}`);

        input.value = '';
        delete activeFilters[col];
        filterTable();
        updateFilters();

        container.classList.remove('active');
        text.classList.remove('hidden');
        icon.style.display = 'inline-flex';
    }

    function closeAllSearches() {
        document.querySelectorAll('.search-input-container.active').forEach(c => {
            const col = c.dataset.column;
            const text = document.querySelector(`.header-text[data-column="${col}"]`);
            const icon = text ? text.nextElementSibling : null;

            c.classList.remove('active');
            if (text) text.classList.remove('hidden');
            if (icon) icon.style.display = 'inline-flex';
        });
    }

    function liveSearch(col) {
        const value = document.getElementById(`search-${col}`).value.trim();

        if (value) {
            activeFilters[col] = {
                type: 'search',
                value: value,
                label: `Search ${col}: ${value}`
            };
        } else {
            delete activeFilters[col];
        }

        filterTable();
        updateFilters();
    }

    function toggleFilter(col, filterType = 'sort') {
        closeAllSearches();

        // Close all other dropdowns
        document.querySelectorAll('.filter-dropdown').forEach(d => {
            if (d.dataset.column !== col || d.dataset.type !== filterType) {
                d.classList.remove('show');
            }
        });

        // Toggle the specific dropdown using both column AND type
        const dropdown = document.querySelector(`.filter-dropdown[data-column="${col}"][data-type="${filterType}"]`);
        if (dropdown) {
            dropdown.classList.toggle('show');
        }
    }

    function applySort(col, dir) {
        activeFilters[col] = {
            type: 'sort',
            value: dir,
            label: `Sort ${col}: ${dir === 'asc' ? 'Ascending' : 'Descending'}`
        };

        // Find the table body that contains this column
        const allTbodies = document.querySelectorAll('tbody[id$="-table-body"], tbody[id="tableBody"]');

        allTbodies.forEach(tbody => {
            // Check if this tbody has the column we're sorting
            const hasColumn = tbody.querySelector(`td[data-column="${col}"]`);
            if (!hasColumn) return;

            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                const aVal = a.querySelector(`td[data-column="${col}"]`)?.textContent.trim() || '';
                const bVal = b.querySelector(`td[data-column="${col}"]`)?.textContent.trim() || '';
                return dir === 'asc'
                    ? aVal.localeCompare(bVal, undefined, { numeric: true })
                    : bVal.localeCompare(aVal, undefined, { numeric: true });
            });

            rows.forEach(row => tbody.appendChild(row));
        });

        filterTable();
        updateFilters();

        const dropdown = document.querySelector(`.filter-dropdown[data-column="${col}"]`);
        if (dropdown) dropdown.classList.remove('show');
    }

    function applyDropdownFilter(col, value, element) {
        // Remove active class from all options in this column's dropdown
        document.querySelectorAll(`.filter-dropdown[data-column="${col}"] .filter-option`).forEach(opt => {
            opt.classList.remove('active');
        });

        if (value === 'all') {
            delete activeFilters[col];
        } else {
            activeFilters[col] = {
                type: 'dropdown',
                value: value,
                label: `Filter ${col}: ${value}`
            };

            // Add active class to selected option
            if (element) {
                element.classList.add('active');
            }
        }

        filterTable();
        updateFilters();

        const dropdown = document.querySelector(`.filter-dropdown[data-column="${col}"][data-type="dropdown"]`);
        if (dropdown) dropdown.classList.remove('show');
    }

    function filterTable() {
        const searchFilters = Object.keys(activeFilters).filter(k => activeFilters[k].type === 'search');
        const dropdownFilters = Object.keys(activeFilters).filter(k => activeFilters[k].type === 'dropdown');

        // Find all table bodies - works for single or multiple tables
        document.querySelectorAll('tbody[id$="-table-body"], tbody[id="tableBody"]').forEach(tbody => {
            tbody.querySelectorAll('tr').forEach(row => {
                // Skip rows without data-column attributes (like loading/error messages)
                if (!row.querySelector('td[data-column]')) {
                    return;
                }

                // Check search filters
                const matchesSearch = searchFilters.every(col => {
                    const cell = row.querySelector(`td[data-column="${col}"]`);
                    if (!cell) return true;
                    const cellText = cell.textContent.toLowerCase();
                    return cellText.includes(activeFilters[col].value.toLowerCase());
                });

                // Check dropdown filters
                const matchesDropdown = dropdownFilters.every(col => {
                    const cell = row.querySelector(`td[data-column="${col}"]`);
                    if (!cell) return true;
                    const cellText = cell.textContent.trim();
                    return cellText === activeFilters[col].value;
                });

                row.style.display = (matchesSearch && matchesDropdown) ? '' : 'none';
            });
        });
    }

    function updateFilters() {
        const container = document.getElementById('active-filters');
        if (!container) return;

        container.innerHTML = Object.keys(activeFilters).map(col => `
            <div class="filter-tag">
                ${activeFilters[col].label}
                <button class="remove-filter" onclick="TableSearch.removeFilter('${col}')">×</button>
            </div>
        `).join('');
    }

    function removeFilter(col) {
        delete activeFilters[col];
        const input = document.getElementById(`search-${col}`);
        if (input) input.value = '';

        // Remove active class from dropdown options
        document.querySelectorAll(`.filter-dropdown[data-column="${col}"] .filter-option`).forEach(opt => {
            opt.classList.remove('active');
        });

        filterTable();
        updateFilters();
    }

    // Close dropdowns when clicking outside
    // IMPROVED CODE:
    document.addEventListener('click', function (e) {
        // Don't close if clicking inside the dropdown itself
        if (!e.target.closest('th') && !e.target.closest('.filter-dropdown')) {
            document.querySelectorAll('.filter-dropdown').forEach(d => d.classList.remove('show'));
        }
    });

    // Public API
    return {
        openSearch,
        closeSearch,
        liveSearch,
        toggleFilter,
        applySort,
        applyDropdownFilter,
        removeFilter
    };
})();