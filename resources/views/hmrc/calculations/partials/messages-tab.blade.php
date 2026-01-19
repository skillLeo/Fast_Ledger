<div class="row g-3">
    @if(!empty($breakdown['messages']))
    <div class="col-12">
        <h5 class="mb-3"><i class="fas fa-envelope me-2"></i> Messages from HMRC</h5>
        
        @foreach($breakdown['messages'] as $message)
        <div class="alert {{ 
            isset($message['type']) && $message['type'] === 'error' ? 'alert-danger' : 
            (isset($message['type']) && $message['type'] === 'warning' ? 'alert-warning' : 'alert-info') 
        }} mb-3">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas {{ 
                        isset($message['type']) && $message['type'] === 'error' ? 'fa-times-circle' : 
                        (isset($message['type']) && $message['type'] === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle')
                    }} fa-2x"></i>
                </div>
                <div class="flex-grow-1">
                    @if(isset($message['id']))
                    <h6 class="alert-heading">{{ $message['id'] }}</h6>
                    @endif
                    
                    <p class="mb-0">
                        {{ $message['text'] ?? $message['message'] ?? 'No message details available' }}
                    </p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="col-12">
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            No messages or warnings from HMRC. Your calculation looks good!
        </div>
    </div>
    @endif
</div>

