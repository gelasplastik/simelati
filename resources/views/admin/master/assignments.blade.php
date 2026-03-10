<x-layouts.app :title="'Assignments'" :pageTitle="'Assignments'" :breadcrumb="'Admin / Master Data / Assignments'">
    <x-panel title="Tambah Assignment" class="mb-3">
        <form method="POST" action="{{ route('admin.assignments.store') }}" class="row g-2">
            @csrf
            <div class="col-md-4"><select class="form-select" name="teacher_id" required><option value="">Pilih Guru</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->user->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><select class="form-select" name="subject_id" required><option value="">Pilih Mapel</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><select class="form-select" name="class_id" required><option value="">Pilih Kelas</option>@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Simpan</button></div>
        </form>
    </x-panel>

    <x-panel>
        <table class="table table-sm align-middle">
            <thead><tr><th>Guru</th><th>Mapel</th><th>Kelas</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item->teacher->user->name }}</td>
                    <td>{{ $item->subject->name }}</td>
                    <td>{{ $item->class->name }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.assignments.update', $item) }}" class="d-inline-flex gap-1 me-1">@csrf @method('PUT')
                            <select class="form-select form-select-sm" name="teacher_id">@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected($item->teacher_id===$teacher->id)>{{ $teacher->user->name }}</option>@endforeach</select>
                            <select class="form-select form-select-sm" name="subject_id">@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected($item->subject_id===$subject->id)>{{ $subject->name }}</option>@endforeach</select>
                            <select class="form-select form-select-sm" name="class_id">@foreach($classes as $class)<option value="{{ $class->id }}" @selected($item->class_id===$class->id)>{{ $class->name }}</option>@endforeach</select>
                            <button class="btn btn-sm btn-outline-primary">Update</button>
                        </form>
                        <form method="POST" action="{{ route('admin.assignments.destroy', $item) }}" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button></form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
