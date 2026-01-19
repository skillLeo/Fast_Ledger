<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $table = 'bankaccount';
    protected $primaryKey = 'Bank_Account_ID';

    public $timestamps = false;

    protected $fillable = [
        'Client_ID',
        'Bank_Type_ID',
        'Bank_Name',
        'Account_Name',
        'Account_No',
        'Sort_Code',
        'Is_Deleted',
        'finexer_account_id',
        'finexer_institution_id',
        'finexer_consent_id',
        'bank_feed_status',
        'bank_feed_connected_at',
        'bank_feed_last_synced_at',
        'bank_feed_sync_from_date',
        'bank_feed_error',
        'auto_sync_enabled',
    ];

    protected $casts = [
        'bank_feed_connected_at' => 'datetime',
        'bank_feed_last_synced_at' => 'datetime',
        'bank_feed_sync_from_date' => 'date',
        'auto_sync_enabled' => 'boolean',
        'Is_Deleted' => 'integer',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================
    
    public function bankAccountType(): BelongsTo
    {
        return $this->belongsTo(BankAccountType::class, 'Bank_Type_ID', 'Bank_Type_ID');
    }

    public function accountRefs(): HasMany
    {
        return $this->hasMany(AccountRef::class, 'Bank_Type_ID');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'Bank_Account_ID', 'Bank_Account_ID');
    }

    public function pendingTransactions(): HasMany
    {
        return $this->hasMany(PendingTransaction::class, 'bank_account_id', 'Bank_Account_ID');
    }

    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(UploadedFile::class, 'bank_account_id', 'Bank_Account_ID');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'Client_ID', 'Client_ID');
    }

    // ==========================================
    // EXISTING METHODS
    // ==========================================
    
    public function fetchAll($clientId, $bankTypeId = null)
    {
        $query = self::where('Client_ID', $clientId);

        if ($bankTypeId) {
            $query->where('Bank_Type_ID', $bankTypeId);
        }

        return $query->orderBy('Bank_Name', 'asc')->get();
    }

    // ==========================================
    // BANK FEED METHODS (FIXED TYPE HINTS)
    // ==========================================
    
    public function isBankFeedConnected(): bool
    {
        return $this->bank_feed_status === 'connected';
    }

    public function isBankFeedExpired(): bool
    {
        return $this->bank_feed_status === 'expired';
    }

    public function hasBankFeedError(): bool
    {
        return $this->bank_feed_status === 'error' || !empty($this->bank_feed_error);
    }

    public function hasAutoSyncEnabled(): bool
    {
        return $this->auto_sync_enabled === true;
    }

    public function getBankFeedStatusLabelAttribute(): string
    {
        return match($this->bank_feed_status) {
            'not_connected' => 'Not Connected',
            'connected' => 'Connected',
            'expired' => 'Expired',
            'error' => 'Error',
            default => 'Unknown',
        };
    }

    public function getBankFeedStatusBadgeAttribute(): string
    {
        return match($this->bank_feed_status) {
            'not_connected' => 'badge-secondary',
            'connected' => 'badge-success',
            'expired' => 'badge-warning',
            'error' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function needsSync(): bool
    {
        if (!$this->isBankFeedConnected()) {
            return false;
        }

        if (!$this->bank_feed_last_synced_at) {
            return true;
        }

        return $this->bank_feed_last_synced_at->lt(now()->subHours(24));
    }

    public function getLastSyncTimeAttribute(): ?string
    {
        return $this->bank_feed_last_synced_at?->diffForHumans();
    }

    // ✅ FIXED: Proper null handling
    public function getBankTypeNameAttribute(): string
    {
        return match($this->Bank_Type_ID) {
            1 => 'Client Bank',
            2 => 'Office Bank',
            default => 'Unknown',
        };
    }

    public function isClientBank(): bool
    {
        return $this->Bank_Type_ID === 1;
    }

    public function isOfficeBank(): bool
    {
        return $this->Bank_Type_ID === 2;
    }

    // ==========================================
    // SCOPES
    // ==========================================
    
    public function scopeBankFeedConnected($query)
    {
        return $query->where('bank_feed_status', 'connected');
    }

    public function scopeNeedsSync($query)
    {
        return $query->where('bank_feed_status', 'connected')
            ->where('auto_sync_enabled', true)
            ->where(function($q) {
                $q->whereNull('bank_feed_last_synced_at')
                  ->orWhere('bank_feed_last_synced_at', '<', now()->subHours(24));
            });
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('bank_feed_status', $status);
    }

    public function scopeForClient($query, int $clientId)
    {
        return $query->where('Client_ID', $clientId);
    }

    // ==========================================
    // BANK FEED ACTIONS
    // ==========================================
    
    public function connectBankFeed(array $connectionData): void
    {
        $this->update([
            'finexer_account_id' => $connectionData['account_id'],
            'finexer_institution_id' => $connectionData['institution_id'] ?? null,
            'finexer_consent_id' => $connectionData['consent_id'] ?? null,
            'bank_feed_status' => 'connected',
            'bank_feed_connected_at' => now(),
            'bank_feed_sync_from_date' => $connectionData['sync_from_date'] ?? now()->subDays(90),
            'auto_sync_enabled' => $connectionData['auto_sync'] ?? true,
            'bank_feed_error' => null,
        ]);
    }

    public function disconnectBankFeed(): void
    {
        $this->update([
            'finexer_account_id' => null,
            'finexer_institution_id' => null,
            'finexer_consent_id' => null,
            'bank_feed_status' => 'not_connected',
            'bank_feed_connected_at' => null,
            'bank_feed_last_synced_at' => null,
            'bank_feed_sync_from_date' => null,
            'bank_feed_error' => null,
            'auto_sync_enabled' => false,
        ]);
    }

    public function updateSyncTimestamp(): void
    {
        $this->update([
            'bank_feed_last_synced_at' => now(),
            'bank_feed_error' => null,
        ]);
    }

    public function updateError(string $error): void
    {
        $this->update([
            'bank_feed_status' => 'error',
            'bank_feed_error' => $error,
        ]);
    }

    public function markAsExpired(): void
    {
        $this->update([
            'bank_feed_status' => 'expired',
        ]);
    }
}