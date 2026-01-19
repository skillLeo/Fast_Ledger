{{-- ========================================================================
     CHART OF ACCOUNTS MODAL
     Two-column selection: Ledger Ref → Account Ref
     ======================================================================== --}}

<div class="coa-modal" id="coaModal">
    <div class="coa-modal-content">
        
        {{-- Header --}}
        <div class="coa-modal-header">
            <h3 class="coa-modal-title">
                <i class="fas fa-chart-line me-2"></i>Select Chart of Account
            </h3>
            <button class="coa-modal-close" id="coaModalClose">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Body: Two Columns --}}
        <div class="coa-modal-body">
            
            {{-- Column 1: Ledger Ref --}}
            <div class="coa-column">
                <div class="coa-column-header">
                    <span>Ledger Ref</span>
                </div>

                {{-- Search Row --}}
                <div class="coa-search-row">
                    <div class="coa-search-wrapper">
                        <i class="fas fa-search coa-search-icon"></i>
                        <input type="text" 
                               class="coa-search-box" 
                               id="ledgerSearchInput"
                               placeholder="Search ledgers...">
                    </div>
                    <div class="coa-balance-label">Balance</div>
                </div>

                {{-- Ledger List --}}
                <div class="coa-column-body" id="ledgerRefList">
                    <div class="coa-empty">Loading ledgers...</div>
                </div>
            </div>

            {{-- Column 2: Account Ref --}}
            <div class="coa-column">
                <div class="coa-column-header">
                    <span>Account Ref</span>
                </div>

                {{-- Search Row --}}
                <div class="coa-search-row">
                    <div class="coa-search-wrapper">
                        <i class="fas fa-search coa-search-icon"></i>
                        <input type="text" 
                               class="coa-search-box" 
                               id="accountSearchInput"
                               placeholder="Search accounts...">
                    </div>
                    <div class="coa-balance-label">Balance</div>
                </div>

                {{-- Account List --}}
                <div class="coa-column-body" id="accountRefList">
                    <div class="coa-empty">
                        <i class="fas fa-hand-pointer fa-2x mb-2"></i>
                        <p>Select a Ledger Ref to view accounts</p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="coa-modal-footer">
            <div class="coa-selected-display">
                <span>Selected: </span>
                <span class="coa-selected-account" id="selectedAccountDisplay">None</span>
            </div>
            <button class="btn-select-account" id="selectAccountBtn" disabled>
                Select Account
            </button>
        </div>

    </div>
</div>