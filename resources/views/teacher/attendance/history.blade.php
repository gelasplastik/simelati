<x-layouts.app :title="'Riwayat Absen Guru'" :pageTitle="'Absen Guru (History)'" :breadcrumb="'Teacher / Attendance History'">
    <x-panel>
        <div class="table-responsive table-sticky" style="max-height:70vh;">
            <table class="table table-sm align-middle">
                <thead>
                <tr><th>Tanggal</th><th>Check-in</th><th>Jarak</th><th>Terlambat</th><th>Check-out</th></tr>
                </thead>
                <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->date->format('d-m-Y') }}</td>
                        <td>{{ optional($item->checkin_at)->format('H:i:s') }}</td>
                        <td>{{ $item->checkin_distance }} m</td>
                        <td>{!! $item->is_late ? '<span class="badge text-bg-warning">Ya</span>' : '<span class="badge text-bg-success">Tidak</span>' !!}</td>
                        <td>{{ optional($item->checkout_at)->format('H:i:s') ?? '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
