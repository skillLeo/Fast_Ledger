<div class="tab-content-pane" id="employment">
    <div class="flex-row-container">
        <!-- Column 1: Employment Start & Starter Info -->
        <div class="flex-col">
            <div class="form-card">
                <h3>Starter Info</h3>

                <x-form.radio-group 
                    label="Starter type" 
                    name="starter_type"
                    fieldClass="employee-form-field"
                    :options="[
                        'before_tax_year' =>
                            '1. This employee was working for this employer from before the start of the tax year',
                        'during_tax_year' => '2. This employee started working for this employer during the tax year',
                        'dont_know' => '3. I don\'t know when the employee started working for this employer',
                    ]"
                    helpText="Select 1 of 3 options" 
                />

                <x-form.input 
                    name="employment_start_date" 
                    label="Employment start date" 
                    type="date"
                    fieldClass="employee-form-field"
                    helpText="Label: 'Start date' on starter screen" 
                    class="mt-3" 
                />
            </div>
        </div>

        <!-- Column 2: Starter Declaration (HMRC) -->
        <div class="flex-col">
            <div class="form-card">
                <h3>Starter Declaration</h3>

                <x-form.radio-group 
                    name="hmrc_declaration"
                    fieldClass="employee-form-field"
                    :options="[
                        'option_a' =>
                            '<strong>Option A:</strong> This is my first job since last 6 April and I have not been receiving taxable Jobseeker\'s Allowance, Employment and Support Allowance, taxable Incapacity Benefit, State or Occupational Pension.',
                        'option_b' =>
                            '<strong>Option B:</strong> This is now my only job but since last 6 April I have had another job, or received taxable Jobseeker\'s Allowance, Employment and Support Allowance, taxable Incapacity Benefit. I do not receive a State or Occupational Pension.',
                        'option_c' =>
                            '<strong>Option C:</strong> As well as my new job, I have another job or receive a State or Occupational Pension.',
                        'option_d' => '<strong>Option D:</strong> I do not know the status of this employee',
                    ]" 
                />
                <small class="text-danger d-block mt-2">*Exactly one must be selected</small>
            </div>
        </div>

        <!-- Column 3: P45 & Loans -->
        <div class="flex-col">
            <div class="form-card">
                <h3>P45 & Loans</h3>

                <x-form.checkbox 
                    name="has_p45" 
                    label="Yes, employee has supplied P45" 
                    value="yes"
                    fieldClass="employee-form-field"
                />

                <div class="form-group mb-2">
                    <label>Student loans <small class="text-muted">(Select one option)</small></label>

                    <div class="radio-group">
                        <input type="radio" id="student_loan_none" name="student_loan" value="none" class="employee-form-field"
                            {{ old('student_loan', 'none') == 'none' ? 'checked' : '' }}>
                        <label for="student_loan_none">None</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="student_loan_type1" name="student_loan" value="type_1" class="employee-form-field"
                            {{ old('student_loan') == 'type_1' ? 'checked' : '' }}>
                        <label for="student_loan_type1">Type 1</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="student_loan_type2" name="student_loan" value="type_2" class="employee-form-field"
                            {{ old('student_loan') == 'type_2' ? 'checked' : '' }}>
                        <label for="student_loan_type2">Type 2</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="student_loan_type4" name="student_loan" value="type_4" class="employee-form-field"
                            {{ old('student_loan') == 'type_4' ? 'checked' : '' }}>
                        <label for="student_loan_type4">Type 4</label>
                    </div>
                    @error('student_loan')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <x-form.checkbox 
                    name="postgrad_loan" 
                    label="Yes, has postgraduate loan" 
                    value="yes"
                    fieldClass="employee-form-field"
                />
            </div>
        </div>
    </div>

    <!-- Second Row -->
    <div class="flex-row-container">
        <!-- Column 1: Auto Tax Code -->
        <div class="flex-col">
            <div class="form-card">
                <h3>Auto Tax Code (Info)</h3>

                <x-form.input 
                    name="tax_code_preview" 
                    label="Tax code preview"
                    value="The tax code used for this employee will be 1257L." 
                    :readonly="true"
                    fieldClass="employee-form-field"
                    helpText="This is automatically calculated based on the information provided" 
                />

                <div class="info-box mt-3">
                    <strong>ℹ️ Information:</strong> The tax code is automatically determined based on the starter
                    declaration and P45 information provided above.
                </div>
            </div>
        </div>

        <!-- Column 2: NI Category -->
        <div class="flex-col">
            <div class="form-card">
                <h3>NI Category</h3>

                {{-- Display Calculated Age --}}
                <div class="form-group mb-3">
                    <label class="d-block mb-2">
                        <i class="fas fa-birthday-cake text-primary"></i>
                        Calculated Age
                    </label>
                    <div class="alert alert-info d-flex align-items-center" style="padding: 12px 16px; margin: 0;">
                        <i class="fas fa-info-circle me-2" style="font-size: 20px;"></i>
                        <div>
                            <strong id="calculated_age" style="font-size: 18px; color: #004085;">
                                N/A
                            </strong>
                            <small class="d-block text-muted" style="font-size: 12px;">
                                Based on date of birth from Personal tab
                            </small>
                        </div>
                    </div>
                </div>

                {{-- NI Category Field --}}
                <x-form.input 
                    name="ni_category_letter" 
                    label="NI category letter" 
                    value="A" 
                    :readonly="true"
                    fieldClass="employee-form-field"
                    helpText="Automatically determined from employee's age and date of birth" 
                />

                {{-- NI Category Info Box with Highlighting --}}
                <div class="info-box mt-3">
                    <strong>Age Band Classifications:</strong>
                    <ul style="list-style: none; padding: 0; margin-top: 10px;">
                        <li class="ni-category-item ni-cat-x"
                            style="padding: 8px; margin: 5px 0; border-radius: 6px; transition: all 0.3s ease;">
                            <strong>Under 16:</strong> Category X
                        </li>
                        <li class="ni-category-item ni-cat-m"
                            style="padding: 8px; margin: 5px 0; border-radius: 6px; transition: all 0.3s ease;">
                            <strong>16 to 20:</strong> Category M
                        </li>
                        <li class="ni-category-item ni-cat-a"
                            style="padding: 8px; margin: 5px 0; border-radius: 6px; transition: all 0.3s ease;">
                            <strong>21 and over (under State Pension age):</strong> Category A
                        </li>
                        <li class="ni-category-item ni-cat-c"
                            style="padding: 8px; margin: 5px 0; border-radius: 6px; transition: all 0.3s ease;">
                            <strong>Over State Pension Age (66+):</strong> Category C
                        </li>
                    </ul>
                </div>

                {{-- Additional Info Alert --}}
                <div class="alert alert-warning mt-3" style="font-size: 13px; padding: 10px;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> NI category updates automatically when you change the date of birth in the
                    Personal tab.
                </div>
            </div>
        </div>

        <!-- Column 3: Work Information -->
        <div class="flex-col">
            <div class="form-card">
                <h3>Work Information</h3>

                <x-form.input 
                    name="job_title" 
                    label="Job title" 
                    placeholder="e.g. Office Assistant"
                    fieldClass="employee-form-field"
                />

                <x-form.select 
                    name="work_department" 
                    label="Department"
                    fieldClass="employee-form-field"
                    :options="[
                        'admin' => 'Admin',
                        'accounts' => 'Accounts',
                        'sales' => 'Sales',
                        'hr' => 'HR',
                        'it' => 'IT',
                        'operations' => 'Operations',
                        'other' => 'Other',
                    ]"
                    placeholder="Select Department" 
                />

                <x-form.select 
                    name="work_hours" 
                    label="Hours"
                    fieldClass="employee-form-field"
                    :options="[
                        'full-time' => 'Full-time',
                        'part-time' => 'Part-time',
                        'other' => 'Other',
                    ]" 
                    placeholder="Select Hours" 
                />

                <x-form.input 
                    name="works_number" 
                    label="Works number / payroll ID"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="ni_number_work" 
                    label="NI number (work tab copy)"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="date_started" 
                    label="Date started" 
                    type="date"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="date_left" 
                    label="Date left" 
                    type="date"
                    fieldClass="employee-form-field"
                    helpText="Must be on or after the date started" 
                />
            </div>
        </div>
    </div>
</div>