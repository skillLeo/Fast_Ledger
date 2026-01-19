{{-- HMRC Statistics Cards Component --}}
@props(['stats'])

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Overdue Card -->
    <div class="stat-card stat-card-danger">
        <div class="stat-card-body">
            <div class="stat-card-content">
                <p class="stat-card-label">Overdue</p>
                <p class="stat-card-value text-danger">{{ $stats['overdue'] ?? 0 }}</p>
            </div>
            <div class="stat-card-icon bg-danger-light">
                <i class="fas fa-exclamation-circle text-danger"></i>
            </div>
        </div>
    </div>

    <!-- Due This Week Card -->
    <div class="stat-card stat-card-warning">
        <div class="stat-card-body">
            <div class="stat-card-content">
                <p class="stat-card-label">Due This Week</p>
                <p class="stat-card-value text-warning">{{ $stats['due_this_week'] ?? 0 }}</p>
            </div>
            <div class="stat-card-icon bg-warning-light">
                <i class="fas fa-clock text-warning"></i>
            </div>
        </div>
    </div>

    <!-- Due This Month Card -->
    <div class="stat-card stat-card-info">
        <div class="stat-card-body">
            <div class="stat-card-content">
                <p class="stat-card-label">Due This Month</p>
                <p class="stat-card-value text-info">{{ $stats['due_this_month'] ?? 0 }}</p>
            </div>
            <div class="stat-card-icon bg-info-light">
                <i class="fas fa-calendar-alt text-info"></i>
            </div>
        </div>
    </div>

    <!-- Fulfilled Card -->
    <div class="stat-card stat-card-success">
        <div class="stat-card-body">
            <div class="stat-card-content">
                <p class="stat-card-label">Fulfilled</p>
                <p class="stat-card-value text-success">{{ $stats['fulfilled_this_year'] ?? 0 }}</p>
            </div>
            <div class="stat-card-icon bg-success-light">
                <i class="fas fa-check-circle text-success"></i>
            </div>
        </div>
    </div>
</div>

<style>
/* Stat Cards Styling */
.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    border-left: 4px solid;
    transition: box-shadow 0.2s ease;
}

.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.stat-card-danger {
    border-left-color: #dc3545;
}

.stat-card-warning {
    border-left-color: #ffc107;
}

.stat-card-info {
    border-left-color: #0dcaf0;
}

.stat-card-success {
    border-left-color: #28a745;
}

.stat-card-body {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.stat-card-content {
    flex: 1;
}

.stat-card-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
}

.stat-card-value {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    line-height: 1;
}

.stat-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.bg-danger-light {
    background-color: #f8d7da;
}

.bg-warning-light {
    background-color: #fff3cd;
}

.bg-info-light {
    background-color: #cff4fc;
}

.bg-success-light {
    background-color: #d1e7dd;
}

.text-danger {
    color: #dc3545;
}

.text-warning {
    color: #ffc107;
}

.text-info {
    color: #0dcaf0;
}

.text-success {
    color: #28a745;
}

/* Grid utilities */
.grid {
    display: grid;
}

.grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

.gap-4 {
    gap: 1.5rem;
}

.mb-6 {
    margin-bottom: 2rem;
}

/* Responsive */
@media (min-width: 768px) {
    .md\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .lg\:grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}
</style>
