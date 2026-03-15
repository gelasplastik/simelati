<x-layouts.app :title="'Penugasan Guru Piket'" :pageTitle="'Penugasan Guru Piket'" :breadcrumb="'Admin / Guru Piket / Penugasan'">
    <x-panel class="mb-3" title="Tambah Penugasan Guru Piket (Mingguan)">
        <form method="POST" action="{{ route('admin.duty-assignments.store') }}" class="row g-3">
            @csrf
            <div class="col-md-3">
                <label class="form-label">Hari</label>
                <select name="day_of_week" class="form-select" required>
                    <option value="">Pilih Hari</option>
                    @foreach($dayOptions as $key => $label)
                        <option value="{{ $key }}" @selected(old('day_of_week') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">Guru</label>
                <select class="form-select" name="teacher_id" required>
                    <option value="">Pilih Guru</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Catatan</label>
                <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Opsional">
            </div>
            <div class="col-12">
                <button class="btn btn-success">Simpan Penugasan</button>
            </div>
        </form>
    </x-panel>

    <x-panel title="Daftar Penugasan Guru Piket">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-sm-4 col-md-3">
                <select name="day_of_week" class="form-select">
                    <option value="">Semua Hari</option>
                    @foreach($dayOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('day_of_week') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-4 col-md-2">
                <button class="btn btn-outline-secondary w-100">Filter</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Hari</th>
                        <th>Guru Piket</th>
                        <th>Catatan</th>
                        <th>Ditugaskan Oleh</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $dayOptions[$item->day_of_week] ?? ($item->date?->translatedFormat('l') ?? '-') }}</td>
                            <td>{{ $item->teacher->user->name }}</td>
                            <td>{{ $item->notes ?: '-' }}</td>
                            <td>{{ $item->assignedBy?->name ?: '-' }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.duty-assignments.destroy', $item) }}" onsubmit="return confirm('Hapus penugasan ini?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary">Belum ada penugasan guru piket.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
