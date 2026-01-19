<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankReconciliationDetail extends Model
{
    protected $table = 'BankReconciliationDetail';
    protected $primaryKey = 'Bank_Recon_Detail_ID';
    public $timestamps = false;

    protected $fillable = [
        'Transaction_ID',
        'Add_Type',
        'Amount',
        'Chq_Date',
        'Created_By',
        'Created_On',
    ];
    
}
