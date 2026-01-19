<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubMatter extends Model
{
    use HasFactory;

    protected $table = 'submatters';

    protected $primaryKey = 'id';

    protected $fillable = [
        'matter_id',
        'submatter',
        'code',
        'Deleted_On',
    ];

    public function matter()
    {
        return $this->belongsTo(Matter::class, 'matter_id', 'id');
    }
}
