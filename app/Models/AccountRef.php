<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountRef extends Model
{
    protected $table = 'accountref';
    protected $primaryKey = 'Account_Ref_ID';


    public function bankAccountType()
    {
        return $this->belongsTo(BankAccountType::class, 'Bank_Type_ID');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'Account_Ref_ID', 'Account_Ref_ID');
    }
    
}
