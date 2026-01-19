<?php

namespace App\Http\Requests\Employees;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the employee ID from the route
        $employeeId = $this->route('id');

        return [
            // A. Personal Details (Required)
            'first_name' => 'required|string|max:100',
            'surname' => 'required|string|max:100',
            
            // A. Personal Details (Optional)
            'title' => 'nullable|string|max:10',
            'known_as' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            
            // ⭐ UNIQUE FIELDS - Ignore current employee
            'ni_number' => [
                'nullable',
                'string',
                'max:9',
                Rule::unique('employees', 'ni_number')->ignore($employeeId, 'id')
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('employees', 'email')->ignore($employeeId, 'id')
            ],
            
            'passport_number' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            
            // B. Address
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'address_line_3' => 'nullable|string|max:255',
            'city_town' => 'nullable|string|max:100',
            'county' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            
            // C. Contact Details
            'primary_phone' => 'nullable|string|max:20',
            'secondary_phone' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            
            // D. Employment Start & Starter Info
            'starter_type' => 'nullable|in:before_tax_year,during_tax_year,dont_know',
            'employment_start_date' => 'nullable|date',
            'starter_type_hmrc' => 'nullable|in:existing,with_p45,without_p45,p45_later,seconded,pension,unknown',
            
            // E. HMRC Starter Declaration
            'hmrc_declaration' => 'nullable|in:option_a,option_b,option_c,option_d',
            'has_p45' => 'nullable|boolean',
            'student_loan' => 'nullable|in:none,type_1,type_2,type_4',
            'postgrad_loan' => 'nullable|boolean',
            
            // F. Tax & NI
            'tax_code_preview' => 'nullable|string|max:100',
            'ni_category_letter' => 'nullable|string|max:5',
            
            // G. Work Information
            'job_title' => 'nullable|string|max:100',
            'work_department' => 'nullable|string|max:100',
            'work_hours' => 'nullable|string|max:50',
            'works_number' => 'nullable|string|max:50',
            'ni_number_work' => 'nullable|string|max:9',
            'date_started' => 'nullable|date',
            'date_left' => 'nullable|date|after_or_equal:date_started',
            
            // H. NIC / Compliance Flags
            'no_employer_nic' => 'nullable|boolean',
            'exclude_nmw' => 'nullable|boolean',
            'holiday_fund_free' => 'nullable|boolean',
            'employee_widows_orphans' => 'nullable|numeric|min:0|max:999999.99',
            'veteran_first_day' => 'nullable|date',
            'off_payroll_worker' => 'nullable|boolean',
            'workplace_postcode' => 'nullable|string|max:20',
            
            // I. Director Information
            'director_flag' => 'nullable|boolean',
            'was_director' => 'nullable|boolean',
            'director_start_date' => 'nullable|date',
            'director_end_date' => 'nullable|date|after_or_equal:director_start_date',
            'director_nic_method' => 'nullable|in:standard,alternative',
            
            // J. Payment Details
            'pay_frequency' => 'nullable|in:weekly,2-weekly,4-weekly,monthly',
            'pay_method' => 'nullable|in:bacs,bacs_hash,cash,cheque,direct_debit,other',
            'annual_pay' => 'nullable|numeric|min:0|max:9999999.99',
            'pay_per_period' => 'nullable|numeric|min:0|max:9999999.99',
            'delivery_method' => 'nullable|in:print,email,both',
            
            // K. Bank Details
            'bank_name' => 'nullable|string|max:100',
            'sort_code' => 'nullable|string|max:8|regex:/^[0-9]{2}-[0-9]{2}-[0-9]{2}$/',
            'account_number' => 'nullable|string|max:8|regex:/^[0-9]{8}$/',
            'account_name' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:100',
            'building_society_ref' => 'nullable|string|max:100',
            
            // L. Auto-enrolment Pension
            'exclude_from_assessment' => 'nullable|boolean',
            'auto_enrolment_pension' => 'nullable|string|max:100',
            'employee_group' => 'nullable|string|max:100',
            'assessment' => 'nullable|in:eligible,non_eligible,entitled,unknown',
            'defer_postpone_until' => 'nullable|date',
            'date_joined' => 'nullable|date',
            'date_opted_out' => 'nullable|date',
            'date_opted_in' => 'nullable|date',
            'do_not_reassess' => 'nullable|boolean',
            'continue_to_assess' => 'nullable|boolean',
            'auto_enrolled_letter_date' => 'nullable|date',
            'not_enrolled_letter_date' => 'nullable|date',
            'postponement_letter_date' => 'nullable|date',
            'contribution_percentages' => 'nullable|string|max:100',
            
            // M. Employment Terms
            'hours_per_week' => 'nullable|numeric|min:0|max:168',
            'paid_overtime' => 'nullable|boolean',
            'weeks_notice' => 'nullable|integer|min:0|max:52',
            'days_sickness_full_pay' => 'nullable|integer|min:0|max:365',
            'retirement_age' => 'nullable|integer|min:16|max:100',
            'may_join_pension' => 'nullable|boolean',
            'days_holiday_per_year' => 'nullable|numeric|min:0|max:365',
            'max_days_carry_over' => 'nullable|numeric|min:0|max:365',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        Log::channel('daily')->error('Employee Update Validation Failed', [
            'employee_id' => $this->route('id'),
            'errors' => $validator->errors()->toArray(),
            'failed_rules' => $validator->failed(),
            'input_data' => $this->except(['password', '_token', '_method']),
            'user_id' => Auth::id(),
            'url' => $this->url(),
        ]);

        foreach ($validator->errors()->messages() as $field => $messages) {
            Log::channel('daily')->warning("Update Validation Failed: {$field}", [
                'field' => $field,
                'value' => $this->input($field),
                'errors' => $messages,
                'rules' => $validator->failed()[$field] ?? [],
            ]);
        }

        parent::failedValidation($validator);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'surname' => 'surname',
            'date_of_birth' => 'date of birth',
            'ni_number' => 'National Insurance number',
            'primary_phone' => 'primary phone number',
            'employment_start_date' => 'employment start date',
            'hmrc_declaration' => 'HMRC starter declaration',
            'pay_frequency' => 'pay frequency',
            'pay_method' => 'payment method',
            'sort_code' => 'sort code',
            'account_number' => 'account number',
            'hours_per_week' => 'hours per week',
            'days_holiday_per_year' => 'days holiday per year',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'The first name is required.',
            'surname.required' => 'The surname is required.',
            'ni_number.unique' => 'This National Insurance number is already registered to another employee.',
            'email.unique' => 'This email address is already registered to another employee.',
            'email.email' => 'Please enter a valid email address.',
            'date_of_birth.before' => 'The date of birth must be in the past.',
            'sort_code.regex' => 'The sort code must be in format: XX-XX-XX',
            'account_number.regex' => 'The account number must be 8 digits.',
            'date_left.after_or_equal' => 'The date left must be on or after the date started.',
            'director_end_date.after_or_equal' => 'The end of directorship must be on or after the start date.',
            'hours_per_week.max' => 'Hours per week cannot exceed 168 hours.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        Log::channel('daily')->info('Employee Update Form Submitted', [
            'employee_id' => $this->route('id'),
            'fields_count' => count($this->all()),
            'user_id' => Auth::id(),
        ]);

        // Convert checkbox values to boolean
        $booleanFields = [
            'has_p45',
            'postgrad_loan',
            'no_employer_nic',
            'exclude_nmw',
            'holiday_fund_free',
            'off_payroll_worker',
            'director_flag',
            'was_director',
            'exclude_from_assessment',
            'do_not_reassess',
            'continue_to_assess',
            'paid_overtime',
            'may_join_pension',
        ];

        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => filter_var($this->input($field), FILTER_VALIDATE_BOOLEAN),
                ]);
            }
        }

        // Format NI number to uppercase
        if ($this->has('ni_number')) {
            $this->merge([
                'ni_number' => strtoupper($this->input('ni_number')),
            ]);
        }

        if ($this->has('ni_number_work')) {
            $this->merge([
                'ni_number_work' => strtoupper($this->input('ni_number_work')),
            ]);
        }
    }
}