<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';
    protected $primaryKey = 'Role_ID';
    public $timestamps = false;

    protected $fillable = ['Role_Name', 'Description'];

    const SUPER_ADMIN = 'superadmin';
    const ADMIN = 'admin';
    const CLIENT = 'client';

    /**
     * Get users with this role
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'userrole',
            'Role_ID',
            'User_ID'
        );
    }
}
