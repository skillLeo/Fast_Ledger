<div class="tab-content-pane" id="terms">
    <div class="flex-row-container">
        <div class="flex-col">
            <div class="form-card">
                <h3>Employment Terms</h3>

                <x-form.input 
                    name="hours_per_week"
                    label="Hours worked per week"
                    type="number"
                    step="0.01"
                    placeholder="e.g. 40.00"
                    fieldClass="employee-form-field"
                    helpText="Maximum 168 hours"
                />

                <x-form.checkbox 
                    name="paid_overtime"
                    label="Paid overtime"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="weeks_notice"
                    label="Weeks notice required"
                    type="number"
                    step="1"
                    placeholder="e.g. 4"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="days_sickness_full_pay"
                    label="Days sickness on full pay"
                    type="number"
                    step="1"
                    placeholder="e.g. 30"
                    fieldClass="employee-form-field"
                />
            </div>
        </div>

        <div class="flex-col">
            <div class="form-card">
                <h3>Retirement & Benefits</h3>

                <x-form.input 
                    name="retirement_age"
                    label="Retirement age"
                    type="number"
                    step="1"
                    placeholder="e.g. 65"
                    fieldClass="employee-form-field"
                />

                <x-form.checkbox 
                    name="may_join_pension"
                    label="May join pension scheme"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="days_holiday_per_year"
                    label="Days holiday per year"
                    type="number"
                    step="0.1"
                    placeholder="e.g. 20.0"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="max_days_carry_over"
                    label="Max days to carry over"
                    type="number"
                    step="0.1"
                    placeholder="e.g. 0.0"
                    fieldClass="employee-form-field"
                />

                <div class="form-group mb-2">
                    <button type="button" class="btn btn-info">Reset to Employer's standard terms</button>
                </div>
            </div>
        </div>

        <div class="flex-col">
            <!-- Empty column for 3-column layout balance -->
        </div>
    </div>
</div>