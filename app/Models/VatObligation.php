<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VatObligation extends Model
{
    use HasFactory;

    protected $fillable = [
        'vrn',
        'period_key',
        'start_date',
        'end_date',
        'due_date',
        'status',
        'received_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'due_date' => 'date',
        'received_date' => 'date',
    ];

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'O');
    }

    public function scopeFulfilled($query)
    {
        return $query->where('status', 'F');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'O')
                     ->where('due_date', '<', now());
    }
}