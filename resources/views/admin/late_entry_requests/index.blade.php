<x-layouts.app :title="'Permintaan Absensi/Jurnal Susulan'" :pageTitle="'Permintaan Absensi/Jurnal Susulan'" :breadcrumb="'Admin / Permintaan Absensi/Jurnal Susulan'">
    <x-panel class="mb-3">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><select class="form-select" name="teacher_id"><option value="">Semua Guru</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected(request('teacher_id')==$teacher->id)>{{ $teacher->user->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><select class="form-select" name="status"><option value="">Semua Status</option><option value="pending" @selected(request('status')==='pending')>Pending</option><option value="approved" @selected(request('status')==='approved')>Approved</option><option value="rejected" @selected(request('status')==='rejected')>Rejected</option></select></div>
            <div class="col-md-2"><input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}"></div>
            <div class="col-md-2"><input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}"></div>
            <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary w-100">Filter</button><a class="btn btn-outline-success" href="{{ route('admin.late-entry-requests.export') }}">CSV</a></div>
        </form>
    </x-panel>

    <x-panel>
        <table class="table table-sm align-middle">
            <thead><tr><th>Guru</th><th>Tanggal</th><th>Kelas</th><th>Mapel</th><th>Jam Ke</th><th>Jenis</th><th>Alasan</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->teacher->user->name }}</td>
                    <td>{{ $item->date->format('d-m-Y') }}</td>
                    <td>{{ $item->class->name }}</td>
                    <td>{{ $item->subject->name }}</td>
                    <td>{{ $item->jam_ke }}</td>
                    <td>{{ strtoupper($item->request_type) }}</td>
                    <td>{{ $item->reason }}</td>
                    <td><span class="badge text-bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : 'secondary') }} text-uppercase">{{ $item->status }}</span></td>
                    <td>
                        @if($item->status === 'pending')
                            <form method="POST" action="{{ route('admin.late-entry-requests.approve', $item) }}" class="mb-1">@csrf @method('PATCH')<input class="form-control form-control-sm mb-1" name="review_notes" placeholder="Catatan review"><button class="btn btn-sm btn-success w-100">Setujui</button></form>
                            <form method="POST" action="{{ route('admin.late-entry-requests.reject', $item) }}">@csrf @method('PATCH')<input class="form-control form-control-sm mb-1" name="review_notes" placeholder="Catatan review"><button class="btn btn-sm btn-danger w-100">Tolak</button></form>
                        @else
                            <small>{{ $item->review_notes ?? '-' }}</small>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-secondary">Tidak ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>

