@extends('admin.layout.app')

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="page-title">Fee Earners</h4>
                            <div class="ms-auto">
                                <a href="{{ route('feeearner.create') }}" class="btn addbutton rounded-pill btn-wave" role="button">Add New</a> 
                                
                                <button id="updateStatusBtn" class="btn btn-danger rounded-pill btn-wave">Inactive</button> 
                                <a href="{{ route('check.active') }}" class="btn btn-primary rounded-pill btn-wave" role="button">Active List</a>  
                                <a href="{{ route('check.inactive') }}" class="btn btn-primary rounded-pill btn-wave" role="button">Inactive List</a>  
                            
                          
                            </div>
                        </div>
                        
                        <div class="card-body">
                            
                             {{-- <div class=" table-responsive">
                                {!! $dataTable->table(['class' => 'table table-striped table-bordered text-nowrap table-sm', 'id' => 'Fee-Earner-table'], true) !!}
                            </div> --}}
                           
                            
                            <div class="table-responsive" id="userTable">
                                <table class="table table-bordered table-striped" id="userTables">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAll"></th>
                                            <th>Full Name</th>
                                            <th>User Name</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Last Login DateTime</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user as $u)
                                            <tr data-user-id="{{ $u->User_ID }}">
                                                <td><input type="checkbox" class="userCheckbox" value="{{ $u->User_ID }}"></td>
                                                <td>{{ $u->Full_Name }}</td>
                                                <td>{{ $u->User_Name }}</td>
                                                <td>{{ $u->email }}</td>
                                                <td>{{ $u->Is_Active == 0 ? 'Active' : 'Inactive' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($u->Last_Login_DateTime)->format('Y-m-d') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            
                            
                            
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
@endsection

@push('scripts')
    {{-- {!! $dataTable->scripts() !!} --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#userTable tbody').on('click', 'tr', function() {
            var userId = $(this).data('user-id');  
             
            $.ajax({
                url: "/edit-Feeearner/" + userId,  
                type: "GET",
                success: function(response) {
                     window.location.href = "/edit-Feeearner/" + userId; 
                },
                error: function(xhr, status, error) {
                    alert("Error loading edit page!");
                }
            });
        });
    });

    $(document).ready(function() {
    $(".userCheckbox").click(function(event) {
        event.stopPropagation();
    });

    $("#selectAll").click(function() {
        $(".userCheckbox").prop("checked", this.checked);
    });

    $("#userTable tbody").on("click", "tr", function(event) {
        if ($(event.target).is(".userCheckbox")) {
            return;
        }
    });

    $("#updateStatusBtn").click(function() {
        var selectedUsers = $(".userCheckbox:checked").map(function() {
            return $(this).val();
        }).get();

        if (selectedUsers.length === 0) {
            Swal.fire({
                title: 'Warning!',
                text: 'Please select at least one user.',
                icon: 'warning',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        $.ajax({
            url: "{{ route('update.feeerner.status') }}",
            type: "POST",
            data: {
                user_ids: selectedUsers,
                _token: "{{ csrf_token() }}" 
            },
            success: function(response) {
                if (response.success) { 
                    Swal.fire({
                        title: 'Updated!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    $("#userTable").load(location.href + " #userTable > *");  

                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message || 'Status could not be updated.',
                        icon: 'error',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Something went wrong. Please try again.',
                    icon: 'error',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    });
});

function attachEventHandlers() {
        $(document).on("click", ".userCheckbox", function(event) {
            event.stopPropagation();
        });

        $(document).on("click", "#selectAll", function() {
            $(".userCheckbox").prop("checked", this.checked);
        });
    }
    </script>
@endpush