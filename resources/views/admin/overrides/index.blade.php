<x-layouts.app :title="'Session Override'" :pageTitle="'Session Override Management'" :breadcrumb="'Admin / Session Override'">
    <x-panel class="mb-3">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><select class="form-select" name="teacher_id"><option value="">Semua Guru</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected(request('teacher_id')==$teacher->id)>{{ $teacher->user->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><select class="form-select" name="class_id"><option value="">Semua Kelas</option>@foreach($classes as $class)<option value="{{ $class->id }}" @selected(request('class_id')==$class->id)>{{ $class->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><input class="form-control" type="date" name="date" value="{{ request('date') }}"></div>
            <div class="col-md-3"><button class="btn btn-primary">Filter</button></div>
        </form>
    </x-panel>

    <x-panel>
        <table class="table table-sm align-middle">
            <thead><tr><th>Sesi</th><th>Override</th><th>Reason</th><th>Expiry</th><th>By</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->date->format('d-m-Y') }} | {{ $item->class->name }} | {{ $item->subject->name }} | Jam {{ $item->jam_ke }}<br><small class="text-secondary">{{ $item->teacher->user->name }}</small></td>
                    <td>{!! $item->hasValidOverride() ? '<span class="badge text-bg-success">Aktif</span>' : ($item->override_allowed ? '<span class="badge text-bg-warning">Expired</span>' : '<span class="badge text-bg-secondary">Off</span>') !!}</td>
                    <td>{{ $item->override_reason ?? '-' }}</td>
                    <td>{{ $item->override_expires_at?->format('d-m-Y H:i') ?? '-' }}</td>
                    <td>{{ $item->overrideAllowedBy?->name ?? '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.overrides.update', $item) }}" class="row g-1">
                            @csrf @method('PUT')
                            <div class="col-12"><input class="form-control form-control-sm" name="override_reason" value="{{ $item->override_reason }}" placeholder="Alasan override"></div>
                            <div class="col-12"><input class="form-control form-control-sm" type="datetime-local" name="override_expires_at" value="{{ $item->override_expires_at?->format('Y-m-d\\TH:i') }}"></div>
                            <div class="col-12 d-flex gap-1">
                                <button class="btn btn-sm btn-outline-success" name="override_allowed" value="1">Enable</button>
                                <button class="btn btn-sm btn-outline-danger" name="override_allowed" value="0">Disable</button>
                            </div>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
