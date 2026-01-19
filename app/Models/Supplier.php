<?php

namespace App\Models;

use App\Models\CompanyModule\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'user_id',
        'company_id',
        // Contact Information
        'contact_name',
        'account_number',
        'phone',
        'email',

        // Primary Person
        'first_name',
        'last_name',
        'website',
        'company_reg_no',

        // Addresses
        'billing_address',
        'delivery_address',
        'city',
        'postal_code',

        // Financial Details
        'bank_account_name',
        'sort_code',
        'bank_account_number',
        'reference',

        // VAT Details
        'vat_number',
        'vat_status',
        'tax_id',
        'currency',

        // Business Details
        'business_type',
        'industry',
        'established_date',
        'employee_count',

        // Payment Terms
        'payment_terms',
        'credit_limit',
        'discount_percentage',
        'payment_method',

        // Status & Rating
        'status',
        'rating',
        'preferred_supplier',
        'last_order_date',

        // Additional Notes
        'notes',
    ];

    protected $casts = [
        'established_date' => 'date',
        'last_order_date' => 'date',
        'credit_limit' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'employee_count' => 'integer',
        'rating' => 'integer',
        'preferred_supplier' => 'boolean',
    ];

    /**
     * Relationship: Supplier belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'User_ID');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }




    // ==========================================
    // ✅ HELPER METHODS
    // =

    /**
     * Check if supplier belongs to main app
     */
    public function isMainApp(): bool
    {
        return is_null($this->company_id);
    }

    /**
     * Check if supplier belongs to company module
     */
    public function isCompanyModule(): bool
    {
        return !is_null($this->company_id);
    }

    /**
     * Get display name for supplier
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->contact_name ?: trim($this->first_name . ' ' . $this->last_name);
    }


    // ==========================================
    // ✅ SCOPES
    // ==========================================

    /**
     * Scope: Main app suppliers (no company_id)
     */
    public function scopeMainApp($query)
    {
        return $query->whereNull('company_id');
    }

    /**
     * Scope: Company module suppliers
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope: Suppliers for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
