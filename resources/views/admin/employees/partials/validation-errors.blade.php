@if ($errors->any())
    <div class="alert alert-danger">
        <h4><i class="fas fa-exclamation-triangle"></i> Validation Errors ({{ $errors->count() }})</h4>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif