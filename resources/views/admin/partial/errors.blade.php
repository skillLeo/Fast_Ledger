@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="flash-message">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" id="flash-message">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif


<script>
    document.addEventListener("DOMContentLoaded", function () {
        const alert = document.querySelector('#flash-message');
        if (alert) {
            setTimeout(() => {
                // Bootstrap fade-out
                alert.classList.remove('show'); // triggers fade-out
                setTimeout(() => alert.remove(), 500); // wait for fade animation before removing
            }, 3000); // 3 seconds
        }
    });
</script>
