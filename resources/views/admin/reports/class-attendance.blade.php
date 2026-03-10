<x-layouts.app :title="'Laporan Absensi Kelas'" :pageTitle="'Report: Class Attendance'" :breadcrumb="'Admin / Reports / Class Attendance'">
    <x-panel>
        <table class="table table-sm">
            <thead><tr><th>Tanggal</th><th>Guru</th><th>Kelas</th><th>Subject</th><th>Rekap</th></tr></thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->date->format('d-m-Y') }}</td>
                    <td>{{ $item->teacher->user->name }}</td>
                    <td>{{ $item->class->name }}</td>
                    <td>{{ $item->subject?->name ?? '-' }}</td>
                    <td>
                        Hadir: {{ $item->attendances->where('status','hadir')->count() }},
                        Izin: {{ $item->attendances->where('status','izin')->count() }},
                        Sakit: {{ $item->attendances->where('status','sakit')->count() }},
                        Alpa: {{ $item->attendances->where('status','alpa')->count() }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
