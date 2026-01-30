@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    @include('admin.partial.errors')

                    <div class="card custom-card">
                        <div class="card-header my-3 d-flex justify-content-between align-items-center">
                            <h4 class="page-title mb-0">Active Clients</h4>
 <div>
    @php
        use App\Models\Client;

        $currentUser = auth()->user();
        $userRole = $currentUser->User_Role;
        $allowed_companies = $currentUser->allowed_companies;

        // ✅ COUNT ONLY ACTIVE COMPANIES (Is_Archive = 0)
        $companyCount = Client::where('agnt_admin_id', $currentUser->User_ID)
            ->where('Is_Archive', 0)
            ->count();
    @endphp

    {{-- Agent Admin reached ACTIVE company limit → open upgrade modal --}}
    @if ($userRole == 3 && $companyCount >= $allowed_companies)
        <button
            class="btn btn-primary btn-wave"
            data-bs-toggle="modal"
            data-bs-target="#upgradePlanModal">
            + New Company
        </button>
    @else
        <a href="{{ route('clients.create') }}" class="btn btn-primary btn-wave">
            + New Company
        </a>
    @endif

    <a href="{{ route('clients.index', ['type' => 'archived']) }}"
       class="btn btn-wave"
       style="background-color: #75bfed; color: #fff; border: none;">
        Inactive Clients
    </a>
</div>





                        </div>

                        <div class="card-body">
                            {{-- Search Bar --}}
                            <form method="GET" action="{{ route('clients.index', request()->route('type')) }}" class="mb-3">
                                <div class="col-md-3">
                                    <div class="input-group col-md-3">
                                        <input type="text" name="search" class="form-control"
                                            placeholder="Search clients..." value="{{ request('search') }}">
                                        <button class="btn btn-outline-primary" type="submit">Search</button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped text-nowrap table-sm">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Contact Name</th>
                                            <th class="text-center">Business Name</th>
                                            <th class="text-center">Address</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($clients as $client)
                                            <tr class="my-2">
                                                <td class="text-start">{{ $client->Contact_Name }}</td>
                                                <td class="text-start">{{ $client->Business_Name }}</td>
                                                <td class="text-start">{{ $client->Address1 }}</td>
                                                <td class="text-center">
                                                    <span
                                                        class="badge rounded-pill px-3 py-2 fw-semibold {{ $client->Is_Archive ? 'bg-secondary' : 'bg-success' }}"
                                                        role="button" data-bs-toggle="modal"
                                                        data-bs-target="#archiveModal-{{ $client->Client_ID }}">
                                                        {{ $client->Is_Archive ? 'Archived' : 'Active' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $adminUsers = $client->users->where('User_Role', 2);
                                                    @endphp
                                                    @foreach ($adminUsers as $adminUser)
                                                        <div class="d-flex justify-content-center gap-2">
                                                            <a href="{{ route('admin.login.as', ['id' => $adminUser->User_ID]) }}"
                                                                class="btn btn-sm btn-primary px-2" data-bs-toggle="tooltip"
                                                                data-bs-placement="top" title="Admin Login">
                                                                Login
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </td>

                                                <!-- Modal for Archiving Client -->
                                                <div class="modal fade" id="archiveModal-{{ $client->Client_ID }}" tabindex="-1"
                                                    aria-labelledby="archiveModalLabel-{{ $client->Client_ID }}"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="archiveModalLabel-{{ $client->Client_ID }}">Deactivate
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                    aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to deactivate this client?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <form
                                                                    action="{{ route('clients.archive', $client->Client_ID) }}"
                                                                    method="POST">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-warning">Yes,
                                                                        Deactivate</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No clients found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-end mt-3">
                                {{ $clients->withQueryString()->links('pagination::bootstrap-5') }}
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="upgradePlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Upgrade Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">
                <p class="mb-2 fw-semibold">
                    You have reached your company limit.
                </p>
                <p class="text-muted mb-0">
                    Your current plan allows up to
                    <strong>{{ $allowed_companies }}</strong> companies.
                    Please upgrade your plan to add more.
                </p>
            </div>

            <div class="modal-footer justify-content-center">
                <a href="{{ route('company.payment.create') }}"
                   class="btn btn-primary px-4">
                    Upgrade Plan
                </a>
                <button type="button"
                        class="btn btn-outline-dark"
                        data-bs-dismiss="modal">
                    Cancel
                </button>
            </div>

        </div>
    </div>
</div>


@endsection


@section('scripts')
    <script>
        setTimeout(function () {
            let alert = document.getElementById('success-alert');
            if (alert) {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = 0;
                setTimeout(() => alert.remove(), 500); // Fully remove from DOM
            }
        }, 3000); // 3 seconds
    </script>
@endsection