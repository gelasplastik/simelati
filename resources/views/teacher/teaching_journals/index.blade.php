<x-layouts.app :title="'Jurnal Mengajar'" :pageTitle="'Jurnal Mengajar'" :breadcrumb="'Teacher / Jurnal Mengajar'">
    <x-panel>
        <table class="table table-sm align-middle">
            <thead><tr><th>Tanggal</th><th>Kelas</th><th>Mapel</th><th>Jam Ke</th><th>Status Jurnal</th><th>Lampiran</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($sessions as $session)
                <tr>
                    <td>{{ $session->date->format('d-m-Y') }}</td>
                    <td>{{ $session->class->name }}</td>
                    <td>{{ $session->subject->name }}</td>
                    <td>{{ $session->jam_ke }}</td>
                    <td>
                        @if($session->teachingJournal)
                            <span class="badge text-bg-success">Sudah Diisi</span>
                        @else
                            <span class="badge text-bg-secondary">Belum Diisi</span>
                        @endif
                    </td>
                    <td>
                        @if($session->teachingJournal?->attachment_path)
                            <a href="{{ asset('storage/'.$session->teachingJournal->attachment_path) }}" target="_blank">Lihat Lampiran</a>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($session->teachingJournal)
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('teacher.teaching-journals.edit', $session->teachingJournal) }}">Edit Jurnal</a>
                        @else
                            <a class="btn btn-sm btn-primary" href="{{ route('teacher.teaching-journals.create', $session) }}">Isi Jurnal</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-secondary">Belum ada sesi absensi kelas.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $sessions->links() }}
    </x-panel>
</x-layouts.app>
