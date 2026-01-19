/**
 * Transaction Code Management Module
 * Handles transaction code generation, validation, and formatting
 */

class TransactionCodeManager {
    constructor() {
        this.SUFFIX_LEN = 6;
        this.CHECK_URL = window.location.origin + "/transactions/check-code-unique";
        this.codeMinSuffix = 1;
        this.codeManual = false;
        
        this.elements = {
            codeSuffix: document.getElementById('codeSuffix'),
            codePrefix: document.getElementById('codePrefix'),
            codeValidationMessage: document.getElementById('codeValidationMessage'),
            invoiceSuffix: document.getElementById('invoiceSuffix'),
            invoicePrefix: document.getElementById('invoicePrefix'),
        };
        
        this.init();
    }

    init() {
        this.loadInitialMinSuffix();
        this.bindEvents();
        this.normalizeSuffix();
        this.syncTransactionCode();
    }

    loadInitialMinSuffix() {
        const fmtMin = document.getElementById('fmtMin');
        if (fmtMin) {
            const minValue = (fmtMin.textContent || '1').replace(/\D/g, '');
            this.codeMinSuffix = parseInt(minValue, 10) || 1;
        }
    }

    bindEvents() {
        if (this.elements.codeSuffix) {
            this.elements.codeSuffix.addEventListener('input', () => {
                this.codeManual = true;
                this.normalizeSuffix();
                this.syncTransactionCode();
                this.clearValidationMessage();
            });

            this.elements.codeSuffix.addEventListener('blur', () => {
                this.codeManual = false;
                this.normalizeSuffix();
                this.syncTransactionCode();
                this.checkCodeUnique();
            });
        }

        // Payment type button listeners
        document.querySelectorAll('.btn-simple').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const type = e.currentTarget.dataset.paymentType;
                if (this.isInvoiceStyle(type)) {
                    this.refreshAutoCodeFor(type).catch(console.error);
                }
            });
        });
    }

    normalizeSuffix() {
        const suffix = this.elements.codeSuffix;
        if (!suffix) return;

        let v = (suffix.value || '').replace(/\D/g, '').slice(0, this.SUFFIX_LEN);

        if (this.codeManual) {
            suffix.value = v;
            return;
        }

        const n = parseInt(v || '0', 10);
        if (!Number.isNaN(n) && n < this.codeMinSuffix) {
            v = String(this.codeMinSuffix);
        }
        suffix.value = String(v || '').padStart(this.SUFFIX_LEN, '0');
    }

    setMinSuffix(n) {
        this.codeMinSuffix = Number(n) || 1;
        const fmtMinEl = document.getElementById('fmtMin');
        if (fmtMinEl) {
            fmtMinEl.textContent = String(this.codeMinSuffix).padStart(this.SUFFIX_LEN, '0');
        }
    }

    isInvoiceStyle(paymentType) {
        return ['sales_invoice', 'sales_credit', 'purchase', 'purchase_credit', 'journal'].includes(paymentType);
    }

    getFullCode() {
        const type = this.getCurrentPaymentType();
        if (this.isInvoiceStyle(type)) {
            const p = document.getElementById('invoicePrefix')?.textContent?.trim() || '';
            const s = document.getElementById('invoiceSuffix')?.value?.trim() || '';
            return p + s;
        } else {
            const p = document.getElementById('codePrefix')?.textContent?.trim() || '';
            const s = document.getElementById('codeSuffix')?.value?.trim() || '';
            return p + s;
        }
    }

    getCurrentPaymentType() {
        const activeButton = document.querySelector('.btn-simple.active');
        if (activeButton) {
            return activeButton.dataset.paymentType;
        }
        return document.getElementById('salesInvoicePaymentType')?.value ||
               document.getElementById('currentPaymentType')?.value ||
               'journal';
    }

    syncTransactionCode() {
        const full = this.getFullCode();
        if (!full) return;

        ['hiddenTransactionCode', 'invoiceTransactionCode', 'invoiceNoHidden', 'entryRefInput']
        .forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = full;
        });
    }

    applyAutoCodeToInvoice(autoCode) {
        const m = /^([A-Z]+)(\d{6})$/.exec(autoCode || '');
        const prefix = m ? m[1] : '';
        const suffix = m ? m[2] : '';

        const invPrefix = document.getElementById('invoicePrefix');
        const invSuffix = document.getElementById('invoiceSuffix');
        const invFmtPrefix = document.getElementById('invFmtPrefix');
        const invFmtMin = document.getElementById('invFmtMin');

        if (invPrefix) invPrefix.textContent = prefix;
        if (invSuffix) invSuffix.value = suffix;
        if (invFmtPrefix) invFmtPrefix.textContent = prefix;
        if (invFmtMin) invFmtMin.textContent = suffix;

        this.setMinSuffix(Number(suffix));
        this.syncTransactionCode();
    }

    async refreshAutoCodeFor(type) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        
        const res = await fetch('/transactions/generate-auto-code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                payment_type: type,
                account_type: 'office'
            })
        });

        const data = await res.json();
        if (!data?.success || !data?.auto_code) {
            throw new Error('Failed to generate');
        }

        this.applyAutoCodeToInvoice(data.auto_code);
    }

    async checkCodeUnique() {
        const full = this.getFullCode();
        if (!full) return;

        const msgEl = this.elements.codeValidationMessage;
        if (!msgEl) return;

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch(this.CHECK_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ transaction_code: full })
            });

            const data = await res.json();
            if (data.success) {
                if (data.exists) {
                    msgEl.className = 'text-danger';
                    msgEl.textContent = 'This code already exists. Please change the number.';
                } else {
                    msgEl.className = 'text-success';
                    msgEl.textContent = 'Code is available.';
                }
            } else {
                msgEl.className = 'text-warning';
                msgEl.textContent = data.message || 'Could not verify code.';
            }
        } catch (e) {
            console.error('Uniqueness check failed:', e);
            msgEl.className = 'text-warning';
            msgEl.textContent = 'Could not verify code.';
        }
    }

    clearValidationMessage() {
        if (this.elements.codeValidationMessage) {
            this.elements.codeValidationMessage.className = '';
            this.elements.codeValidationMessage.textContent = '';
        }
    }

    applyAutoCode(autoCode) {
        try {
            const m = String(autoCode).match(/^([A-Z]+)(\d{6})$/);
            if (!m) {
                console.error('Bad auto code format:', autoCode);
                return;
            }

            const prefix = m[1];
            const suffix = m[2];

            if (this.elements.codePrefix) {
                this.elements.codePrefix.textContent = prefix;
            }

            if (this.elements.codeSuffix) {
                this.elements.codeSuffix.value = suffix;
            }

            this.setMinSuffix(parseInt(suffix, 10));
            this.normalizeSuffix();
            this.syncTransactionCode();
            this.checkCodeUnique();

        } catch (error) {
            console.error('Error in applyAutoCode:', error);
        }
    }
}

// Initialize and expose globally
window.TransactionCodeManager = TransactionCodeManager;
window.codeManager = null;

document.addEventListener('DOMContentLoaded', () => {
    window.codeManager = new TransactionCodeManager();
});