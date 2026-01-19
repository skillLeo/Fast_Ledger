<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceActivityLog extends Model
{
    // Only created_at, no updated_at
    const UPDATED_AT = null;
    protected $table = 'invoice_activity_logs'; // ← Make sure this matches your table name

    protected $fillable = [
        'invoice_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'notes',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'User_ID');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByInvoice($query, int $invoiceId)
    {
        return $query->where('invoice_id', $invoiceId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Quick log method - static helper
     */
    public static function log($invoiceId, $action, $oldData = null, $newData = null, $notes = null)
    {
        return self::create([
            'invoice_id' => $invoiceId,
            'user_id' => auth()->id(),                              // ✅ REQUIRED
            'action' => $action,
            'old_values' => $oldData ? json_encode($oldData) : null,
            'new_values' => $newData ? json_encode($newData) : null,
            'notes' => $notes,
            'ip_address' => request()->ip(),                        // Optional
            'user_agent' => request()->userAgent(),                 // Optional
        ]);
    }

    /**
     * Get action label
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created' => 'Created',
            'edited' => 'Edited',
            'issued' => 'Issued',
            'sent' => 'Sent',
            'cancelled' => 'Cancelled',
            'viewed' => 'Viewed',
            default => ucfirst($this->action)
        };
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute(): string
    {
        return match ($this->action) {
            'created' => 'fa-plus',
            'edited' => 'fa-edit',
            'issued' => 'fa-check',
            'sent' => 'fa-envelope',
            'cancelled' => 'fa-ban',
            'viewed' => 'fa-eye',
            default => 'fa-circle'
        };
    }

    /**
     * Get action color
     */
    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'created' => 'success',
            'edited' => 'info',
            'issued' => 'primary',
            'sent' => 'warning',
            'cancelled' => 'danger',
            'viewed' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Get formatted changes
     */
    public function getFormattedChangesAttribute(): string
    {
        if (empty($this->old_values) && empty($this->new_values)) {
            return 'No changes recorded';
        }

        $changes = [];

        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? null;
                if ($oldValue != $newValue) {
                    $changes[] = ucfirst(str_replace('_', ' ', $key)) . ": {$oldValue} → {$newValue}";
                }
            }
        }

        return implode(', ', $changes) ?: 'See details';
    }
}
