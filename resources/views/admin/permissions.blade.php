<x-layouts.app :title="'Permissions'" :pageTitle="'Izin Siswa'" :breadcrumb="'Admin / Permissions'">
    <x-panel>
        <table class="table table-sm align-middle">
            <thead><tr><th>Siswa</th><th>NISN</th><th>Kelas</th><th>Pengaju</th><th>Kontak</th><th>Tanggal</th><th>Status</th><th>Lampiran</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach($items as $item)
                @php($path = $item->effective_attachment_path)
                @php($isImage = $path && preg_match('/\.(jpg|jpeg|png|webp)$/i', $path))
                <tr>
                    <td>{{ $item->student->full_name }}</td>
                    <td>{{ $item->student->nisn }}</td>
                    <td>{{ $item->student->class?->name }}</td>
                    <td>{{ $item->submitter_name ?: ($item->parent?->user?->name ?: '-') }}</td>
                    <td>
                        {{ $item->submitter_phone ?: '-' }}
                        <div class="small text-secondary">{{ $item->submitter_relationship ?: '-' }}</div>
                    </td>
                    <td>{{ $item->date_from->format('d-m-Y') }} - {{ $item->date_to->format('d-m-Y') }}</td>
                    <td><span class="badge text-bg-secondary text-uppercase">{{ $item->status }}</span></td>
                    <td>
                        @if($path)
                            @if($isImage)
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ asset('storage/'.$path) }}" alt="Lampiran" style="width:36px;height:36px;object-fit:cover;" class="rounded border">
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
                        <form method="POST" action="{{ route('admin.permissions.approve', $item) }}">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                        <form method="POST" action="{{ route('admin.permissions.reject', $item) }}">@csrf<button class="btn btn-sm btn-danger">Reject</button></form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
