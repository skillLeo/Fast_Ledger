{{-- ========================================================================
     REGULAR OFFICE FORM
     For: Payment, Receipt, Cheque, Inter Bank Office
     ======================================================================== --}}

<div class="regular-office-form" id="regularOfficeForm">
    <div class="row p-2">
        <div class="col-md-4">
            
            <x-form method="POST" action="transactions.store" id="regularOfficeTransactionForm">

                {{-- Hidden Fields --}}
                <input type="hidden" id="hiddenTransactionCode" 
                       name="Transaction_Code" value="{{ $autoCode }}">
                <input type="hidden" id="currentPaymentType" 
                       name="current_payment_type" value="{{ $paymentType }}">
                <input type="hidden" name="account_type" value="office">
                <input type="hidden" name="Paid_In_Out" value="2">

                {{-- Date Field --}}
                <div class="mb-1 row">
                    <label class="col-sm-3 col-form-label form-label-grey">Date</label>
                    <div class="col-sm-6">
                        <input type="date" 
                               name="Transaction_Date"
                               value="{{ old('Transaction_Date', date('Y-m-d')) }}"
                               class="@error('Transaction_Date') is-invalid @enderror"
                               style="width: 290px;">
                        @error('Transaction_Date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Single Bank Account Field --}}
                <div class="mb-1 row" id="singleBankAccountField">
                    <label class="col-sm-3 col-form-label form-label-grey">Bank Account</label>
                    <div class="col-sm-4">
                        <select name="Bank_Account_ID" 
                                id="BankAccountDropdown"
                                style="width: 290px; height: 25px;"
                                class="@error('Bank_Account_ID') is-invalid @enderror">
                            <option value="" disabled selected>Select Office Bank Account</option>
                            @foreach ($bankAccounts as $bankAccount)
                                <option value="{{ $bankAccount->Bank_Account_ID }}"
                                        {{ old('Bank_Account_ID') == $bankAccount->Bank_Account_ID ? 'selected' : '' }}>
                                    {{ $bankAccount->Bank_Name }}
                                    ({{ $bankAccount->bankAccountType->Bank_Type ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                        @error('Bank_Account_ID')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Inter Bank Office: Bank From --}}
                <div class="mb-1 row" id="bankAccountFromField" style="display:none;">
                    <label class="col-sm-3 col-form-label form-label-grey">Bank Acc From</label>
                    <div class="col-sm-4">
                        <select id="BankAccountFromDropdown" style="width: 290px; height: 25px;">
                            <option value="" disabled selected>Select Source Bank Account</option>
                            @foreach ($bankAccounts as $bankAccount)
                                <option value="{{ $bankAccount->Bank_Account_ID }}"
                                        data-bank-type="{{ $bankAccount->Bank_Type_ID }}">
                                    {{ $bankAccount->Bank_Name }}
                                    ({{ $bankAccount->bankAccountType->Bank_Type ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Inter Bank Office: Bank To --}}
                <div class="mb-1 row" id="bankAccountToField" style="display:none;">
                    <label class="col-sm-3 col-form-label form-label-grey">Bank Acc To</label>
                    <div class="col-sm-4">
                        <select id="BankAccountToDropdown" style="width: 290px; height: 25px;">
                            <option value="" disabled selected>Select Destination Bank Account</option>
                        </select>
                    </div>
                </div>

                {{-- Analysis Account (COA) --}}
                <div class="mb-1 row">
                    <label class="col-sm-3 col-form-label form-label-grey">Analysis Acc</label>
                    <div class="col-sm-9">
                        <div class="coa-trigger" id="coaModalTrigger">
                            <span class="coa-placeholder" id="coaPlaceholder">COA + CL</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <input type="hidden" name="chart_of_account_id" 
                               id="chartOfAccountsId" value="{{ old('chart_of_account_id') }}">
                        <input type="hidden" name="account_ref" 
                               id="accountRefHidden" value="{{ old('account_ref') }}">
                        <input type="hidden" name="ledger_ref" 
                               id="ledgerRefHidden" value="{{ old('ledger_ref') }}">
                        <input type="hidden" name="coa_description" 
                               id="coaDescriptionHidden" value="{{ old('coa_description') }}">
                        @error('chart_of_account_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- VAT Type --}}
                <div class="mb-1 row">
                    <label class="col-sm-3 col-form-label form-label-grey">VAT Type</label>
                    <div class="col-sm-4">
                        <select name="VAT_ID" id="VATDropdown"
                                style="width: 290px; height: 25px;"
                                class="@error('VAT_ID') is-invalid @enderror">
                            <option value="">Select VAT Type</option>
                            @foreach ($vatTypes as $vatType)
                                <option value="{{ $vatType->VAT_ID }}"
                                        {{ old('VAT_ID') == $vatType->VAT_ID ? 'selected' : '' }}>
                                    {{ $vatType->VAT_Name }}
                                </option>
                            @endforeach
                        </select>
                        @error('VAT_ID')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Payment Reference --}}
                <div class="mb-1 row">
                    <label class="col-sm-3 col-form-label form-label-grey">Payment Ref</label>
                    <div class="col-sm-4">
                        <input type="text" name="Payment_Ref" 
                               style="width: 290px;"
                               value="{{ old('Payment_Ref') }}"
                               class="@error('Payment_Ref') is-invalid @enderror"
                               placeholder="Payment Reference">
                        @error('Payment_Ref')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Amount Total --}}
                <div class="mb-1 row">
                    <label class="col-sm-3 col-form-label form-label-grey">Amount Total</label>
                    <div class="col-sm-4">
                        <input type="number" name="Amount" 
                               id="totalAmount"
                               value="{{ old('Amount') }}" 
                               style="width: 290px;"
                               class="@error('Amount') is-invalid @enderror"
                               placeholder="Total Amount" 
                               step="0.01" 
                               min="0.01">
                        @error('Amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Description --}}
                <div class="mb-1 row">
                    <label class="col-sm-3 col-form-label form-label-grey">Description</label>
                    <div class="col-sm-4">
                        <textarea name="Description" 
                                  rows="1" 
                                  style="width: 290px;"
                                  class="@error('Description') is-invalid @enderror" 
                                  placeholder="Transaction Description">{{ old('Description') }}</textarea>
                        @error('Description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="row mt-3">
                    <div class="col-sm-7 offset-sm-1 d-flex justify-content-start gap-3">
                        <button type="submit" class="btn teal-custom px-4">Save</button>
                        <a href="{{ url()->previous() }}" class="btn btn-warning px-4">Cancel</a>
                    </div>
                </div>

            </x-form>
        </div>

        {{-- Right Column: Chart of Account Details --}}
        <div class="col-md-4">
            <div id="chartOfAccountDetails" class="border p-3 bg-light" style="display: none;">
                <h6><strong>Selected Account Details</strong></h6>
                <p><strong>Ledger Ref:</strong> <span id="chartLedgerRef"></span></p>
                <p><strong>Account Ref:</strong> <span id="chartAccountRef"></span></p>
                <p><strong>Description:</strong> <span id="chartDescription"></span></p>
                <p><strong>Account Balance:</strong> <span id="accountBalance">0.00</span></p>
            </div>
        </div>
    </div>
</div>