@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between mb-3">
                            <h4 class="page-title">{{ __('company.tax_invoice') }}</h4>
                            
                            <a href="{{ route('company.invoices.templates.create') }}" class="teal-custom-btn">
                                <i class="fas fa-plus me-2"></i>{{ __('company.create_new_template') }}
                            </a>
                        </div>

                        <div class="card-body">
                            <!-- Templates Grid -->
                            @if ($templates->count() > 0)
                                <div class="row g-4">
                                    @foreach ($templates as $template)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card template-card h-100 shadow-sm">
                                                <!-- Template Preview -->
                                                <div class="card-img-top bg-light p-3" style="min-height: 200px;">
                                                    @if ($template->logo_path)
                                                        <img src="{{ route('uploadfiles.show', ['folder' => 'invoice_logos', 'filename' => basename($template->logo_path)]) }}"
                                                            alt="{{ $template->name }}" class="img-fluid rounded"
                                                            style="max-height: 180px; object-fit: contain;">
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center h-100">
                                                            <i class="fas fa-file-invoice fa-4x text-muted"></i>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h5 class="mb-0">
                                                            {{ $template->name }}
                                                            @if ($template->is_default)
                                                                <i class="fas fa-star text-warning ms-1"
                                                                    title="{{ __('company.default_template') }}"></i>
                                                            @endif
                                                        </h5>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-link text-muted p-0"
                                                                data-bs-toggle="dropdown">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('company.invoices.templates.create', ['template_id' => $template->id]) }}">
                                                                        <i class="fas fa-edit me-2"></i>{{ __('company.edit') }}
                                                                    </a>
                                                                </li>
                                                                @if (!$template->is_default)
                                                                    <li>
                                                                        <hr class="dropdown-divider">
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item text-danger" href="#"
                                                                            onclick="deleteTemplate({{ $template->id }}, '{{ $template->name }}')">
                                                                            <i class="fas fa-trash me-2"></i>{{ __('company.delete') }}
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </div>

                                                    <p class="text-muted small mb-3">{{ $template->description }}</p>

                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>
                                                            {{ $template->updated_at->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="card-footer bg-white border-top-0">
                                                    <a href="{{ route('company.invoices.templates.create', ['template_id' => $template->id]) }}"
                                                        class="btn btn-outline-primary w-100">
                                                        <i class="fas fa-palette me-2"></i>{{ __('company.customize_template') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <!-- Empty State -->
                                <div class="text-center py-5">
                                    <i class="fas fa-palette fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">{{ __('company.no_templates_yet') }}</h5>
                                    <p class="text-muted mb-4">{{ __('company.create_first_template') }}</p>
                                    <a href="{{ route('company.invoices.templates.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>{{ __('company.create_template') }}
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Delete Confirmation Modal -->
                        <div class="modal fade" id="deleteModal" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ __('company.delete_template') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>{{ __('company.are_you_sure_delete_template') }} <strong id="templateName"></strong>?</p>
                                        <p class="text-danger small mb-0">{{ __('company.action_cannot_undone') }}</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">{{ __('company.cancel') }}</button>
                                        <button type="button" class="btn btn-danger" id="confirmDelete">{{ __('company.delete') }}</button>
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

@section('scripts')
    <script>
        let deleteTemplateId = null;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

        function deleteTemplate(id, name) {
            deleteTemplateId = id;
            document.getElementById('templateName').textContent = name;
            deleteModal.show();
        }

        document.getElementById('confirmDelete').addEventListener('click', function() {
            if (!deleteTemplateId) return;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>{{ __('company.deleting') }}...';

            fetch(`{{ url('company/invoices/templates/delete') }}/${deleteTemplateId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        deleteModal.hide();
                        location.reload();
                    } else {
                        alert(data.message || '{{ __('company.failed_delete_template') }}');
                        this.disabled = false;
                        this.innerHTML = '{{ __('company.delete') }}';
                    }
                })
                .catch(error => {
                    alert('{{ __('company.error') }}');
                    this.disabled = false;
                    this.innerHTML = '{{ __('company.delete') }}';
                });
        });
    </script>

    <style>
        .template-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .template-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
        }
    </style>
@endsection