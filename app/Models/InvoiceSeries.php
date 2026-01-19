<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InvoiceSeries extends Model
{
    protected $fillable = [
        'name',
        'prefix',
        'next_number',
        'number_format',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'next_number' => 'integer',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'series_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ==========================================
    // METHODS
    // ==========================================

    /**
     * Generate next sequential invoice number with database lock
     * This prevents duplicate numbers in concurrent requests
     */
    public function generateNextNumber(): string
    {
        return DB::transaction(function () {
            // Lock the row to prevent race conditions
            $series = self::where('id', $this->id)->lockForUpdate()->first();
            
            $currentNumber = $series->next_number;
            $formatted = $this->formatNumber($currentNumber);
            
            // Safety check: ensure this number doesn't exist
            $exists = Invoice::where('invoice_no', $formatted)->exists();
            if ($exists) {
                // If somehow it exists, increment and try again
                $series->increment('next_number');
                return $this->generateNextNumber();
            }
            
            // Increment for next time
            $series->increment('next_number');
            
            return $formatted;
        });
    }

    /**
     * Format number according to template
     * Example: '{prefix}{number:6}' with prefix='SIN' and number=1 becomes 'SIN000001'
     */
    public function formatNumber(int $number): string
    {
        $formatted = str_replace('{prefix}', $this->prefix, $this->number_format);
        
        // Extract padding from {number:6}
        if (preg_match('/{number:(\d+)}/', $formatted, $matches)) {
            $padding = (int)$matches[1];
            $paddedNumber = str_pad($number, $padding, '0', STR_PAD_LEFT);
            $formatted = preg_replace('/{number:\d+}/', $paddedNumber, $formatted);
        } else {
            $formatted = str_replace('{number}', $number, $formatted);
        }
        
        return $formatted;
    }

    /**
     * Preview what the next number will be
     */
    public function previewNextNumber(): string
    {
        return $this->formatNumber($this->next_number);
    }

    /**
     * Reset numbering (use with caution!)
     */
    public function resetNumbering(int $startFrom = 1): void
    {
        $this->update(['next_number' => $startFrom]);
    }
}