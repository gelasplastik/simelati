@if(session('success'))
    <div class="alert alert-success d-flex justify-content-between align-items-center shadow-sm" role="alert">
        <span><i class="bi bi-check-circle me-1"></i>{{ session('success') }}</span>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger d-flex justify-content-between align-items-center shadow-sm" role="alert">
        <span><i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}</span>
        @if(session('late_entry_request_url'))
            <a class="btn btn-sm btn-warning" href="{{ session('late_entry_request_url') }}">Ajukan Izin</a>
        @endif
    </div>
@endif
