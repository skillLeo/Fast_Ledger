<div class="collapse" id="filterForm">
    <div class="card-body border-bottom">
        <form id="obligationsFilterForm" method="GET">
            <div class="row g-3">
                <!-- Business Filter -->
                <div class="col-md-3">
                    <label for="business_id" class="form-label">Business</label>
                    <select name="business_id" id="business_id" class="form-select">
                        <option value="">All Businesses</option>
                        @foreach($filterOptions['businesses'] as $business)
                            <option value="{{ $business['id'] }}" 
                                    {{ request('business_id') === $business['id'] ? 'selected' : '' }}>
                                {{ $business['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach($filterOptions['statuses'] as $status)
                            <option value="{{ $status }}" 
                                    {{ request('status') === $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Obligation Type Filter -->
                <div class="col-md-2">
                    <label for="obligation_type" class="form-label">Type</label>
                    <select name="obligation_type" id="obligation_type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($filterOptions['obligation_types'] as $type)
                            <option value="{{ $type }}" 
                                    {{ request('obligation_type') === $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('-', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tax Year Filter -->
                <div class="col-md-2">
                    <label for="tax_year" class="form-label">Tax Year</label>
                    <select name="tax_year" id="tax_year" class="form-select">
                        <option value="">All Years</option>
                        @foreach($filterOptions['tax_years'] as $year)
                            <option value="{{ $year }}" 
                                    {{ request('tax_year') === $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Urgency Filter -->
                <div class="col-md-2">
                    <label for="urgency" class="form-label">Urgency</label>
                    <select name="urgency" id="urgency" class="form-select">
                        <option value="">All Levels</option>
                        @foreach($filterOptions['urgency_levels'] as $level)
                            <option value="{{ $level }}" 
                                    {{ request('urgency') === $level ? 'selected' : '' }}>
                                {{ ucfirst($level) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="col-md-1 d-flex align-items-end">
                    <div class="btn-group w-100" role="group">
                        <button type="submit" class="btn btn-primary" title="Apply Filters">
                            <i class="fas fa-filter"></i>
                        </button>
                        <button type="button" class="btn btn-secondary" id="resetFilters" title="Reset Filters">
                            <i class="fas fa-redo"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sort Options -->
            <div class="row g-3 mt-2">
                <div class="col-md-3">
                    <label for="sort_by" class="form-label">Sort By</label>
                    <select name="sort_by" id="sort_by" class="form-select">
                        <option value="due_date" {{ request('sort_by', 'due_date') === 'due_date' ? 'selected' : '' }}>
                            Due Date
                        </option>
                        <option value="period_start_date" {{ request('sort_by') === 'period_start_date' ? 'selected' : '' }}>
                            Period Start
                        </option>
                        <option value="period_end_date" {{ request('sort_by') === 'period_end_date' ? 'selected' : '' }}>
                            Period End
                        </option>
                        <option value="status" {{ request('sort_by') === 'status' ? 'selected' : '' }}>
                            Status
                        </option>
                        <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>
                            Created Date
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="sort_order" class="form-label">Order</label>
                    <select name="sort_order" id="sort_order" class="form-select">
                        <option value="asc" {{ request('sort_order', 'asc') === 'asc' ? 'selected' : '' }}>
                            Ascending
                        </option>
                        <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>
                            Descending
                        </option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    #filterForm .form-label {
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    #filterForm .form-select {
        font-size: 0.875rem;
    }
</style>

