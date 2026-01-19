<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserModuleAccess extends Model
{
    protected $table = 'user_module_access';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'User_ID',
        'Module_ID',
        'Has_Access',
        'Granted_By',
        'Granted_At',
        'Revoked_At',
        'Is_Active',
    ];

    protected $casts = [
        'Has_Access' => 'boolean',
        'Is_Active' => 'boolean',
        'Granted_At' => 'datetime',
        'Revoked_At' => 'datetime',
    ];

    /**
     * User who has access
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'User_ID', 'User_ID');
    }

    /**
     * Module being accessed
     */
    public function module()
    {
        return $this->belongsTo(Module::class, 'Module_ID', 'Module_ID');
    }

    /**
     * User who granted access
     */
    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'Granted_By', 'User_ID');
    }

    /**
     * Scope: Only active access
     */
    public function scopeActive($query)
    {
        return $query->where('Is_Active', true)
                     ->where('Has_Access', true);
    }

    /**
     * Grant access to a module for a user
     */
    public static function grantAccess($userId, $moduleId, $grantedBy = null)
    {
        return self::updateOrCreate(
            [
                'User_ID' => $userId,
                'Module_ID' => $moduleId,
            ],
            [
                'Has_Access' => true,
                'Is_Active' => true,
                'Granted_By' => $grantedBy,
                'Granted_At' => now(),
            ]
        );
    }

    /**
     * Revoke access to a module for a user
     */
    public static function revokeAccess($userId, $moduleId)
    {
        return self::where('User_ID', $userId)
                   ->where('Module_ID', $moduleId)
                   ->update([
                       'Has_Access' => false,
                       'Is_Active' => false,
                       'Revoked_At' => now(),
                   ]);
    }
}