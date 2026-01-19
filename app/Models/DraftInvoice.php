<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class DraftInvoice extends Model
{

    protected $fillable = [
        'draft_key',
        'client_id',
        'invoice_id',
        'status',
        'invoice_data',
        'expires_at'
    ];

    protected $casts = [
        'invoice_data' => 'array',
        'expires_at' => 'datetime'
    ];

    public const STATUS_PREVIEW = 'preview';
    public const STATUS_DRAFT   = 'draft';
    public const STATUS_ISSUED  = 'issued';


    /**
     * Generate unique draft key (like: a3x9k2m4p7q1...)
     */
    public static function generateKey(): string
    {
        return Str::random(32);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'User_ID');
        //                                     ↑          ↑
        //                              foreign key   local key (User table)
    }
    /**
     * Create new draft
     */
    public static function createDraft(array $invoiceData, int $clientId): self
    {
        return self::create([
            'draft_key' => self::generateKey(),
            'client_id' => $clientId,
            'invoice_data' => $invoiceData,
            'expires_at' => now()->addHours(24) // Draft expires in 24 hours
        ]);
    }

    /**
     * Get draft by key (and check if not expired)
     */
    public static function getByKey(string $key): ?self
    {
        return self::where('draft_key', $key)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Clean up expired drafts (run this via cron job)
     */
    public static function cleanExpired(): int
    {
        return self::where('expires_at', '<', now())->delete();
    }

    /**
     * Relationship to Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'Client_ID');
    }

    /**
     * Get the invoice if it exists
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * Check if draft is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get formatted invoice data with normalized items
     */
    public function getFormattedDataAttribute()
    {
        $data = is_array($this->invoice_data)
            ? $this->invoice_data
            : json_decode($this->invoice_data, true);

        // ✅ FIX: Normalize items to proper indexed array (0, 1, 2...)
        if (isset($data['items']) && is_array($data['items'])) {
            $data['items'] = array_values($data['items']);
        }

        return $data;
    }

    /**
     * Get invoice number from data
     */
    public function getInvoiceNumberAttribute()
    {
        $data = $this->formatted_data;
        return $data['invoice_no'] ?? 'N/A';
    }

    /**
     * Get total amount from data
     */
    public function getTotalAmountAttribute()
    {
        $data = $this->formatted_data;
        return $data['invoice_total_amount'] ?? 0;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'issued' => 'badge bg-success',
            'preview' => 'badge bg-warning',
            'draft' => 'badge bg-secondary',
            default => 'badge bg-info',
        };
    }

    /**
     * Scope to get only non-expired drafts
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    // ✅ Add these accessor methods for easier use in blade
    public function getActionLabelAttribute()
    {
        $labels = [
            'created' => 'Created',
            'edited' => 'Edited',
            'issued' => 'Issued',
            'sent' => 'Sent',
            'cancelled' => 'Cancelled',
            'viewed' => 'Viewed',
        ];
        return $labels[$this->action] ?? ucfirst($this->action);
    }

    public function getActionIconAttribute()
    {
        $icons = [
            'created' => 'fa-plus',
            'edited' => 'fa-edit',
            'issued' => 'fa-check',
            'sent' => 'fa-envelope',
            'cancelled' => 'fa-ban',
            'viewed' => 'fa-eye',
        ];
        return $icons[$this->action] ?? 'fa-circle';
    }

    public function getActionColorAttribute()
    {
        $colors = [
            'created' => 'success',
            'edited' => 'info',
            'issued' => 'primary',
            'sent' => 'warning',
            'cancelled' => 'danger',
            'viewed' => 'secondary',
        ];
        return $colors[$this->action] ?? 'secondary';
    }
    /**
     * Get the file associated with this invoice (if file_id exists in invoice_data)
     */
    public function file()
    {
        $fileId = $this->invoice_data['file_id'] ?? null;

        if ($fileId) {
            return File::where('File_ID', $fileId)->first();
        }

        return null;
    }

    /**
     * Get file ID from invoice data
     */
    public function getFileIdAttribute()
    {
        $data = $this->formatted_data;
        return $data['file_id'] ?? null;
    }
}
