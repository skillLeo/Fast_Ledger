<div class="tab-content-pane" id="payment">
    <div class="flex-row-container">
        <div class="flex-col">
            <div class="form-card">
                <h3>Pay Details</h3>

                <x-form.select 
                    name="pay_frequency"
                    label="Pay frequency"
                    fieldClass="employee-form-field"
                    :options="[
                        'weekly' => 'Weekly',
                        '2-weekly' => '2-Weekly',
                        '4-weekly' => '4-Weekly',
                        'monthly' => 'Monthly'
                    ]"
                    placeholder="Select pay frequency"
                    helpText="Dropdown selection (Required)"
                />

                <x-form.select 
                    name="pay_method"
                    label="Pay method"
                    fieldClass="employee-form-field"
                    :options="[
                        'bacs' => 'BACS',
                        'bacs_hash' => 'BACS (with hash code)',
                        'cash' => 'Cash',
                        'cheque' => 'Cheque',
                        'direct_debit' => 'Direct Debit',
                        'other' => 'Other'
                    ]"
                    placeholder="Select pay method"
                    helpText="Dropdown selection (Required)"
                />

                <x-form.input 
                    name="annual_pay"
                    label="Annual pay"
                    type="number"
                    step="0.01"
                    placeholder="0.00"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="pay_per_period"
                    label="Pay per period"
                    type="number"
                    step="0.01"
                    placeholder="0.00"
                    fieldClass="employee-form-field"
                    helpText="Numeric input (Optional)"
                />

                <x-form.select 
                    name="delivery_method"
                    label="Delivery method"
                    fieldClass="employee-form-field"
                    :options="[
                        'print' => 'Print',
                        'email' => 'Email'
                    ]"
                    placeholder="Select delivery method"
                    helpText="Dropdown selection (Optional)"
                />
            </div>
        </div>

        <div class="flex-col">
            <div class="form-card">
                <h3>Bank Details</h3>

                <x-form.input 
                    name="bank_name"
                    label="Bank name"
                    fieldClass="employee-form-field"
                    helpText="Text input (Required for BACS/DD)"
                />

                <x-form.input 
                    name="sort_code"
                    label="Sort code"
                    placeholder="00-00-00"
                    maxlength="8"
                    fieldClass="employee-form-field"
                    helpText="Format: XX-XX-XX"
                />

                <x-form.input 
                    name="account_number"
                    label="Account number"
                    maxlength="8"
                    placeholder="12345678"
                    fieldClass="employee-form-field"
                    helpText="Must be exactly 8 digits"
                />

                <x-form.input 
                    name="account_name"
                    label="Account name"
                    fieldClass="employee-form-field"
                    helpText="Text input (Required)"
                />

                <x-form.input 
                    name="payment_reference"
                    label="Payment reference"
                    fieldClass="employee-form-field"
                    helpText="Text input (Optional)"
                />

                <x-form.input 
                    name="building_society_ref"
                    label="Building society ref."
                    fieldClass="employee-form-field"
                    helpText="Text input (Optional)"
                />
            </div>
        </div>

        <div class="flex-col">
            <!-- Empty column -->
        </div>
    </div>
</div>