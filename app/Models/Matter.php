<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Matter extends Model
{
    use HasFactory;

    protected $table = 'matters';

    protected $primaryKey = 'id';

    protected $fillable = [
        'matter',
        'code',
        'Deleted_On',
    ];

    public function submatters()
    {
        return $this->hasMany(SubMatter::class, 'matter_id', 'id');
    }
}
