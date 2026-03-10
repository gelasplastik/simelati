<x-layouts.app :title="'Izin Siswa'" :pageTitle="'Izin Siswa (History)'" :breadcrumb="'Parent / Permissions'">
    <x-panel>
        <table class="table table-sm">
            <thead><tr><th>Siswa</th><th>Tanggal</th><th>Alasan</th><th>Status</th><th>Lampiran</th></tr></thead>
            <tbody>
            @foreach($items as $item)
                @php($path = $item->effective_attachment_path)
                @php($isImage = $path && preg_match('/\.(jpg|jpeg|png|webp)$/i', $path))
                <tr>
                    <td>{{ $item->student->full_name }}</td>
                    <td>{{ $item->date_from->format('d-m-Y') }} s/d {{ $item->date_to->format('d-m-Y') }}</td>
                    <td>{{ $item->reason }}</td>
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
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
