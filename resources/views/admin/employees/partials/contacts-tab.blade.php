<div class="tab-content-pane" id="contacts">
    <div class="flex-row-container">
        <div class="flex-col">
            <div class="form-card">
                <h3>(Emergency Contact) - 1</h3>

                <x-form.input 
                    name="contact1_name"
                    label="Name"
                    fieldClass="employee-form-field"
                    :required="true"
                />

                <x-form.input 
                    name="contact1_relationship"
                    label="Relationship"
                    placeholder="e.g. Mother, Friend"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="contact1_telephone"
                    label="Telephone"
                    type="tel"
                    fieldClass="employee-form-field"
                    :required="true"
                />

                <x-form.input 
                    name="contact1_mobile"
                    label="Mobile"
                    type="tel"
                    fieldClass="employee-form-field"
                    :required="true"
                />

                <x-form.textarea 
                    name="contact1_address"
                    label="Address"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="contact1_postcode"
                    label="Postcode"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.textarea 
                    name="contact1_notes"
                    label="Notes"
                    fieldClass="employee-form-field"
                    :optional="true"
                />
            </div>
        </div>

        <div class="flex-col">
            <div class="form-card">
                <h3>(Emergency Contact) - 2</h3>

                <x-form.input 
                    name="contact2_name"
                    label="Name"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="contact2_relationship"
                    label="Relationship"
                    placeholder="e.g. Mother, Friend"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="contact2_telephone"
                    label="Telephone"
                    type="tel"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="contact2_mobile"
                    label="Mobile"
                    type="tel"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.textarea 
                    name="contact2_address"
                    label="Address"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="contact2_postcode"
                    label="Postcode"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.textarea 
                    name="contact2_notes"
                    label="Notes"
                    fieldClass="employee-form-field"
                    :optional="true"
                />
            </div>
        </div>

        <div class="flex-col">
            <div class="form-card">
                <h3>Employment History</h3>

                <div class="info-box mb-3">
                    <strong>Note:</strong> This section displays a log of all changes made to this employee's record, including pay changes, department transfers, and other modifications.
                </div>

                <x-form.input 
                    name="history_date"
                    label="Date"
                    type="datetime-local"
                    fieldClass="employee-form-field"
                    helpText="Date of event"
                />

                <x-form.input 
                    name="history_category"
                    label="Category"
                    placeholder="e.g. Pay change, Department change"
                    fieldClass="employee-form-field"
                    helpText="e.g. Pay change, Department change"
                />

                <x-form.textarea 
                    name="history_description"
                    label="Description"
                    placeholder="What happened"
                    fieldClass="employee-form-field"
                    helpText="What happened"
                />

                <x-form.input 
                    name="history_user"
                    label="User"
                    placeholder="Who made the change"
                    fieldClass="employee-form-field"
                    helpText="Who made the change"
                />
            </div>
        </div>
    </div>
</div>