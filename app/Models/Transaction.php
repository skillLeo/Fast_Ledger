<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transaction';
    protected $primaryKey = 'Transaction_ID';

    public $timestamps = false;

    // ==========================================
    // CONSTANTS
    // ==========================================

    public const MONEY_IN = 1;
    public const MONEY_OUT = 2;
    public const MONEY_NEUTRAL = 0;

    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_BANK_FEED = 'bank_feed';

    // ==========================================
    // FILLABLE ATTRIBUTES
    // ==========================================

    protected $fillable = [
        // Existing columns
        'Transaction_Date',
        'File_ID',
        'Bank_Account_ID',
        'Paid_In_Out',
        'Payment_Type_ID',
        'Account_Ref_ID',
        'chart_of_account_id',
        'invoice_id',
        'Cheque',
        'Amount',
        'Description',
        'Is_Imported',
        'entry_type',
        'Created_By',
        'Created_On',
        'Modified_By',
        'Modified_On',
        'Deleted_By',
        'Deleted_On',
        'VAT_ID',
        'Is_Bill',
        'Transaction_Code',
        // 'Payee_ID',

        // ✅ NEW: Bank Feed Columns
        'source',
        'finexer_transaction_id',
        'finexer_reference',
        'bank_feed_synced_at',
    ];

    // ==========================================
    // CASTS
    // ==========================================

    protected $casts = [
        'Transaction_Date' => 'datetime',
        'Amount' => 'decimal:2',
        'Is_Imported' => 'integer',
        'Is_Bill' => 'integer',
        'bank_feed_synced_at' => 'datetime',
    ];

    // ==========================================
    // EXISTING ACCESSORS
    // ==========================================

    public function getBankAccountNameAttribute()
    {
        return $this->bankAccount ? $this->bankAccount->Account_Name : null;
    }

    public function getChartOfAccountNameAttribute()
    {
        return $this->chartOfAccount ? $this->chartOfAccount->full_account_ref : null;
    }

    public function getFullAccountRefAttribute()
    {
        return $this->ledger_ref . ' - ' . $this->account_ref;
    }

    public function getVatPercentageAttribute()
    {
        return $this->vatFormLabel?->percentage ?? 0;
    }

    public function getVatFormKeyAttribute()
    {
        return $this->vatFormLabel?->form_key ?? null;
    }

    public function getVatDisplayNameAttribute()
    {
        return $this->vatFormLabel?->display_name ?? null;
    }

    // ==========================================
    // EXISTING RELATIONSHIPS
    // ==========================================

    public function file()
    {
        return $this->belongsTo(File::class, 'File_ID');
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'Bank_Account_ID');
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class, 'Payment_Type_ID');
    }

    public function accountRef()
    {
        return $this->belongsTo(AccountRef::class, 'Account_Ref_ID');
    }

    public function vatType()
    {
        return $this->belongsTo(VatType::class, 'VAT_ID');
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id', 'id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function pendingTransaction()
    {
        return $this->belongsTo(PendingTransaction::class);
    }

    public function vatFormLabel()
    {
        return $this->belongsTo(VatFormLabel::class, 'VAT_ID', 'id');
    }

    public function bankReconciliation()
    {
        return $this->hasOne(BankReconciliationDetail::class, 'Transaction_ID', 'Transaction_ID');
    }



    /**
     * Get supplier through invoice relationship
     * Used for Purchase and Purchase Credit transactions
     */
    public function supplier()
    {
        return $this->hasOneThrough(
            Supplier::class,           // Final model
            Invoice::class,            // Intermediate model
            'id',                      // Foreign key on Invoice table
            'id',                      // Foreign key on Supplier table
            'invoice_id',              // Local key on Transaction table
            'customer'                 // Local key on Invoice table (stores supplier.id)
        );
    }

    // ==========================================
    // EXISTING SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->whereNull('transaction.Deleted_On')
            ->where('transaction.Is_Imported', 1)
            ->where('transaction.Is_Bill', 0);
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
     * Scope: Get transactions by source
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope: Get transactions for specific bank account
     */
    public function scopeForBankAccount($query, int $bankAccountId)
    {
        return $query->where('Bank_Account_ID', $bankAccountId);
    }

    /**
     * Scope: Get transactions with Finexer ID
     */
    public function scopeWithFinexerId($query)
    {
        return $query->whereNotNull('finexer_transaction_id');
    }

    /**
     * Scope: Get transactions by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('Transaction_Date', [$startDate, $endDate]);
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
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        return '£' . number_format($this->Amount, 2);
    }

    /**
     * Check if transaction is money in (receipt)
     */
    public function isMoneyIn(): bool
    {
        return $this->Paid_In_Out === self::MONEY_IN;
    }

    /**
     * Check if transaction is money out (payment)
     */
    public function isMoneyOut(): bool
    {
        return $this->Paid_In_Out === self::MONEY_OUT;
    }

    // ==========================================
    // ✅ NEW: STATIC HELPER METHODS
    // ==========================================

    /**
     * Get statistics by source
     */
    public static function getStatsBySource(int $clientId = null): array
    {
        $query = self::query();

        if ($clientId) {
            $query->whereHas('bankAccount', function ($q) use ($clientId) {
                $q->where('Client_ID', $clientId);
            });
        }

        $stats = $query->selectRaw('
                source,
                COUNT(*) as count,
                SUM(CASE WHEN Paid_In_Out = 1 THEN Amount ELSE 0 END) as total_in,
                SUM(CASE WHEN Paid_In_Out = 2 THEN Amount ELSE 0 END) as total_out
            ')
            ->groupBy('source')
            ->get();

        return $stats->keyBy('source')->toArray();
    }

    /**
     * Find transaction by Finexer ID
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
}
