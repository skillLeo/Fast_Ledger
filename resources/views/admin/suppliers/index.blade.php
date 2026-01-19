@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    @include('admin.partial.errors')

                    <div class="card custom-card">
                        <div class="card-header mb-2">
                            <h4 class="page-title mb-0">
                                {{ $isCompanyModule ? 'Company Suppliers' : 'Suppliers' }}
                            </h4>
                        </div>

                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                {{-- Search Bar --}}
                                <form method="GET" 
                                      action="{{ $isCompanyModule ? route('company.suppliers.index') : route('suppliers.index') }}" 
                                      class="mb-0">
                                    <div class="input-group" style="width: 300px;">
                                        <input type="text" 
                                               name="search" 
                                               class="form-control" 
                                               placeholder="Search suppliers..." 
                                               value="{{ request('search') }}">
                                        <button style="background: #13667d;" 
                                                class="btn text-white border-0" 
                                                type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </form>

                                {{-- Action Buttons --}}
                                <div>
                                    <a href="{{ $isCompanyModule ? route('company.suppliers.create') : route('suppliers.create') }}" 
                                       class="btn teal-custom" 
                                       role="button">
                                        <i class="fas fa-plus me-1"></i> Add New Supplier
                                    </a>
                                </div>
                            </div>

                            {{-- Suppliers Table --}}
                            <div class="table-responsive shadow">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead style="background-color: #f8f9fa;">
                                        <tr>
                                            <th class="text-center" style="width: 5%;">#</th>
                                            <th style="width: 15%;">Contact Name</th>
                                            <th style="width: 12%;">Account Number</th>
                                            <th style="width: 15%;">Email</th>
                                            <th style="width: 12%;">Phone</th>
                                            <th style="width: 10%;">Status</th>
                                            <th style="width: 8%;">Rating</th>
                                            <th class="text-center" style="width: 13%;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($suppliers as $supplier)
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration + ($suppliers->currentPage() - 1) * $suppliers->perPage() }}</td>
                                                <td>
                                                    <strong>{{ $supplier->contact_name }}</strong>
                                                    @if($supplier->company_reg)
                                                        <br><small class="text-muted">{{ $supplier->company_reg }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $supplier->account_number ?? '-' }}</td>
                                                <td>{{ $supplier->email ?? '-' }}</td>
                                                <td>{{ $supplier->phone ?? '-' }}</td>
                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'active' => 'success',
                                                            'inactive' => 'secondary',
                                                            'pending' => 'warning',
                                                            'suspended' => 'danger'
                                                        ];
                                                        $color = $statusColors[$supplier->status] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge bg-{{ $color }}">
                                                        {{ ucfirst($supplier->status) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($supplier->rating)
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <i class="fas fa-star {{ $i <= $supplier->rating ? 'text-warning' : 'text-muted' }}"></i>
                                                        @endfor
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    {{-- View Button --}}
                                                    <a href="{{ $isCompanyModule ? route('company.suppliers.show', $supplier->id) : route('suppliers.show', $supplier->id) }}" 
                                                       class="btn btn-sm btn-info" 
                                                       title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    {{-- Edit Button --}}
                                                    <a href="{{ $isCompanyModule ? route('company.suppliers.edit', $supplier->id) : route('suppliers.edit', $supplier->id) }}" 
                                                       class="btn btn-sm btn-primary" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    {{-- Delete Button --}}
                                                    <form action="{{ $isCompanyModule ? route('company.suppliers.destroy', $supplier->id) : route('suppliers.destroy', $supplier->id) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-danger" 
                                                                title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                    <p class="text-muted mb-0">No suppliers found.</p>
                                                    <a href="{{ $isCompanyModule ? route('company.suppliers.create') : route('suppliers.create') }}" 
                                                       class="btn btn-sm teal-custom mt-2">
                                                        <i class="fas fa-plus me-1"></i> Add Your First Supplier
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            @if($suppliers->hasPages())
                                <div class="d-flex justify-content-end mt-3">
                                    {{ $suppliers->withQueryString()->links('pagination::bootstrap-5') }}
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
        // Auto-hide success alerts
        setTimeout(function() {
            let alert = document.getElementById('success-alert');
            if (alert) {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = 0;
                setTimeout(() => alert.remove(), 500);
            }
        }, 3000);
    </script>
@endsection