<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UploadedFile extends Model
{
     use HasFactory;

    protected $fillable = [
        'original_filename',
        'stored_filename',
        'file_path',
        'status',
        'column_mapping',
        'file_headers',
        'total_rows',
        'processed_rows',
        'error_message'
    ];

    protected $casts = [
        'column_mapping' => 'array',
        'file_headers' => 'array',
    ];

    public function pendingTransactions()
    {
        return $this->hasMany(PendingTransaction::class);
    }
}
