@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid mt-4">

            <div class="card shadow-sm border-0 rounded-3">

                @include('admin.partial.errors')

                <div class="card-header bg-light d-flex justify-content-between align-items-center">

                    <h5 class="fw-bold mb-0" style="color: #1b598c;">

                        Bank Account Listing ({{ $user->Full_Name }}) - {{ request('inactive') ? 'Inactive' : 'Active' }}
                    </h5>
                    <div class="btn-group" role="group" aria-label="Actions">
                        <a href="{{ route('banks.create', $user->User_ID) }}" class="btn btn-primary btn-sm me-2"
                            style="background-color: #1b598c; color: #fff; border: none;">
                            <i class="fas fa-plus-circle"></i> New
                        </a>
                        <button type="submit" form="inactivateForm" class="btn btn-info btn-sm me-2"
                            style="background-color: #4bb6e0; color: #fff; border: none;">
                            <i class="fas fa-times-circle"></i> Inactive
                        </button>
                        <a href="{{ request()->fullUrlWithQuery(['inactive' => false]) }}"
                            class="btn btn-success btn-sm me-2"
                            style="background-color: #218c5a; color: #fff; border: none;">
                            Active List
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['inactive' => true]) }}" class="btn  btn-sm"
                            style="background-color: #71c0f5; color: #fff; border: none;">
                            Inactive List
                        </a>
                    </div>
                </div>

                <div class="card-body">

                    <!-- Filters -->
                    <div class="row g-2 justify-content-end mb-3">
                        @foreach (['start_month', 'start_year', 'end_month', 'end_year'] as $filter)
                            <div class="col-auto">
                                <select class="form-select form-select-sm">
                                    @if (Str::contains($filter, 'month'))
                                        @for ($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m < 10 ? '0' . $m : $m }}">{{ $m < 10 ? '0' . $m : $m }}
                                            </option>
                                        @endfor
                                    @else
                                        @for ($y = date('Y'); $y <= date('Y') + 5; $y++)
                                            <option value="{{ $y }}">{{ $y }}</option>
                                        @endfor
                                    @endif
                                </select>
                            </div>
                        @endforeach
                    </div>

                    <!-- Bank Account Table -->
                    <form method="POST" action="{{ route('banks.inactivate') }}" id="inactivateForm">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle table-sm text-nowrap">
                                <thead class="table-success">
                                    <tr>
                                        <th><input type="checkbox" id="checkAll" /></th>
                                        <th>Account Type</th>
                                        <th>Bank Name</th>
                                        <th>Account Name</th>
                                        <th>Account No</th>
                                        <th>Sort Code</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($banks as $bank)
                                        <tr>
                                            <td><input type="checkbox" name="bank_ids[]"
                                                    value="{{ $bank['Bank_Account_ID'] }}"></td>
                                            <td>{{ $bank['Bank_Type'] ?? '-' }}</td>
                                            <td>{{ $bank['Bank_Name'] ?? '-' }}</td>
                                            <td>{{ $bank['Bank_Type'] ?? '-' }}</td>
                                            <td>{{ $bank['Account_No'] ?? '-' }}</td>
                                            <td>{{ $bank['Sort_Code'] ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No bank accounts found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>

                </div>

                <div class="card-footer text-end">
                    <a href="{{ route('clients.index', 'active') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Clients
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <script>
        document.getElementById('checkAll')?.addEventListener('change', function(e) {
            const checkboxes = document.querySelectorAll('input[name="bank_ids[]"]');
            checkboxes.forEach(cb => cb.checked = e.target.checked);
        });

        setTimeout(function() {
            let alert = document.getElementById('success-alert');
            if (alert) {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = 0;
                setTimeout(() => alert.remove(), 500); // Fully remove from DOM
            }
        }, 3000); // 3 seconds
    </script>
@endsection
