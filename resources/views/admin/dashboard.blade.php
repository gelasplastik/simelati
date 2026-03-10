<x-layouts.app :title="'Dashboard Analitik - SIMELATI'" :pageTitle="'Dashboard Analitik Sekolah'" :breadcrumb="'Admin / Dashboard Analitik'">
    <x-panel class="mb-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3 col-sm-6">
                <label class="form-label">Periode Grafik</label>
                <select class="form-select" name="period" onchange="this.form.submit()">
                    <option value="today" @selected($period==='today')>Hari Ini</option>
                    <option value="week" @selected($period==='week')>Minggu Ini</option>
                    <option value="month" @selected($period==='month')>Bulan Ini</option>
                </select>
            </div>
            <div class="col-md-9 col-sm-6">
                <div class="small text-secondary">Rentang data grafik: {{ $analytics['range_label'] }}</div>
            </div>
        </form>
    </x-panel>

    <div class="row g-3 mb-3">
        <div class="col-lg-2 col-md-4 col-6"><x-stat-card label="Guru Hadir Hari Ini" :value="$analytics['summary']['teacher_present_today']" icon="bi-person-check" variant="success" /></div>
        <div class="col-lg-2 col-md-4 col-6"><x-stat-card label="Guru Izin/Sakit" :value="$analytics['summary']['teacher_leave_today']" icon="bi-person-exclamation" variant="warning" /></div>
        <div class="col-lg-2 col-md-4 col-6"><x-stat-card label="Siswa Hadir Hari Ini" :value="$analytics['summary']['student_present_today']" icon="bi-emoji-smile" variant="primary" /></div>
        <div class="col-lg-2 col-md-4 col-6"><x-stat-card label="Siswa Tidak Hadir" :value="$analytics['summary']['student_absent_today']" icon="bi-emoji-frown" variant="danger" /></div>
        <div class="col-lg-2 col-md-4 col-6"><x-stat-card label="Jurnal Belum Diisi" :value="$analytics['summary']['journal_missing_today']" icon="bi-journal-x" variant="info" /></div>
        <div class="col-lg-2 col-md-4 col-6"><x-stat-card label="Permintaan Pending" :value="$analytics['summary']['pending_requests']" icon="bi-hourglass-split" variant="warning" /></div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <x-panel title="Grafik Kehadiran Guru">
                <div style="height: 260px;"><canvas id="teacherAttendanceChart"></canvas></div>
            </x-panel>
        </div>
        <div class="col-lg-6">
            <x-panel title="Grafik Kehadiran Siswa">
                <div style="height: 260px;"><canvas id="studentAttendanceChart"></canvas></div>
            </x-panel>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-4">
            <x-panel title="Guru Belum Isi Jurnal Hari Ini">
                <div class="table-responsive table-responsive-mobile-cards">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Nama Guru</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($disciplineToday['missing_journal_teachers'] as $teacher)
                            <tr>
                                <td data-label="Nama Guru">{{ $teacher->user->name }}</td>
                                <td data-label="Status"><x-status-badge status="belum jurnal">Belum Lengkap</x-status-badge></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-secondary">Semua jurnal sudah lengkap.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-panel>
        </div>

        <div class="col-lg-4">
            <x-panel title="Guru Telat Hari Ini">
                <div class="table-responsive table-responsive-mobile-cards">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Nama Guru</th><th>Jam Check-in</th><th>Keterlambatan</th></tr></thead>
                        <tbody>
                        @forelse($disciplineToday['late_teachers'] as $attendance)
                            <tr>
                                <td data-label="Nama Guru">{{ $attendance->teacher->user->name }}</td>
                                <td data-label="Jam Check-in">{{ optional($attendance->checkin_at)->format('H:i:s') }}</td>
                                <td data-label="Keterlambatan"><x-status-badge status="alpa">Terlambat</x-status-badge></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-secondary">Tidak ada guru telat.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-panel>
        </div>

        <div class="col-lg-4">
            <x-panel title="Guru Belum Absen Hari Ini">
                <div class="table-responsive table-responsive-mobile-cards">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Nama Guru</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($disciplineToday['not_checked_in_teachers'] as $teacher)
                            <tr>
                                <td data-label="Nama Guru">{{ $teacher->user->name }}</td>
                                <td data-label="Status"><x-status-badge status="pending">Belum Absen</x-status-badge></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-secondary">Semua guru sudah absen.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-panel>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <x-panel title="Kelas Perlu Perhatian">
                <div class="table-responsive table-responsive-mobile-cards">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Kelas</th><th>% Hadir</th><th>Alpa</th><th>Izin</th><th>Indikator</th></tr></thead>
                        <tbody>
                        @forelse($analytics['classes_attention'] as $row)
                            <tr>
                                <td data-label="Kelas">{{ $row->class_name }}</td>
                                <td data-label="% Hadir">{{ $row->attendance_pct }}%</td>
                                <td data-label="Alpa">{{ $row->alpa }}</td>
                                <td data-label="Izin">{{ $row->izin }}</td>
                                <td data-label="Indikator">
                                    @if(!empty($row->attention_notes))
                                        @foreach($row->attention_notes as $note)
                                            <div><x-status-badge status="pending">{{ $note }}</x-status-badge></div>
                                        @endforeach
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary">Belum ada data absensi kelas pada periode ini.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-panel>
        </div>

        <div class="col-lg-6">
            <x-panel title="Siswa dengan Ketidakhadiran Tinggi (Bulan Ini)">
                <div class="table-responsive table-responsive-mobile-cards">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Siswa</th><th>Kelas</th><th>Izin</th><th>Sakit</th><th>Alpa</th><th>Total</th></tr></thead>
                        <tbody>
                        @forelse($analytics['students_high_absence'] as $row)
                            <tr>
                                <td data-label="Siswa">{{ $row->full_name }}<div class="small text-secondary">NISN: {{ $row->nisn }}</div></td>
                                <td data-label="Kelas">{{ $row->class_name ?? '-' }}</td>
                                <td data-label="Izin">{{ $row->izin }}</td>
                                <td data-label="Sakit">{{ $row->sakit }}</td>
                                <td data-label="Alpa">{{ $row->alpa }}</td>
                                <td data-label="Total"><strong>{{ $row->total_absence }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-secondary">Belum ada data ketidakhadiran tinggi.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-panel>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <x-panel title="Aktivitas Terbaru">
                <div class="list-group list-group-flush">
                    @forelse($analytics['activity_feed'] as $item)
                        <div class="list-group-item d-flex justify-content-between align-items-start px-0">
                            <div>
                                <div class="fw-semibold">{{ $item['title'] }}</div>
                                <div class="small text-secondary">{{ $item['text'] }}</div>
                            </div>
                            <div class="text-end">
                                <x-status-badge :status="$item['status']" class="text-uppercase">{{ $item['status'] }}</x-status-badge>
                                <div class="small text-secondary mt-1">{{ $item['time']->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">Belum ada aktivitas terbaru.</div>
                    @endforelse
                </div>
            </x-panel>
        </div>

        <div class="col-lg-5">
            <x-panel title="Permintaan Override Terbaru">
                <div class="table-responsive table-responsive-mobile-cards">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Guru</th><th>Tanggal</th><th>Kelas</th><th>Mapel</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse($disciplineToday['latest_requests'] as $item)
                            <tr>
                                <td data-label="Guru">{{ $item->teacher->user->name }}</td>
                                <td data-label="Tanggal">{{ $item->date->format('d-m-Y') }}</td>
                                <td data-label="Kelas">{{ $item->class->name }}</td>
                                <td data-label="Mapel">{{ $item->subject->name }}</td>
                                <td data-label="Status"><x-status-badge :status="$item->status" class="text-uppercase">{{ $item->status }}</x-status-badge></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary">Belum ada permintaan override.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-panel>
        </div>
    </div>

    <x-panel title="Rekap Disiplin Guru Bulan Ini">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead><tr><th>Peringkat</th><th>Guru</th><th>Hari Hadir</th><th>Tepat Waktu</th><th>Absensi Kelas Lengkap</th><th>Jurnal Lengkap</th><th>Request Override</th><th>Skor Disiplin</th></tr></thead>
                <tbody>
                @forelse($disciplineRanking as $i => $row)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $row['teacher']->user->name }}</td>
                        <td>{{ $row['present'] }}</td>
                        <td>{{ $row['on_time'] }}</td>
                        <td>{{ $row['class_completion'] }}</td>
                        <td>{{ $row['journal_completion'] }}</td>
                        <td>{{ $row['override_requests'] }}</td>
                        <td><strong>{{ $row['discipline_score'] }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-secondary">Belum ada data disiplin guru bulan ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-panel>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const teacherCtx = document.getElementById('teacherAttendanceChart');
        if (teacherCtx) {
            new Chart(teacherCtx, {
                type: 'line',
                data: {
                    labels: @json($analytics['teacher_chart']['labels']),
                    datasets: [
                        { label: 'Hadir', data: @json($analytics['teacher_chart']['hadir']), borderColor: '#2E7D32', backgroundColor: 'rgba(46,125,50,.15)', tension: 0.35 },
                        { label: 'Izin/Sakit', data: @json($analytics['teacher_chart']['izin']), borderColor: '#FB8C00', backgroundColor: 'rgba(251,140,0,.15)', tension: 0.35 },
                        { label: 'Telat', data: @json($analytics['teacher_chart']['telat']), borderColor: '#E53935', backgroundColor: 'rgba(229,57,53,.15)', tension: 0.35 },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                },
            });
        }

        const studentCtx = document.getElementById('studentAttendanceChart');
        if (studentCtx) {
            new Chart(studentCtx, {
                type: 'bar',
                data: {
                    labels: @json($analytics['student_chart']['labels']),
                    datasets: [
                        { label: 'Hadir', data: @json($analytics['student_chart']['hadir']), backgroundColor: '#43A047' },
                        { label: 'Izin', data: @json($analytics['student_chart']['izin']), backgroundColor: '#FB8C00' },
                        { label: 'Sakit', data: @json($analytics['student_chart']['sakit']), backgroundColor: '#1E88E5' },
                        { label: 'Alpa', data: @json($analytics['student_chart']['alpa']), backgroundColor: '#E53935' },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { x: { stacked: true }, y: { stacked: true } },
                },
            });
        }
    </script>
</x-layouts.app>
