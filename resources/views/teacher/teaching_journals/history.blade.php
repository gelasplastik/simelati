<x-layouts.app :title="'Riwayat Jurnal Mengajar'" :pageTitle="'Riwayat Jurnal'" :breadcrumb="'Teacher / Riwayat Jurnal'">
    <x-panel>
        <table class="table table-sm align-middle">
            <thead><tr><th>Tanggal</th><th>Kelas</th><th>Mapel</th><th>Jam Ke</th><th>Pertemuan</th><th>Materi</th><th>Lampiran</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                @php($path = $item->attachment_path)
                @php($isImage = $path && preg_match('/\.(jpg|jpeg|png|webp)$/i', $path))
                <tr>
                    <td>{{ $item->date->format('d-m-Y') }}</td>
                    <td>{{ $item->class->name }}</td>
                    <td>{{ $item->subject->name }}</td>
                    <td>{{ $item->jam_ke }}</td>
                    <td>{{ $item->pertemuan_ke }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($item->materi, 80) }}</td>
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
                    <td><a class="btn btn-sm btn-outline-primary" href="{{ route('teacher.teaching-journals.edit', $item) }}">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-secondary">Belum ada jurnal.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
