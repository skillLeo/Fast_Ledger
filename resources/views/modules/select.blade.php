@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            
            {{-- Page Header --}}
            <div class="d-md-flex d-block align-items-center justify-content-between mb-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">Module Selection</h4>
                    <p class="text-muted fs-13 mb-0">Welcome! Choose a module to get started</p>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-12">
                    
                    {{-- Active Modules --}}
                    @if($activeModules->isNotEmpty())
                        <div class="card custom-card mb-4">
                            <div class="card-header">
                                <div class="card-title">
                                    Your Active Modules
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach($activeModules as $module)
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                            <div class="card border border-success">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="me-3">
                                                            <span class="avatar avatar-lg bg-success-transparent rounded-circle">
                                                                <i class="{{ $module->Module_Icon ?? 'ri-apps-line' }} fs-20"></i>
                                                            </span>
                                                        </div>
                                                        <div class="flex-fill">
                                                            <h6 class="fw-semibold mb-1">{{ $module->Module_Display_Name }}</h6>
                                                            <span class="badge bg-success-transparent">
                                                                <i class="ri-check-line me-1"></i>Active
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <p class="text-muted fs-12 mb-3">{{ $module->Description }}</p>
                                                    <a href="{{ route($module->Module_Route . '.dashboard') }}" 
                                                       class="btn btn-sm btn-success w-100">
                                                        <i class="ri-external-link-line me-1"></i> Open Module
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Available Modules --}}
                    @if($availableModules->isNotEmpty())
                        <div class="card custom-card">
                            <div class="card-header">
                                <div class="card-title">
                                    Available Modules
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    @foreach($availableModules as $module)
                                        <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <div class="me-3">
                                                            <span class="avatar avatar-lg bg-primary-transparent rounded-circle">
                                                                <i class="{{ $module->Module_Icon ?? 'ri-apps-line' }} fs-20"></i>
                                                            </span>
                                                        </div>
                                                        <div class="flex-fill">
                                                            <h6 class="fw-semibold mb-1">{{ $module->Module_Display_Name }}</h6>
                                                        </div>
                                                    </div>
                                                    <p class="text-muted fs-12 mb-3">{{ $module->Description }}</p>
                                                    <form action="{{ route('modules.activate', $module->Module_Name) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-primary w-100">
                                                            <i class="ri-add-line me-1"></i> Activate Module
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- No Modules Available --}}
                    @if($activeModules->isEmpty() && $availableModules->isEmpty())
                        <div class="card custom-card">
                            <div class="card-body text-center py-5">
                                <div class="mb-3">
                                    <i class="ri-apps-line fs-50 text-muted op-5"></i>
                                </div>
                                <h5 class="fw-semibold mb-2">No Modules Available</h5>
                                <p class="text-muted mb-4">Please contact your administrator to get access to modules.</p>
                                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                                    <i class="ri-arrow-left-line me-1"></i> Go to Dashboard
                                </a>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection