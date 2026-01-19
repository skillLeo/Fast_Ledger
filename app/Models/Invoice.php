<?php

namespace App\Models;

use App\Models\CompanyModule\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    // ==========================================
    // ✅ STATUS CONSTANTS
    // ==========================================
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_PAID = 'paid';
    const STATUS_PARTIALLY_PAID = 'partially_paid';
    const STATUS_OVERDUE = 'overdue';

    protected $fillable = [
        'customer',           // Keep for backward compatibility
        'customer_type',      // ✅ NEW: Polymorphic type
        'invoice_date',
        'operation_date',
        'due_date',
        'invoice_no',
        'invoice_ref',
        'status',
        'series_id',
        'net_amount',
        'vat_amount',
        'total_amount',
        'paid',
        'balance',
        'notes',
        'company_id',
        'issued_at',
        'issued_by',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'operation_date' => 'date',
        'due_date' => 'date',
        'issued_at' => 'datetime',
        'net_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    // ==========================================
    // ✅ POLYMORPHIC RELATIONSHIPS (NEW!)
    // ==========================================

    /**
     * Get the owning customer (File or Customer model)
     */
    public function customerable()
    {
        return $this->morphTo('customerable', 'customer_type', 'customer');
    }

    /**
     * ✅ BACKWARD COMPATIBILITY: Keep old relationship for existing code
     * This will be deprecated once we fully migrate
     */
    public function customerFile()
    {
        return $this->belongsTo(File::class, 'customer', 'File_ID');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'customer', 'id');
    }

    // ==========================================
    // ✅ OTHER RELATIONSHIPS (Unchanged)
    // ==========================================

    public function items()
    {
        return $this->hasMany(DraftInvoiceItem::class, 'invoice_id')->orderBy('order_index');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'invoice_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(InvoiceDocument::class, 'invoice_id');
    }

    public function series()
    {
        return $this->belongsTo(InvoiceSeries::class, 'series_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(InvoiceActivityLog::class, 'invoice_id')->latest();
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by', 'Client_ID');
    }

    public function issuedByUser()
    {
        return $this->belongsTo(User::class, 'issued_by', 'Client_ID');
    }

    // ==========================================
    // ✅ COMPUTED ATTRIBUTES (UPDATED!)
    // ==========================================

    public function getDocumentCountAttribute()
    {
        if (!$this->relationLoaded('documents')) {
            return $this->documents()->count();
        }
        return $this->documents->count();
    }

    /**
     * ✅ UPDATED: Works with both File and Customer models
     */
    public function getCustomerNameAttribute()
    {
        // Check if this is a purchase invoice
        $isPurchase = (strpos($this->invoice_no, 'PUR') === 0 || strpos($this->invoice_no, 'PUC') === 0);

        if ($isPurchase) {
            // For purchases, customer is supplier
            $supplier = $this->supplier;
            if ($supplier) {
                // Use contact_name, or fall back to first_name + last_name
                return $supplier->contact_name ?: trim($supplier->first_name . ' ' . $supplier->last_name);
            }
            return 'Unknown Supplier';
        } else {
            // For sales, customer is file
            $customer = $this->customerFile;
            if ($customer) {
                return trim($customer->First_Name . ' ' . $customer->Last_Name);
            }
            return 'Unknown Customer';
        }
    }

    /**
     * ✅ UPDATED: Works with both File and Customer models
     */
    public function getCustomerRefAttribute()
    {
        // Check if this is a purchase invoice
        $isPurchase = (strpos($this->invoice_no, 'PUR') === 0 || strpos($this->invoice_no, 'PUC') === 0);

        if ($isPurchase) {
            // For purchases, get supplier reference
            $supplier = $this->supplier;
            return $supplier ? ($supplier->account_number ?: $supplier->reference ?: '-') : '-';
        } else {
            // For sales, get customer reference
            $customer = $this->customerFile;
            return $customer ? ($customer->Ledger_Ref ?? '-') : '-';
        }
    }

    /**
     * ✅ Status badge CSS
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'badge bg-secondary',
            self::STATUS_SENT => 'badge bg-info',
            self::STATUS_PAID => 'badge bg-success',
            self::STATUS_PARTIALLY_PAID => 'badge bg-warning text-dark',
            self::STATUS_OVERDUE => 'badge bg-danger',
            default => 'badge bg-secondary'
        };
    }

    /**
     * ✅ Status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENT => 'Sent',
            self::STATUS_PAID => 'Paid',
            self::STATUS_PARTIALLY_PAID => 'Partially Paid',
            self::STATUS_OVERDUE => 'Overdue',
            default => 'Unknown'
        };
    }

    // ==========================================
    // ✅ HELPER METHODS (Unchanged)
    // ==========================================

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === self::STATUS_PARTIALLY_PAID;
    }

    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }
        return $this->due_date->isPast() && $this->balance > 0;
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENT => 'Sent',
            self::STATUS_PAID => 'Paid',
            self::STATUS_PARTIALLY_PAID => 'Partially Paid',
            self::STATUS_OVERDUE => 'Overdue',
        ];
    }

    public function updateStatus(string $newStatus, ?float $paymentAmount = null): void
    {
        $this->status = $newStatus;

        switch ($newStatus) {
            case self::STATUS_PAID:
                $this->paid = $this->total_amount;
                $this->balance = 0;
                break;

            case self::STATUS_PARTIALLY_PAID:
                if ($paymentAmount !== null) {
                    $this->paid += $paymentAmount;
                    $this->balance = $this->total_amount - $this->paid;

                    if ($this->balance < 0) {
                        $this->balance = 0;
                    }
                }
                break;

            case self::STATUS_OVERDUE:
            case self::STATUS_SENT:
            case self::STATUS_DRAFT:
                break;
        }

        $this->save();
    }

    // ==========================================
    // ✅ MODEL EVENTS (UPDATED!)
    // ==========================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (is_null($invoice->paid)) {
                $invoice->paid = 0;
            }
            if (is_null($invoice->balance)) {
                $invoice->balance = $invoice->total_amount;
            }
            if (is_null($invoice->status)) {
                $invoice->status = self::STATUS_DRAFT;
            }

            // ✅ NEW: Auto-set customer_type if not set
            if (is_null($invoice->customer_type) && !is_null($invoice->customer)) {
                // Default to File model for backward compatibility
                $invoice->customer_type = File::class;
            }
        });

        static::updating(function ($invoice) {
            if ($invoice->isOverdue() && $invoice->status !== self::STATUS_PAID) {
                $invoice->status = self::STATUS_OVERDUE;
            }
        });

        static::deleting(function ($invoice) {
            $invoice->documents()->each(function ($document) {
                $document->delete();
            });
            $invoice->items()->delete();
            $invoice->activityLogs()->delete();
        });
    }
}
