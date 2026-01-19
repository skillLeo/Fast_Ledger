<?php

namespace App\Models\CompanyModule;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\CompanyModule\Company;

class VerifactuConnection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', // ✅ Added
        'name',
        'nif',
        'company_name',
        'environment',
        'certificate_path',
        'certificate_password',
        'sif_id',
        'status',
        'last_error',
        'last_connected_at',
    ];

    protected $casts = [
        'last_connected_at' => 'datetime',
    ];

    protected $hidden = [
        'certificate_password',
    ];

    // ============================================
    // RELATIONSHIPS
    // ============================================
    
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // public function invoices(): HasMany
    // {
    //     return $this->hasMany(VerifactuInvoice::class);
    // }

    // public function logs(): HasMany
    // {
    //     return $this->hasMany(VerifactuLog::class);
    // }

    // ============================================
    // HELPER METHODS
    // ============================================

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    public function markAsConnected(): void
    {
        $this->update([
            'status' => 'connected',
            'last_connected_at' => now(),
            'last_error' => null,
        ]);
    }

    public function markAsDisconnected(string $error = null): void
    {
        $this->update([
            'status' => $error ? 'error' : 'disconnected',
            'last_error' => $error,
        ]);
    }
}