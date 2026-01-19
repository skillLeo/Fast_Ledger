<div class="tab-content-pane active" id="personal">
    <div class="flex-row-container">
        <!-- Personal Details Column -->
        <div class="flex-col">
            <div class="form-card">
                <h3>Personal Details</h3>

                <x-form.select 
                    name="title"
                    label="Title"
                    fieldClass="employee-form-field"
                    :options="[
                        'mr' => 'Mr',
                        'mrs' => 'Mrs',
                        'miss' => 'Miss',
                        'ms' => 'Ms',
                        'dr' => 'Dr',
                        'prof' => 'Prof'
                    ]"
                    placeholder="Select Title"
                />

                <x-form.input 
                    name="first_name"
                    label="First name(s)"
                    fieldClass="employee-form-field"
                    :required="true"
                />

                <x-form.input 
                    name="surname"
                    label="Surname / Family name"
                    fieldClass="employee-form-field"
                    :required="true"
                />

                <x-form.input 
                    name="known_as"
                    label="Known as / Preferred name"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="date_of_birth"
                    label="Date of birth"
                    type="date"
                    fieldClass="employee-form-field"
                />

                <x-form.select 
                    name="gender"
                    label="Gender"
                    fieldClass="employee-form-field"
                    :options="[
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                        'prefer_not_to_say' => 'Prefer not to say'
                    ]"
                    placeholder="Select Gender"
                />

                <x-form.input 
                    name="ni_number"
                    label="National Insurance number"
                    placeholder="XX123456X"
                    fieldClass="employee-form-field"
                    helpText="Format: 2 letters, 6 digits, 1 letter"
                />

                <x-form.input 
                    name="passport_number"
                    label="Passport number"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="nationality"
                    label="Nationality"
                    fieldClass="employee-form-field"
                    :optional="true"
                />
            </div>
        </div>

        <!-- Address Column -->
        <div class="flex-col">
            <div class="form-card">
                <h3>Home Address</h3>

                <x-form.input 
                    name="address_line_1"
                    label="Address line 1"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="address_line_2"
                    label="Address line 2"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="address_line_3"
                    label="Address line 3"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="city_town"
                    label="City / Town"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="county"
                    label="County"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="postcode"
                    label="Postcode"
                    placeholder="SW1A 1AA"
                    fieldClass="employee-form-field"
                />

                <x-form.select 
                    name="country"
                    label="Country"
                    fieldClass="employee-form-field"
                    :options="[
                        'uk' => 'United Kingdom',
                        'england' => 'England',
                        'scotland' => 'Scotland',
                        'wales' => 'Wales',
                        'ni' => 'Northern Ireland'
                    ]"
                    value="uk"
                />
            </div>
        </div>

        <!-- Contact Details Column -->
        <div class="flex-col">
            <div class="form-card">
                <h3>Contact Details</h3>

                <x-form.input 
                    name="primary_phone"
                    label="Primary phone number"
                    type="tel"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="secondary_phone"
                    label="Secondary phone"
                    type="tel"
                    fieldClass="employee-form-field"
                    :optional="true"
                />

                <x-form.input 
                    name="email"
                    label="Email address"
                    type="email"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="emergency_contact_name"
                    label="Emergency contact name"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="emergency_contact_phone"
                    label="Emergency contact phone"
                    type="tel"
                    fieldClass="employee-form-field"
                />

                <x-form.input 
                    name="emergency_contact_relationship"
                    label="Relationship"
                    fieldClass="employee-form-field"
                    :optional="true"
                    placeholder="e.g. Spouse, Parent"
                />
            </div>
        </div>
    </div>
</div>