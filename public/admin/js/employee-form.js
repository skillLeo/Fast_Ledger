/**
 * Employee Form Manager Class
 * Handles tab navigation, form validation, and field formatting
 */
class EmployeeFormManager {
    constructor() {
        this.tabItems = document.querySelectorAll('.tab-item');
        this.tabPanes = document.querySelectorAll('.tab-content-pane');
        this.nextBtn = document.getElementById('nextTabBtn');
        this.prevBtn = document.getElementById('prevTabBtn');
        this.saveBtn = document.getElementById('saveEmployeeBtn');
        this.currentTabIndex = 0;
        this.totalTabs = this.tabItems.length;
        
        this.init();
    }

    /**
     * Initialize the form manager
     */
    init() {
        this.bindEvents();
        this.initFieldFormatters();
        this.showTab(0);
    }

    /**
     * Bind all event listeners
     */
    bindEvents() {
        // Tab click handlers
        this.tabItems.forEach((tab, index) => {
            tab.addEventListener('click', () => this.showTab(index));
        });

        // Navigation buttons
        this.nextBtn?.addEventListener('click', () => this.nextTab());
        this.prevBtn?.addEventListener('click', () => this.prevTab());
    }

    /**
     * Show specific tab by index
     * @param {number} index - Tab index to show
     */
    showTab(index) {
        // Validate index
        if (index < 0 || index >= this.totalTabs) {
            console.warn(`Invalid tab index: ${index}`);
            return;
        }

        // Remove active class from all tabs
        this.tabItems.forEach(t => t.classList.remove('active'));
        this.tabPanes.forEach(pane => {
            pane.classList.remove('active');
            pane.style.display = 'none';
        });

        // Show selected tab
        this.tabItems[index].classList.add('active');
        this.tabPanes[index].classList.add('active');
        this.tabPanes[index].style.display = 'block';

        this.currentTabIndex = index;
        this.updateButtons();

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    /**
     * Navigate to next tab
     */
    nextTab() {
        if (this.currentTabIndex < this.totalTabs - 1) {
            this.showTab(this.currentTabIndex + 1);
        }
    }

    /**
     * Navigate to previous tab
     */
    prevTab() {
        if (this.currentTabIndex > 0) {
            this.showTab(this.currentTabIndex - 1);
        }
    }

    /**
     * Update navigation button visibility
     */
    updateButtons() {
        const isLastTab = this.currentTabIndex === this.totalTabs - 1;
        const isFirstTab = this.currentTabIndex === 0;

        if (this.prevBtn) {
            this.prevBtn.style.display = isFirstTab ? 'none' : 'inline-block';
        }
        
        if (this.nextBtn && this.saveBtn) {
            if (isLastTab) {
                this.nextBtn.style.display = 'none';
                this.saveBtn.style.display = 'inline-block';
            } else {
                this.nextBtn.style.display = 'inline-block';
                this.saveBtn.style.display = 'none';
            }
        }
    }

    /**
     * Initialize all field formatters
     */
    initFieldFormatters() {
        this.initProbationCalculator();
        this.initSortCodeFormatter();
        this.initNINumberFormatter();
        this.initAccountNumberFormatter();
        this.initAgeAndNICategoryCalculator();
    }

    /**
     * Calculate age and automatically set NI Category
     */
    initAgeAndNICategoryCalculator() {
        const dobField = document.getElementById('date_of_birth');
        const niCategoryField = document.getElementById('ni_category_letter');
        const ageDisplayField = document.getElementById('calculated_age');

        const calculateAgeAndNI = () => {
            const dob = dobField?.value;
            
            if (!dob) {
                if (niCategoryField) niCategoryField.value = 'A';
                if (ageDisplayField) ageDisplayField.textContent = 'N/A';
                return;
            }

            const birthDate = new Date(dob);
            const today = new Date();
            
            // Calculate exact age
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            const dayDiff = today.getDate() - birthDate.getDate();
            
            // Adjust age if birthday hasn't occurred this year
            if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
                age--;
            }

            // Display calculated age
            if (ageDisplayField) {
                ageDisplayField.textContent = age + ' years old';
            }

            // Determine NI Category based on age
            let niCategory = 'A'; // Default
            
            if (age < 16) {
                niCategory = 'X';
            } else if (age >= 16 && age <= 20) {
                niCategory = 'M';
            } else if (age >= 21 && age < 66) { // State Pension age is typically 66
                niCategory = 'A';
            } else if (age >= 66) {
                niCategory = 'C';
            }

            // Set NI category
            if (niCategoryField) {
                niCategoryField.value = niCategory;
                
                // Trigger change event for validation
                niCategoryField.dispatchEvent(new Event('change', { bubbles: true }));
            }

            // Visual feedback - highlight the category
            this.highlightNICategory(niCategory, age);
        };

        // Bind to date of birth change
        dobField?.addEventListener('change', calculateAgeAndNI);
        dobField?.addEventListener('blur', calculateAgeAndNI);

        // Calculate on page load if DOB exists
        if (dobField?.value) {
            calculateAgeAndNI();
        }
    }

    /**
     * Highlight the active NI category in the info box
     */
    highlightNICategory(category, age) {
        // Remove previous highlights
        const allCategories = document.querySelectorAll('.ni-category-item');
        allCategories.forEach(item => {
            item.style.background = 'transparent';
            item.style.fontWeight = 'normal';
            item.style.padding = '0';
            item.style.borderRadius = '0';
        });

        // Highlight current category
        let targetCategory = null;
        if (category === 'X') {
            targetCategory = document.querySelector('.ni-cat-x');
        } else if (category === 'M') {
            targetCategory = document.querySelector('.ni-cat-m');
        } else if (category === 'A') {
            targetCategory = document.querySelector('.ni-cat-a');
        } else if (category === 'C') {
            targetCategory = document.querySelector('.ni-cat-c');
        }

        if (targetCategory) {
            targetCategory.style.background = '#d4edda';
            targetCategory.style.fontWeight = '600';
            targetCategory.style.padding = '8px';
            targetCategory.style.borderRadius = '6px';
            targetCategory.style.border = '2px solid #28a745';
            targetCategory.style.transition = 'all 0.3s ease';
        }
    }

    /**
     * Calculate probation end date automatically
     */
    initProbationCalculator() {
        const startDateField = document.getElementById('employment_start_date');
        const probationPeriodField = document.getElementById('probation_period');
        const probationEndDateField = document.getElementById('probation_end_date');

        const calculate = () => {
            const startDate = startDateField?.value;
            const probationMonths = parseInt(probationPeriodField?.value);

            if (startDate && probationMonths) {
                const date = new Date(startDate);
                date.setMonth(date.getMonth() + probationMonths);
                if (probationEndDateField) {
                    probationEndDateField.value = date.toISOString().split('T')[0];
                }
            }
        };

        startDateField?.addEventListener('change', calculate);
        probationPeriodField?.addEventListener('change', calculate);
    }

    /**
     * Format sort code input (XX-XX-XX)
     */
    initSortCodeFormatter() {
        const sortCodeField = document.getElementById('sort_code');
        
        sortCodeField?.addEventListener('input', (e) => {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length > 6) value = value.substring(0, 6);

            if (value.length >= 2) {
                value = value.substring(0, 2) + '-' + value.substring(2);
            }
            if (value.length >= 6) {
                value = value.substring(0, 5) + '-' + value.substring(5);
            }

            e.target.value = value;
        });
    }

    /**
     * Format National Insurance number
     */
    initNINumberFormatter() {
        const niNumberFields = [
            document.getElementById('ni_number'),
            document.getElementById('ni_number_work')
        ];
        
        niNumberFields.forEach(field => {
            field?.addEventListener('input', (e) => {
                let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                if (value.length > 9) value = value.substring(0, 9);
                e.target.value = value;
            });
        });
    }

    /**
     * Format account number (8 digits only)
     */
    initAccountNumberFormatter() {
        const accountNumberField = document.getElementById('account_number');
        
        accountNumberField?.addEventListener('input', (e) => {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length > 8) value = value.substring(0, 8);
            e.target.value = value;
        });
    }
}

// Initialize when DOM is ready and expose instance globally
document.addEventListener('DOMContentLoaded', () => {
    window.employeeFormManagerInstance = new EmployeeFormManager();
});

// Export for use in validation script
window.EmployeeFormManager = EmployeeFormManager;