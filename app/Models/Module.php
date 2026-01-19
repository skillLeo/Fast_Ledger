<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'modules';
    protected $primaryKey = 'Module_ID';
    public $timestamps = true;

    protected $fillable = [
        'Module_Name',
        'Module_Display_Name',
        'Description',
        'Module_Icon',
        'Module_Route',
        'Is_Active',
        'Display_Order',
    ];

    protected $casts = [
        'Is_Active' => 'boolean',
        'Display_Order' => 'integer',
    ];

    /**
     * Get users who have access to this module
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_module_access',
            'Module_ID',
            'User_ID'
        )->withPivot('Has_Access', 'Is_Active', 'Granted_At')
          ->withTimestamps();
    }

    /**
     * Scope: Only active modules
     */
    public function scopeActive($query)
    {
        return $query->where('Is_Active', true);
    }

    /**
     * Scope: Order by display order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('Display_Order');
    }
}