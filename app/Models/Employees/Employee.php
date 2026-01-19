<?php

namespace App\Models\Employees;

use App\Models\User;
use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employees';
    protected $primaryKey = 'id';
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // Foreign Keys
        'client_id',
        'created_by',
        'updated_by',
        
        // A. Personal Details
        'title',
        'first_name',
        'surname',
        'known_as',
        'date_of_birth',
        'gender',
        'ni_number',
        'passport_number',
        'nationality',
        
        // B. Address
        'address_line_1',
        'address_line_2',
        'address_line_3',
        'city_town',
        'county',
        'postcode',
        'country',
        
        // C. Contact Details
        'primary_phone',
        'secondary_phone',
        'email',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        
        // D. Employment Start & Starter Info
        'starter_type',
        'employment_start_date',
        'starter_type_hmrc',
        
        // E. HMRC Starter Declaration
        'hmrc_declaration',
        'has_p45',
        'student_loan',
        'postgrad_loan',
        
        // F. Tax & NI
        'tax_code_preview',
        'ni_category_letter',
        
        // G. Work Information
        'job_title',
        'work_department',
        'work_hours',
        'works_number',
        'ni_number_work',
        'date_started',
        'date_left',
        
        // H. NIC / Compliance Flags
        'no_employer_nic',
        'exclude_nmw',
        'holiday_fund_free',
        'employee_widows_orphans',
        'veteran_first_day',
        'off_payroll_worker',
        'workplace_postcode',
        
        // I. Director Information
        'director_flag',
        'was_director',
        'director_start_date',
        'director_end_date',
        'director_nic_method',
        
        // J. Payment Details
        'pay_frequency',
        'pay_method',
        'annual_pay',
        'pay_per_period',
        'delivery_method',
        
        // K. Bank Details
        'bank_name',
        'sort_code',
        'account_number',
        'account_name',
        'payment_reference',
        'building_society_ref',
        
        // L. Auto-enrolment Pension
        'exclude_from_assessment',
        'auto_enrolment_pension',
        'employee_group',
        'assessment',
        'defer_postpone_until',
        'date_joined',
        'date_opted_out',
        'date_opted_in',
        'do_not_reassess',
        'continue_to_assess',
        'auto_enrolled_letter_date',
        'not_enrolled_letter_date',
        'postponement_letter_date',
        'contribution_percentages',
        
        // M. Employment Terms
        'hours_per_week',
        'paid_overtime',
        'weeks_notice',
        'days_sickness_full_pay',
        'retirement_age',
        'may_join_pension',
        'days_holiday_per_year',
        'max_days_carry_over',
        
        // N. Status
        'is_archive',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        // Dates
        'date_of_birth' => 'date',
        'employment_start_date' => 'date',
        'date_started' => 'date',
        'date_left' => 'date',
        'veteran_first_day' => 'date',
        'director_start_date' => 'date',
        'director_end_date' => 'date',
        'defer_postpone_until' => 'date',
        'date_joined' => 'date',
        'date_opted_out' => 'date',
        'date_opted_in' => 'date',
        'auto_enrolled_letter_date' => 'date',
        'not_enrolled_letter_date' => 'date',
        'postponement_letter_date' => 'date',
        
        // Booleans
        'has_p45' => 'boolean',
        'postgrad_loan' => 'boolean',
        'no_employer_nic' => 'boolean',
        'exclude_nmw' => 'boolean',
        'holiday_fund_free' => 'boolean',
        'off_payroll_worker' => 'boolean',
        'director_flag' => 'boolean',
        'was_director' => 'boolean',
        'exclude_from_assessment' => 'boolean',
        'do_not_reassess' => 'boolean',
        'continue_to_assess' => 'boolean',
        'paid_overtime' => 'boolean',
        'may_join_pension' => 'boolean',
        'is_archive' => 'boolean',
        
        // Decimals
        'employee_widows_orphans' => 'decimal:2',
        'annual_pay' => 'decimal:2',
        'pay_per_period' => 'decimal:2',
        'hours_per_week' => 'decimal:2',
        'days_holiday_per_year' => 'decimal:1',
        'max_days_carry_over' => 'decimal:1',
        
        // Integers
        'weeks_notice' => 'integer',
        'days_sickness_full_pay' => 'integer',
        'retirement_age' => 'integer',
    ];

    /**
     * The attributes that should be hidden.
     */
    protected $hidden = [
        'deleted_at',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the client that owns the employee.
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'Client_ID');
    }

    /**
     * Get the user who created this employee.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'User_ID');
    }

    /**
     * Get the user who last updated this employee.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'User_ID');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope a query to only include active employees.
     */
    public function scopeActive($query)
    {
        return $query->where('is_archive', false);
    }

    /**
     * Scope a query to only include archived employees.
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archive', true);
    }

    /**
     * Scope a query to filter by client.
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Scope a query to filter by department.
     */
    public function scopeInDepartment($query, $department)
    {
        return $query->where('work_department', $department);
    }

    // ==========================================
    // ACCESSOR & MUTATORS
    // ==========================================

    /**
     * Get the employee's full name.
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->surname}");
    }

    /**
     * Get the employee's display name (preferred name or full name).
     */
    public function getDisplayNameAttribute()
    {
        return $this->known_as ?: $this->full_name;
    }

    /**
     * Format sort code with dashes.
     */
    public function setSortCodeAttribute($value)
    {
        // Remove any existing dashes and format as XX-XX-XX
        $cleanValue = preg_replace('/[^0-9]/', '', $value);
        
        if (strlen($cleanValue) === 6) {
            $this->attributes['sort_code'] = substr($cleanValue, 0, 2) . '-' . 
                                              substr($cleanValue, 2, 2) . '-' . 
                                              substr($cleanValue, 4, 2);
        } else {
            $this->attributes['sort_code'] = $value;
        }
    }

    /**
     * Format NI number to uppercase.
     */
    public function setNiNumberAttribute($value)
    {
        $this->attributes['ni_number'] = strtoupper($value);
    }

    /**
     * Format NI number work to uppercase.
     */
    public function setNiNumberWorkAttribute($value)
    {
        $this->attributes['ni_number_work'] = strtoupper($value);
    }
}