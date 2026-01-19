@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    @include('admin.partial.errors')

                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="page-title mb-0">Active Clients</h4>
                            <div>

                                <a href="{{ route('clients.create') }}" class="btn btn-primary  btn-wave" role="button">
                                    Add New
                                </a>
                                <a href="{{ route('clients.index', ['type' => 'archived']) }}" class="btn  btn-wave"
                                    style="background-color: #75bfed; color: #fff; border: none;">Closed Clients</a>

                            </div>

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
                                                    <span
                                                        class="badge hstack gap-2 fs-15 text-center mb-1 {{ $client->Is_Archive ? 'bg-secondary' : 'bg-primary' }}"
                                                        style="cursor: pointer;" data-bs-toggle="modal"
                                                        data-bs-target="#archiveModal-{{ $client->Client_ID }}">
                                                        {{ $client->Is_Archive ? 'Archived' : 'Active' }}
                                                    </span>
                                                </td>

                                                <td>
                                                    @php
                                                        $adminUsers = $client->users->where('User_Role', 2);
                                                    @endphp
                                                    @foreach ($adminUsers as $adminUser)
                                                        <div class="hstack gap-2 fs-15 text-center mb-1">
                                                            <a href="{{ route('admin.users.banks', ['user' => $adminUser->User_ID]) }}"
                                                                class="btn btn-sm btn-info">
                                                                Manage Bank Account
                                                            </a>


                                                            <a href="{{ route('users.impersonate', ['id' => $adminUser->User_ID]) }}"
                                                                class="btn btn-sm"
                                                                style="background-color: #75bfed; color: #fff; border: none;">
                                                                Authorized Login</a>
                                                            <a href="{{ route('admin.login.as', ['id' => $adminUser->User_ID]) }}"
                                                                class="btn btn-sm btn-primary">
                                                                Admin Login
                                                            </a>

                                                        </div>
                                                    @endforeach

                                                </td>
                                                <!-- This modal must go INSIDE the loop -->
                                                <div class="modal fade" id="archiveModal-{{ $client->Client_ID }}"
                                                    tabindex="-1"
                                                    aria-labelledby="archiveModalLabel-{{ $client->Client_ID }}"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog modal-dialog-centered">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="archiveModalLabel-{{ $client->Client_ID }}">Confirm
                                                                    Archive</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to archive this client?
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
                                                                        Archive</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
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
                                {{ $clients->withQueryString()->links('pagination::bootstrap-5') }}

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
