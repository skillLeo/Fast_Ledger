@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.partial.errors')

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa-light fa-circle-check me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">

                        <div class="d-md-flex d-block align-items-center justify-content-between mb-4">
                            <span class="page-title">{{ __('company.customers') }}</span>
                          
                            <div class="ms-md-auto mt-md-0 mt-2">
                                <a href="{{ route('company.customers.create', $company) }}" class="teal-custom-btn">
                                    <i class="fa-light fa-plus me-1"></i> {{ __('company.add_new_customer') }}
                                </a>
                            </div>
                        </div>

                        <div class="card-body">
                            @if ($customers->count() > 0)
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="card-title mb-0">
                                        {{ __('company.all_customers') }} ({{ $customers->total() }})
                                    </div>
                                    <div class="d-flex gap-2">
                                        <input type="text" class="form-control form-control-sm"
                                            placeholder="{{ __('company.search_customers') }}" style="width: 200px;" id="searchCustomer">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table text-nowrap table-hover">
                                        <thead>
                                            <tr>
                                                <th scope="col">{{ __('company.customer_name') }}</th>
                                                <th scope="col">{{ __('company.type') }}</th>
                                                <th scope="col">{{ __('company.tax_id') }}</th>
                                                <th scope="col">{{ __('company.email') }}</th>
                                                <th scope="col">{{ __('company.phone') }}</th>
                                                <th scope="col">{{ __('company.tax_config') }}</th>
                                                <th scope="col">{{ __('company.payment') }}</th>
                                                <th scope="col" class="text-end">{{ __('company.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($customers as $customer)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar avatar-sm me-2 bg-light">
                                                                <span class="text-primary fw-semibold">
                                                                    {{ substr($customer->Legal_Name_Company_Name, 0, 2) }}
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <a href="{{ route('company.customers.show', [$company, $customer]) }}"
                                                                    class="fw-semibold text-dark">
                                                                    {{ $customer->Legal_Name_Company_Name }}
                                                                </a>
                                                                @if ($customer->Contact_Person_Name)
                                                                    <small class="d-block text-muted">
                                                                        {{ __('company.contact') }}: {{ $customer->Contact_Person_Name }}
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $customer->Customer_Type === 'Company' ? 'bg-primary-transparent' : 'bg-info-transparent' }}">
                                                            {{ $customer->Customer_Type === 'Company' ? __('company.company_type') : __('company.individual') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted d-block">{{ $customer->Tax_ID_Type }}</small>
                                                        <span class="fw-medium">{{ $customer->Tax_ID_Number }}</span>
                                                    </td>
                                                    <td>
                                                        <a href="mailto:{{ $customer->Email }}" class="text-primary">
                                                            {{ $customer->Email }}
                                                        </a>
                                                    </td>
                                                    <td>
                                                        @if ($customer->Phone)
                                                            <a href="tel:{{ $customer->Phone }}">{{ $customer->Phone }}</a>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-column gap-1">
                                                            @if ($customer->Has_VAT)
                                                                <span class="badge bg-success-transparent" style="font-size: 10px;">
                                                                    {{ __('company.vat_rate') }}: {{ str_replace('_', ' ', $customer->VAT_Rate) }}
                                                                </span>
                                                            @endif
                                                            @if ($customer->Has_IRPF)
                                                                <span class="badge bg-warning-transparent" style="font-size: 10px;">
                                                                    {{ __('company.irpf_rate') }}: {{ $customer->IRPF_Rate }}%
                                                                </span>
                                                            @endif
                                                            @if (!$customer->Has_VAT && !$customer->Has_IRPF)
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $customer->Payment_Method === 'Bank_Transfer' ? 'bg-primary-transparent' : 'bg-secondary-transparent' }}">
                                                            {{ $customer->Payment_Method === 'Bank_Transfer' ? __('company.bank_transfer') : __('company.cash') }}
                                                        </span>
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="btn-group">
                                                            <a href="{{ route('company.customers.show', [$company, $customer]) }}"
                                                                class="btn btn-sm btn-icon btn-info-light"
                                                                data-bs-toggle="tooltip" title="{{ __('company.view') }}">
                                                                <i class="fa-light fa-eye"></i>
                                                            </a>

                                                            @if (in_array(request()->_company_role, ['owner', 'admin', 'accountant']))
                                                                <a href="{{ route('company.customers.edit', [$company, $customer]) }}"
                                                                    class="btn btn-sm btn-icon btn-warning-light"
                                                                    data-bs-toggle="tooltip" title="{{ __('company.edit') }}">
                                                                    <i class="fa-light fa-pen"></i>
                                                                </a>
                                                            @endif

                                                            @if (in_array(request()->_company_role, ['owner', 'admin']))
                                                                <button type="button"
                                                                    class="btn btn-sm btn-icon btn-danger-light"
                                                                    data-bs-toggle="tooltip" title="{{ __('company.delete') }}"
                                                                    onclick="deleteCustomer({{ $customer->id }})">
                                                                    <i class="fa-light fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="text-muted">
                                        {{ __('company.showing_entries', [
                                            'from' => $customers->firstItem(),
                                            'to' => $customers->lastItem(),
                                            'total' => $customers->total()
                                        ]) }}
                                    </div>
                                    <div>
                                        {{ $customers->links() }}
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="fa-light fa-users fa-4x text-muted"></i>
                                    </div>
                                    <h5 class="mb-2">{{ __('company.no_customers_yet') }}</h5>
                                    <p class="text-muted mb-4">{{ __('company.start_by_adding_first') }}</p>
                                    <a href="{{ route('company.customers.create', $company) }}" class="btn btn-primary">
                                        <i class="fa-light fa-plus me-1"></i> {{ __('company.add_first_customer') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="deleteCustomerForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('scripts')
    <script>
        const messages = {
            confirmDelete: "{{ __('company.confirm_delete_customer') }}"
        };

        function deleteCustomer(customerId) {
            if (confirm(messages.confirmDelete)) {
                const form = document.getElementById('deleteCustomerForm');
                form.action = `{{ route('company.customers.index', $company) }}/${customerId}`;
                form.submit();
            }
        }

        document.getElementById('searchCustomer')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection