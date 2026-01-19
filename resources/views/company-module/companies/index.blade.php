{{-- resources/views/company-module/companies/index.blade.php --}}
@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between card-title mb-3" style="margin-top: -5px">
                            <span class="page-title">
                                {{ __('company.all_companies') }}
                            </span>
                            <a href="{{ route('company.create') }}" class="teal-custom-btn">
                                <i class="ri-add-line me-1"></i> {{ __('company.add_new_company') }}
                            </a>
                        </div>
                        
                        <div class="card-body">
                            @if ($companies->isEmpty())
                                <div class="text-center py-5">
                                    <div class="mb-3">
                                        <i class="ri-building-line fs-50 text-muted op-5"></i>
                                    </div>
                                    <h5 class="fw-semibold mb-2">{{ __('company.no_companies_found') }}</h5>
                                    <p class="text-muted mb-4">{{ __('company.create_first_company') }}</p>
                                    <a href="{{ route('company.create') }}" class="btn btn-primary">
                                        <i class="ri-add-line me-1"></i> {{ __('company.create_company') }}
                                    </a>
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table text-nowrap table-hover" id="companiesTable">
                                        <thead>
                                            <tr>
                                                <th>{{ __('company.company_name') }}</th>
                                                <th>{{ __('company.country') }}</th>
                                                <th>{{ __('company.tax_id') }}</th>
                                                <th>{{ __('company.contact') }}</th>
                                                <th>{{ __('company.profile') }}</th>
                                                <th>{{ __('company.status') }}</th>
                                                <th>{{ __('company.created') }}</th>
                                                <th>{{ __('company.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($companies as $company)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if ($company->Logo_Path)
                                                                <span class="avatar avatar-sm me-2">
                                                                    <img src="{{ asset('storage/' . $company->Logo_Path) }}"
                                                                        alt="logo">
                                                                </span>
                                                            @else
                                                                <span class="avatar avatar-sm bg-primary-transparent me-2">
                                                                    <i class="ri-building-line"></i>
                                                                </span>
                                                            @endif
                                                            <div>
                                                                <span class="fw-semibold d-block">{{ $company->Company_Name }}</span>
                                                                @if ($company->Trade_Name)
                                                                    <small class="text-muted">{{ $company->Trade_Name }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            {{ $company->Country }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted">{{ $company->Tax_ID }}</span>
                                                    </td>
                                                    <td>
                                                        @if ($company->Email)
                                                            <small class="d-block">
                                                                <i class="ri-mail-line me-1"></i>{{ $company->Email }}
                                                            </small>
                                                        @endif
                                                        @if ($company->Phone_Number)
                                                            <small class="d-block">
                                                                <i class="ri-phone-line me-1"></i>{{ $company->Phone_Number }}
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <div class="progress progress-xs mb-1">
                                                                <div class="progress-bar bg-{{ $company->Profile_Completion_Percentage >= 80 ? 'success' : ($company->Profile_Completion_Percentage >= 50 ? 'warning' : 'danger') }}"
                                                                    role="progressbar"
                                                                    style="width: {{ $company->Profile_Completion_Percentage }}%">
                                                                </div>
                                                            </div>
                                                            <small class="text-muted">
                                                                {{ $company->Profile_Completion_Percentage }}% {{ __('company.complete') }}
                                                            </small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if ($company->Is_Active)
                                                            <span class="badge bg-success-transparent">
                                                                <i class="ri-check-line me-1"></i>{{ __('company.active') }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-danger-transparent">
                                                                <i class="ri-close-line me-1"></i>{{ __('company.inactive') }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="text-muted">{{ $company->Created_On->format('d M Y') }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('company.show', $company->id) }}"
                                                                class="btn btn-sm btn-primary-light"
                                                                data-bs-toggle="tooltip" title="{{ __('company.view') }}">
                                                                <i class="ri-eye-line"></i>
                                                            </a>
                                                            <a href="{{ route('company.edit', $company->id) }}"
                                                                class="btn btn-sm btn-info-light" 
                                                                data-bs-toggle="tooltip"
                                                                title="{{ __('company.edit') }}">
                                                                <i class="ri-edit-line"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
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
        $(document).ready(function() {
            $('#companiesTable').DataTable({
                "pageLength": 10,
                "ordering": true,
                "searching": true,
                "language": {
                    "search": "{{ __('company.search_companies') }}"
                }
            });

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection