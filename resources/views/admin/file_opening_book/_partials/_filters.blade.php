<!-- Filter Row - UPDATED -->
<div class="row align-items-end mb-3">
    <div class="col-12">
        <!-- Navigation Tabs -->
        @include('admin.file_opening_book._components._navigation-tabs')

        <!-- Filters and Buttons Row -->
        <div class="d-flex align-items-center justify-content-between">
            <!-- Left Side: Filters (Dynamic Width) -->
            <div class="filters-wrapper">
                <!-- Matter Filters - Only visible for Matters tab -->
                <div id="matter-filters" class="d-flex align-items-center gap-2">
                    <!-- All Matters Dropdown -->
                    <div class="split-dropdown-wrapper" style="width: 150px; flex-shrink: 0;">
                        <button type="button" class="split-dropdown-btn" id="matter-dropdown" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <span class="dropdown-text">All Matters</span>
                            <span class="dropdown-icon">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item matter-filter" href="#" data-matter="all">All Matters</a></li>
                            @foreach ($matters as $matter)
                                <li><a class="dropdown-item matter-filter" href="#"
                                        data-matter="{{ $matter->id }}">{{ $matter->matter }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Live Matters Dropdown -->
                    <div class="split-dropdown-wrapper" style="width: 150px; flex-shrink: 0;">
                        <button type="button" class="split-dropdown-btn" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <span class="dropdown-text">Live Matters</span>
                            <span class="dropdown-icon">
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" data-status="all">All Status</a></li>
                            @foreach ($statuses as $status)
                                <li><a class="dropdown-item" href="#"
                                        data-status="{{ $status }}">{{ ucfirst($status) }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Search Input (Flexible Width) -->
                <div class="search-input-wrapper">
                    <div class="input-group">
                        <input type="text" class="search-input form-control" placeholder="Search ledger or name..."
                            aria-label="Search">
                        <button style="background: #13667d;" class="btn text-white border-0" type="button"
                            id="searchButton">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Side: Action Buttons -->
            <div>
                <!-- Matters Buttons -->
                <div id="matters-buttons" class="action-buttons-group">
                    <a href="{{ route('files.create') }}" class="btn addbutton border-none" role="button">
                        <i class="fas fa-plus"></i> Add New Matter
                    </a>
                    <x-download-dropdown pdf-id="downloadPDF" csv-id="downloadCSV" />
                </div>

                <!-- Suppliers Buttons -->
                <div id="suppliers-buttons" class="action-buttons-group d-none">
                    <a href="#" class="btn addbutton border-none" role="button">
                        <i class="fas fa-plus"></i> Add New Supplier
                    </a>
                    <x-download-dropdown pdf-id="downloadSupplierPDF" csv-id="downloadSupplierCSV" />
                </div>

                <!-- Employees Buttons -->
                <div id="employees-buttons" class="action-buttons-group d-none">
                    <a href="{{route('employees.create')}}" class="btn addbutton border-none" role="button">
                        <i class="fas fa-plus"></i> Add New Employee
                    </a>
                    <x-download-dropdown pdf-id="downloadEmployeePDF" csv-id="downloadEmployeeCSV" />
                </div>
            </div>
        </div>
    </div>
</div>