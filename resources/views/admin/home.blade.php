@extends('admin.layout.app')
@section('content')
    <!-- Start::app-content -->
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Start::page-header -->
            <div class="d-flex align-items-center justify-content-between my-4 page-header-breadcrumb flex-wrap gap-2">
                <div>
                    @auth
                        <p>Welcome, {{ auth()->user()->Full_Name }}</p>
                    @else
                        {{-- {{ dd(auth()->user()) }} --}}
                        <p>Welcome, Guest!</p>
                    @endauth
                    <p class="fs-13 text-muted mb-0">Let's make today a productive one!</p>
                </div>
                <!--<div class="d-flex align-items-center gap-2 flex-wrap">-->
                <!--    <div class="form-group">-->
                <!--        <div class="input-group">-->
                <!--            <div class="input-group-text bg-primary-transparent text-primary"> <i-->
                <!--                    class="ri-calendar-line"></i> </div>-->
                <!--            <input type="text" class="form-control breadcrumb-input" id="daterange"-->
                <!--                placeholder="Search By Date Range">-->
                <!--        </div>-->
                <!--    </div>-->
                <!--    <div class="btn-list">-->
                <!--        <button class="btn btn-secondary-light btn-wave">-->
                <!--            <i class="ri-upload-cloud-line align-middle me-1 lh-1"></i> Export Report-->
                <!--        </button>-->
                <!--        <button class="btn btn-icon btn-success btn-wave me-0">-->
                <!--            <i class="ri-filter-3-line"></i>-->
                <!--        </button>-->
                <!--    </div>-->
                <!--</div>-->
            </div>
            <!-- End::page-header -->

            <!-- Start:: row-1 -->
            @if (auth()->user()->User_Role == 1)
                {{-- Admin View --}}
                <div class="row">
                    {{-- Active Clients --}}
                    <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                        <div class="card custom-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <span class="d-block mb-2">Active Client</span>
                                        <h5 class="mb-4 fs-4">{{ number_format($activeUserCount) }}</h5>
                                        <span class="text-muted">Since last month</span>
                                    </div>
                                    <div class="main-card-icon primary">
                                        <div
                                            class="avatar avatar-lg bg-primary-transparent border border-primary border-opacity-10">
                                            <div class="avatar avatar-sm svg-white">
                                                {{-- Icon --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                                    fill="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        d="M12 12c2.67 0 8 1.34 8 4v2H4v-2c0-2.66 5.33-4 8-4zm0-2a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Archive Clients --}}
                    <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-6 col-12">
                        <div class="card custom-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <span class="d-block mb-2">Archive Client</span>
                                        <h5 class="mb-4 fs-4">{{ number_format($archiveUserCount) }}</h5>
                                        <span class="text-muted">Since last month</span>
                                    </div>
                                    <div class="main-card-icon success">
                                        <div
                                            class="avatar avatar-lg bg-success-transparent border border-success border-opacity-10">
                                            <div class="avatar avatar-sm svg-white">
                                                {{-- Icon --}}
                                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                                    fill="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        d="M12 12c2.67 0 8 1.34 8 4v2H4v-2c0-2.66 5.33-4 8-4zm0-2a4 4 0 1 1 0-8 4 4 0 0 1 0 8z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Fee Earner Count --}}
                    
                </div>
            @elseif(auth()->user()->User_Role == 2)
                {{-- Fee Earner View --}}
                <div class="row">
                    {{-- Live Files --}}
                    <div class="col-md-6 col-lg-4">
                        <a href="{{ route('files.index') }}" style="text-decoration: none; color: inherit; cursor: pointer;">

                            <div class="card custom-card">
                                <div class="card-body">
                                    <h5>Live Files</h5>
                                    <p class="fs-4">{{ number_format($liveFileCount) }}</p>
                                    <span class="text-muted">Currently Active Files</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    {{-- Closed Files --}}
                    <div class="col-md-6 col-lg-4">
                        <div class="card custom-card">
                            <div class="card-body">
                                <h5>Closed Files</h5>
                                <p class="fs-4">{{ number_format($closedFileCount) }}</p>
                                <span class="text-muted">Completed/Archived Files</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- End:: row-1 -->


        </div>
    </div>
    <!-- End::app-content -->
@endsection
