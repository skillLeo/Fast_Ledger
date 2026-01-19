<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'client_id',
        'company_id',  // ✅ Added
        'category',
        'item_code',
        'name',  
        'description',
        'ledger_id',
        'account_ref',
        'unit_amount',
        'vat_rate_id',
        'file_path',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'unit_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationship: Product belongs to a Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'Client_ID');
    }

    /**
     * ✅ Relationship: Product belongs to a Company
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\CompanyModule\Company::class, 'company_id', 'id');
    }

    /**
     * Relationship: Product belongs to Chart of Account (Ledger)
     */
    public function ledger()
    {
        return $this->belongsTo(ChartOfAccount::class, 'ledger_id', 'id');
    }

    /**
     * Relationship: Product has VAT Rate
     */
    public function vatRate()
    {
        return $this->belongsTo(VatFormLabel::class, 'vat_rate_id', 'id');
    }

    /**
     * Scope: Get only active products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get products by category (purchase or sales)
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * ✅ Scope: Get products for current authenticated user (context-aware)
     */
    // public function scopeForCurrentContext($query)
    // {
    //     $url = request()->path();
        
    //     if (str_contains($url, 'company/')) {
    //         // Company Module: Filter by company_id
    //         $companyId = session('current_company_id');
    //         if ($companyId) {
    //             return $query->where('company_id', $companyId);
    //         }
    //     } else {
    //         // Main App: Filter by client_id
    //         $clientId = auth()->user()->Client_ID ?? null;
    //         if ($clientId) {
    //             return $query->where('client_id', $clientId)->whereNull('company_id');
    //         }
    //     }
        
    //     return $query;
    // }

    /**
     * Scope: Get products for current authenticated user (legacy - kept for backward compatibility)
     */
    public function scopeForCurrentClient($query)
    {
        $clientId = auth()->user()->Client_ID;
        return $query->where('client_id', $clientId)->whereNull('company_id');
    }

    /**
     * ✅ Scope: Get products for specific company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Get formatted unit amount with currency
     */
    public function getFormattedUnitAmountAttribute()
    {
        return '£' . number_format($this->unit_amount, 2);
    }

    /**
     * Get full display name (item_code - description)
     */
    public function getDisplayNameAttribute()
    {
        return $this->item_code . ' - ' . $this->description;
    }

    /**
     * Get file URL if file exists
     */
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }
}