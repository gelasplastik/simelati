<x-layouts.app :title="'Tugas Guru Pengganti'" :pageTitle="'Tugas Guru Pengganti'" :breadcrumb="'Teacher / Tugas Pengganti'">
    <x-panel class="mb-3" title="Tugas Hari Ini">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead><tr><th>Tanggal</th><th>Kelas</th><th>Mapel</th><th>Jam Ke</th><th>Guru Asli</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($todayTasks as $item)
                    <tr>
                        <td>{{ $item->date->format('d-m-Y') }}</td>
                        <td>{{ $item->class->name }}</td>
                        <td>{{ $item->subject->name }}</td>
                        <td>{{ $item->jam_ke }}</td>
                        <td>{{ $item->originalTeacher->user->name }}</td>
                        <td><span class="badge text-bg-{{ $item->status === 'completed' ? 'success' : 'warning' }} text-uppercase">{{ $item->status }}</span></td>
                        <td>
                            <a class="btn btn-sm btn-primary" href="{{ route('teacher.class-attendance.index', ['substitute_assignment_id' => $item->id]) }}">Isi Absensi</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-secondary">Tidak ada tugas guru pengganti hari ini.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-panel>

    <x-panel title="Tugas Mendatang">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead><tr><th>Tanggal</th><th>Kelas</th><th>Mapel</th><th>Jam Ke</th><th>Guru Asli</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($upcomingTasks as $item)
                    <tr>
                        <td>{{ $item->date->format('d-m-Y') }}</td>
                        <td>{{ $item->class->name }}</td>
                        <td>{{ $item->subject->name }}</td>
                        <td>{{ $item->jam_ke }}</td>
                        <td>{{ $item->originalTeacher->user->name }}</td>
                        <td><span class="badge text-bg-{{ $item->status === 'completed' ? 'success' : 'warning' }} text-uppercase">{{ $item->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-secondary">Tidak ada tugas pengganti mendatang.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $upcomingTasks->links() }}
    </x-panel>
</x-layouts.app>
