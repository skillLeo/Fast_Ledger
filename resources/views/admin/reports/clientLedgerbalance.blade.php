@extends('admin.layout.app')
{{-- <style>
    thead th {
        position: relative;
        white-space: nowrap;
        padding: 12px;
        overflow: hidden;
        /* ← Clip overflow content */
        max-width: max-content;
        /* ← Prevent expansion */
    }

    thead th {
        position: relative;
        white-space: nowrap;
        padding: 12px;
    }

    /* Styled icon buttons */
    thead th i {
        font-size: 14px;
        cursor: pointer;
        background: #2c7a8c;
        color: white;
        padding: 6px 8px;
        border-radius: 0px;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
    }

    thead th i:hover {
        background: #1f5a68;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        gap: 8px;
    }

    .header-text {
        flex: 1;
    }

    .header-text.hidden {
        display: none;
    }

    .search-input-container {
        display: none;
        align-items: center;
        width: 100%;
        /* Takes th width */
        max-width: 100%;
        /* Never exceeds th width */
        /* gap: 5px; */
        box-sizing: border-box;
    }

    .search-input-container.active {
        display: flex;
    }

    .search-input-container input {
        flex: 1;
        /* Grows to fill available space */
        min-width: 0;
        /* Allows shrinking below default */
        max-width: 100%;
        /* Never exceeds container */
        padding: 6px 8px;
        /* Reduce padding if needed */
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 13px;
        /* Slightly smaller font */
    }

    .search-input-container .btn-close {
        background: #dc3545;
        color: white;
        border: none;
        padding: 6px 8px;
        /* Compact button */
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        flex-shrink: 0;
        /* Prevent button from shrinking */
    }

    .search-input-container .btn-close:hover {
        background: #c82333;
    }

    .filter-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1050;
        display: none;
        min-width: 200px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        margin-top: 5px;
    }

    .filter-dropdown.show {
        display: block;
    }

    .filter-dropdown .filter-option {
        padding: 10px 15px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .filter-dropdown .filter-option:hover {
        background: #f8f9fa;
    }

    .active-filters {
        margin-bottom: 15px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #17a2b8;
        color: white;
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 14px;
    }

    .filter-tag .remove-filter {
        background: white;
        color: #17a2b8;
        border: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
</style> --}}

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between mb-3">
                            <h4 class="page-title">14 Days Passed Check</h4>
                            <x-download-dropdown pdf-id="download-pdf" csv-id="print-csv" />
                        </div>
                        <div class="card-body">
                            <div id="active-filters" class="active-filters"></div>

                            <div class="table-sticky-wrapper">
                                <div style="max-height: calc(100vh - 290px);">
                                    <table class="table table-bordered table-truncate resizable-draggable-table"
                                        id="dataTable">
                                        <thead class="table-dark">
                                            <tr>
                                                <x-table-search-header column="status" label="Ledger Status"
                                                    type="search" />
                                                <x-table-search-header column="date" label="Transaction Date"
                                                    type="search" class="position-relative" />

                                                <x-table-search-header column="balance" label="Client A/C Balance"
                                                    type="search" />
                                                <x-table-search-header column="ledger" label="Ledger Ref" type="search" />

                                                <x-table-search-header column="matter" label="Matter" type="search" />
                                                <x-table-search-header column="name" label="Name" type="search" />
                                                <x-table-search-header column="address" label="Address" type="search" />
                                                <x-table-search-header column="feeearner" label="Fee Earner" type="sort"
                                                    class="position-relative" />
                                            </tr>
                                        </thead>
                                        <tbody id="tableBody">
                                            @foreach ($fileSummaries as $clientsdata)
                                                <tr>
                                                    <td data-column="status">
                                                        {{ $clientsdata['Days_Since_Last_Transaction'] }}
                                                    </td>
                                                    <td data-column="date">{{ $clientsdata['Last_Transaction_Date'] }}</td>
                                                    <td data-column="balance">{{ $clientsdata['Total_Balance'] }}</td>
                                                    <td data-column="ledger">{{ $clientsdata['Ledger_Ref'] }}</td>
                                                    <td data-column="matter">{{ $clientsdata['Matter'] }}</td>
                                                    <td data-column="name">{{ $clientsdata['First_Name'] }}
                                                        {{ $clientsdata['Last_Name'] }}</td>
                                                    <td data-column="address">{{ $clientsdata['Address1'] }}
                                                        {{ $clientsdata['Address2'] }}</td>
                                                    <td data-column="feeearner">{{ $clientsdata['Fee_Earner'] }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

{{-- @section('scripts')
    <script>
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
                const icon = text.nextElementSibling;

                c.classList.remove('active');
                text.classList.remove('hidden');
                icon.style.display = 'inline-flex';
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

        function toggleFilter(col) {
            closeAllSearches();
            document.querySelectorAll('.filter-dropdown').forEach(d => {
                if (d.dataset.column !== col) d.classList.remove('show');
            });

            document.querySelector(`.filter-dropdown[data-column="${col}"]`).classList.toggle('show');
        }

        function applySort(col, dir) {
            activeFilters[col] = {
                type: 'sort',
                value: dir,
                label: `Sort ${col}: ${dir === 'asc' ? 'Ascending' : 'Descending'}`
            };

            const tbody = document.getElementById('tableBody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                const aVal = a.querySelector(`td[data-column="${col}"]`).textContent.trim();
                const bVal = b.querySelector(`td[data-column="${col}"]`).textContent.trim();
                return dir === 'asc' ? aVal.localeCompare(bVal, undefined, {
                    numeric: true
                }) : bVal.localeCompare(aVal, undefined, {
                    numeric: true
                });
            });

            rows.forEach(row => tbody.appendChild(row));
            filterTable();
            updateFilters();
            document.querySelector(`.filter-dropdown[data-column="${col}"]`).classList.remove('show');
        }

        function filterTable() {
            const searchFilters = Object.keys(activeFilters).filter(k => activeFilters[k].type === 'search');

            document.querySelectorAll('#tableBody tr').forEach(row => {
                const show = searchFilters.every(col => {
                    const cell = row.querySelector(`td[data-column="${col}"]`).textContent.toLowerCase();
                    return cell.includes(activeFilters[col].value.toLowerCase());
                });
                row.style.display = show ? '' : 'none';
            });
        }

        function updateFilters() {
            const container = document.getElementById('active-filters');
            container.innerHTML = Object.keys(activeFilters).map(col => `
                <div class="filter-tag">
                    ${activeFilters[col].label}
                    <button class="remove-filter" onclick="removeFilter('${col}')">×</button>
                </div>
            `).join('');
        }

        function removeFilter(col) {
            delete activeFilters[col];
            const input = document.getElementById(`search-${col}`);
            if (input) input.value = '';
            filterTable();
            updateFilters();
        }

        document.addEventListener('click', e => {
            if (!e.target.closest('th')) {
                document.querySelectorAll('.filter-dropdown').forEach(d => d.classList.remove('show'));
            }
        });

        document.getElementById('download-pdf').addEventListener('click', () => {
            window.location.href = "{{ route('download.pdf') }}";
        });
    </script>
@endsection --}}
