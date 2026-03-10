<x-layouts.app :title="'Students'" :pageTitle="'Students'" :breadcrumb="'Admin / Master Data / Students'">
    <div class="mb-3 d-flex justify-content-end">
        <a href="{{ route('admin.students.import.index') }}" class="btn btn-outline-success">
            <i class="bi bi-upload me-1"></i>Import Siswa
        </a>
    </div>
    <x-panel title="Tambah Siswa" class="mb-3">
        <form method="POST" action="{{ route('admin.students.store') }}" class="row g-2">
            @csrf
            <div class="col-md-2"><input class="form-control" name="nisn" placeholder="NISN" value="{{ old('nisn') }}" required></div>
            <div class="col-md-2"><input class="form-control" name="nis" placeholder="NIS (Opsional)" value="{{ old('nis') }}"></div>
            <div class="col-md-3"><input class="form-control" name="full_name" placeholder="Nama Lengkap" value="{{ old('full_name') }}" required></div>
            <div class="col-md-3"><select class="form-select" name="class_id"><option value="">Pilih Kelas</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></div>
            <div class="col-md-2 d-flex align-items-center"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><label class="form-check-label">Aktif</label></div></div>
            <div class="col-md-12"><button class="btn btn-primary">Simpan</button></div>
        </form>
    </x-panel>

    <x-panel>
        <table class="table table-sm align-middle">
            <thead><tr><th>NISN</th><th>NIS</th><th>Nama</th><th>Kelas</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->nisn }}</td><td>{{ $item->nis ?: '-' }}</td><td>{{ $item->full_name }}</td><td>{{ $item->class?->name }}</td>
                    <td>{!! $item->is_active ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>' !!}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.students.update', $item) }}" class="d-flex gap-1">
                            @csrf @method('PUT')
                            <select class="form-select form-select-sm" name="class_id" style="max-width:130px;">
                                <option value="">Kelas</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" @selected($item->class_id===$class->id)>{{ $class->name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="nisn" value="{{ $item->nisn }}">
                            <input type="hidden" name="nis" value="{{ $item->nis }}">
                            <input type="hidden" name="full_name" value="{{ $item->full_name }}">
                            <input type="hidden" name="is_active" value="{{ $item->is_active ? 1 : 0 }}">
                            <button class="btn btn-sm btn-outline-primary">Update</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
