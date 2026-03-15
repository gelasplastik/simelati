<x-layouts.app :title="'Modul Ajar Guru'" :pageTitle="'Review Modul Ajar Guru'" :breadcrumb="'Admin / Modul Ajar Guru'">
    <x-panel class="mb-3">
        <form method="GET" action="{{ route('admin.teaching-modules.index') }}" class="row g-2">
            <div class="col-md-2">
                <select class="form-select" name="teacher_id">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(request('teacher_id') == $teacher->id)>{{ $teacher->user?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="subject_id">
                    <option value="">Semua Mapel</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="class_id">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" name="academic_year" class="form-control" placeholder="Tahun Ajaran" value="{{ request('academic_year') }}">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="semester">
                    <option value="">Semua Semester</option>
                    <option value="ganjil" @selected(request('semester') === 'ganjil')>Ganjil</option>
                    <option value="genap" @selected(request('semester') === 'genap')>Genap</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">Semua Status</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="submitted" @selected(request('status') === 'submitted')>Submitted</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                </select>
            </div>
            <div class="col-md-12">
                <button class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.teaching-modules.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </x-panel>

    <x-panel>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                <tr>
                    <th>Guru</th>
                    <th>Mapel</th>
                    <th>Kelas</th>
                    <th>Tahun / Semester</th>
                    <th>Judul</th>
                    <th>Status</th>
                    <th>File</th>
                    <th>Catatan Guru</th>
                    <th>Catatan Admin</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->teacher?->user?->name ?? '-' }}</td>
                        <td>{{ $item->subject?->name ?? '-' }}</td>
                        <td>{{ $item->class?->name ?? '-' }}</td>
                        <td>{{ $item->academic_year }} / {{ ucfirst($item->semester) }}</td>
                        <td>{{ $item->title }}</td>
                        <td><x-status-badge :status="$item->status">{{ strtoupper($item->status) }}</x-status-badge></td>
                        <td><a href="{{ asset('storage/'.$item->file_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Buka File</a></td>
                        <td class="small">{{ $item->teacher_notes ?: '-' }}</td>
                        <td class="small">{{ $item->admin_notes ?: '-' }}</td>
                        <td>
                            @if($item->status === 'submitted' || $item->status === 'rejected')
                                <form method="POST" action="{{ route('admin.teaching-modules.review', $item) }}" class="mb-1">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <input type="hidden" name="admin_notes" value="{{ $item->admin_notes }}">
                                    <button class="btn btn-sm btn-success w-100">Setujui</button>
                                </form>
                                <form method="POST" action="{{ route('admin.teaching-modules.review', $item) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <input type="text" name="admin_notes" class="form-control form-control-sm mb-1" placeholder="Catatan penolakan" required>
                                    <button class="btn btn-sm btn-danger w-100">Tolak</button>
                                </form>
                            @else
                                <span class="text-secondary small">Sudah approved</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-secondary">Belum ada modul ajar.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
