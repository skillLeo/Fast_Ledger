/**
 * VAT Management Module
 * Handles VAT type loading, dropdown population, and form updates
 */

class VatManager {
    constructor() {
        this.vatTypesByForm = {};
        this.currentFormVatTypes = [];
    }

    /**
     * Load VAT types by form/payment type
     */
    loadVatTypesByForm(formType, callback) {
        fetch(`/api/vat-types-by-form/${formType}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.vat_types && data.vat_types.length > 0) {
                    const vatTypes = data.vat_types.map(vatType => ({
                        id: vatType.id,
                        vat_type_id: vatType.vat_type_id,
                        vat_name: vatType.display_name || vatType.vat_name,
                        percentage: parseFloat(vatType.percentage) || 0,
                        form_key: vatType.form_key
                    }));

                    this.currentFormVatTypes = vatTypes;
                    callback(vatTypes);
                } else {
                    console.error('No VAT types returned for form:', formType);
                    this.currentFormVatTypes = [];
                    callback([]);
                    this.showVatLoadError(formType);
                }
            })
            .catch(error => {
                console.error('Failed to load VAT types:', error);
                this.currentFormVatTypes = [];
                callback([]);
                this.showVatLoadError(formType);
            });
    }

    /**
     * Show user-friendly error when VAT types can't be loaded
     */
    showVatLoadError(formType) {
        const message = `Unable to load VAT types for ${formType}. Please refresh the page or contact support.`;
        alert(message);
    }

    /**
     * Create VAT dropdown options HTML
     */
    /**
 * Create VAT dropdown options HTML
 */
    createVatDropdownOptions(vatTypes, selectedValue = null) {
        console.log('🔧 createVatDropdownOptions called', {
            vatTypes_count: vatTypes?.length || 0,
            selectedValue: selectedValue,
            vatTypes_sample: vatTypes?.slice(0, 2)
        });

        let options = '<option value="">Select VAT Rate</option>'; // ✅ FIXED: Add default option

        if (!vatTypes || vatTypes.length === 0) {
            console.warn('⚠️ No VAT types provided to createVatDropdownOptions');
            return options;
        }

        vatTypes.forEach((vatType, index) => {
            const selected = selectedValue && selectedValue == vatType.percentage ? 'selected' : '';
            const vatName = vatType.vat_name || vatType.display_name || 'VAT';
            const percentage = vatType.percentage || 0;
            const vatId = vatType.id;

            options += `<option value="${percentage}" data-vat-id="${vatId}" ${selected}>
            ${vatName} (${percentage}%)
        </option>`;

            // ✅ DEBUG: Log each option being created
            console.log(`  [${index}] Created option: value="${percentage}" data-vat-id="${vatId}" text="${vatName}"`);
        });

        console.log('✅ Dropdown HTML created with', vatTypes.length, 'options');
        return options;
    }

    /**
     * Update form VAT rates when payment type changes
     */
    updateFormVatRates(paymentType) {
        // For invoice items
        const vatRateSelects = document.querySelectorAll('#invoiceItemsTable .vat-rate');
        vatRateSelects.forEach(select => {
            const currentValue = select.value;
            this.loadVatTypesByForm(paymentType, (vatTypes) => {
                select.innerHTML = this.createVatDropdownOptions(vatTypes, currentValue);
            });
        });

        // For journal entries
        const journalTaxSelects = document.querySelectorAll('#journalRows .journal-tax-select');
        journalTaxSelects.forEach(select => {
            const currentValue = select.value;
            this.loadVatTypesByForm(paymentType, (vatTypes) => {
                let vatOptions = '<option value="0">No Tax (0%)</option>';
                vatTypes.forEach(vatType => {
                    const selected = currentValue == vatType.percentage ? 'selected' : '';
                    vatOptions += `<option value="${vatType.percentage}" data-vat-id="${vatType.id}" ${selected}>
                        ${vatType.vat_name} (${vatType.percentage}%)
                    </option>`;
                });
                select.innerHTML = vatOptions;
            });
        });

        // For regular office form
        const regularVatDropdown = document.getElementById('VATDropdown');
        if (regularVatDropdown) {
            const currentValue = regularVatDropdown.value;
            this.loadVatTypesByForm(paymentType, (vatTypes) => {
                let vatOptions = '<option value="">Select VAT Type</option>';
                vatTypes.forEach(vatType => {
                    const selected = currentValue == vatType.id ? 'selected' : '';
                    vatOptions += `<option value="${vatType.id}" ${selected}>
                        ${vatType.vat_name} (${vatType.percentage}%)
                    </option>`;
                });
                regularVatDropdown.innerHTML = vatOptions;
                console.log('✅ Updated regular office VAT dropdown for:', paymentType);
            });
        }
    }

    /**
     * Get current form VAT types
     */
    getCurrentFormVatTypes() {
        return this.currentFormVatTypes;
    }
}

// Initialize and expose globally
window.VatManager = VatManager;
window.vatManager = null;

document.addEventListener('DOMContentLoaded', () => {
    window.vatManager = new VatManager();
});