{{-- ========================================================================
     READ-ONLY INVOICE NOTES
     Displays invoice notes history with support for rich content
     ======================================================================== --}}

<div class="mb-4">
    <h6><strong>{{ __('company.notes') }}</strong></h6>
    
    @php
        // Decode notes if it's a JSON string
        $notesArray = $notes;
        if (is_string($notesArray)) {
            $notesArray = json_decode($notesArray, true);
        }
        
        // Ensure it's an array
        if (!is_array($notesArray)) {
            $notesArray = [];
        }
    @endphp

    @if(!empty($notesArray) && count($notesArray) > 0)
        @foreach($notesArray as $index => $note)
            <div class="note-item mb-3 p-3 border rounded bg-white">
                {{-- Note Header --}}
                <div class="note-header d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div class="note-meta text-muted small">
                        <i class="fas fa-calendar me-1"></i>
                        <strong>
                            @if(isset($note['timestamp']))
                                {{ \Carbon\Carbon::parse($note['timestamp'])->format('d/m/Y, H:i') }}
                            @else
                                {{ __('company.n_a') }}
                            @endif
                        </strong>
                        <span class="mx-2">•</span>
                        <i class="fas fa-user me-1"></i>
                        <strong>{{ $note['user'] ?? __('company.system') }}</strong>
                    </div>
                    <span class="badge bg-secondary">#{{ $index + 1 }}</span>
                </div>

                {{-- Note Content --}}
                <div class="note-content">
                    @if(isset($note['content']))
                        @if(str_contains($note['content'], '<table'))
                            {{-- Rich content with table --}}
                            <div class="note-rich-content">
                                {!! $note['content'] !!}
                            </div>
                        @else
                            {{-- Plain text content --}}
                            <div class="note-text-content">
                                {!! nl2br(e($note['content'])) !!}
                            </div>
                        @endif
                    @else
                        <em class="text-muted">{{ __('company.no_content') }}</em>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-info mb-0">
            <i class="fas fa-info-circle me-2"></i>
            {{ __('company.no_notes_available') }}
        </div>
    @endif
</div>

<style>
    /* Note Item Container */
    .note-item {
        background: #f8f9fa;
        transition: all 0.3s ease;
    }

    .note-item:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transform: translateX(5px);
    }

    /* Note Header */
    .note-header {
        color: #495057;
    }

    .note-meta strong {
        color: #212529;
    }

    /* Plain Text Content */
    .note-text-content {
        padding: 10px 15px;
        background: #ffffff;
        border-left: 3px solid #0dcaf0;
        border-radius: 4px;
        font-size: 14px;
        line-height: 1.6;
        color: #212529;
    }

    /* Rich Content (Tables) */
    .note-rich-content {
        margin-top: 10px;
    }

    .note-rich-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 10px 0;
        font-size: 13px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .note-rich-content table td,
    .note-rich-content table th {
        border: 1px solid #dee2e6;
        padding: 10px 12px;
        text-align: left;
    }
    
    .note-rich-content table th {
        background-color: #b8daff;
        font-weight: 600;
        color: #004085;
    }

    .note-rich-content table td {
        background-color: #fff;
    }

    .note-rich-content table tr:hover td {
        background-color: #f1f3f5;
    }

    /* Any text below the table */
    .note-rich-content > *:not(table) {
        margin-top: 10px;
        padding: 10px 15px;
        background: #ffffff;
        border-left: 3px solid #0dcaf0;
        border-radius: 4px;
    }
</style>