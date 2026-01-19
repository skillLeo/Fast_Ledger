<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class InvoiceDocument extends Model
{
    protected $fillable = [
        'invoice_id',
        'document_path',
        'document_name',
        'file_type',
        'file_size',
        'created_by'
    ];

    /**
     * Get the invoice that owns the document
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the full URL for the document
     */
    public function getDocumentUrlAttribute()
    {
        return Storage::url($this->document_path);
    }

    /**
     * Get file extension
     */
    public function getExtensionAttribute()
    {
        return pathinfo($this->document_name, PATHINFO_EXTENSION);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    /**
     * Delete file from storage when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($document) {
            if ($document->document_path && Storage::disk('public')->exists($document->document_path)) {
                Storage::disk('public')->delete($document->document_path);
            }
        });
    }
}