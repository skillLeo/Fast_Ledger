<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class HmrcBusiness extends Model
{
    use HasFactory;

    protected $table = 'hmrc_businesses';

    protected $fillable = [
        'user_id',
        'nino',
        'business_id',
        'type_of_business',
        'trading_name',
        'accounting_type',
        'commencement_date',
        'cessation_date',
        'quarterly_period_type',
        'tax_year_of_choice',
        'last_synced_at',
        'business_address_json',
        'accounting_periods_json',
        'metadata_json',
    ];

    protected $casts = [
        'commencement_date' => 'date',
        'cessation_date' => 'date',
        'last_synced_at' => 'datetime',
        'business_address_json' => 'array',
        'accounting_periods_json' => 'array',
        'metadata_json' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'User_ID');
    }

    public function obligations()
    {
        return $this->hasMany(HmrcObligation::class, 'business_id', 'business_id');
    }

    public function submissions()
    {
        return $this->hasMany(HmrcPeriodicSubmission::class, 'business_id', 'business_id');
    }

    public function ukPropertyAnnualSubmissions()
    {
        return $this->hasMany(HmrcUkPropertyAnnualSubmission::class, 'business_id', 'business_id');
    }

    public function ukPropertyPeriodSummaries()
    {
        return $this->hasMany(HmrcUkPropertyPeriodSummary::class, 'business_id', 'business_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('cessation_date')->where('is_active', true);
    }

    public function scopeSelfEmployment(Builder $query): Builder
    {
        return $query->where('type_of_business', 'self-employment');
    }

    public function scopeProperty(Builder $query): Builder
    {
        return $query->whereIn('type_of_business', ['uk-property', 'foreign-property', 'property-unspecified']);
    }

    public function scopeUkProperty(Builder $query): Builder
    {
        return $query->where('type_of_business', 'uk-property');
    }

    public function scopeForeignProperty(Builder $query): Builder
    {
        return $query->where('type_of_business', 'foreign-property');
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->cessation_date === null;
    }
}
