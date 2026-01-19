<?php

// app/Models/InvoiceTemplate.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceTemplate extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'description',
        'template_data',
        'logo_path',
        'is_default',
        'created_by'
    ];

    protected $casts = [
        'template_data' => 'array',
        'is_default' => 'boolean'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'Client_ID');
    }

    // Fix: Explicitly specify the foreign key
    public function elements(): HasMany
    {
        return $this->hasMany(TemplateElement::class, 'template_id');
    }

    // Fix: Explicitly specify the foreign key
    public function tableSettings(): HasMany
    {
        return $this->hasMany(TemplateTableSetting::class, 'template_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Get default template for a client
    public static function getDefault($clientId)
    {
        return static::where('client_id', $clientId)
            ->where('is_default', true)
            ->first();
    }

    // Set this template as default (unset others)
    public function setAsDefault()
    {
        // Unset other defaults
        static::where('client_id', $this->client_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set this as default
        $this->update(['is_default' => true]);
    }
}
