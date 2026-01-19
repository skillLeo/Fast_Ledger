<?php

namespace App\Models\CompanyModule;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CompanyActivityLog extends Model
{
    protected $table = 'company_module_activity_logs';
    protected $primaryKey = 'id';
    public $timestamps = false; // Using custom Created_At

    const CREATED_AT = 'Created_At';
    const UPDATED_AT = null; // No updated_at column

    protected $fillable = [
        'Company_ID',
        'User_ID',
        'Activity_Type',
        'Entity_Type',
        'Entity_ID',
        'Description',
        'IP_Address',
        'User_Agent',
        'Request_Method',
        'Request_URL',
        'Old_Values',
        'New_Values',
        'Timezone',
    ];

    protected $casts = [
        'Old_Values' => 'array',
        'New_Values' => 'array',
        'Created_At' => 'datetime',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->Created_At = now();
            $model->Timezone = config('app.timezone');
        });
    }

    /**
     * Company this log belongs to
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'Company_ID', 'id');
    }

    /**
     * User who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    /**
     * Scope: Filter by activity type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('Activity_Type', $type);
    }

    /**
     * Scope: Filter by entity
     */
    public function scopeForEntity($query, $entityType, $entityId = null)
    {
        $query->where('Entity_Type', $entityType);
        
        if ($entityId) {
            $query->where('Entity_ID', $entityId);
        }
        
        return $query;
    }

    /**
     * Scope: Recent logs
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('Created_At', '>=', now()->subDays($days));
    }

    /**
     * Create log entry (static helper)
     */
    public static function log($companyId, $activityType, $entityType, $description, $entityId = null, $oldValues = null, $newValues = null)
    {
        return self::create([
            'Company_ID' => $companyId,
            'User_ID' => auth()->id(),
            'Activity_Type' => $activityType,
            'Entity_Type' => $entityType,
            'Entity_ID' => $entityId,
            'Description' => $description,
            'IP_Address' => request()->ip(),
            'User_Agent' => request()->userAgent(),
            'Request_Method' => request()->method(),
            'Request_URL' => request()->fullUrl(),
            'Old_Values' => $oldValues,
            'New_Values' => $newValues,
        ]);
    }
}