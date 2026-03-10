<x-layouts.app :title="'Laporan Izin Siswa'" :pageTitle="'Report: Student Permissions'" :breadcrumb="'Admin / Reports / Student Permissions'">
    <x-panel>
        <table class="table table-sm align-middle">
            <thead>
            <tr>
                <th>Siswa</th>
                <th>NISN</th>
                <th>Kelas</th>
                <th>Pengaju</th>
                <th>Kontak</th>
                <th>Dari</th>
                <th>Sampai</th>
                <th>Status</th>
                <th>Lampiran</th>
            </tr>
            </thead>
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
                    <td>{{ $item->date_from->format('d-m-Y') }}</td>
                    <td>{{ $item->date_to->format('d-m-Y') }}</td>
                    <td>{{ strtoupper($item->status) }}</td>
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
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
