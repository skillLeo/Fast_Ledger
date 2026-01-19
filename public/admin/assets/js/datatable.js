 
    $(document).on('click', '.status-modal-trigger', function () {
const fileId = $(this).data('id');
const currentStatus = $(this).data('status');

// Populate the modal fields
$('#modalFileId').val(fileId);
$('#newStatus').val(currentStatus);
});
$('#statusUpdateForm').on('submit', function (e) {
e.preventDefault();

const formData = $(this).serialize();
const updateUrl = '{{ route("files.update.status") }}';

$.ajax({
    url: updateUrl,
    method: 'POST',
    data: formData,
    success: function (response) {
        if (response.success) {
            Swal.fire({
                title: 'Updated!',
                text: 'Status updated successfully!',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });

            setTimeout(function () {
                location.reload(); // Reload the page after 2 seconds
            }, 2000);
        } else {
            Swal.fire({
                title: 'Error!',
                text: 'Status could not be updated.',
                icon: 'error',
                timer: 2000,
                showConfirmButton: false
            });

            setTimeout(function () {
                location.reload(); // Reload the page after 2 seconds
            }, 2000);
        }
    },
    error: function () {
        Swal.fire({
            title: 'Error!',
            text: 'An error occurred. Please try again later.',
            icon: 'error',
            timer: 2000,
            showConfirmButton: false
        });
    }
});
});

$(document).on('click', '.delete-button', function (e) {
e.preventDefault();

const button = $(this);
const fileId = button.data('id');
const url = '{{ route("files.destroy") }}';

Swal.fire({
    title: 'Are you sure?',
    text: "This action cannot be undone!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
}).then((result) => {
    if (result.isConfirmed) {
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                id: fileId,
                _token: $('meta[name="csrf-token"]').attr('content') // Pass CSRF token here
            },
            success: function (response) {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'The record has been deleted successfully.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });

                setTimeout(function () {
                    location.reload();
                }, 2000);
            },
            error: function () {
                Swal.fire({
                    title: 'Error!',
                    text: 'An unexpected error occurred. Please try again later.',
                    icon: 'error',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    }
});
});