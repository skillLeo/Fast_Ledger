<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VatFormLabel extends Model
{
    protected $fillable = ['vat_type_id', 'form_key', 'display_name'];

    public function vatType()
    {
        return $this->belongsTo(VatType::class, 'vat_type_id', 'VAT_ID');
    }
}
