<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PendingTransaction extends Model
{
    use HasFactory;

    // ==========================================
    // CONSTANTS
    // ==========================================
    
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_BANK_FEED = 'bank_feed';
    
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    // ==========================================
    // FILLABLE ATTRIBUTES
    // ==========================================
    
    protected $fillable = [
        // Existing columns
        'uploaded_file_id',
        'transaction_id',
        'date',
        'time',
        'type',
        'name',
        'emoji',
        'category',
        'amount',
        'currency',
        'local_amount',
        'local_currency',
        'notes_and_tags',
        'address',
        'receipt',
        'description',
        'category_split',
        'money_out',
        'money_in',
        'status',
        'raw_data',
        'bank_account_id',
        'completed_at',
        
        // ✅ NEW: Bank Feed Columns
        'source',
        'finexer_transaction_id',
        'finexer_reference',
        'finexer_raw_data',
        'bank_feed_fetched_at',
    ];

    // ==========================================
    // CASTS
    // ==========================================
    
    protected $casts = [
        'raw_data' => 'array',
        'finexer_raw_data' => 'array',
        'date' => 'date',
        'amount' => 'decimal:2',
        'local_amount' => 'decimal:2',
        'money_out' => 'decimal:2',
        'money_in' => 'decimal:2',
        'completed_at' => 'datetime',
        'bank_feed_fetched_at' => 'datetime',
    ];

    // ==========================================
    // EXISTING RELATIONSHIPS
    // ==========================================
    
    public function uploadedFile()
    {
        return $this->belongsTo(UploadedFile::class);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id', 'Bank_Account_ID');
    }

    // ==========================================
    // ✅ NEW: BANK FEED ACCESSORS
    // ==========================================
    
    /**
     * Check if transaction is from bank feed
     */
    public function isFromBankFeed(): bool
    {
        return $this->source === self::SOURCE_BANK_FEED;
    }

    /**
     * Check if transaction is manual upload
     */
    public function isManualUpload(): bool
    {
        return $this->source === self::SOURCE_MANUAL;
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if transaction failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Get source display label
     */
    public function getSourceLabelAttribute(): string
    {
        $labels = [
            self::SOURCE_MANUAL => 'Manual Upload',
            self::SOURCE_BANK_FEED => 'Bank Feed',
        ];

        return $labels[$this->source] ?? 'Unknown';
    }

    /**
     * Get source icon class
     */
    public function getSourceIconAttribute(): string
    {
        $icons = [
            self::SOURCE_MANUAL => 'fa-upload',
            self::SOURCE_BANK_FEED => 'fa-sync',
        ];

        return $icons[$this->source] ?? 'fa-question';
    }

    /**
     * Get source badge color
     */
    public function getSourceBadgeAttribute(): string
    {
        $badges = [
            self::SOURCE_MANUAL => 'badge-info',
            self::SOURCE_BANK_FEED => 'badge-success',
        ];

        return $badges[$this->source] ?? 'badge-secondary';
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            self::STATUS_PENDING => 'badge-warning',
            self::STATUS_COMPLETED => 'badge-success',
            self::STATUS_FAILED => 'badge-danger',
        ];

        return $badges[$this->status] ?? 'badge-secondary';
    }

    /**
     * Check if transaction is credit (money in)
     */
    public function isCreditAttribute(): bool
    {
        return $this->amount > 0 || $this->money_in > 0;
    }

    /**
     * Check if transaction is debit (money out)
     */
    public function isDebitAttribute(): bool
    {
        return $this->amount < 0 || $this->money_out > 0;
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return '£' . number_format(abs($this->amount), 2);
    }

    /**
     * Get transaction type (payment/receipt)
     */
    public function getTransactionTypeAttribute(): string
    {
        return $this->amount > 0 ? 'receipt' : 'payment';
    }

    // ==========================================
    // ✅ NEW: BANK FEED SCOPES
    // ==========================================
    
    /**
     * Scope: Get only bank feed transactions
     */
    public function scopeFromBankFeed($query)
    {
        return $query->where('source', self::SOURCE_BANK_FEED);
    }

    /**
     * Scope: Get only manual transactions
     */
    public function scopeManualUpload($query)
    {
        return $query->where('source', self::SOURCE_MANUAL);
    }

    /**
     * Scope: Get by source
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope: Get pending transactions only
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Get completed transactions only
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Get by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Get for specific bank account
     */
    public function scopeForBankAccount($query, int $bankAccountId)
    {
        return $query->where('bank_account_id', $bankAccountId);
    }

    /**
     * Scope: Get with Finexer ID
     */
    public function scopeWithFinexerId($query)
    {
        return $query->whereNotNull('finexer_transaction_id');
    }

    /**
     * Scope: Get by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // ==========================================
    // ✅ NEW: BANK FEED METHODS
    // ==========================================
    
    /**
     * Check if transaction has external bank reference
     */
    public function hasFinexerReference(): bool
    {
        return !empty($this->finexer_transaction_id);
    }

    /**
     * Get bank type (CL/OF) from related bank account
     */
    public function getBankTypeId(): ?int
    {
        return $this->bankAccount?->Bank_Type_ID;
    }

    /**
     * Check if transaction is from client bank (CL)
     */
    public function isFromClientBank(): bool
    {
        return $this->getBankTypeId() === 1;
    }

    /**
     * Check if transaction is from office bank (OF)
     */
    public function isFromOfficeBank(): bool
    {
        return $this->getBankTypeId() === 2;
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
        ]);
    }

    /**
     * Get validation rules based on bank type
     */
    public function getValidationRules(): array
    {
        $bankTypeId = $this->getBankTypeId();

        if ($bankTypeId === 1) {
            // Client Bank (CL) - Requires Entry Details
            return [
                'entry_details' => 'required|string|max:500',
                'ledger_ref' => 'nullable|string',
                'chart_of_account_id' => 'nullable|integer',
                'vat_id' => 'nullable|integer',
            ];
        } elseif ($bankTypeId === 2) {
            // Office Bank (OF) - Requires Ledger + Account Ref
            return [
                'ledger_ref' => 'required|string',
                'chart_of_account_id' => 'required|integer',
                'vat_id' => 'nullable|integer',
                'entry_details' => 'nullable|string|max:500',
            ];
        }

        return [];
    }

    // ==========================================
    // ✅ NEW: STATIC HELPER METHODS
    // ==========================================
    
    /**
     * Find by Finexer transaction ID
     */
    public static function findByFinexerId(string $finexerId): ?self
    {
        return self::where('finexer_transaction_id', $finexerId)->first();
    }

    /**
     * Check if Finexer transaction already exists
     */
    public static function finexerTransactionExists(string $finexerId): bool
    {
        return self::where('finexer_transaction_id', $finexerId)->exists();
    }

    /**
     * Get statistics by source
     */
    public static function getStatsBySource(int $bankAccountId = null): array
    {
        $query = self::query();

        if ($bankAccountId) {
            $query->where('bank_account_id', $bankAccountId);
        }

        $stats = $query->selectRaw('
                source,
                status,
                COUNT(*) as count,
                SUM(money_in) as total_in,
                SUM(money_out) as total_out
            ')
            ->groupBy('source', 'status')
            ->get();

        return $stats->groupBy('source')->toArray();
    }

    /**
     * Get pending count for bank account
     */
    public static function getPendingCount(int $bankAccountId, string $source = null): int
    {
        $query = self::where('bank_account_id', $bankAccountId)
            ->where('status', self::STATUS_PENDING);

        if ($source) {
            $query->where('source', $source);
        }

        return $query->count();
    }

    /**
     * Get completed count for bank account
     */
    public static function getCompletedCount(int $bankAccountId, string $source = null): int
    {
        $query = self::where('bank_account_id', $bankAccountId)
            ->where('status', self::STATUS_COMPLETED);

        if ($source) {
            $query->where('source', $source);
        }

        return $query->count();
    }

    /**
     * Create from bank feed data
     */
    public static function createFromBankFeed(int $bankAccountId, array $transactionData): self
    {
        $amount = (float) ($transactionData['amount'] ?? 0);

        return self::create([
            'bank_account_id' => $bankAccountId,
            'source' => self::SOURCE_BANK_FEED,
            'finexer_transaction_id' => $transactionData['id'],
            'finexer_reference' => $transactionData['reference'] ?? null,
            'date' => $transactionData['date'],
            'description' => $transactionData['description'] ?? null,
            'amount' => $amount,
            'money_in' => $amount > 0 ? $amount : 0,
            'money_out' => $amount < 0 ? abs($amount) : 0,
            'currency' => $transactionData['currency'] ?? 'GBP',
            'status' => self::STATUS_PENDING,
            'finexer_raw_data' => $transactionData,
            'bank_feed_fetched_at' => now(),
        ]);
    }
}