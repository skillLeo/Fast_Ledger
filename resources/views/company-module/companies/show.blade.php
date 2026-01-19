@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h4 class="page-title">{{ __('company.company_details') }}</h4>
                            <div class="ms-auto pageheader-btn">
                                <a href="{{ route('company.index') }}" class="btn btn-light me-2">
                                    <i class="ri-arrow-left-line me-1"></i> {{ __('company.back_to_companies') }}
                                </a>
                                @if ($company->pivot->Role === 'owner' || $company->pivot->Role === 'admin')
                                    <a href="{{ route('company.edit', $company->id) }}" class="btn btn-primary">
                                        <i class="ri-edit-line me-1"></i> {{ __('company.edit_company') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        {{-- Company Overview Card --}}
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-body">
                                    <div class="row">
                                        {{-- Company Logo --}}
                                        <div class="col-auto">
                                            @if ($company->Logo_Path)
                                                <img src="{{ asset('storage/' . $company->Logo_Path) }}" alt="{{ __('company.company_logo') }}"
                                                    class="rounded"
                                                    style="width: 120px; height: 120px; object-fit: contain;">
                                            @else
                                                <div class="avatar avatar-xxl bg-primary-transparent">
                                                    <i class="ri-building-line fs-1"></i>
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Company Info --}}
                                        <div class="col">
                                            <h4 class="fw-semibold mb-2">{{ $company->Company_Name }}</h4>
                                            @if ($company->Trade_Name)
                                                <p class="text-muted mb-2">{{ __('company.trade_name') }}: {{ $company->Trade_Name }}</p>
                                            @endif

                                            <div class="d-flex gap-3 flex-wrap mb-3">
                                                <span class="badge bg-primary-transparent">
                                                    <i class="ri-global-line me-1"></i> {{ $company->Country }}
                                                </span>
                                                <span class="badge bg-success-transparent">
                                                    <i class="ri-shield-user-line me-1"></i>
                                                    {{ __('company.your_role') }} <strong>{{ ucfirst($company->pivot->Role) }}</strong>
                                                </span>
                                                @if ($company->Is_Active)
                                                    <span class="badge bg-success">
                                                        <i class="ri-check-line me-1"></i> {{ __('company.active') }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="ri-close-line me-1"></i> {{ __('company.inactive') }}
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Profile Completion --}}
                                            <div class="mb-2">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <small>{{ __('company.profile_completion') }}</small>
                                                    <small>{{ $company->Profile_Completion_Percentage }}%</small>
                                                </div>
                                                <div class="progress progress-sm">
                                                    <div class="progress-bar bg-{{ $company->Profile_Completion_Percentage >= 80 ? 'success' : ($company->Profile_Completion_Percentage >= 50 ? 'warning' : 'danger') }}"
                                                        style="width: {{ $company->Profile_Completion_Percentage }}%">
                                                    </div>
                                                </div>
                                            </div>

                                            @if ($company->Profile_Completion_Percentage < 100)
                                                <div class="alert alert-warning py-2 mb-0">
                                                    <i class="ri-information-line me-1"></i>
                                                    <small>{{ __('company.complete_profile_message') }}</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Basic Information --}}
                        <div class="col-xl-4 col-lg-6">
                            <div class="card custom-card h-100">
                                <div class="card-header">
                                    <div class="card-title">{{ __('company.basic_information') }}</div>
                                </div>
                                <div class="card-body p-3">
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="fw-semibold" style="width: 40%;">{{ __('company.tax_id_label') }}</td>
                                                <td>{{ $company->Tax_ID }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold">{{ __('company.country_label') }}</td>
                                                <td>{{ $company->Country }}</td>
                                            </tr>
                                            @if ($company->Country === 'GB' && $company->Company_Type_UK)
                                                <tr>
                                                    <td class="fw-semibold">{{ __('company.company_type') }}:</td>
                                                    <td>{{ $company->Company_Type_UK }}</td>
                                                </tr>
                                            @endif
                                            @if ($company->Country === 'ES' && $company->Company_Type_ES)
                                                <tr>
                                                    <td class="fw-semibold">{{ __('company.tipo_empresa') }}:</td>
                                                    <td>{{ $company->Company_Type_ES }}</td>
                                                </tr>
                                            @endif
                                            @if ($company->Tax_Regime)
                                                <tr>
                                                    <td class="fw-semibold">{{ __('company.tax_regime') }}:</td>
                                                    <td>{{ $company->Tax_Regime }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td class="fw-semibold">{{ __('company.tax_residence_label') }}</td>
                                                <td>{{ $company->Country_Tax_Residence }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold">{{ __('company.currency_label') }}</td>
                                                <td>{{ $company->Currency }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Contact Information --}}
                        <div class="col-xl-4 col-lg-6">
                            <div class="card custom-card h-100">
                                <div class="card-header">
                                    <div class="card-title">{{ __('company.contact_information') }}</div>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="fw-semibold" style="width: 40%;">{{ __('company.phone_label') }}</td>
                                                <td>{{ $company->Phone_Number ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold">{{ __('company.email_label') }}</td>
                                                <td>{{ $company->Email ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold">{{ __('company.website_label') }}</td>
                                                <td>
                                                    @if ($company->Website)
                                                        <a href="{{ $company->Website }}" target="_blank">
                                                            {{ $company->Website }}
                                                        </a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold">{{ __('company.address_label') }}</td>
                                                <td>
                                                    {{ $company->Street_Address }}<br>
                                                    {{ $company->City }}, {{ $company->State }}
                                                    {{ $company->Postal_Code }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Invoice Settings --}}
                        <div class="col-xl-4 col-lg-6">
                            <div class="card custom-card h-100">
                                <div class="card-header">
                                    <div class="card-title">{{ __('company.invoice_settings') }}</div>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="fw-semibold" style="width: 40%;">{{ __('company.invoice_prefix_label') }}</td>
                                                <td>{{ $company->Invoice_Prefix ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold">{{ __('company.next_invoice_number_label') }}</td>
                                                <td>{{ $company->Next_Invoice_Number }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold">{{ __('company.sample_invoice_number_label') }}</td>
                                                <td>
                                                    <code>{{ $company->Invoice_Prefix ?? 'INV' }}-{{ str_pad($company->Next_Invoice_Number, 5, '0', STR_PAD_LEFT) }}</code>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Company Statistics --}}
                        <div class="col-xl-4 col-lg-6">
                            <div class="card custom-card h-100">
                                <div class="card-header">
                                    <div class="card-title">{{ __('company.company_statistics') }}</div>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="fw-semibold" style="width: 40%;">{{ __('company.created_on_label') }}</td>
                                                <td>{{ $company->Created_On->format('d M Y, h:i A') }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold">{{ __('company.last_modified_label') }}</td>
                                                <td>{{ $company->Modified_On ? $company->Modified_On->format('d M Y, h:i A') : '—' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-semibold">{{ __('company.created_by_label') }}</td>
                                                <td>{{ $company->creator->username ?? '—' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Company Users (if admin/owner) --}}
                        @if (in_array($company->pivot->Role, ['owner', 'admin']))
                            <div class="col-xl-12">
                                <div class="card custom-card">
                                    <div class="card-header justify-content-between">
                                        <div class="card-title">{{ __('company.company_users') }}</div>
                                        <a href="{{ route('company.users.index', $company->id) }}"
                                            class="btn btn-sm btn-primary">
                                            <i class="ri-team-line me-1"></i> {{ __('company.manage_users') }}
                                        </a>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table text-nowrap mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('company.user') }}</th>
                                                        <th>{{ __('company.role') }}</th>
                                                        <th>{{ __('company.status') }}</th>
                                                        <th>{{ __('company.joined') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($company->users()->limit(5)->get() as $user)
                                                        <tr>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    <span class="avatar avatar-sm bg-primary-transparent me-2">
                                                                        {{ strtoupper(substr($user->username, 0, 1)) }}
                                                                    </span>
                                                                    <div>
                                                                        <span class="fw-semibold">{{ $user->username }}</span>
                                                                        <br><small class="text-muted">{{ $user->email }}</small>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $user->pivot->Role === 'owner' ? 'primary' : ($user->pivot->Role === 'admin' ? 'success' : 'light') }}">
                                                                    {{ __('company.' . $user->pivot->Role) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if ($user->pivot->Is_Active)
                                                                    <span class="badge bg-success-transparent">{{ __('company.active') }}</span>
                                                                @else
                                                                    <span class="badge bg-danger-transparent">{{ __('company.inactive') }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ \Carbon\Carbon::parse($user->pivot->Created_At)->format('d M Y') }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center text-muted py-3">{{ __('company.no_users_found') }}</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Recent Activity --}}
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">{{ __('company.recent_activity') }}</div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table text-nowrap mb-0">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('company.action') }}</th>
                                                    <th>{{ __('company.description') }}</th>
                                                    <th>{{ __('company.user') }}</th>
                                                    <th>{{ __('company.date') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($company->activityLogs()->limit(10)->orderBy('Created_At', 'desc')->get() as $log)
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-primary-transparent">
                                                                {{ str_replace('_', ' ', ucfirst($log->Action_Type)) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $log->Description }}</td>
                                                        <td>{{ $log->user->username ?? __('company.system') }}</td>
                                                        <td>{{ $log->Created_At->format('d M Y, h:i A') }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center text-muted py-3">{{ __('company.no_activity_yet') }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection