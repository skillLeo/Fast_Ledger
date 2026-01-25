<?php

namespace App\Models;

use App\Models\Employees\Employee;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'client';
    protected $primaryKey = 'Client_ID';
    protected $fillable = [
        'Client_ID',
        'Client_Ref',
        'Contact_Name',
        'Business_Name',
        'Address1',
        'Address2',
        'Town',
        'Country_ID',
        'Post_Code',
        'Phone',
        'Mobile',
        'Fax',
        'Email',
        'Company_Reg_No',
        'VAT_Registration_No',
        'Contact_No',
        'Fee_Agreed',
        'Created_By',
        'Created_On',
        'Modified_By',
        'Modified_On',
        'Deleted_By',
        'Deleted_On',
        'Is_Archive',
        'date_lock',
        'transaction_lock'
    ];

    public $timestamps = false;

    protected $dates = [
        'Created_On',
        'Modified_On',
        'Deleted_On',

    ];


 // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get all users belonging to this client.
     */
    
    public function users()
    {
        return $this->hasMany(User::class, 'Client_ID', 'Client_ID');
    }


    public function user()
{
    return $this->belongsTo(User::class, 'User_ID', 'id'); // Ensure the correct foreign and local keys
}
    /**
     * Get all employees belonging to this client.
     */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'client_id', 'Client_ID');
    }

    /**
     * Get all files belonging to this client.
     */
    public function files()
    {
        return $this->hasMany(File::class, 'Client_ID', 'Client_ID');
    }

    /**
     * Get the country of this client.
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'Country_ID');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope a query to only include non-archived clients.
     */
    public function scopeNotArchived($query)
    {
        return $query->where('Is_Archive', 0);
    }

    /**
     * Scope a query to only include archived clients.
     */
    public function scopeArchived($query)
    {
        return $query->where('Is_Archive', 1);
    }
}
