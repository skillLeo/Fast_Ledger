@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.partial.errors')





            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        {{-- Page Header --}}
                        <div
                            class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-4">
                            <h1 class="page-title fw-medium fs-18 mb-0">Verifactu AEAT Connection</h1>
                           
                                <button type="button" class="teal-custom-btn p-1" data-bs-toggle="modal"
                                    data-bs-target="#addConnectionModal">
                                    <i class="fa fa-plus"></i>Add New Connection
                                </button>
                       
                        </div>

                        {{-- Success/Error Messages --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fa fa-exclamation-triangle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="card-body">
                            @if ($connections->isEmpty())
                                {{-- Empty State --}}
                                <div class="text-center py-5">
                                    <div class="mb-4">
                                        <i class="fa fa-plug fa-4x text-muted opacity-50"></i>
                                    </div>
                                    <h4 class="fw-semibold mb-2">No AEAT Connections Found</h4>
                                    <p class="text-muted mb-4">
                                        Connect your business to Spain's Tax Agency (AEAT) to start sending invoices
                                        through Verifactu system.
                                    </p>
                                    <button type="button" class="teal-custom-btn p-1" data-bs-toggle="modal"
                                        data-bs-target="#addConnectionModal">
                                        <i class="fa fa-plus me-2"></i>Create Your First Connection
                                    </button>
                                </div>
                            @else
                                {{-- Connections Grid --}}
                                <div class="row">
                                    @foreach ($connections as $connection)
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                            <div class="card custom-card border shadow-sm">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-start justify-content-between mb-3">
                                                        <div class="flex-grow-1">
                                                            <h5 class="fw-semibold mb-1">{{ $connection->name }}</h5>
                                                            <p class="text-muted mb-0 fs-12">{{ $connection->company_name }}
                                                            </p>
                                                        </div>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-light" type="button"
                                                                data-bs-toggle="dropdown">
                                                                <i class="fa fa-ellipsis-v"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a class="dropdown-item" href="#"
                                                                        onclick="event.preventDefault(); if(confirm('Are you sure?')) document.getElementById('delete-form-{{ $connection->id }}').submit();">
                                                                        <i class="fa fa-trash me-2"></i>Delete
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                            <form id="delete-form-{{ $connection->id }}"
                                                                action="{{ route('company.verifactu.connections.destroy', $connection) }}"
                                                                method="POST" class="d-none">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-muted fs-13">NIF:</span>
                                                            <span class="fw-medium">{{ $connection->nif }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-muted fs-13">Environment:</span>
                                                            <span>
                                                                @if ($connection->environment === 'production')
                                                                    <span class="badge bg-danger-transparent">
                                                                        <i class="fa fa-circle fs-8 me-1"></i>PRODUCTION
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-info-transparent">
                                                                        <i class="fa fa-circle fs-8 me-1"></i>SANDBOX
                                                                    </span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-muted fs-13">SIF ID:</span>
                                                            <code class="fs-11">{{ $connection->sif_id }}</code>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted fs-13">Status:</span>
                                                            <span>
                                                                @if ($connection->status === 'connected')
                                                                    <span class="badge bg-success-transparent">
                                                                        <i class="fa fa-check-circle me-1"></i>Connected
                                                                    </span>
                                                                @elseif($connection->status === 'error')
                                                                    <span class="badge bg-danger-transparent"
                                                                        data-bs-toggle="tooltip"
                                                                        title="{{ $connection->last_error }}">
                                                                        <i class="fa fa-times-circle me-1"></i>Error
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-secondary-transparent">
                                                                        <i class="fa fa-circle me-1"></i>Disconnected
                                                                    </span>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>

                                                    @if ($connection->last_error)
                                                        <div class="alert alert-danger p-2 mb-3" role="alert">
                                                            <small>
                                                                <i class="fa fa-exclamation-circle me-1"></i>
                                                                {{ Str::limit($connection->last_error, 80) }}
                                                            </small>
                                                        </div>
                                                    @endif

                                                    <div class="d-flex gap-2">
                                                        <form
                                                            action="{{ route('company.verifactu.connections.test', $connection) }}"
                                                            method="POST" class="flex-grow-1">
                                                            @csrf
                                                            <button type="submit" class="btn btn-primary w-100 btn-sm">
                                                                <i class="fa fa-plug me-1"></i>Connect AEAT
                                                            </button>
                                                        </form>
                                                    </div>

                                                    @if ($connection->last_connected_at)
                                                        <div class="text-center mt-3">
                                                            <small class="text-muted fs-11">
                                                                <i class="fa fa-clock me-1"></i>
                                                                Last connected
                                                                {{ $connection->last_connected_at->diffForHumans() }}
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Information Card --}}
                                <div class="row mt-4">
                                    <div class="col-xl-12">
                                        <div class="card custom-card border-primary">
                                            <div class="card-body">
                                                <div class="d-flex align-items-start">
                                                    <div class="me-3">
                                                        <i class="fa fa-info-circle fa-2x text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-semibold mb-2">About Verifactu AEAT Connection</h6>
                                                        <p class="text-muted mb-2">
                                                            Verifactu is Spain's new electronic invoicing system developed
                                                            by
                                                            AEAT (Agencia Tributaria) to strengthen fiscal oversight and
                                                            combat tax fraud.
                                                        </p>
                                                        <ul class="text-muted mb-0 ps-3">
                                                            <li>All invoices must be traceable and electronically verifiable
                                                            </li>
                                                            <li>Mandatory for companies from <strong>January 1,
                                                                    2027</strong>
                                                            </li>
                                                            <li>Self-employed from <strong>July 1, 2027</strong></li>
                                                            <li>Requires a valid digital certificate (.pfx or .p12)</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Connection Modal --}}
    <div class="modal fade" id="addConnectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('company.verifactu.connections.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h6 class="modal-title">
                            <i class="fa fa-plug me-2"></i>Add AEAT Connection
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Connection Name <span class="text-danger">*</span></label>
                                <input type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                    placeholder="e.g., Production AEAT" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">NIF <span class="text-danger">*</span></label>
                                <input type="text" name="nif"
                                    class="form-control @error('nif') is-invalid @enderror" value="{{ old('nif') }}"
                                    placeholder="B12345678" required>
                                @error('nif')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Format: B12345678 (Letter + 8 digits)</small>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" name="company_name"
                                    class="form-control @error('company_name') is-invalid @enderror"
                                    value="{{ old('company_name') }}" placeholder="Your Company S.L." required>
                                @error('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Environment <span class="text-danger">*</span></label>
                                <select name="environment" class="form-select @error('environment') is-invalid @enderror"
                                    required>
                                    <option value="">Select Environment</option>
                                    <option value="sandbox" {{ old('environment') === 'sandbox' ? 'selected' : '' }}>
                                        Sandbox (Testing)
                                    </option>
                                    <option value="production"
                                        {{ old('environment') === 'production' ? 'selected' : '' }}>
                                        Production (Live)
                                    </option>
                                </select>
                                @error('environment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Digital Certificate <span class="text-danger">*</span></label>
                                <input type="file" name="certificate"
                                    class="form-control @error('certificate') is-invalid @enderror" accept=".pfx,.p12"
                                    required>
                                @error('certificate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Upload .pfx or .p12 file</small>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Certificate Password</label>
                                <input type="password" name="certificate_password"
                                    class="form-control @error('certificate_password') is-invalid @enderror"
                                    placeholder="Leave empty if no password">
                                @error('certificate_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-warning border-warning" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="fa fa-exclamation-triangle me-2 mt-1"></i>
                                <div>
                                    <strong>Important:</strong>
                                    <ul class="mb-0 ps-3">
                                        <li>Ensure your certificate is valid and recognized by AEAT</li>
                                        <li>Test the connection in Sandbox before using Production</li>
                                        <li>Keep your certificate password secure</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="teal-custom-btn p-2">
                            <i class="fa fa-save me-2"></i>Save Connection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });

        // Show modal if there are validation errors
        @if ($errors->any())
            var modal = new bootstrap.Modal(document.getElementById('addConnectionModal'));
            modal.show();
        @endif
    </script>
@endpush
