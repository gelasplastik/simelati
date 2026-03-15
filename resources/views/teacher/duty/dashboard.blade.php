<x-layouts.app :title="'Dashboard Guru Piket'" :pageTitle="'Dashboard Guru Piket'" :breadcrumb="'Teacher / Guru Piket'">
    @if(!$isAssigned)
        <x-panel title="Status Penugasan">
            <div class="alert alert-info mb-0">
                Anda belum ditugaskan sebagai guru piket hari ini.
                @if($assignedTeachers->count())
                    Guru piket hari ini: {{ $assignedTeachers->pluck('teacher.user.name')->implode(', ') }}.
                @endif
            </div>
        </x-panel>
    @else
        <div class="row g-3 mb-3">
            <div class="col-md-4 col-xl-2"><x-stat-card label="Total Guru" :value="$dashboard['teacher']['total_teachers']" icon="bi-people" variant="primary"/></div>
            <div class="col-md-4 col-xl-2"><x-stat-card label="Guru Hadir" :value="$dashboard['teacher']['present_teachers']" icon="bi-person-check" variant="success"/></div>
            <div class="col-md-4 col-xl-2"><x-stat-card label="Guru Izin/Sakit" :value="$dashboard['teacher']['leave_teachers']" icon="bi-person-exclamation" variant="warning"/></div>
            <div class="col-md-4 col-xl-2"><x-stat-card label="Guru Tidak Hadir" :value="$dashboard['teacher']['absent_teachers']" icon="bi-person-x" variant="danger"/></div>
            <div class="col-md-4 col-xl-2"><x-stat-card label="Sudah Ada Pengganti" :value="$dashboard['teacher']['covered_absent_teachers']" icon="bi-person-workspace" variant="info"/></div>
            <div class="col-md-4 col-xl-2"><x-stat-card label="Belum Ada Pengganti" :value="$dashboard['teacher']['uncovered_absent_teachers']" icon="bi-exclamation-triangle" variant="danger"/></div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4 col-xl-2"><x-stat-card label="Total Kelas" :value="$dashboard['student']['total_classes']" icon="bi-grid" variant="primary"/></div>
            <div class="col-md-4 col-xl-2"><x-stat-card label="Total Siswa" :value="$dashboard['student']['total_students']" icon="bi-people-fill" variant="primary"/></div>
            <div class="col-md-4 col-xl-2"><x-stat-card label="Siswa Hadir" :value="$dashboard['student']['present_students']" icon="bi-emoji-smile" variant="success"/></div>
            <div class="col-md-4 col-xl-2"><x-stat-card label="Siswa Sakit" :value="$dashboard['student']['sick_students']" icon="bi-bandaid" variant="warning"/></div>
            <div class="col-md-4 col-xl-2"><x-stat-card label="Siswa Izin" :value="$dashboard['student']['izin_students']" icon="bi-envelope-check" variant="warning"/></div>
            <div class="col-md-4 col-xl-2"><x-stat-card label="Siswa Alpa" :value="$dashboard['student']['alpa_students']" icon="bi-emoji-frown" variant="danger"/></div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <x-panel title="Guru Tidak Hadir Hari Ini">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Guru</th><th>Status</th><th>Pengganti</th></tr></thead>
                            <tbody>
                            @forelse(collect($dashboard['teacher']['rows'])->where('attendance_status','absent') as $row)
                                <tr>
                                    <td>{{ $row['teacher_name'] }}</td>
                                    <td><span class="badge text-bg-danger">Tidak Hadir</span></td>
                                    <td>{{ $row['substitute_teacher_name'] ?: 'Belum ada' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-secondary">Tidak ada guru yang tidak hadir.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-panel>
            </div>
            <div class="col-lg-6">
                <x-panel title="Kelas Perlu Perhatian">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Kelas</th><th>Alpa</th><th>Terlambat</th></tr></thead>
                            <tbody>
                            @forelse($dashboard['classes_need_attention'] as $row)
                                <tr>
                                    <td>{{ $row['class_name'] }}</td>
                                    <td>{{ $row['alpa_count'] }}</td>
                                    <td>{{ $row['late_count'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-secondary">Tidak ada kelas yang perlu perhatian khusus.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-panel>
            </div>
        </div>

        <x-panel class="mt-3" title="Laporan Piket Harian">
            <p class="text-secondary mb-2">Laporan untuk {{ now()->translatedFormat('l, d F Y') }} status: <strong>{{ strtoupper($report->status) }}</strong>.</p>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('teacher.duty.report.edit', $report) }}" class="btn btn-primary">{{ $report->isFinalized() ? 'Lihat Laporan' : 'Isi / Verifikasi Laporan' }}</a>
                <a href="{{ route('teacher.duty.report.print', $report) }}" target="_blank" class="btn btn-outline-secondary">Print Laporan</a>
            </div>
        </x-panel>
    @endif
</x-layouts.app>
