<x-layouts.app :title="'Permintaan Izin Pengisian Susulan'" :pageTitle="'Permintaan Izin Pengisian Susulan'" :breadcrumb="'Teacher / Permintaan Izin'">
    <div class="mb-3"><a class="btn btn-primary" href="{{ route('teacher.late-entry-requests.create') }}">Ajukan Izin</a></div>
    <x-panel>
        <table class="table table-sm align-middle">
            <thead><tr><th>Tanggal</th><th>Kelas</th><th>Mapel</th><th>Jam Ke</th><th>Jenis</th><th>Alasan</th><th>Status</th><th>Dibuat Pada</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->date->format('d-m-Y') }}</td>
                    <td>{{ $item->class->name }}</td>
                    <td>{{ $item->subject->name }}</td>
                    <td>{{ $item->jam_ke }}</td>
                    <td>{{ strtoupper($item->request_type) }}</td>
                    <td>{{ $item->reason }}</td>
                    <td><span class="badge text-bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : 'secondary') }} text-uppercase">{{ $item->status }}</span></td>
                    <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-secondary">Belum ada permintaan.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
