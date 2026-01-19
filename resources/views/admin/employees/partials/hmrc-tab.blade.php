<div class="tab-content-pane" id="hmrc">
    <div class="flex-row-container">
        <div class="flex-col">
            <div class="form-card">
                <h3>Starter Data (HMRC Classification)</h3>

                <div class="form-group mb-2">
                    <label>Starter type (HMRC)</label>
                    <div class="radio-group">
                        <input type="radio" id="starter_existing" name="starter_type_hmrc" value="existing" class="employee-form-field" {{ old('starter_type_hmrc') == 'existing' ? 'checked' : '' }}>
                        <label for="starter_existing">Existing employee</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="starter_with_p45" name="starter_type_hmrc" value="with_p45" class="employee-form-field" {{ old('starter_type_hmrc') == 'with_p45' ? 'checked' : '' }}>
                        <label for="starter_with_p45">Starter with a P45</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="starter_without_p45" name="starter_type_hmrc" value="without_p45" class="employee-form-field" {{ old('starter_type_hmrc') == 'without_p45' ? 'checked' : '' }}>
                        <label for="starter_without_p45">Starter without a P45</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="starter_p45_later" name="starter_type_hmrc" value="p45_later" class="employee-form-field" {{ old('starter_type_hmrc') == 'p45_later' ? 'checked' : '' }}>
                        <label for="starter_p45_later">P45 was provided later</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="starter_seconded" name="starter_type_hmrc" value="seconded" class="employee-form-field" {{ old('starter_type_hmrc') == 'seconded' ? 'checked' : '' }}>
                        <label for="starter_seconded">Employee seconded to work in the UK</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="starter_pension" name="starter_type_hmrc" value="pension" class="employee-form-field" {{ old('starter_type_hmrc') == 'pension' ? 'checked' : '' }}>
                        <label for="starter_pension">Non-employee being paid a pension</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="starter_unknown" name="starter_type_hmrc" value="unknown" class="employee-form-field" {{ old('starter_type_hmrc') == 'unknown' ? 'checked' : '' }}>
                        <label for="starter_unknown">Don't know</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex-col">
            <div class="form-card">
                <h3>Auto-enrolment Pension Details</h3>

                <x-form.checkbox 
                    name="exclude_from_assessment"
                    label="Exclude from assessment"
                    fieldClass="employee-form-field"
                />

                <x-form.select 
                    name="auto_enrolment_pension"
                    label="Auto-enrolment pension"
                    fieldClass="employee-form-field"
                    :options="[
                        'none' => 'None'
                    ]"
                    placeholder="Select pension scheme"
                />

                <x-form.input 
                    name="employee_group"
                    label="Employee group"
                    fieldClass="employee-form-field"
                />

                <x-form.select 
                    name="assessment"
                    label="Assessment"
                    fieldClass="employee-form-field"
                    :options="[
                        'eligible' => 'Eligible',
                        'non_eligible' => 'Non-eligible',
                        'entitled' => 'Entitled',
                        'unknown' => 'Unknown'
                    ]"
                    placeholder="Select assessment"
                />
            </div>
        </div>

        <div class="flex-col">
            <div class="form-card">
                <h3>Dates & Status</h3>

                <x-form.input 
                    name="defer_postpone_until"
                    label="Defer/postpone until"
                    type="date"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="date_joined"
                    label="Date joined"
                    type="date"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="date_left_hmrc"
                    label="Date left"
                    type="date"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="date_opted_out"
                    label="Date opted out"
                    type="date"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="date_opted_in"
                    label="Date opted in"
                    type="date"
                    fieldClass="employee-form-field"
                    :optional="true"
                />
            </div>
        </div>
    </div>

    <!-- Second Row -->
    <div class="flex-row-container">
        <div class="flex-col">
            <div class="form-card">
                <h3>Re-assessment Settings</h3>

                <x-form.checkbox 
                    name="do_not_reassess"
                    label="Do not re-assess on the re-enrolment date"
                    fieldClass="employee-form-field"
                />

                <x-form.checkbox 
                    name="continue_to_assess"
                    label="Continue to assess"
                    fieldClass="employee-form-field"
                />
            </div>
        </div>

        <div class="flex-col">
            <div class="form-card">
                <h3>Letters and Contributions</h3>

                <x-form.input 
                    name="auto_enrolled_letter_date"
                    label="Auto-enrolled letter date"
                    type="date"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="not_enrolled_letter_date"
                    label="Not enrolled letter date"
                    type="date"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="postponement_letter_date"
                    label="Postponement letter date"
                    type="date"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="contribution_percentages"
                    label="Contribution percentages"
                    fieldClass="employee-form-field"
                    :readonly="true"
                />

                <div class="form-group mb-2">
                    <button type="button" class="btn btn-info">Set contributions %</button>
                </div>
            </div>
        </div>

        <div class="flex-col">
            <!-- Empty column -->
        </div>
    </div>
</div>