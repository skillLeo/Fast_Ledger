<?php
// app/Models/User.php

namespace App\Models;

use App\Models\Employees\Employee;
use Illuminate\Support\Facades\DB;
use App\Models\CompanyModule\Company;
use App\Models\CompanyModule\Customer;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'User_ID';
    public $incrementing = true;

    protected $fillable = [
        'Full_Name',
        'User_Name',
        'email',
        'email_verified_at',
        'language',
        'password',
        'remember_token',
        'Is_Active',
        'Sys_IP',
        'Last_Login_DateTime',
        'User_Role',
        'Client_ID',
        'Created_By',
        'Modified_By',
        'Deleted_By',
        'Deleted_On',
        'Is_Archive',
        // ✅ Subscription fields
        'allowed_companies',
        'subscription_price',
        'subscription_status',
        'trial_starts_at',
        'trial_ends_at',
        'payment_frequency',
        'subscription_starts_at',
        'subscription_ends_at',
    ];

    const CREATED_AT = 'Created_On';
    const UPDATED_AT = 'Modified_On';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'Last_Login_DateTime' => 'datetime',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        // ✅ Subscription casts
        'trial_starts_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'subscription_starts_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    // ==========================================
    // ✅ SUBSCRIPTION METHODS
    // ==========================================

    /**
     * Check if user has completed subscription setup
     */
    public function hasCompletedSubscriptionSetup(): bool
    {
        return $this->subscription_status !== null 
            && $this->subscription_status !== 'pending_payment';
    }

    /**
     * Check if user is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->subscription_status === 'trial' 
            && $this->trial_ends_at 
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if trial has expired
     */
    public function trialExpired(): bool
    {
        return $this->subscription_status === 'trial' 
            && $this->trial_ends_at 
            && $this->trial_ends_at->isPast();
    }

    /**
     * Check if user has active subscription
     */
    public function hasActiveSubscription(): bool
    {
        if ($this->isOnTrial()) {
            return true;
        }

        if ($this->subscription_status === 'active' 
            && $this->subscription_ends_at 
            && $this->subscription_ends_at->isFuture()) {
            return true;
        }

        return false;
    }

    /**
     * Get days remaining in trial
     */
    public function trialDaysRemaining(): int
    {
        if (!$this->isOnTrial()) {
            return 0;
        }

        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Check if user needs payment
     */
    public function needsPayment(): bool
    {
        return $this->subscription_status === 'pending_payment';
    }

    // ==========================================
    // RELATIONSHIPS (Keep all your existing ones)
    // ==========================================

    public function customers()
    {
        return $this->hasMany(Customer::class, 'User_ID', 'User_ID');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'Client_ID', 'Client_ID');
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class, 'user_id', 'User_ID');
    }

    public function createdEmployees()
    {
        return $this->hasMany(Employee::class, 'created_by', 'User_ID');
    }

    public function updatedEmployees()
    {
        return $this->hasMany(Employee::class, 'updated_by', 'User_ID');
    }

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'userrole',
            'User_ID',
            'Role_ID'
        );
    }

    public function hasRole($roleId)
    {
        return DB::table('role')
            ->join('userrole', 'role.Role_ID', '=', 'userrole.Role_ID')
            ->where('userrole.User_ID', $this->User_ID)
            ->where('role.Role_ID', $roleId)
            ->exists();
    }

    public function getRoleIds()
    {
        return $this->roles->pluck('Role_ID')->toArray();
    }

    public function isCompanyUser()
    {
        return $this->hasRole(4);
    }

    public function ownedCompanies()
    {
        return $this->belongsToMany(
            Company::class,
            'company_module_users',
            'User_ID',
            'Company_ID'
        )->wherePivot('Is_Active', true)
            ->wherePivot('Role', 'owner');
    }

    public function companies()
    {
        return $this->belongsToMany(
            Company::class,
            'company_module_users',
            'User_ID',
            'Company_ID'
        )
            ->wherePivot('Is_Active', true)
            ->withPivot('Role', 'Is_Primary', 'Created_At', 'Is_Active');
    }

    public function hasAccessToCompany($companyId)
    {
        return DB::table('company_module_users')
            ->where('User_ID', $this->User_ID)
            ->where('Company_ID', $companyId)
            ->where('Is_Active', true)
            ->exists();
    }

    public function getCompanyRole($companyId)
    {
        $record = DB::table('company_module_users')
            ->where('User_ID', $this->User_ID)
            ->where('Company_ID', $companyId)
            ->where('Is_Active', true)
            ->first();

        return $record ? $record->Role : null;
    }

    public function getPrimaryCompany()
    {
        return $this->companies()
            ->wherePivot('Is_Primary', true)
            ->first();
    }

    public function modules()
    {
        return $this->belongsToMany(
            Module::class,
            'user_module_access',
            'User_ID',
            'Module_ID'
        )->withPivot('Has_Access', 'Is_Active')
            ->withTimestamps();
    }

    public function hasModuleAccess($moduleNameOrId)
    {
        $query = $this->modules()
            ->wherePivot('Has_Access', 1)
            ->wherePivot('Is_Active', 1);

        if (is_numeric($moduleNameOrId)) {
            $query->where('modules.Module_ID', (int) $moduleNameOrId);
        } else {
            $query->where('modules.Module_Name', $moduleNameOrId);
        }

        return $query->exists();
    }

    public function activeModules()
    {
        return $this->modules()
            ->wherePivot('Has_Access', true)
            ->wherePivot('Is_Active', true)
            ->where('Is_Active', true)
            ->get();
    }

    public function hmrcTokens()
    {
        return $this->hasMany(HmrcToken::class, 'user_id', 'User_ID');
    }

    public function hmrcOAuthToken()
    {
        return $this->hasOne(HmrcToken::class, 'user_id', 'User_ID');
    }

    public function hmrcBusinesses()
    {
        return $this->hasMany(HmrcBusiness::class, 'user_id', 'User_ID');
    }

    public function hmrcObligations()
    {
        return $this->hasMany(HmrcObligation::class, 'user_id', 'User_ID');
    }
}