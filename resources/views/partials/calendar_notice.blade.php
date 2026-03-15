@if(!empty($todayCalendarNotice))
    <div class="alert alert-info d-flex align-items-start gap-2" role="alert">
        <i class="bi bi-calendar-event fs-5"></i>
        <div>
            <div class="fw-semibold">Pemberitahuan Kalender Akademik</div>
            <div>{{ $todayCalendarNotice['message'] }}</div>
            <div class="small text-secondary">Tanggal: {{ $todayCalendarNotice['date'] }}</div>
        </div>
    </div>
@endif
