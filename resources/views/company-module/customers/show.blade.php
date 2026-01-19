@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        
                        {{-- Header --}}
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="page-title">{{ __('company.customer_details') }}</h4>
                            
                            <div class="d-flex gap-2">
                                @if (in_array(request()->_company_role, ['owner', 'admin', 'accountant']))
                                    <a href="{{ route('company.customers.edit', [$company, $customer]) }}" 
                                        class="btn btn-warning">
                                        <i class="fa-light fa-pen me-1"></i> {{ __('company.edit') }}
                                    </a>
                                @endif
                                
                                <a href="{{ route('company.customers.index', $company) }}" 
                                    class="teal-custom-btn">
                                    <i class="fa-light fa-arrow-left me-1"></i> {{ __('company.back') }}
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            
                            {{-- Customer Header Card --}}
                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <div class="avatar avatar-xl bg-primary-transparent">
                                                <span class="text-primary fs-4 fw-bold">
                                                    {{ substr($customer->Legal_Name_Company_Name, 0, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <h5 class="mb-1">{{ $customer->Legal_Name_Company_Name }}</h5>
                                            <div class="d-flex flex-wrap gap-2">
                                                <span class="badge {{ $customer->Customer_Type === 'Company' ? 'bg-primary' : 'bg-info' }}">
                                                    {{ $customer->Customer_Type === 'Company' ? __('company.company_type') : __('company.individual') }}
                                                </span>
                                                @if($customer->Contact_Person_Name)
                                                    <span class="badge bg-secondary">
                                                        {{ __('company.contact') }}: {{ $customer->Contact_Person_Name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                
                                {{-- Identity Information --}}
                                <div class="col-md-6">
                                    <div class="card border h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">{{ __('company.identity_information') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <td class="text-muted" width="40%">{{ __('company.customer_type') }}</td>
                                                    <td>
                                                        <span class="badge {{ $customer->Customer_Type === 'Company' ? 'bg-primary-transparent' : 'bg-info-transparent' }}">
                                                            {{ $customer->Customer_Type === 'Company' ? __('company.company_type') : __('company.individual') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('company.legal_name') }}</td>
                                                    <td><strong>{{ $customer->Legal_Name_Company_Name }}</strong></td>
                                                </tr>
                                                @if($customer->Contact_Person_Name)
                                                    <tr>
                                                        <td class="text-muted">{{ __('company.contact_person') }}</td>
                                                        <td>{{ $customer->Contact_Person_Name }}</td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td class="text-muted">{{ __('company.tax_id_type') }}</td>
                                                    <td>{{ $customer->Tax_ID_Type }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('company.tax_id_number') }}</td>
                                                    <td><code>{{ $customer->Tax_ID_Number }}</code></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- Contact Information --}}
                                <div class="col-md-6">
                                    <div class="card border h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">{{ __('company.contact_information') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <td class="text-muted" width="40%">{{ __('company.email') }}</td>
                                                    <td>
                                                        <a href="mailto:{{ $customer->Email }}" class="text-primary">
                                                            {{ $customer->Email }}
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('company.phone') }}</td>
                                                    <td>
                                                        @if($customer->Phone)
                                                            <a href="tel:{{ $customer->Phone }}">{{ $customer->Phone }}</a>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('company.street_address') }}</td>
                                                    <td>{{ $customer->Street_Address }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('company.city') }}</td>
                                                    <td>{{ $customer->City }}, {{ $customer->Postal_Code }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('company.province') }}</td>
                                                    <td>{{ $customer->Province }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">{{ __('company.country') }}</td>
                                                    <td>{{ $customer->Country }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tax Configuration --}}
                                <div class="col-md-6">
                                    <div class="card border h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">{{ __('company.tax_configuration') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <td class="text-muted" width="40%">{{ __('company.has_vat') }}</td>
                                                    <td>
                                                        @if($customer->Has_VAT)
                                                            <span class="badge bg-success">
                                                                <i class="fa fa-check me-1"></i> {{ __('company.yes') }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ __('company.no') }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @if($customer->Has_VAT)
                                                    <tr>
                                                        <td class="text-muted">{{ __('company.vat_rate') }}</td>
                                                        <td>
                                                            <span class="badge bg-success-transparent">
                                                                {{ str_replace('_', ' ', $customer->VAT_Rate) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td class="text-muted">{{ __('company.has_irpf') }}</td>
                                                    <td>
                                                        @if($customer->Has_IRPF)
                                                            <span class="badge bg-warning">
                                                                <i class="fa fa-check me-1"></i> {{ __('company.yes') }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ __('company.no') }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @if($customer->Has_IRPF)
                                                    <tr>
                                                        <td class="text-muted">{{ __('company.irpf_rate') }}</td>
                                                        <td>
                                                            <span class="badge bg-warning-transparent">
                                                                {{ $customer->IRPF_Rate }}%
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- Payment Settings --}}
                                <div class="col-md-6">
                                    <div class="card border h-100">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">{{ __('company.payment_settings') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <td class="text-muted" width="40%">{{ __('company.payment_method') }}</td>
                                                    <td>
                                                        <span class="badge {{ $customer->Payment_Method === 'Bank_Transfer' ? 'bg-primary' : 'bg-secondary' }}">
                                                            {{ $customer->Payment_Method === 'Bank_Transfer' ? __('company.bank_transfer') : __('company.cash') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @if($customer->Payment_Method === 'Bank_Transfer')
                                                    @if($customer->IBAN)
                                                        <tr>
                                                            <td class="text-muted">{{ __('company.iban') }}</td>
                                                            <td><code>{{ $customer->IBAN }}</code></td>
                                                        </tr>
                                                    @endif
                                                    @if($customer->Bank_Name)
                                                        <tr>
                                                            <td class="text-muted">{{ __('company.bank_name') }}</td>
                                                            <td>{{ $customer->Bank_Name }}</td>
                                                        </tr>
                                                    @endif
                                                @endif
                                            </table>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- Delete Button (Owner/Admin Only) --}}
                            @if (in_array(request()->_company_role, ['owner', 'admin']))
                                <div class="mt-4 text-end">
                                    <button type="button" class="btn btn-danger" onclick="deleteCustomer()">
                                        <i class="fa-light fa-trash me-1"></i> {{ __('company.delete_customer') }}
                                    </button>
                                </div>

                                <form id="deleteCustomerForm" method="POST" 
                                    action="{{ route('company.customers.destroy', [$company, $customer]) }}" 
                                    style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const messages = {
            confirmDelete: "{{ __('company.confirm_delete_customer') }}"
        };

        function deleteCustomer() {
            if (confirm(messages.confirmDelete)) {
                document.getElementById('deleteCustomerForm').submit();
            }
        }
    </script>
@endsection