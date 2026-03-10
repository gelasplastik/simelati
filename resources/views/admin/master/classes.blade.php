<x-layouts.app :title="'Classes'" :pageTitle="'Classes'" :breadcrumb="'Admin / Master Data / Classes'">
    <x-panel title="Tambah Kelas" class="mb-3">
        <form method="POST" action="{{ route('admin.classes.store') }}" class="row g-2">
            @csrf
            <div class="col-md-3"><input class="form-control" name="name" placeholder="Nama Kelas" required></div>
            <div class="col-md-4">
                <select class="form-select" name="homeroom_teacher_id">
                    <option value="">Tanpa Wali Kelas</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-center"><div class="form-check"><input type="checkbox" name="is_active" value="1" checked class="form-check-input"><label class="form-check-label">Aktif</label></div></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Simpan</button></div>
        </form>
    </x-panel>

    <x-panel>
        <table class="table table-sm align-middle">
            <thead><tr><th>Kelas</th><th>Status</th><th>Wali Kelas</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{!! $item->is_active ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>' !!}</td>
                    <td>{{ $item->homeroomTeacher?->user?->name ?? '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.classes.update', $item) }}" class="row g-1">
                            @csrf @method('PUT')
                            <div class="col-md-3"><input class="form-control form-control-sm" name="name" value="{{ $item->name }}" required></div>
                            <div class="col-md-4"><select class="form-select form-select-sm" name="homeroom_teacher_id"><option value="">Tanpa Wali</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected($item->homeroom_teacher_id===$teacher->id)>{{ $teacher->user->name }}</option>@endforeach</select></div>
                            <div class="col-md-3 d-flex align-items-center"><div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" @checked($item->is_active)><label class="form-check-label">Aktif</label></div></div>
                            <div class="col-md-2"><button class="btn btn-sm btn-outline-primary w-100">Update</button></div>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
