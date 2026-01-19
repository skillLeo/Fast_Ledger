@props([
    'column' => '',
    'label' => '',
    'type' => 'search', // 'search', 'sort', 'dropdown', 'both' (search+sort)
    'options' => [], // For dropdown type
])

<th class="table-search-header" {{ $attributes }}>
    <div class="header-content">
        <span class="header-text" data-column="{{ $column }}">{{ $label }}</span>

        @if ($type === 'search' || $type === 'both')
            <i class="bi bi-search" onclick="TableSearch.openSearch('{{ $column }}')"></i>
            <div class="search-input-container" data-column="{{ $column }}">
                <input type="text" placeholder="Search..." id="search-{{ $column }}"
                    oninput="TableSearch.liveSearch('{{ $column }}')">
                <button class="btn-close" onclick="TableSearch.closeSearch('{{ $column }}')">×</button>
            </div>
        @endif

        @if ($type === 'sort' || $type === 'both')
            <i class="bi bi-funnel" onclick="TableSearch.toggleFilter('{{ $column }}', 'sort')"></i>
            <div class="filter-dropdown filter-sort" data-column="{{ $column }}" data-type="sort">
                <div class="filter-option" onclick="TableSearch.applySort('{{ $column }}', 'asc')">
                    <i class="bi bi-sort-up"></i> Sort Ascending
                </div>
                <div class="filter-option" onclick="TableSearch.applySort('{{ $column }}', 'desc')">
                    <i class="bi bi-sort-down"></i> Sort Descending
                </div>
            </div>
        @endif

        @if ($type === 'dropdown')
            <i class="bi bi-filter" onclick="TableSearch.toggleFilter('{{ $column }}', 'dropdown')"></i>
            <div class="filter-dropdown filter-dropdown-list" data-column="{{ $column }}" data-type="dropdown">
                <div class="filter-option"
                    onclick="TableSearch.applyDropdownFilter('{{ $column }}', 'all', this)">
                    <i class="bi bi-x-circle"></i> Show All
                </div>
                @foreach ($options as $value => $optionLabel)
                    <div class="filter-option"
                        onclick="TableSearch.applyDropdownFilter('{{ $column }}', '{{ $value }}', this)">
                        {{ $optionLabel }}
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</th>
