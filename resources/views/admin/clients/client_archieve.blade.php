@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @php
                $currentUser = auth()->user();
                $allowedCompanies = (int) ($currentUser->allowed_companies ?? 0);

                // IMPORTANT: count ONLY ACTIVE companies (Is_Archive = 0)
                $activeCompanyCount = \App\Models\Client::where('agnt_admin_id', $currentUser->User_ID)
                    ->where('Is_Archive', 0)
                    ->count();
            @endphp

            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header my-2 d-flex justify-content-between align-items-center">
                            <h4 class="page-title mb-0">File Export Datatable</h4>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-primary btn-sm">
                                ← Back
                            </a>
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
                                            @php
                                                $isInactive = (int) $client->Is_Archive === 1;
                                                $canActivate = $activeCompanyCount < $allowedCompanies;
                                            @endphp

                                            <tr class="my-2">
                                                <td class="text-start">{{ $client->Contact_Name }}</td>
                                                <td class="text-start">{{ $client->Business_Name }}</td>
                                                <td class="text-start">{{ $client->Address1 }}</td>

                                                <td class="text-center">
                                                    {{-- ACTIVE => always allow deactivate --}}
                                                    @if (!$isInactive)
                                                        <span class="badge rounded-pill bg-success text-white"
                                                            style="cursor:pointer"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#archiveModal-{{ $client->Client_ID }}">
                                                            Active
                                                        </span>
                                                    @else
                                                        {{-- INACTIVE => allow activate only if active limit not full --}}
                                                        @if ($canActivate)
                                                            <span class="badge rounded-pill bg-secondary text-white"
                                                                style="cursor:pointer"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#recoverModal-{{ $client->Client_ID }}">
                                                                Inactive
                                                            </span>
                                                        @else
                                                            <span class="badge rounded-pill bg-danger text-white"
                                                                style="cursor:pointer"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#upgradeModal-{{ $client->Client_ID }}">
                                                                Inactive (Upgrade Plan)
                                                            </span>
                                                        @endif
                                                    @endif
                                                </td>

                                                <td class="text-center">
                                                    @php
                                                        $adminUser = $client->users->where('User_Role', 2)->first();
                                                    @endphp
                                                    <div class="hstack gap-2 fs-15 text-center">
                                                        <a href="#" class="btn btn-sm btn-info">Manage Account</a>
                                                    </div>
                                                </td>

                                                {{-- UPGRADE MODAL (only when trying to activate but limit full) --}}
                                                <div class="modal fade" id="upgradeModal-{{ $client->Client_ID }}" tabindex="-1"
                                                    aria-labelledby="upgradeModalLabel-{{ $client->Client_ID }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="upgradeModalLabel-{{ $client->Client_ID }}">
                                                                    Upgrade Your Plan
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                You have reached the maximum number of active companies for your current plan.
                                                                Please upgrade your plan to activate more companies.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <a href="{{ route('company.payment.create') }}" class="btn btn-primary">Upgrade Plan</a>
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- ARCHIVE (DEACTIVATE) MODAL --}}
                                                <div class="modal fade" id="archiveModal-{{ $client->Client_ID }}" tabindex="-1"
                                                    aria-labelledby="archiveModalLabel-{{ $client->Client_ID }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="archiveModalLabel-{{ $client->Client_ID }}">
                                                                    Confirm Deactivate
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to deactivate this company?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <form action="{{ route('clients.archive', $client->Client_ID) }}" method="POST">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-warning">Yes, Deactivate</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- RECOVER (ACTIVATE) MODAL --}}
                                                <div class="modal fade" id="recoverModal-{{ $client->Client_ID }}" tabindex="-1"
                                                    aria-labelledby="recoverModalLabel-{{ $client->Client_ID }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="recoverModalLabel-{{ $client->Client_ID }}">
                                                                    Confirm Activate
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to activate this company?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <form action="{{ route('clients.recover', $client->Client_ID) }}" method="POST">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-success">Yes, Activate</button>
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
@endsection
