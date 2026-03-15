<x-layouts.app :title="'Modul Ajar Guru'" :pageTitle="'Modul Ajar / Perangkat Ajar'" :breadcrumb="'Teacher / Modul Ajar'">
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('teacher.modules.create') }}" class="btn btn-primary">
            <i class="bi bi-upload me-1"></i> Upload Modul
        </a>
    </div>

    <x-panel>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                <tr>
                    <th>Mapel</th>
                    <th>Kelas</th>
                    <th>Tahun Ajaran</th>
                    <th>Semester</th>
                    <th>Judul</th>
                    <th>Status</th>
                    <th>Upload</th>
                    <th>Catatan Admin</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->subject?->name ?? '-' }}</td>
                        <td>{{ $item->class?->name ?? '-' }}</td>
                        <td>{{ $item->academic_year }}</td>
                        <td class="text-capitalize">{{ $item->semester }}</td>
                        <td>{{ $item->title }}</td>
                        <td><x-status-badge :status="$item->status">{{ strtoupper($item->status) }}</x-status-badge></td>
                        <td>{{ $item->uploaded_at?->format('d-m-Y H:i') ?? '-' }}</td>
                        <td>
                            @if($item->status === 'rejected' && $item->admin_notes)
                                <span class="text-danger small">{{ $item->admin_notes }}</span>
                            @elseif($item->admin_notes)
                                <span class="small">{{ $item->admin_notes }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ asset('storage/'.$item->file_path) }}" target="_blank">Lihat File</a>
                                @if($item->canTeacherEdit())
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('teacher.modules.edit', $item) }}">Edit</a>
                                    <form method="POST" action="{{ route('teacher.modules.destroy', $item) }}" onsubmit="return confirm('Hapus modul ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                @endif
                                @if(in_array($item->status, ['rejected', 'draft'], true))
                                    <form method="POST" action="{{ route('teacher.modules.resubmit', $item) }}" onsubmit="return confirm('Kirim ulang modul ini untuk direview admin?')">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Resubmit</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-secondary">Belum ada modul ajar yang diupload.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
