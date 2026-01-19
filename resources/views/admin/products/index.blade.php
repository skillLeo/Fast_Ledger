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
                        {{-- ✅ Use translation --}}
                        <span class="page-title">{{ __('company.product_management') }}</span>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route($routePrefix . '.index') }}"
                                    class="nav-link-btn {{ !request('category') ? 'active' : '' }}">
                                    {{ __('company.all_products') }}
                                </a>
                                <a href="{{ route($routePrefix . '.index', ['category' => 'purchase']) }}"
                                    class="nav-link-btn {{ request('category') == 'purchase' ? 'active' : '' }}">
                                    {{ __('company.purchase_products') }}
                                </a>
                                <a href="{{ route($routePrefix . '.index', ['category' => 'sales']) }}"
                                    class="nav-link-btn {{ request('category') == 'sales' ? 'active' : '' }}">
                                    {{ __('company.sales_products') }}
                                </a>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary"
                                    onclick="openProductModalForCreate('purchase')">
                                    <i class="fa fa-plus me-2"></i>{{ __('company.add_product') }}
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Search Section -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="text" id="searchInput" class="form-control"
                                            placeholder="{{ __('company.search_placeholder') }}">
                                        <button class="btn btn-outline-secondary" type="button">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Products Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="productsTable">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="8%">{{ __('company.image') }}</th>
                                            <th width="10%">{{ __('company.item_code') }}</th>
                                            <th width="15%">{{ __('company.name') }}</th>
                                            <th width="10%">{{ __('company.category') }}</th>
                                            <th width="20%">{{ __('company.description') }}</th>
                                            <th width="10%">{{ __('company.unit_amount') }}</th>
                                            <th width="10%">{{ __('company.vat_rate') }}</th>
                                            <th width="12%">{{ __('company.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody id="productsTableBody">
                                        @forelse($products as $index => $product)
                                            <tr data-product-id="{{ $product->id }}" class="product-row">
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    @if ($product->file_url)
                                                        <img src="{{ $product->file_url }}" alt="{{ $product->name }}"
                                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                    @else
                                                        <div class="bg-light d-flex align-items-center justify-content-center"
                                                            style="width: 50px; height: 50px; border-radius: 4px;">
                                                            <i class="fa fa-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td><strong>{{ $product->item_code }}</strong></td>
                                                <td>{{ $product->name }}</td>
                                                <td>
                                                    @if ($product->category == 'purchase')
                                                        <span
                                                            class="badge bg-info">{{ __('company.purchase_products') }}</span>
                                                    @else
                                                        <span
                                                            class="badge bg-success">{{ __('company.sales_products') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $product->description ?? '-' }}</td>
                                                <td>£{{ number_format($product->unit_amount, 2) }}</td>
                                                <td>{{ $product->vatRate->vat_name ?? '-' }}
                                                    ({{ $product->vatRate->percentage ?? 0 }}%)
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route($routePrefix . '.show', $product->id) }}"
                                                            class="btn btn-sm btn-info" title="{{ __('company.view') }}">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-warning"
                                                            onclick="openProductModalForEdit({{ $product->id }})"
                                                            title="{{ __('company.edit') }}">
                                                            <i class="fa fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger delete-btn"
                                                            data-id="{{ $product->id }}"
                                                            title="{{ __('company.delete') }}">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-muted">
                                                    <i class="fa fa-inbox fa-3x mb-3 d-block"></i>
                                                    {{ __('company.no_products_found') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div id="tableInfo">
                                    {{ __('company.showing_count', ['count' => $products->count()]) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('admin.day_book._modals._product-modal')
@endsection

@section('scripts')
    <script src="{{ asset('admin/js/transactions/vat-manager.js') }}"></script>
    <script src="{{ asset('admin/js/transactions/data-loader.js') }}"></script>
    <script src="{{ asset('admin/js/transactions/product-modal.js') }}"></script>

    <script>
        // ✅ Determine context
        const isCompanyModule = {{ request()->routeIs('company.*') ? 'true' : 'false' }};
        const routeBase = isCompanyModule ? '/company/products' : '/products';

        document.addEventListener('DOMContentLoaded', function() {
            if (!window.vatManager) {
                window.vatManager = new VatManager();
                window.vatManager.initialize();
            }

            if (!window.dataLoader) {
                window.dataLoader = new DataLoader();
                window.dataLoader.initialize();
            }

            if (window.productModal) {
                window.productModal.initialize();
                console.log('✅ Product modal initialized');
            }
        });

        $(document).ready(function() {
            let deleteProductId = null;

            // Search functionality
            $('#searchInput').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();

                $('.product-row').each(function() {
                    const row = $(this);
                    const itemCode = row.find('td:eq(2)').text().toLowerCase();
                    const name = row.find('td:eq(3)').text().toLowerCase();
                    const description = row.find('td:eq(5)').text().toLowerCase();

                    if (itemCode.includes(searchTerm) || name.includes(searchTerm) || description
                        .includes(searchTerm)) {
                        row.show();
                    } else {
                        row.hide();
                    }
                });

                $('#showingCount').text($('.product-row:visible').length);
            });

            // Delete product (delegated for dynamic rows)
            $(document).on('click', '.delete-btn', function() {
                const productId = $(this).data('id');

                if (confirm('Are you sure you want to delete this product?')) {
                    $.ajax({
                        url: `${routeBase}/${productId}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                alert(response.message);
                                $(`.product-row[data-product-id="${productId}"]`).fadeOut(300,
                                    function() {
                                        $(this).remove();
                                        updateRowNumbers();
                                    });
                            }
                        },
                        error: function(xhr) {
                            alert('Error: ' + (xhr.responseJSON?.message || 'Unknown error'));
                        }
                    });
                }
            });

            function updateRowNumbers() {
                $('.product-row:visible').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            }

            function updateRowNumbers() {
                $('.product-row:visible').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            }
        });
    </script>
@endsection
