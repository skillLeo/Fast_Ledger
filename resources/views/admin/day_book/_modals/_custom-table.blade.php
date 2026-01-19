{{-- ========================================================================
     CHART OF ACCOUNTS MODAL
     Two-column selection: Ledger Ref → Account Ref with search & balance
     ======================================================================== --}}

<div class="coa-modal" id="coaModal">
    <div class="coa-modal-content">
        
        {{-- Header --}}
        <div class="coa-modal-header">
            <h3 class="coa-modal-title">
                <i class="fas fa-chart-line me-2"></i>Select Chart of Account
            </h3>
            <button class="coa-modal-close" id="coaModalClose" type="button">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Body: Two Columns --}}
        <div class="coa-modal-body">
            
            {{-- LEFT COLUMN: Ledger Ref --}}
            <div class="coa-column">
                <div class="coa-column-header">
                    <span>Ledger Ref</span>
                </div>

                {{-- Search Row with Balance Label --}}
                <div class="coa-search-row">
                    <div class="coa-search-wrapper">
                        <i class="fas fa-search coa-search-icon"></i>
                        <input type="text" 
                               class="coa-search-box" 
                               id="ledgerSearchInput"
                               placeholder="Search ledgers..."
                               autocomplete="off">
                    </div>
                    <div class="coa-balance-label">Balance</div>
                </div>

                {{-- Ledger List (populated by JavaScript) --}}
                <div class="coa-column-body" id="ledgerRefList">
                    <div class="coa-empty">
                        <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                        <p>Loading ledgers...</p>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: Account Ref --}}
            <div class="coa-column">
                <div class="coa-column-header">
                    <span>Account Ref</span>
                </div>

                {{-- Search Row with Balance Label --}}
                <div class="coa-search-row">
                    <div class="coa-search-wrapper">
                        <i class="fas fa-search coa-search-icon"></i>
                        <input type="text" 
                               class="coa-search-box" 
                               id="accountSearchInput"
                               placeholder="Search accounts..."
                               autocomplete="off">
                    </div>
                    <div class="coa-balance-label">Balance</div>
                </div>

                {{-- Account List (populated by JavaScript) --}}
                <div class="coa-column-body" id="accountRefList">
                    <div class="coa-empty">
                        <i class="fas fa-hand-pointer fa-2x mb-3 text-muted"></i>
                        <p class="text-muted mb-0">Select a Ledger Ref to view accounts</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="coa-modal-footer">
            <div class="coa-selected-display">
                <span class="text-muted">Selected: </span>
                <span class="coa-selected-account fw-bold" id="selectedAccountDisplay">None</span>
            </div>
            <button class="btn-select-account" id="selectAccountBtn" type="button" disabled>
                <i class="fas fa-check me-2"></i>Select Account
            </button>
        </div>

    </div>
</div>

{{-- 
    USAGE IN JAVASCRIPT:
    - Call openCoaModal() to show
    - Call closeCoaModal() to hide
    - Listen for selection via selectAccountToForm()
--}}