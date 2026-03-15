@php($user = auth()->user())
<div class="brand-block d-flex align-items-center gap-2 mb-3 pb-2">
    <img src="{{ asset('assets/logo/simelati-logo.png') }}" alt="Logo SIMELATI" class="brand-logo">
    <div>
        <strong class="d-block">SIMELATI</strong>
        <div class="small text-secondary">Sistem Informasi Manajemen SD Plus Melati</div>
    </div>
</div>

<ul class="nav nav-pills flex-column gap-1 sidebar-nav">
    @if($user->role === 'admin')
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.dashboard') }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
        <li class="text-uppercase small text-secondary mt-2">Master Data</li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.teachers.*') }}" href="{{ route('admin.teachers.index') }}"><i class="bi bi-person-badge me-2"></i>Guru</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.students.*') }}" href="{{ route('admin.students.index') }}"><i class="bi bi-people me-2"></i>Siswa</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.students.import.*') }}" href="{{ route('admin.students.import.index') }}"><i class="bi bi-upload me-2"></i>Import Siswa</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.parents.*') }}" href="{{ route('admin.parents.index') }}"><i class="bi bi-person-hearts me-2"></i>Orang Tua</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.classes.*') }}" href="{{ route('admin.classes.index') }}"><i class="bi bi-grid me-2"></i>Kelas</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.subjects.*') }}" href="{{ route('admin.subjects.index') }}"><i class="bi bi-journal-text me-2"></i>Mapel</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.assignments.*') }}" href="{{ route('admin.assignments.index') }}"><i class="bi bi-diagram-3 me-2"></i>Assignment</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.teaching-schedules.*') }}" href="{{ route('admin.teaching-schedules.index') }}"><i class="bi bi-calendar3 me-2"></i>Jadwal Mengajar</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.teaching-schedules.profiles*') }}" href="{{ route('admin.teaching-schedules.profiles') }}"><i class="bi bi-collection me-2"></i>Profil Jadwal</a></li>

        <li class="text-uppercase small text-secondary mt-2">Permintaan</li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.late-entry-requests.*') }}" href="{{ route('admin.late-entry-requests.index') }}"><i class="bi bi-hourglass-split me-2"></i>Permintaan Absensi/Jurnal</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.teacher-leave-requests.*') }}" href="{{ route('admin.teacher-leave-requests.index') }}"><i class="bi bi-calendar2-check me-2"></i>Izin Guru</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.teaching-modules.*') }}" href="{{ route('admin.teaching-modules.index') }}"><i class="bi bi-folder2-open me-2"></i>Modul Ajar Guru</a></li>

        <li class="text-uppercase small text-secondary mt-2">Guru Piket</li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.duty-assignments.*') }}" href="{{ route('admin.duty-assignments.index') }}"><i class="bi bi-person-lines-fill me-2"></i>Penugasan Piket</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.duty-reports.*') }}" href="{{ route('admin.duty-reports.index') }}"><i class="bi bi-file-earmark-text me-2"></i>Laporan Harian</a></li>

        <li class="text-uppercase small text-secondary mt-2">Laporan</li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.reports.attendance') }}" href="{{ route('admin.reports.attendance') }}"><i class="bi bi-geo-alt me-2"></i>Kehadiran Guru</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.reports.journals') }}" href="{{ route('admin.reports.journals') }}"><i class="bi bi-journal-richtext me-2"></i>Jurnal Mengajar</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.reports.class-attendance') }}" href="{{ route('admin.reports.class-attendance') }}"><i class="bi bi-clipboard-check me-2"></i>Absensi Kelas</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.reports.student-attendance-recap') }}" href="{{ route('admin.reports.student-attendance-recap') }}"><i class="bi bi-bar-chart me-2"></i>Rekap Kehadiran Siswa</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.reports.kpi') }}" href="{{ route('admin.reports.kpi') }}"><i class="bi bi-trophy me-2"></i>KPI</a></li>

        <li class="text-uppercase small text-secondary mt-2">Pengaturan</li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.settings.*') }}" href="{{ route('admin.settings.edit') }}"><i class="bi bi-gear me-2"></i>Setting Absen</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.calendar.*') }}" href="{{ route('admin.calendar.index') }}"><i class="bi bi-calendar-week me-2"></i>Kalender Akademik</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('admin.overrides.*') }}" href="{{ route('admin.overrides.index') }}"><i class="bi bi-shield-check me-2"></i>Session Override</a></li>
    @elseif($user->role === 'teacher')
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('teacher.dashboard') }}" href="{{ route('teacher.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('teacher.attendance.history') }}" href="{{ route('teacher.attendance.history') }}"><i class="bi bi-geo me-2"></i>Absen Guru</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('teacher.class-attendance.*') }}" href="{{ route('teacher.class-attendance.index') }}"><i class="bi bi-clipboard-data me-2"></i>Absensi Kelas</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive(['teacher.journals.*']) }}" href="{{ route('teacher.journals.index') }}"><i class="bi bi-journal-plus me-2"></i>Jurnal Mengajar</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('teacher.teaching-journals.history') }}" href="{{ route('teacher.teaching-journals.history') }}"><i class="bi bi-journal-check me-2"></i>Riwayat Jurnal</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('teacher.substitute-tasks.*') }}" href="{{ route('teacher.substitute-tasks.index') }}"><i class="bi bi-person-workspace me-2"></i>Tugas Pengganti</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('teacher.modules.*') }}" href="{{ route('teacher.modules.index') }}"><i class="bi bi-folder2-open me-2"></i>Modul Ajar</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('teacher.duty.*') }}" href="{{ route('teacher.duty.dashboard') }}"><i class="bi bi-person-lines-fill me-2"></i>Guru Piket</a></li>
        @if($user->teacher?->homeroomClasses()->where('is_active', true)->exists())
            <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('teacher.student-attendance-recap') }}" href="{{ route('teacher.student-attendance-recap') }}"><i class="bi bi-bar-chart me-2"></i>Rekap Kehadiran Siswa</a></li>
        @endif
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('teacher.leave-requests.*') }}" href="{{ route('teacher.leave-requests.index') }}"><i class="bi bi-calendar2-plus me-2"></i>Izin Guru</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('teacher.late-entry-requests.*') }}" href="{{ route('teacher.late-entry-requests.index') }}"><i class="bi bi-hourglass-split me-2"></i>Permintaan Absensi/Jurnal</a></li>
    @else
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('parent.dashboard') }}" href="{{ route('parent.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('parent.permissions.index') }}" href="{{ route('parent.permissions.index') }}"><i class="bi bi-clock-history me-2"></i>Izin Siswa (Riwayat)</a></li>
        <li class="nav-item"><a class="nav-link {{ \App\Support\MenuHelper::isActive('parent.permissions.create') }}" href="{{ route('parent.permissions.create') }}"><i class="bi bi-send-plus me-2"></i>Ajukan Izin</a></li>
    @endif
</ul>
