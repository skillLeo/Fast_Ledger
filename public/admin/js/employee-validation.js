/**
 * Complete Employee Form Validation Manager
 * Validates ALL 7 tabs with inline errors, badges, and auto-navigation
 */
class EmployeeFormValidator {
    constructor(formElement) {
        this.form = formElement;
        this.validationRules = this.initValidationRules();
        this.init();
    }

    init() {
        this.bindRealTimeValidation();
        this.bindFormSubmit();
        
        // Check for server-side errors on load
        setTimeout(() => this.updateTabIndicators(), 100);
    }

    initValidationRules() {
        return {
            // ========================================
            // TAB 1: PERSONAL
            // ========================================
            first_name: {
                required: true,
                minLength: 2,
                maxLength: 100,
                pattern: /^[a-zA-Z\s'-]+$/,
                message: 'First name is required (2-100 letters only)'
            },
            surname: {
                required: true,
                minLength: 2,
                maxLength: 100,
                pattern: /^[a-zA-Z\s'-]+$/,
                message: 'Surname is required (2-100 letters only)'
            },
            date_of_birth: {
                custom: (value) => {
                    if (!value) return true;
                    const dob = new Date(value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const age = today.getFullYear() - dob.getFullYear();
                    const monthDiff = today.getMonth() - dob.getMonth();
                    const finalAge = monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate()) ? age - 1 : age;
                    return finalAge >= 16 && finalAge <= 100 && dob < today;
                },
                message: 'Must be between 16-100 years old and in the past'
            },
            ni_number: {
                pattern: /^[A-Z]{2}[0-9]{6}[A-Z]$/,
                message: 'Format: XX123456X (2 letters, 6 digits, 1 letter)'
            },
            email: {
                pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                message: 'Please enter a valid email address'
            },
            postcode: {
                pattern: /^[A-Z]{1,2}[0-9]{1,2}[A-Z]?\s?[0-9][A-Z]{2}$/i,
                message: 'Please enter a valid UK postcode'
            },
            primary_phone: {
                pattern: /^[0-9\s\+\-\(\)]+$/,
                minLength: 10,
                message: 'Valid phone number required (min 10 digits)'
            },

            // ========================================
            // TAB 2: EMPLOYMENT INFO
            // ========================================
            employment_start_date: {
                custom: (value) => {
                    if (!value) return true;
                    return new Date(value) <= new Date();
                },
                message: 'Employment start date cannot be in the future'
            },
            date_started: {
                custom: (value) => {
                    if (!value) return true;
                    return new Date(value) <= new Date();
                },
                message: 'Start date cannot be in the future'
            },
            date_left: {
                custom: (value, formData) => {
                    if (!value) return true;
                    const dateStarted = formData.get('date_started') || formData.get('employment_start_date');
                    if (!dateStarted) return true;
                    return new Date(value) >= new Date(dateStarted);
                },
                message: 'Date left must be on or after date started'
            },
            ni_number_work: {
                pattern: /^[A-Z]{2}[0-9]{6}[A-Z]$/,
                message: 'Format: XX123456X'
            },

            // ========================================
            // TAB 3: NIC
            // ========================================
            employee_widows_orphans: {
                custom: (value) => {
                    if (!value) return true;
                    const num = parseFloat(value);
                    return !isNaN(num) && num >= 0 && num <= 999999.99;
                },
                message: 'Must be between 0 and 999,999.99'
            },
            veteran_first_day: {
                custom: (value) => {
                    if (!value) return true;
                    return new Date(value) <= new Date();
                },
                message: 'Date cannot be in the future'
            },
            workplace_postcode: {
                pattern: /^[A-Z]{1,2}[0-9]{1,2}[A-Z]?\s?[0-9][A-Z]{2}$/i,
                message: 'Please enter a valid UK postcode'
            },
            director_start_date: {
                custom: (value) => {
                    if (!value) return true;
                    return new Date(value) <= new Date();
                },
                message: 'Director start date cannot be in the future'
            },
            director_end_date: {
                custom: (value, formData) => {
                    if (!value) return true;
                    const startDate = formData.get('director_start_date');
                    if (!startDate) return true;
                    return new Date(value) >= new Date(startDate);
                },
                message: 'End date must be on or after start date'
            },

            // ========================================
            // TAB 4: HMRC STARTER DECLARATION
            // ========================================
            defer_postpone_until: {
                custom: (value) => {
                    if (!value) return true;
                    const selectedDate = new Date(value);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    return selectedDate >= today;
                },
                message: 'Defer date must be today or in the future'
            },
            date_joined: {
                custom: (value) => {
                    if (!value) return true;
                    return new Date(value) <= new Date();
                },
                message: 'Date joined cannot be in the future'
            },
            date_opted_out: {
                custom: (value) => {
                    if (!value) return true;
                    return new Date(value) <= new Date();
                },
                message: 'Date opted out cannot be in the future'
            },
            date_opted_in: {
                custom: (value) => {
                    if (!value) return true;
                    return new Date(value) <= new Date();
                },
                message: 'Date opted in cannot be in the future'
            },
            auto_enrolled_letter_date: {
                custom: (value) => {
                    if (!value) return true;
                    return new Date(value) <= new Date();
                },
                message: 'Letter date cannot be in the future'
            },
            not_enrolled_letter_date: {
                custom: (value) => {
                    if (!value) return true;
                    return new Date(value) <= new Date();
                },
                message: 'Letter date cannot be in the future'
            },
            postponement_letter_date: {
                custom: (value) => {
                    if (!value) return true;
                    return new Date(value) <= new Date();
                },
                message: 'Letter date cannot be in the future'
            },

            // ========================================
            // TAB 5: CONTACTS & HISTORY
            // ========================================
            contact1_name: {
                required: true,
                minLength: 2,
                maxLength: 100,
                message: 'Emergency contact name is required'
            },
            contact1_telephone: {
                required: true,
                pattern: /^[0-9\s\+\-\(\)]+$/,
                minLength: 10,
                message: 'Valid telephone number required'
            },
            contact1_mobile: {
                required: true,
                pattern: /^[0-9\s\+\-\(\)]+$/,
                minLength: 10,
                message: 'Valid mobile number required'
            },
            contact1_postcode: {
                pattern: /^[A-Z]{1,2}[0-9]{1,2}[A-Z]?\s?[0-9][A-Z]{2}$/i,
                message: 'Please enter a valid UK postcode'
            },
            contact2_telephone: {
                pattern: /^[0-9\s\+\-\(\)]+$/,
                minLength: 10,
                message: 'Valid telephone number required'
            },
            contact2_mobile: {
                pattern: /^[0-9\s\+\-\(\)]+$/,
                minLength: 10,
                message: 'Valid mobile number required'
            },
            contact2_postcode: {
                pattern: /^[A-Z]{1,2}[0-9]{1,2}[A-Z]?\s?[0-9][A-Z]{2}$/i,
                message: 'Please enter a valid UK postcode'
            },

            // ========================================
            // TAB 6: TERMS
            // ========================================
            hours_per_week: {
                custom: (value) => {
                    if (!value) return true;
                    const num = parseFloat(value);
                    return !isNaN(num) && num >= 0 && num <= 168;
                },
                message: 'Hours per week must be between 0 and 168'
            },
            weeks_notice: {
                custom: (value) => {
                    if (!value) return true;
                    const num = parseInt(value);
                    return !isNaN(num) && num >= 0 && num <= 52;
                },
                message: 'Weeks notice must be between 0 and 52'
            },
            days_sickness_full_pay: {
                custom: (value) => {
                    if (!value) return true;
                    const num = parseInt(value);
                    return !isNaN(num) && num >= 0 && num <= 365;
                },
                message: 'Days must be between 0 and 365'
            },
            retirement_age: {
                custom: (value) => {
                    if (!value) return true;
                    const num = parseInt(value);
                    return !isNaN(num) && num >= 16 && num <= 100;
                },
                message: 'Retirement age must be between 16 and 100'
            },
            days_holiday_per_year: {
                custom: (value) => {
                    if (!value) return true;
                    const num = parseFloat(value);
                    return !isNaN(num) && num >= 0 && num <= 365;
                },
                message: 'Holiday days must be between 0 and 365'
            },
            max_days_carry_over: {
                custom: (value) => {
                    if (!value) return true;
                    const num = parseFloat(value);
                    return !isNaN(num) && num >= 0 && num <= 365;
                },
                message: 'Carry over days must be between 0 and 365'
            },

            // ========================================
            // TAB 7: PAYMENT & BANKING
            // ========================================
            sort_code: {
                pattern: /^[0-9]{2}-[0-9]{2}-[0-9]{2}$/,
                message: 'Sort code format: XX-XX-XX'
            },
            account_number: {
                pattern: /^[0-9]{8}$/,
                message: 'Account number must be exactly 8 digits'
            },
            annual_pay: {
                custom: (value) => {
                    if (!value) return true;
                    const num = parseFloat(value);
                    return !isNaN(num) && num >= 0 && num <= 9999999.99;
                },
                message: 'Annual pay must be between 0 and 9,999,999.99'
            },
            pay_per_period: {
                custom: (value) => {
                    if (!value) return true;
                    const num = parseFloat(value);
                    return !isNaN(num) && num >= 0 && num <= 9999999.99;
                },
                message: 'Pay per period must be between 0 and 9,999,999.99'
            }
        };
    }

    /**
     * Get tab info for a field
     */
    getFieldTab(field) {
        const tabPane = field.closest('.tab-content-pane');
        if (tabPane) {
            const tabId = tabPane.id;
            const allPanes = document.querySelectorAll('.tab-content-pane');
            const index = Array.from(allPanes).indexOf(tabPane);
            return { id: tabId, element: tabPane, index };
        }
        return { id: 'unknown', element: null, index: 0 };
    }

    /**
     * Bind real-time validation
     */
    bindRealTimeValidation() {
        const fields = this.form.querySelectorAll('input, select, textarea');
        
        fields.forEach(field => {
            // Validate on blur
            field.addEventListener('blur', (e) => {
                this.validateField(e.target);
                this.updateTabIndicators();
            });

            // Real-time validation for text inputs
            if (['text', 'email', 'tel', 'number', 'date'].includes(field.type)) {
                field.addEventListener('input', (e) => {
                    if (field.classList.contains('is-invalid') || field.classList.contains('is-valid')) {
                        this.validateField(e.target);
                        this.updateTabIndicators();
                    }
                });
            }

            // Immediate validation for selects/radios/checkboxes
            if (['select-one', 'radio', 'checkbox'].includes(field.type)) {
                field.addEventListener('change', (e) => {
                    this.validateField(e.target);
                    this.updateTabIndicators();
                });
            }
        });
    }

    /**
     * Validate individual field
     */
    validateField(field) {
        const fieldName = field.name;
        const fieldValue = field.value.trim();
        const rules = this.validationRules[fieldName];

        // Clear previous validation
        field.classList.remove('is-valid', 'is-invalid');
        this.clearFieldError(field);

        // No rules = valid
        if (!rules) return true;

        // Check required
        if (rules.required && !fieldValue) {
            this.markFieldInvalid(field, rules.message || `${this.getFieldLabel(field)} is required`);
            return false;
        }

        // Empty and not required = valid
        if (!fieldValue && !rules.required) {
            this.markFieldValid(field);
            return true;
        }

        // Min length
        if (rules.minLength && fieldValue.length < rules.minLength) {
            this.markFieldInvalid(field, `Must be at least ${rules.minLength} characters`);
            return false;
        }

        // Max length
        if (rules.maxLength && fieldValue.length > rules.maxLength) {
            this.markFieldInvalid(field, `Must not exceed ${rules.maxLength} characters`);
            return false;
        }

        // Pattern check
        if (rules.pattern && !rules.pattern.test(fieldValue)) {
            this.markFieldInvalid(field, rules.message || 'Invalid format');
            return false;
        }

        // Min value
        if (rules.min !== undefined) {
            const numValue = parseFloat(fieldValue);
            if (isNaN(numValue) || numValue < rules.min) {
                this.markFieldInvalid(field, `Must be at least ${rules.min}`);
                return false;
            }
        }

        // Max value
        if (rules.max !== undefined) {
            const numValue = parseFloat(fieldValue);
            if (isNaN(numValue) || numValue > rules.max) {
                this.markFieldInvalid(field, `Must not exceed ${rules.max}`);
                return false;
            }
        }

        // Custom validation
        if (rules.custom) {
            const formData = new FormData(this.form);
            if (!rules.custom(fieldValue, formData)) {
                this.markFieldInvalid(field, rules.message || 'Invalid value');
                return false;
            }
        }

        this.markFieldValid(field);
        return true;
    }

    markFieldInvalid(field, message) {
        field.classList.add('is-invalid');
        field.classList.remove('is-valid');
        
        const feedbackElement = this.getOrCreateFeedbackElement(field);
        feedbackElement.textContent = message;
        feedbackElement.classList.add('invalid-feedback');
        feedbackElement.classList.remove('valid-feedback');
        feedbackElement.style.display = 'block';
    }

    markFieldValid(field) {
        field.classList.add('is-valid');
        field.classList.remove('is-invalid');
        this.clearFieldError(field);
    }

    clearFieldError(field) {
        const feedbackElement = field.parentElement.querySelector('.invalid-feedback');
        if (feedbackElement) {
            feedbackElement.textContent = '';
            feedbackElement.style.display = 'none';
        }
    }

    getOrCreateFeedbackElement(field) {
        let feedbackElement = field.parentElement.querySelector('.invalid-feedback');
        
        if (!feedbackElement) {
            feedbackElement = document.createElement('div');
            feedbackElement.className = 'invalid-feedback';
            field.parentElement.appendChild(feedbackElement);
        }
        
        return feedbackElement;
    }

    getFieldLabel(field) {
        const label = field.closest('.form-group')?.querySelector('label');
        return label ? label.textContent.replace('*', '').replace(/\(Optional\)/g, '').trim() : field.name;
    }

    /**
     * Update tab indicators with error badges
     */
    updateTabIndicators() {
        const tabItems = document.querySelectorAll('.tab-item');
        const tabPanes = document.querySelectorAll('.tab-content-pane');
        
        // Reset all tabs
        tabItems.forEach(tab => {
            tab.classList.remove('has-error');
            const errorBadge = tab.querySelector('.error-count');
            if (errorBadge) errorBadge.remove();
        });

        // Count errors per tab
        tabPanes.forEach((pane, index) => {
            const invalidFields = pane.querySelectorAll('.is-invalid');
            const errorCount = invalidFields.length;
            
            if (errorCount > 0 && tabItems[index]) {
                tabItems[index].classList.add('has-error');
                
                const badge = document.createElement('span');
                badge.className = 'error-count';
                badge.textContent = errorCount;
                tabItems[index].appendChild(badge);
            }
        });
    }

    bindFormSubmit() {
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.validateAndSubmitForm();
        });
    }

    /**
     * Validate entire form and submit
     */
    async validateAndSubmitForm() {
        const fields = this.form.querySelectorAll('input, select, textarea');
        let isValid = true;
        let firstInvalidField = null;
        let firstInvalidTabInfo = null;

        // Validate ALL fields
        fields.forEach(field => {
            const fieldValid = this.validateField(field);
            
            if (!fieldValid && !firstInvalidField) {
                firstInvalidField = field;
                firstInvalidTabInfo = this.getFieldTab(field);
            }
            
            if (!fieldValid) isValid = false;
        });

        // Update badges
        this.updateTabIndicators();

        if (!isValid) {
            // Navigate to first error
            if (firstInvalidTabInfo && window.employeeFormManagerInstance) {
                window.employeeFormManagerInstance.showTab(firstInvalidTabInfo.index);
            }
            
            // Scroll to first error
            setTimeout(() => {
                if (firstInvalidField) {
                    firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalidField.focus();
                }
            }, 300);
            
            this.showToast('Please fix all validation errors before submitting', 'error');
            return;
        }

        // Submit via AJAX
        await this.submitFormAjax();
    }

    /**
     * Submit form via AJAX
     */
    async submitFormAjax() {
        const submitButton = this.form.querySelector('button[type="submit"]');
        const formData = new FormData(this.form);
        
        submitButton.classList.add('btn-loading');
        submitButton.disabled = true;

        try {
            const response = await fetch(this.form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned non-JSON response');
            }

            const data = await response.json();

            if (response.ok) {
                this.showToast('Employee created successfully!', 'success');
                
                setTimeout(() => {
                    window.location.href = data.redirect || '/admin/employees';
                }, 1500);
            } else {
                if (data.errors) {
                    this.displayServerErrors(data.errors);
                } else {
                    this.showToast(data.message || 'An error occurred', 'error');
                }
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.showToast('An error occurred: ' + error.message, 'error');
        } finally {
            submitButton.classList.remove('btn-loading');
            submitButton.disabled = false;
        }
    }

    /**
     * Display server validation errors
     */
    displayServerErrors(errors) {
        let firstErrorField = null;
        let firstErrorTabInfo = null;
        
        Object.keys(errors).forEach(fieldName => {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            const messages = errors[fieldName];
            
            if (field) {
                this.markFieldInvalid(field, Array.isArray(messages) ? messages[0] : messages);
                
                if (!firstErrorField) {
                    firstErrorField = field;
                    firstErrorTabInfo = this.getFieldTab(field);
                }
            }
        });

        this.updateTabIndicators();
        
        // Navigate to first error
        if (firstErrorTabInfo && window.employeeFormManagerInstance) {
            window.employeeFormManagerInstance.showTab(firstErrorTabInfo.index);
            
            setTimeout(() => {
                if (firstErrorField) {
                    firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstErrorField.focus();
                }
            }, 300);
        }
        
        this.showToast('Please fix the validation errors', 'error');
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        const existingToasts = document.querySelectorAll('.custom-toast');
        existingToasts.forEach(toast => toast.remove());

        const toast = document.createElement('div');
        toast.className = `custom-toast custom-toast-${type}`;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 300px;
            padding: 15px 20px;
            background: ${type === 'success' ? '#28a745' : '#dc3545'};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 10px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        `;
        
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const employeeForm = document.querySelector('form[action*="employees"]');
    if (employeeForm) {
        window.employeeFormValidatorInstance = new EmployeeFormValidator(employeeForm);
    }
});