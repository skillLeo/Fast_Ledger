@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="page-title mb-0">File Export Datatable</h4>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-primary btn-sm">
                                ← Back
                            </a>
                        </div>
                        <div class="card-body">

                            {{-- Search Bar --}}
                            <form method="GET" action="{{ route('clients.index', request()->route('type')) }}"
                                class="mb-3">
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
                                            <th>Client Ref</th>
                                            <th>Contact Name</th>
                                            <th>Business Name</th>
                                            <th>Address</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($clients as $client)
                                            <tr>
                                                <td>{{ $client->Client_Ref }}</td>
                                                <td>{{ $client->Contact_Name }}</td>
                                                <td>{{ $client->Business_Name }}</td>
                                                <td>{{ $client->Address1 }}</td>
                                                <td>
                                                    @if ($client->Is_Archive)
                                                        <span class="badge bg-primary" style="cursor: pointer;"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#recoverModal-{{ $client->Client_ID }}">
                                                            Archived
                                                        </span>
                                                    @else
                                                        <span class="badge bg-primary">Active</span>
                                                    @endif
                                                </td>

                                                <td>
                                                    @php
                                                        $adminUser = $client->users->where('User_Role', 2)->first();
                                                    @endphp
                                                    <div class="hstack gap-2 fs-15 text-center">
                                                        <a href="#" class="btn btn-sm btn-info">Manage Account</a>
                                                        {{-- </div> @if ($adminUser)
                                                        <a href="{{ route('users.impersonate', $adminUser->id) }}" class="btn btn-sm btn-warning">Authorized Login</a>
                                                        <a href="{{ route('admin.login.as', $adminUser->id) }}" class="btn btn-sm btn-primary">Admin Login</a>
                                                    @endif --}}
                                                    </div>
                                                </td>

                                                @if ($client->Is_Archive)
                                                    <div class="modal fade" id="recoverModal-{{ $client->Client_ID }}"
                                                        tabindex="-1"
                                                        aria-labelledby="recoverModalLabel-{{ $client->Client_ID }}"
                                                        aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title"
                                                                        id="recoverModalLabel-{{ $client->Client_ID }}">
                                                                        Confirm Recover</h5>
                                                                    <button type="button" class="btn-close"
                                                                        data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Are you sure you want to recover this client?
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <form
                                                                        action="{{ route('clients.recover', $client->Client_ID) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        @method('PATCH')
                                                                        <button type="button" class="btn btn-warning"
                                                                            data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-success">Yes,
                                                                            Recover</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No clients found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-end mt-3">
                                {{-- {{ $clients->links() }} --}}
                                {{ $clients->withQueryString()->links('pagination::bootstrap-5') }}

                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
