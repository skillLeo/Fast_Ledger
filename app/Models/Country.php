<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'country';  

    public $incrementing = false;
    protected $primaryKey = 'Country_ID';
   
    protected $fillable = [
        'Country_ID',  // If you want to allow mass assignment of this column
        'Country_Name', // The name of the country
    ];

   

    public function files()
    {
        return $this->hasMany(File::class, 'Country_ID', 'Country_ID');
    }
}
