<x-layouts.app :title="'Teacher Dashboard - SIMELATI'" :pageTitle="'Dashboard Guru'" :breadcrumb="'Teacher / Dashboard'">
    <div class="dashboard-shell">
        <div class="dashboard-hero">
            <div class="title">Ringkasan Aktivitas Guru Hari Ini</div>
            <div class="small text-secondary">Aksi utama, jadwal, dan progres modul Anda tersaji dalam satu layar.</div>
        </div>

        <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <x-stat-card label="Status Absen Hari Ini" :value="$todayAttendance ? 'Sudah Absen' : 'Belum Absen'" icon="bi-fingerprint" variant="success" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="KPI Bulan Ini" :value="$kpi['total_score'] ?? 0" icon="bi-award" variant="warning" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Total Jurnal Bulan Ini" :value="$journalCount" icon="bi-journal-check" variant="primary" />
        </div>
        <div class="col-6 col-md-3">
            <x-stat-card label="Progress Modul Ajar" :value="$moduleProgress['completion_percentage'].'%'" icon="bi-folder2-open" variant="info" />
            <div class="small text-secondary px-2 mt-1">
                Upload: {{ $moduleProgress['uploaded_count'] }} | Approved: {{ $moduleProgress['approved_count'] }} / {{ $moduleProgress['total_assigned'] }} assignment
            </div>
        </div>
    </div>

    @if(! $journalNotifications['checked_in_today'])
        <div class="alert alert-warning">Anda belum melakukan absen guru hari ini.</div>
    @endif

    <x-panel class="mb-3" title="Status Hari Ini">
        <div class="row g-2">
            <div class="col-md-4">
                <button type="button" class="btn btn-primary btn-action-lg w-100" data-bs-toggle="modal" data-bs-target="#checkinModal" {{ $todayAttendance ? 'disabled' : '' }}>
                    <i class="bi bi-geo-alt me-1"></i> Absen Masuk
                </button>
            </div>
            <div class="col-md-4">
                <button type="button" class="btn btn-outline-primary btn-action-lg w-100" data-bs-toggle="modal" data-bs-target="#checkoutModal" {{ ! $todayAttendance || $todayAttendance->checkout_at ? 'disabled' : '' }}>
                    <i class="bi bi-box-arrow-right me-1"></i> Absen Pulang
                </button>
            </div>
            <div class="col-md-4">
                <a class="btn btn-outline-warning btn-action-lg w-100" href="{{ route('teacher.leave-requests.create') }}">
                    <i class="bi bi-calendar-plus me-1"></i> Ajukan Izin
                </a>
            </div>
        </div>
        <div class="small text-secondary mt-2">GPS status: izinkan akses lokasi browser. Geolocation hanya berfungsi di localhost atau HTTPS.</div>
        @if($todayAttendance)
            <hr>
            <div class="small">Check-in: {{ optional($todayAttendance->checkin_at)->format('H:i:s') ?? '-' }} | Check-out: {{ optional($todayAttendance->checkout_at)->format('H:i:s') ?? '-' }}</div>
        @endif
    </x-panel>

    <x-panel title="Jadwal Hari Ini" class="mb-3">
        <div class="small text-secondary mb-2">
            Total jadwal: {{ $journalNotifications['total_schedules'] }} |
            Belum absensi: {{ $journalNotifications['attendance_pending_count'] }} |
            Belum jurnal: {{ $journalNotifications['journal_pending_count'] }}.
        </div>
        <div class="d-md-none schedule-mobile-list">
            @forelse($todaySchedules as $item)
                @php($schedule = $item['schedule'])
                @php($session = $item['session'])
                <div class="schedule-mobile-item">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <div class="fw-semibold">Jam {{ $schedule->jam_ke }} - {{ $schedule->subject->name }}</div>
                            <div class="schedule-mobile-meta">Kelas {{ $schedule->class->name }} - {{ $schedule->start_time ?: '-' }} - {{ $schedule->end_time ?: '-' }}</div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        @if($item['attendance_done'])
                            <x-status-badge status="sudah absensi kelas">Sudah Absensi</x-status-badge>
                        @else
                            <x-status-badge status="belum absensi kelas">Belum Absensi</x-status-badge>
                        @endif

                        @if($item['journal_done'])
                            <x-status-badge status="sudah jurnal">Sudah Jurnal</x-status-badge>
                        @else
                            <x-status-badge status="belum jurnal">Belum Jurnal</x-status-badge>
                        @endif
                    </div>
                    <div class="d-grid gap-1">
                        <a class="btn btn-sm btn-outline-primary {{ ! $journalNotifications['checked_in_today'] ? 'disabled' : '' }}"
                           href="{{ route('teacher.class-attendance.index', ['teaching_schedule_id' => $schedule->id, 'date' => now()->toDateString()]) }}">
                            Isi Absensi Kelas
                        </a>
                        @if($session && $session->teachingJournal)
                            <a class="btn btn-sm btn-outline-success {{ ! $journalNotifications['checked_in_today'] ? 'disabled' : '' }}" href="{{ route('teacher.teaching-journals.edit', $session->teachingJournal) }}">Edit Jurnal</a>
                        @elseif($session)
                            <a class="btn btn-sm btn-success {{ ! $journalNotifications['checked_in_today'] ? 'disabled' : '' }}" href="{{ route('teacher.teaching-journals.create', $session) }}">Isi Jurnal</a>
                        @else
                            <button class="btn btn-sm btn-outline-secondary" disabled>Isi Jurnal</button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state">Tidak ada jadwal mengajar hari ini.</div>
            @endforelse
        </div>

        <div class="table-responsive table-sticky d-none d-md-block">
            <table class="table table-sm align-middle">
                <thead>
                <tr>
                    <th>Jam Ke</th>
                    <th>Mapel</th>
                    <th>Kelas</th>
                    <th>Waktu</th>
                    <th>Status Absensi</th>
                    <th>Status Jurnal</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($todaySchedules as $item)
                    @php($schedule = $item['schedule'])
                    @php($session = $item['session'])
                    <tr>
                        <td>{{ $schedule->jam_ke }}</td>
                        <td>{{ $schedule->subject->name }}</td>
                        <td>{{ $schedule->class->name }}</td>
                        <td>{{ $schedule->start_time ?: '-' }} - {{ $schedule->end_time ?: '-' }}</td>
                        <td>
                            @if($item['attendance_done'])
                                <x-status-badge status="sudah absensi kelas">Sudah Absensi Kelas</x-status-badge>
                            @else
                                <x-status-badge status="belum absensi kelas">Belum Absensi Kelas</x-status-badge>
                            @endif
                        </td>
                        <td>
                            @if($item['journal_done'])
                                <x-status-badge status="sudah jurnal">Sudah Jurnal</x-status-badge>
                            @else
                                <x-status-badge status="belum jurnal">Belum Jurnal</x-status-badge>
                            @endif
                        </td>
                        <td class="d-flex gap-1 flex-wrap">
                            <a class="btn btn-sm btn-outline-primary {{ ! $journalNotifications['checked_in_today'] ? 'disabled' : '' }}"
                               href="{{ route('teacher.class-attendance.index', ['teaching_schedule_id' => $schedule->id, 'date' => now()->toDateString()]) }}">
                                Isi Absensi Kelas
                            </a>
                            @if($session && $session->teachingJournal)
                                <a class="btn btn-sm btn-outline-success {{ ! $journalNotifications['checked_in_today'] ? 'disabled' : '' }}" href="{{ route('teacher.teaching-journals.edit', $session->teachingJournal) }}">Edit Jurnal</a>
                            @elseif($session)
                                <a class="btn btn-sm btn-success {{ ! $journalNotifications['checked_in_today'] ? 'disabled' : '' }}" href="{{ route('teacher.teaching-journals.create', $session) }}">Isi Jurnal</a>
                            @else
                                <button class="btn btn-sm btn-outline-secondary" disabled>Isi Jurnal</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><div class="empty-state">Tidak ada jadwal mengajar hari ini.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-panel>

    <x-panel title="Kalender Bulanan Saya" class="mb-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="fw-semibold">{{ $calendarWidget['month_label'] }}</div>
            <div class="d-flex gap-2">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('teacher.dashboard', ['calendar_year' => $calendarWidget['prev']['year'], 'calendar_month' => $calendarWidget['prev']['month']]) }}"><i class="bi bi-chevron-left"></i></a>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('teacher.dashboard', ['calendar_year' => $calendarWidget['next']['year'], 'calendar_month' => $calendarWidget['next']['month']]) }}"><i class="bi bi-chevron-right"></i></a>
            </div>
        </div>

        <div class="month-calendar-grid">
            @foreach($calendarWidget['weekdays'] as $weekday)
                <div class="month-calendar-weekday">{{ $weekday }}</div>
            @endforeach
            @foreach($calendarWidget['weeks'] as $week)
                @foreach($week as $cell)
                    <div class="month-calendar-cell {{ ! $cell['is_current_month'] ? 'is-muted' : '' }} {{ $cell['is_today'] ? 'is-today' : '' }}">
                        <div class="month-calendar-date">{{ $cell['date']->day }}</div>
                        <div class="month-calendar-events">
                            @foreach($cell['events']->take(2) as $event)
                                <div class="month-event-chip" style="--event-color: {{ $event['color'] }};" title="{{ $event['title'] }}">{{ $event['label'] }}: {{ $event['title'] }}</div>
                            @endforeach

                            @foreach($cell['schedules']->take(2) as $schedule)
                                <div class="month-schedule-chip" title="Jam {{ $schedule['jam_ke'] }} {{ $schedule['subject'] }} Kelas {{ $schedule['class'] }}">
                                    J{{ $schedule['jam_ke'] }} • {{ $schedule['subject'] }} ({{ $schedule['class'] }})
                                </div>
                            @endforeach

                            @if($cell['events']->isEmpty() && $cell['schedules']->isEmpty())
                                <div class="month-event-empty">-</div>
                            @endif

                            @php($extraCount = max(0, $cell['events']->count() - 2) + max(0, $cell['schedules']->count() - 2))
                            @if($extraCount > 0)
                                <div class="month-event-more">+{{ $extraCount }} item</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
    </x-panel>

    @if($homeroom)
        <x-panel title="Dashboard Wali Kelas - Kelas {{ $homeroom['class']->name }}" class="mb-3">
            @php($todayTotal = $homeroom['today_hadir'] + $homeroom['today_sakit'] + $homeroom['today_izin'] + $homeroom['today_alpa'])
            @php($monthlyTotal = $homeroom['monthly_hadir'] + $homeroom['monthly_izin'] + $homeroom['monthly_sakit'] + $homeroom['monthly_alpa'])

            <x-panel title="Kehadiran Kelas Hari Ini" class="mb-3">
                @if($todayTotal > 0)
                    <div class="row g-3">
                        <div class="col-6 col-md-3"><x-stat-card label="Hadir Hari Ini" :value="$homeroom['today_hadir']" icon="bi-person-check" variant="success" /></div>
                        <div class="col-6 col-md-3"><x-stat-card label="Sakit Hari Ini" :value="$homeroom['today_sakit']" icon="bi-heart-pulse" variant="warning" /></div>
                        <div class="col-6 col-md-3"><x-stat-card label="Izin Hari Ini" :value="$homeroom['today_izin']" icon="bi-envelope-check" variant="info" /></div>
                        <div class="col-6 col-md-3"><x-stat-card label="Alpa Hari Ini" :value="$homeroom['today_alpa']" icon="bi-person-x" variant="danger" /></div>
                    </div>
                @else
                    <div class="text-center py-4 text-secondary">
                        <i class="bi bi-inbox d-block fs-3 mb-2"></i>
                        Belum ada absensi siswa hari ini.
                    </div>
                @endif
            </x-panel>

            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <x-panel title="Chart Kehadiran Bulan Ini">
                        @if($monthlyTotal > 0)
                            <div style="height: 260px;">
                                <canvas id="homeroomMonthlyStatusChart"></canvas>
                            </div>
                        @else
                            <div class="text-center py-4 text-secondary">
                                <i class="bi bi-bar-chart d-block fs-3 mb-2"></i>
                                Belum ada data kehadiran bulan ini.
                            </div>
                        @endif
                    </x-panel>
                </div>
                <div class="col-12 col-lg-6">
                    <x-panel title="Top 5 Siswa Paling Sering Tidak Hadir">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Siswa</th>
                                    <th>Izin</th>
                                    <th>Sakit</th>
                                    <th>Alpa</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($homeroom['top_absent_students'] as $student)
                                    <tr>
                                        <td>{{ $student['student_name'] }}</td>
                                        <td>{{ $student['izin_count'] }}</td>
                                        <td>{{ $student['sakit_count'] }}</td>
                                        <td>{{ $student['alpa_count'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-secondary text-center py-4">Belum ada data ketidakhadiran.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-panel>
                </div>
                <div class="col-12">
                    <x-panel title="Izin Siswa Terbaru">
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Nama Siswa</th>
                                    <th>Tanggal</th>
                                    <th>Alasan</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($homeroom['recent_permissions'] as $permission)
                                    <tr>
                                        <td>{{ $permission->student?->full_name ?? '-' }}</td>
                                        <td>{{ $permission->date_from?->format('d-m-Y') }} s/d {{ $permission->date_to?->format('d-m-Y') }}</td>
                                        <td>{{ $permission->reason }}</td>
                                        <td><x-status-badge :status="$permission->status">{{ ucfirst($permission->status) }}</x-status-badge></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-secondary text-center py-4">Belum ada pengajuan izin terbaru.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-panel>
                </div>
            </div>
        </x-panel>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const monthlyStatusCtx = document.getElementById('homeroomMonthlyStatusChart');
            if (monthlyStatusCtx) {
                new Chart(monthlyStatusCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Hadir', 'Izin', 'Sakit', 'Alpa'],
                        datasets: [{
                            label: 'Jumlah',
                            data: [
                                {{ $homeroom['monthly_hadir'] }},
                                {{ $homeroom['monthly_izin'] }},
                                {{ $homeroom['monthly_sakit'] }},
                                {{ $homeroom['monthly_alpa'] }},
                            ],
                            backgroundColor: ['#198754', '#0dcaf0', '#ffc107', '#dc3545'],
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                    }
                });
            }
        </script>
    @endif
    </div>

    <div class="modal fade" id="checkinModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('teacher.attendance.checkin') }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Absen Masuk</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="lat" id="ci_lat"><input type="hidden" name="lng" id="ci_lng"><input type="hidden" name="accuracy" id="ci_accuracy">
                    <button type="button" class="btn btn-outline-secondary btn-action-lg" id="btnCheckinLocation">Ambil Lokasi</button>
                    <div id="checkinGpsStatus" class="small mt-2 text-secondary"></div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary btn-action-lg" type="submit" id="btnCheckinSubmit" disabled>Absen Masuk</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="checkoutModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="{{ route('teacher.attendance.checkout') }}">
                @csrf
                <div class="modal-header"><h5 class="modal-title">Absen Pulang</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="lat" id="co_lat"><input type="hidden" name="lng" id="co_lng"><input type="hidden" name="accuracy" id="co_accuracy">
                    <button type="button" class="btn btn-outline-secondary btn-action-lg" id="btnCheckoutLocation">Ambil Lokasi</button>
                    <div id="checkoutGpsStatus" class="small mt-2 text-secondary"></div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary btn-action-lg" type="submit" id="btnCheckoutSubmit" disabled>Absen Pulang</button></div>
            </form>
        </div>
    </div>

    <script>
        function bindGps(buttonId, statusId, latId, lngId, accId, submitId) {
            document.getElementById(buttonId).addEventListener('click', function () {
                const status = document.getElementById(statusId);
                if (!navigator.geolocation) {
                    status.textContent = 'Browser tidak mendukung geolocation.';
                    return;
                }
                status.textContent = 'Mengambil lokasi...';
                navigator.geolocation.getCurrentPosition(function (pos) {
                    document.getElementById(latId).value = pos.coords.latitude;
                    document.getElementById(lngId).value = pos.coords.longitude;
                    document.getElementById(accId).value = pos.coords.accuracy;
                    document.getElementById(submitId).disabled = false;
                    status.textContent = 'Lokasi berhasil diambil.';
                }, function () {
                    status.textContent = 'Akses lokasi ditolak atau gagal.';
                }, { enableHighAccuracy: true, timeout: 10000 });
            });
        }
        bindGps('btnCheckinLocation', 'checkinGpsStatus', 'ci_lat', 'ci_lng', 'ci_accuracy', 'btnCheckinSubmit');
        bindGps('btnCheckoutLocation', 'checkoutGpsStatus', 'co_lat', 'co_lng', 'co_accuracy', 'btnCheckoutSubmit');
    </script>
</x-layouts.app>




