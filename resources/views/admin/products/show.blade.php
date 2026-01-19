@extends('admin.layout.app')

@section('content')
    @php
        $isCompanyModule = request()->routeIs('company.*');
        $routePrefix = $isCompanyModule ? 'company.products' : 'products';
    @endphp

    <div class="main-content app-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="page-title">{{ __('company.product_details') }}</span>

                            <a href="{{ route($routePrefix . '.index') }}" class="teal-custom-btn">
                                <i class="fa fa-arrow-left me-2"></i>{{ __('company.back_to_products') }}
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="row">

                                {{-- PRODUCT IMAGE --}}
                                <div class="col-md-4">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">{{ __('company.product_image') }}</h6>
                                        </div>
                                        <div class="card-body text-center">

                                            @if ($product->file_url)
                                                <img src="{{ $product->file_url }}" alt="{{ $product->name }}"
                                                    class="img-fluid rounded shadow-sm mb-3"
                                                    style="max-height:400px;width:100%;object-fit:contain;">

                                                <div class="mt-3">
                                                    <a href="{{ $product->file_url }}" target="_blank"
                                                        class="teal-custom-btn me-2 p-2">
                                                        <i class="fa fa-external-link me-1"></i>
                                                        {{ __('company.view_full_size') }}
                                                    </a>
                                                    <a href="{{ $product->file_url }}" download
                                                        class="btn btn-sm btn-success rounded-0" style="padding: 6px">
                                                        <i class="fa fa-download"></i>
                                                        {{ __('company.download') }}
                                                    </a>
                                                </div>
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center"
                                                    style="height:300px;border-radius:8px;">
                                                    <div class="text-center text-muted">
                                                        <i class="fa fa-image fa-4x mb-3 d-block"></i>
                                                        <p>{{ __('company.no_image_available') }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- FILE INFO --}}
                                    @if ($product->file_path)
                                        <div class="card border mt-3">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">{{ __('company.file_information') }}</h6>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-sm table-borderless mb-0">
                                                    <tr>
                                                        <td class="text-muted" width="40%">
                                                            {{ __('company.file_name') }}
                                                        </td>
                                                        <td><small>{{ basename($product->file_path) }}</small></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">
                                                            {{ __('company.file_path') }}
                                                        </td>
                                                        <td>
                                                            <small class="text-break">{{ $product->file_path }}</small>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted">
                                                            {{ __('company.uploaded') }}
                                                        </td>
                                                        <td>
                                                            <small>{{ $product->created_at->format('d M Y, H:i') }}</small>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- PRODUCT DETAILS --}}
                                <div class="col-md-8">

                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">{{ __('company.product_information') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <th class="bg-light" width="30%">
                                                            {{ __('company.item_code') }}
                                                        </th>
                                                        <td>
                                                            <strong class="text-primary">
                                                                {{ $product->item_code }}
                                                            </strong>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">
                                                            {{ __('company.product_name') }}
                                                        </th>
                                                        <td><strong>{{ $product->name }}</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">
                                                            {{ __('company.category') }}
                                                        </th>
                                                        <td>
                                                            @if ($product->category === 'purchase')
                                                                <span class="badge bg-info">
                                                                    {{ __('company.purchase_products') }}
                                                                </span>
                                                            @else
                                                                <span class="badge bg-success">
                                                                    {{ __('company.sales_products') }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">
                                                            {{ __('company.description') }}
                                                        </th>
                                                        <td>{{ $product->description ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">
                                                            {{ __('company.display_name') }}
                                                        </th>
                                                        <td>{{ $product->display_name }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- FINANCIAL --}}
                                    <div class="card border mt-3">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">{{ __('company.financial_details') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <th class="bg-light" width="30%">
                                                            {{ __('company.unit_amount') }}
                                                        </th>
                                                        <td>
                                                            <strong class="text-success fs-5">
                                                                £{{ number_format($product->unit_amount, 2) }}
                                                            </strong>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">
                                                            {{ __('company.ledger_account') }}
                                                        </th>
                                                        <td>
                                                            @if ($product->ledger)
                                                                <span class="badge bg-primary">
                                                                    {{ $product->ledger->code }}
                                                                </span>
                                                                {{ $product->ledger->ledger_ref ?? '-' }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">
                                                            {{ __('company.account_reference') }}
                                                        </th>
                                                        <td>{{ $product->account_ref ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th class="bg-light">
                                                            {{ __('company.vat_rate') }}
                                                        </th>
                                                        <td>
                                                            @if ($product->vatRate)
                                                                <span class="badge bg-warning text-dark">
                                                                    {{ $product->vatRate->percentage }}%
                                                                </span>
                                                                {{ $product->vatRate->vat_name }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- SYSTEM INFO --}}
                                    <div class="card border mt-3">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">{{ __('company.system_information') }}</h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td class="text-muted" width="30%">
                                                        {{ __('company.product_id') }}
                                                    </td>
                                                    <td><strong>{{ $product->id }}</strong></td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">
                                                        {{ __('company.status_label') }}
                                                    </td>
                                                    <td>
                                                        @if ($product->is_active)
                                                            <span class="badge bg-success">
                                                                {{ __('company.active') }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-danger">
                                                                {{ __('company.inactive') }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">
                                                        {{ __('company.created_at') }}
                                                    </td>
                                                    <td>{{ $product->created_at->format('d M Y, H:i A') }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted">
                                                        {{ __('company.last_updated') }}
                                                    </td>
                                                    <td>{{ $product->updated_at->format('d M Y, H:i A') }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- ACTIONS --}}
                                    <div class="mt-4">
                                        <button class="btn btn-warning" id="editProductBtn">
                                            <i class="fa fa-edit me-2"></i>
                                            {{ __('company.edit_product') }}
                                        </button>
                                        <button class="btn btn-danger" id="deleteProductBtn">
                                            <i class="fa fa-trash me-2"></i>
                                            {{ __('company.delete_product') }}
                                        </button>
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
        const isCompanyModule = {{ request()->routeIs('company.*') ? 'true' : 'false' }};
        const routeBase = isCompanyModule ? '/company/products' : '/products';
        const productId = {{ $product->id }};

        $(document).ready(function() {
            $('#deleteProductBtn').on('click', function() {
                $('#deleteModal').modal('show');
            });

            $('#confirmDeleteBtn').on('click', function() {
                $(this).prop('disabled', true).html(
                    '<i class="fa fa-spinner fa-spin me-2"></i>Deleting...');

                $.ajax({
                    url: `${routeBase}/${productId}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            window.location.href = isCompanyModule ?
                                '{{ route('company.products.index') }}' :
                                '{{ route('products.index') }}';
                        }
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Unknown error'));
                        $('#confirmDeleteBtn').prop('disabled', false).html('Delete Product');
                    }
                });
            });
        });
    </script>
@endsection
