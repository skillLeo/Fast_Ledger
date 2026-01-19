<div class="tab-content-pane" id="nic">
    <div class="flex-row-container">
        <div class="flex-col">
            <div class="form-card">
                <h3>NIC</h3>

                <x-form.checkbox 
                    name="no_employer_nic"
                    label="No Employer NIC liability"
                    fieldClass="employee-form-field"
                />

                <x-form.checkbox 
                    name="exclude_nmw"
                    label="Exclude from NMW checks"
                    fieldClass="employee-form-field"
                />

                <x-form.checkbox 
                    name="holiday_fund_free"
                    label="Holiday fund pmts free of NIC"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="employee_widows_orphans"
                    label="Employee's widows and orphans / life assurance contributions"
                    type="number"
                    step="0.01"
                    placeholder="0.00"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="veteran_first_day"
                    label="First day of the veteran's first civilian employment"
                    type="date"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.checkbox 
                    name="off_payroll_worker"
                    label="Off payroll worker"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="workplace_postcode"
                    label="Workplace postcode"
                    fieldClass="employee-form-field"
                    :optional="true"
                />
            </div>
        </div>

        <div class="flex-col">
            <div class="form-card">
                <h3>Director Information</h3>

                <x-form.checkbox 
                    name="director_flag"
                    label="Director (flag on initial screen)"
                    fieldClass="employee-form-field"
                />

                <x-form.checkbox 
                    name="was_director"
                    label="Tick here if the employee was a director of this company at any point during the year"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="director_start_date"
                    label="Start of directorship"
                    type="date"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="director_end_date"
                    label="End of directorship"
                    type="date"
                    fieldClass="employee-form-field"
                    :optional="true"
                    helpText="Optional – blank if still a director"
                />

                <div class="form-group mb-2">
                    <div class="radio-group">
                        <input type="radio" id="director_nic_standard" name="director_nic_method" value="standard" class="employee-form-field">
                        <label for="director_nic_standard">Use the "standard annual earnings period" method</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="director_nic_alternative" name="director_nic_method" value="alternative" class="employee-form-field">
                        <label for="director_nic_alternative">Use the "alternative arrangements" method (period-by-period) – this should only be used if the director is being paid a regular amount</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-col">
            <!-- Empty column for 3-column layout balance -->
        </div>
    </div>
</div>