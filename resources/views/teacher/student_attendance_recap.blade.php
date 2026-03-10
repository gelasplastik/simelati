<x-layouts.app :title="'Rekap Kehadiran Siswa'" :pageTitle="'Rekap Kehadiran Siswa'" :breadcrumb="'Teacher / Rekap Kehadiran Siswa'">
    <x-panel class="mb-3">
        <form method="GET" action="{{ route('teacher.student-attendance-recap') }}" class="row g-2">
            <div class="col-md-4">
                <input class="form-control" value="Kelas {{ $class->name }}" readonly>
            </div>
            <div class="col-md-2"><input class="form-control" type="number" name="month" min="1" max="12" value="{{ $month }}" required></div>
            <div class="col-md-2"><input class="form-control" type="number" name="year" min="2024" max="2100" value="{{ $year }}" required></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Tampilkan</button></div>
        </form>
    </x-panel>

    <div class="row g-3 mb-3">
        <div class="col-md-4"><x-panel><div class="small text-secondary">Total Siswa</div><div class="h5 mb-0">{{ $recap['summary']['total_students'] }}</div></x-panel></div>
        <div class="col-md-4"><x-panel><div class="small text-secondary">Rata-rata Kehadiran</div><div class="h5 mb-0">{{ $recap['summary']['avg_percentage'] }}%</div></x-panel></div>
        <div class="col-md-4"><x-panel><div class="small text-secondary">Siswa Pernah Tidak Hadir</div><div class="h5 mb-0">{{ $recap['summary']['most_absent_students_count'] }}</div></x-panel></div>
    </div>

    <x-panel>
        <table class="table table-sm align-middle">
            <thead><tr><th>Siswa</th><th>Hadir</th><th>Izin</th><th>Sakit</th><th>Alpa</th><th>% Kehadiran</th></tr></thead>
            <tbody>
            @foreach($recap['rows'] as $row)
                <tr>
                    <td>{{ $row['student']->full_name }}</td>
                    <td>{{ $row['hadir'] }}</td>
                    <td>{{ $row['izin'] }}</td>
                    <td>{{ $row['sakit'] }}</td>
                    <td>{{ $row['alpa'] }}</td>
                    <td>{{ $row['percentage'] }}%</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-panel>
</x-layouts.app>
