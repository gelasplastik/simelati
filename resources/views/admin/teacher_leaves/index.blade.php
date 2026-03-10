<x-layouts.app :title="'Izin Guru'" :pageTitle="'Izin Guru'" :breadcrumb="'Admin / Izin Guru'">
    <x-panel class="mb-3">
        <form method="GET" action="{{ route('admin.teacher-leave-requests.index') }}" class="row g-2">
            <div class="col-md-3">
                <select class="form-select" name="teacher_id">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(request('teacher_id') == $teacher->id)>{{ $teacher->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">Semua Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>PENDING</option>
                    <option value="approved" @selected(request('status') === 'approved')>APPROVED</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>REJECTED</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}"></div>
            <div class="col-md-2"><input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
        </form>
    </x-panel>

    <x-panel>
        <table class="table table-sm align-middle">
            <thead>
            <tr>
                <th>Guru</th>
                <th>Tanggal</th>
                <th>Alasan</th>
                <th>Status</th>
                <th>Lampiran</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse($items as $item)
                @php($path = $item->attachment_path)
                @php($isImage = $path && preg_match('/\.(jpg|jpeg|png|webp)$/i', $path))
                <tr>
                    <td>{{ $item->teacher->user->name }}</td>
                    <td>{{ $item->date_from->format('d-m-Y') }} s/d {{ $item->date_to->format('d-m-Y') }}</td>
                    <td>{{ $item->reason }}</td>
                    <td><span class="badge text-bg-secondary text-uppercase">{{ $item->status }}</span></td>
                    <td>
                        @if($path)
                            @if($isImage)
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ asset('storage/'.$path) }}" alt="Lampiran" style="width:40px;height:40px;object-fit:cover;" class="rounded border">
                                    <a href="{{ asset('storage/'.$path) }}" target="_blank">Lihat Lampiran</a>
                                </div>
                            @else
                                <a href="{{ asset('storage/'.$path) }}" target="_blank">Lihat Lampiran</a>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="d-flex gap-1">
                        @if($item->status === 'pending')
                            <form method="POST" action="{{ route('admin.teacher-leave-requests.review', $item) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="approved">
                                <button class="btn btn-sm btn-success">Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('admin.teacher-leave-requests.review', $item) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="rejected">
                                <button class="btn btn-sm btn-danger">Tolak</button>
                            </form>
                        @else
                            <span class="text-secondary small">Sudah diproses</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary">Belum ada data izin guru.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
