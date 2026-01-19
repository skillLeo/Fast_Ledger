@extends('admin.layout.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/employee-form.css') }}">
@endpush
@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header justify-content-between">
                            <div class="page-title">Employee Details</div>
                        </div>

                        @include('admin.employees.partials.validation-errors')
                        @include('admin.employees.partials.success-message')

                        <div class="card-body">
                            <form method="POST" action="{{ route('employees.store') }}">
                                @csrf

                                @include('admin.employees.partials.tab-navigation')

                                <div class="tab-content-wrapper">
                                    @include('admin.employees.partials.personal-tab')
                                    @include('admin.employees.partials.employment-tab')
                                    @include('admin.employees.partials.nic-tab')
                                    @include('admin.employees.partials.hmrc-tab')
                                    @include('admin.employees.partials.contacts-tab')
                                    @include('admin.employees.partials.terms-tab')
                                    @include('admin.employees.partials.payment-tab')
                                </div>

                                @include('admin.employees.partials.form-actions')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="{{ asset('admin/js/employee-form.js') }}"></script>
    <script src="{{ asset('admin/js/employee-validation.js') }}"></script>
@endsection
