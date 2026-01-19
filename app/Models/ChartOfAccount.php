<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'ledger_ref',
        'account_ref',
        'vat_id',
        'description',
        'is_active',
        'pl_bs'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get the VAT type associated with this chart of account
     */
    public function vatType()
    {
        return $this->belongsTo(VatType::class, 'vat_id', 'VAT_ID');
    }

    /**
     * Get all transactions using this chart of account
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'chart_of_account_id', 'id');
    }

    /**
     * Scope to get only active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by ledger reference
     */
    public function scopeByLedger($query, $ledgerRef)
    {
        return $query->where('ledger_ref', $ledgerRef);
    }

    // public function getBalanceAttribute()
    // {
    //     return $this->transactions()->sum('amount'); // Adjust based on debit/credit if needed
    // }

    /**
     * Get full account reference (ledger + account)
     */
    public function getFullAccountRefAttribute()
    {
        return $this->ledger_ref . ' - ' . $this->account_ref;
    }
}
