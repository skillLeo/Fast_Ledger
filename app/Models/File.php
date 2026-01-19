<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
{
    protected $table = 'file';
        use SoftDeletes;

    protected $primaryKey = 'File_ID'; // Set the primary key
    public $incrementing = true; // Assuming the primary key is auto-incrementing
    protected $keyType = 'int';
    protected $fillable = [
        'File_ID ',
        'Client_ID',
        'File_Date',
        'Ledger_Ref',
        'Matter',
        'Sub_Matter',
        'Fee_Earner',
        'Fee_Agreed',
        'Referral_Name',
        'Referral_Fee',
        'First_Name',
        'Last_Name',
        'Address1',
        'Address2',
        'Town',
        'Country_ID',
        'Post_Code',
        'Phone',
        'Mobile',
        'Email',
        'Date_Of_Birth',
        'NIC_No',
        'Key_Date',
        'Special_Note',
        'Status',
        'Created_By',
        'Created_On',
        'Modified_By',
        'Modified_On',
        'Deleted_By',
        'Deleted_On'
    ];


    public $timestamps = false;

    // Define custom columns for timestamps if needed
    protected $dates = [
        'File_Date',
        'Date_Of_Birth',
        'Key_Date',
        // 'Created_On',
        // 'Modified_On',
        'Deleted_On',
    ];
  const CREATED_AT = 'Created_On';
    const UPDATED_AT = 'Modified_On';
    const DELETED_AT = 'Deleted_On'; // This tells Laravel to use your custom column
    // Define relationship with Client
    public function client()
    {
        return $this->belongsTo(Client::class, 'Client_ID', 'Client_ID');
    }

    // Define scope for date range
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('file_date', [$from, $to]);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'Country_ID', 'Country_ID');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'File_ID', 'File_ID');
    }

    // Other methods, like custom getters and setters, if needed.
}
