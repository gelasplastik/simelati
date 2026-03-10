<x-layouts.app :title="'Izin Guru'" :pageTitle="'Izin Guru (History)'" :breadcrumb="'Teacher / Izin Guru'">
    <x-panel class="mb-3 d-flex justify-content-end">
        <a href="{{ route('teacher.leave-requests.create') }}" class="btn btn-primary">Ajukan Izin Guru</a>
    </x-panel>

    <x-panel>
        <table class="table table-sm align-middle">
            <thead>
            <tr>
                <th>Tanggal</th>
                <th>Alasan</th>
                <th>Status</th>
                <th>Lampiran</th>
                <th>Dibuat</th>
            </tr>
            </thead>
            <tbody>
            @forelse($items as $item)
                @php($path = $item->attachment_path)
                @php($isImage = $path && preg_match('/\.(jpg|jpeg|png|webp)$/i', $path))
                <tr>
                    <td>{{ $item->date_from->format('d-m-Y') }} s/d {{ $item->date_to->format('d-m-Y') }}</td>
                    <td>{{ $item->reason }}</td>
                    <td><span class="badge text-bg-secondary text-uppercase">{{ $item->status }}</span></td>
                    <td>
                        @if($path)
                            @if($isImage)
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ asset('storage/'.$path) }}" alt="Lampiran" style="width:40px;height:40px;object-fit:cover;" class="rounded border">
                                    <a href="{{ asset('storage/'.$path) }}" target="_blank">Lihat Lampiran</a>
                                </div>
                            @else
                                <a href="{{ asset('storage/'.$path) }}" target="_blank">Lihat Lampiran</a>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-secondary">Belum ada izin guru.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
