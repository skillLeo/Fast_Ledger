<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;

class HmrcToken extends Model
{
    use HasFactory;

    protected $table = 'hmrc_tokens';

    protected $fillable = [
        'user_id',
        'service_type', // Service type (e.g., 'income-tax', 'vat')
        'vrn', // VAT Registration Number (optional, for VAT scopes)
        'access_token',
        'refresh_token',
        'expires_at',
        'scope',
        'token_type',
        'last_refreshed_at',
        'is_active'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_refreshed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Encrypt tokens when setting
     */
    public function setAccessTokenAttribute($value)
    {
        $this->attributes['access_token'] = Crypt::encryptString($value);
    }

    public function setRefreshTokenAttribute($value)
    {
        $this->attributes['refresh_token'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt tokens when getting
     */
    public function getAccessTokenAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    public function getRefreshTokenAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'User_ID');
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        if ($this->expires_at === null) {
            return true;
        }
        return Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Check if token needs refresh (expires in less than 5 minutes)
     */
    public function needsRefresh(): bool
    {
        if ($this->expires_at === null) {
            return true;
        }
        /** @var CarbonInterface $expiry */
        $expiry = $this->expires_at;
        return now()->addMinutes(5)->greaterThanOrEqualTo($expiry);
    }

    /**
     * Check if token is expiring soon (configurable threshold in minutes)
     */
    public function isExpiringSoon(int $minutes = 30): bool
    {
        if ($this->expires_at === null) {
            return true;
        }
        return Carbon::now()->addMinutes($minutes)->isAfter($this->expires_at);
    }

    /**
     * Get days until expiry
     */
    public function daysUntilExpiry(): int
    {
        if ($this->expires_at === null) {
            return 0;
        }
        return max(0, (int) $this->expires_at->diffInDays(now()));
    }

    /**
     * Scope for active tokens
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for tokens with specific VRN
     */
    public function scopeForVrn($query, ?string $vrn)
    {
        if ($vrn === null) {
            return $query;
        }
        return $query->where('vrn', $vrn);
    }
}
