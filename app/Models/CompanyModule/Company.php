<?php

namespace App\Models\CompanyModule;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'company_module_companies';
    protected $primaryKey = 'id';
    public $timestamps = false; // Using custom timestamp columns

    // ← IMPORTANT: Match your actual column names!
    const CREATED_AT = 'Created_On';
    const UPDATED_AT = 'Modified_On';
    const DELETED_AT = 'Deleted_At';


    protected $fillable = [
        'User_ID',
        'Company_Name',
        'Trade_Name',
        'Street_Address',
        'City',
        'State',
        'Postal_Code',
        'Country',
        'Company_Type_ES',
        'Company_Type_UK',
        'Tax_ID',
        'Country_Tax_Residence',
        'Tax_Regime',
        'Phone_Number',
        'Email',
        'Website',
        'Verifactu_Enabled',
        'AEAT_Certificate_Path',
        'SIF_Identifier',
        'Is_Test_Mode',
        'Logo_Path',
        'Currency',
        'Invoice_Prefix',
        'Next_Invoice_Number',
        'Is_Active',
        'Subscription_Status',
        'Subscription_End_Date',
        'Profile_Completed',
        'Profile_Completion_Percentage',
        'Last_Profile_Reminder_At',
        'Created_By',
        'Modified_By',
        'Deleted_By',
    ];

    protected $casts = [
        'Verifactu_Enabled' => 'boolean',
        'Is_Test_Mode' => 'boolean',
        'Is_Active' => 'boolean',
        'Is_Archive' => 'boolean',
        'Profile_Completed' => 'boolean',
        'Profile_Completion_Percentage' => 'integer',
        'Next_Invoice_Number' => 'integer',
        'Subscription_End_Date' => 'datetime',
        'Last_Profile_Reminder_At' => 'datetime',
        'Created_On' => 'datetime',
        'Modified_On' => 'datetime',
        'Deleted_On' => 'datetime',
    ];

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set Created_On when creating
        static::creating(function ($model) {
            $model->Created_On = now();
            $model->Created_By = auth()->id();
        });

        // Auto-set Modified_On when updating
        static::updating(function ($model) {
            $model->Modified_On = now();
            $model->Modified_By = auth()->id();
        });
    }

    /**
     * Owner of the company
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    /**
     * Users associated with this company
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'company_module_users',
            'Company_ID',
            'User_ID'
        )->withPivot('Role', 'Is_Primary', 'Is_Active')
            ->withTimestamps('Created_At', 'Updated_At');
    }

    public function customers()
    {
        return $this->hasMany(
            \App\Models\CompanyModule\Customer::class,
            'Company_ID',  // ✅ Foreign key in customers table
            'id'  // ✅ Local key in companies table
        );
    }

    /**
     * Activity logs for this company
     */
    public function activityLogs()
    {
        return $this->hasMany(CompanyActivityLog::class, 'Company_ID', 'id');
    }

    /**
     * Creator of the company
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'Created_By', 'User_ID');
    }

    /**
     * Scope: Active companies
     */
    public function scopeActive($query)
    {
        return $query->where('Is_Active', true)
            ->where('Is_Archive', false);
    }

    public function products()
    {
        return $this->hasMany(\App\Models\Product::class, 'company_id', 'id');
    }

    /**
     * Scope: User's companies
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('User_ID', $userId);
    }

    /**
     * Scope: Incomplete profiles
     */
    public function scopeIncompleteProfile($query)
    {
        return $query->where('Profile_Completed', false);
    }

    /**
     * Check if company is from UK
     */
    public function isUK()
    {
        return $this->Country === 'GB';
    }

    /**
     * Check if company is from Spain
     */
    public function isSpain()
    {
        return $this->Country === 'ES';
    }

    /**
     * Get company type based on country
     */
    public function getCompanyTypeAttribute()
    {
        return $this->isUK() ? $this->Company_Type_UK : $this->Company_Type_ES;
    }

    /**
     * Calculate profile completion percentage
     */
    public function calculateProfileCompletion()
    {
        $fields = [
            'Company_Name',
            'Street_Address',
            'City',
            'Postal_Code',
            'Country',
            'Tax_ID',
            'Country_Tax_Residence',
            'Phone_Number',
            'Email',
            'Website',
            'Logo_Path',
        ];

        $filled = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $filled++;
            }
        }

        // Add company type check
        if ($this->isUK() && !empty($this->Company_Type_UK)) {
            $filled++;
        } elseif ($this->isSpain() && !empty($this->Company_Type_ES)) {
            $filled++;
        }

        $total = count($fields) + 1; // +1 for company type
        $percentage = round(($filled / $total) * 100);

        $this->Profile_Completion_Percentage = $percentage;
        $this->Profile_Completed = $percentage >= 80; // Consider 80%+ as complete

        return $percentage;
    }

    /**
     * Get next invoice number and increment
     */
    public function getNextInvoiceNumber()
    {
        $current = $this->Next_Invoice_Number;
        $this->increment('Next_Invoice_Number');

        return $this->Invoice_Prefix . str_pad($current, 5, '0', STR_PAD_LEFT);
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class, 'company_id', 'id');
    }
}
