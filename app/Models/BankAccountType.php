<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccountType extends Model
{
    protected $table = 'bankaccounttype';
    protected $primaryKey = 'Bank_Type_ID';

    public function accountRefs()
    {
        return $this->hasMany(AccountRef::class, 'Bank_Type_ID');
    }
}
