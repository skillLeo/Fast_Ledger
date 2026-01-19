<div class="row g-3 mb-4">
    <!-- Overdue -->
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-danger rounded-3 {{ $stats['overdue'] > 0 ? 'bg-danger bg-opacity-10' : '' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Overdue</h6>
                        <h2 class="mb-0 text-danger">{{ $stats['overdue'] }}</h2>
                    </div>
                    <div class="avatar avatar-lg bg-danger bg-opacity-25 rounded">
                        <i class="fas fa-exclamation-circle fa-2x text-danger"></i>
                    </div>
                </div>
                @if($stats['overdue'] > 0)
                    <div class="mt-2">
                        <small class="text-danger">
                            <i class="fas fa-arrow-up me-1"></i>
                            Requires immediate attention
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Due This Week -->
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-warning rounded-3 {{ $stats['due_this_week'] > 0 ? 'bg-warning bg-opacity-10' : '' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Due This Week</h6>
                        <h2 class="mb-0 text-warning">{{ $stats['due_this_week'] }}</h2>
                    </div>
                    <div class="avatar avatar-lg bg-warning bg-opacity-25 rounded">
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        Next 7 days
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Due This Month -->
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-info rounded-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Due This Month</h6>
                        <h2 class="mb-0 text-info">{{ $stats['due_this_month'] }}</h2>
                    </div>
                    <div class="avatar avatar-lg bg-info bg-opacity-25 rounded">
                        <i class="fas fa-calendar-alt fa-2x text-info"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        Next 30 days
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Fulfilled This Year -->
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-success rounded-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted mb-2">Fulfilled ({{ now()->year }})</h6>
                        <h2 class="mb-0 text-success">{{ $stats['fulfilled_this_year'] }}</h2>
                    </div>
                    <div class="avatar avatar-lg bg-success bg-opacity-25 rounded">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        Total: {{ $stats['total_fulfilled'] }} all time
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Bar -->
<div class="card shadow-sm border-0 rounded-3 mb-4">
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="border-end">
                    <h3 class="mb-1">{{ $stats['total_open'] }}</h3>
                    <p class="text-muted mb-0">Total Open</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border-end">
                    <h3 class="mb-1">{{ $stats['upcoming'] }}</h3>
                    <p class="text-muted mb-0">Upcoming</p>
                </div>
            </div>
            <div class="col-md-4">
                <h3 class="mb-1">{{ $stats['total_fulfilled'] }}</h3>
                <p class="text-muted mb-0">Total Fulfilled</p>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

