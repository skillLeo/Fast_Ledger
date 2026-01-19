{{-- ========================================================================
     ACTIVITY LOG SECTION
     File: resources/views/admin/day_book/_partials/_activity_log.blade.php
     ======================================================================== --}}

<div class="card custom-card mt-3" id="activityLogSection" style="display: none;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0">
            <i class="fas fa-history me-2"></i>Activity Log
        </h6>
        <button type="button" class="btn btn-sm btn-light" onclick="toggleActivityLog()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="card-body">
        <div id="activityLogContent">
            {{-- Activity log will be loaded here via AJAX --}}
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading activity log...</p>
            </div>
        </div>
    </div>
</div>

<style>
/* ========================================================================
   ACTIVITY LOG STYLES
   ======================================================================== */

/* Activity Timeline */
.activity-timeline {
    position: relative;
    padding-left: 10px;
}

.activity-item {
    position: relative;
    display: flex;
    margin-bottom: 20px;
}

.activity-item:last-child .activity-line {
    display: none;
}

/* Icon Wrapper */
.activity-icon-wrapper {
    position: relative;
    flex-shrink: 0;
    margin-right: 20px;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    position: relative;
    z-index: 2;
}

/* Connecting Line */
.activity-line {
    position: absolute;
    left: 50%;
    top: 40px;
    width: 2px;
    height: calc(100% + 20px);
    background: #e9ecef;
    transform: translateX(-50%);
    z-index: 1;
}

/* Content Box */
.activity-content {
    flex-grow: 1;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 3px solid #dee2e6;
    transition: all 0.3s ease;
}

.activity-content:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateX(5px);
}

/* Action-Specific Colors */
.activity-item[data-action="created"] .activity-content {
    border-left-color: #198754;
}

.activity-item[data-action="edited"] .activity-content {
    border-left-color: #0dcaf0;
}

.activity-item[data-action="issued"] .activity-content {
    border-left-color: #0d6efd;
}

.activity-item[data-action="sent"] .activity-content {
    border-left-color: #ffc107;
}

.activity-item[data-action="cancelled"] .activity-content {
    border-left-color: #dc3545;
}

.activity-item[data-action="viewed"] .activity-content {
    border-left-color: #6c757d;
}

/* Changes Display */
.activity-changes {
    background: white;
    border-radius: 4px;
    padding: 10px 15px;
    margin-top: 10px;
    border: 1px solid #e9ecef;
}

.activity-changes ul {
    margin: 0;
    padding-left: 20px;
}

.activity-changes li {
    margin-bottom: 5px;
    font-size: 13px;
}

/* Meta Information */
.activity-meta {
    font-size: 12px;
    opacity: 0.7;
}

/* Empty State */
.activity-empty-state {
    text-align: center;
    padding: 40px 20px;
}

.activity-empty-state i {
    font-size: 48px;
    color: #dee2e6;
    margin-bottom: 15px;
}

/* Responsive */
@media (max-width: 768px) {
    .activity-icon-wrapper {
        margin-right: 15px;
    }
    
    .activity-icon {
        width: 35px;
        height: 35px;
        font-size: 14px;
    }
    
    .activity-content {
        padding: 12px;
    }
}
</style>

<script>
/**
 * ========================================================================
 * ACTIVITY LOG MANAGER
 * ======================================================================== 
 */
class ActivityLogManager {
    constructor() {
        this.currentInvoiceId = null;
        this.activityLogSection = document.getElementById('activityLogSection');
        this.activityLogContent = document.getElementById('activityLogContent');
    }

    /**
     * Show activity log for invoice
     */
    async showActivityLog(invoiceId) {
        if (!invoiceId) {
            console.error('❌ Invoice ID is required');
            return;
        }

        this.currentInvoiceId = invoiceId;
        console.log('📋 Loading activity log for invoice:', invoiceId);
        
        // Show section
        this.activityLogSection.style.display = 'block';
        
        // Scroll to activity log
        this.activityLogSection.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
        
        // Load activity log data
        await this.loadActivityLog(invoiceId);
    }

    /**
     * Load activity log from server
     */
    async loadActivityLog(invoiceId) {
        try {
            // Show loading state
            this.showLoading();

            // Fetch activity log
            const response = await fetch(`/invoices/${invoiceId}/activity-log`);
            
            if (!response.ok) {
                throw new Error('Failed to load activity log');
            }

            const data = await response.json();

            if (data.success) {
                console.log('✅ Activity log loaded:', data.activities.length, 'items');
                this.renderActivityLog(data.activities);
            } else {
                this.showError(data.message || 'Failed to load activity log');
            }

        } catch (error) {
            console.error('❌ Activity log error:', error);
            this.showError('Failed to load activity log. Please try again.');
        }
    }

    /**
     * Render activity log items
     */
    renderActivityLog(activities) {
        if (!activities || activities.length === 0) {
            this.activityLogContent.innerHTML = `
                <div class="activity-empty-state">
                    <i class="fas fa-history"></i>
                    <p class="text-muted mb-0">No activity log yet</p>
                </div>
            `;
            return;
        }

        const timeline = document.createElement('div');
        timeline.className = 'activity-timeline';

        activities.forEach(activity => {
            const item = this.createActivityItem(activity);
            timeline.appendChild(item);
        });

        this.activityLogContent.innerHTML = '';
        this.activityLogContent.appendChild(timeline);
    }

    /**
     * Create activity log item
     */
    createActivityItem(activity) {
        const item = document.createElement('div');
        item.className = 'activity-item d-flex mb-3';
        item.dataset.action = activity.action;

        // Get action metadata
        const metadata = this.getActionMetadata(activity.action);

        item.innerHTML = `
            <div class="activity-icon-wrapper">
                <div class="activity-icon bg-${metadata.color} text-white">
                    <i class="fas ${metadata.icon}"></i>
                </div>
                <div class="activity-line"></div>
            </div>
            <div class="activity-content flex-grow-1">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong class="text-${metadata.color}">${metadata.label}</strong>
                        <span class="text-muted ms-2">by ${activity.user_name || 'System'}</span>
                    </div>
                    <small class="text-muted">${this.formatTime(activity.created_at)}</small>
                </div>
                ${activity.notes ? `<p class="mb-2 text-muted small">${activity.notes}</p>` : ''}
                ${this.renderChanges(activity)}
                <div class="activity-meta mt-2">
                    <small class="text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i>${activity.ip_address || 'N/A'}
                        <i class="fas fa-laptop ms-3 me-1"></i>${this.formatUserAgent(activity.user_agent)}
                    </small>
                </div>
            </div>
        `;

        return item;
    }

    /**
     * Get action metadata (color, icon, label)
     */
    getActionMetadata(action) {
        const metadata = {
            'created': { color: 'success', icon: 'fa-plus', label: 'Created' },
            'edited': { color: 'info', icon: 'fa-edit', label: 'Edited' },
            'issued': { color: 'primary', icon: 'fa-check', label: 'Issued' },
            'sent': { color: 'warning', icon: 'fa-envelope', label: 'Sent' },
            'cancelled': { color: 'danger', icon: 'fa-ban', label: 'Cancelled' },
            'viewed': { color: 'secondary', icon: 'fa-eye', label: 'Viewed' },
        };

        return metadata[action] || { 
            color: 'secondary', 
            icon: 'fa-circle', 
            label: action.charAt(0).toUpperCase() + action.slice(1) 
        };
    }

    /**
     * Render changes
     */
    renderChanges(activity) {
        if (!activity.old_values || !activity.new_values) {
            return '';
        }

        const changes = [];
        const oldValues = typeof activity.old_values === 'string' 
            ? JSON.parse(activity.old_values) 
            : activity.old_values;
        
        const newValues = typeof activity.new_values === 'string' 
            ? JSON.parse(activity.new_values) 
            : activity.new_values;

        for (const key in newValues) {
            if (oldValues[key] != newValues[key]) {
                const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                changes.push(`
                    <li>
                        <strong>${label}:</strong> 
                        <span class="text-danger">${oldValues[key] || 'N/A'}</span> 
                        → 
                        <span class="text-success">${newValues[key]}</span>
                    </li>
                `);
            }
        }

        if (changes.length === 0) {
            return '';
        }

        return `
            <div class="activity-changes">
                <strong class="d-block mb-2">Changes:</strong>
                <ul class="mb-0">
                    ${changes.join('')}
                </ul>
            </div>
        `;
    }

    /**
     * Format timestamp
     */
    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
        if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
        if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;

        return date.toLocaleDateString('en-GB', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    /**
     * Format user agent
     */
    formatUserAgent(userAgent) {
        if (!userAgent) return 'Unknown Device';

        if (userAgent.includes('Chrome')) return 'Chrome';
        if (userAgent.includes('Firefox')) return 'Firefox';
        if (userAgent.includes('Safari')) return 'Safari';
        if (userAgent.includes('Edge')) return 'Edge';
        
        return 'Browser';
    }

    /**
     * Show loading state
     */
    showLoading() {
        this.activityLogContent.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading activity log...</p>
            </div>
        `;
    }

    /**
     * Show error message
     */
    showError(message) {
        this.activityLogContent.innerHTML = `
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
    }

    /**
     * Hide activity log
     */
    hide() {
        this.activityLogSection.style.display = 'none';
        this.currentInvoiceId = null;
    }
}

// ========================================================================
// INITIALIZATION
// ======================================================================== 

// Initialize activity log manager
let activityLogManager;

document.addEventListener('DOMContentLoaded', function() {
    activityLogManager = new ActivityLogManager();
    console.log('✅ Activity Log Manager initialized');
});

/**
 * Toggle activity log visibility
 */
function toggleActivityLog() {
    if (activityLogManager) {
        activityLogManager.hide();
    }
}

/**
 * Show activity log for invoice (called from button)
 */
function showInvoiceActivityLog(invoiceId) {
    if (activityLogManager) {
        activityLogManager.showActivityLog(invoiceId);
    } else {
        console.error('❌ Activity Log Manager not initialized');
    }
}
</script>