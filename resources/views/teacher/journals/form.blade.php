<x-layouts.app :title="'Isi Jurnal Mengajar'" :pageTitle="'Isi Jurnal Mengajar'" :breadcrumb="'Teacher / Jurnal Mengajar / Form'">
    <x-panel class="mb-3" title="Jadwal Hari Ini">
        <div class="row g-2 small">
            <div class="col-md-3"><strong>Tanggal:</strong><br>{{ \Carbon\Carbon::parse($session->date)->translatedFormat('l, d-m-Y') }}</div>
            <div class="col-md-2"><strong>Jam Ke:</strong><br>{{ $session->jam_ke }}</div>
            <div class="col-md-3"><strong>Kelas:</strong><br>{{ $session->class->name }}</div>
            <div class="col-md-4"><strong>Mapel:</strong><br>{{ $session->subject->name }}</div>
        </div>
    </x-panel>

    <x-panel>
        <form method="POST" action="{{ route('teacher.journals.store', $schedule) }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-12">
                <label class="form-label">Materi Pembelajaran</label>
                <textarea class="form-control" rows="4" name="materi" required>{{ old('materi', $journal?->materi) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Catatan Peserta Didik</label>
                <textarea class="form-control" rows="3" name="student_notes">{{ old('student_notes', $journal?->student_notes) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Peserta Didik Tidak Hadir (Auto)</label>
                <input class="form-control" type="text" value="{{ $absentText }}" readonly>
            </div>
            <div class="col-12">
                <label class="form-label">Lampiran (Opsional)</label>
                <input type="file" class="form-control" name="attachment" accept=".png,.jpg,.jpeg,application/pdf" capture="environment">
                <small class="text-secondary">Bisa foto langsung dari HP atau upload dari galeri/dokumen. Maks 10MB. Opsional.</small>
                @if($journal?->attachment_path)
                    <div class="mt-2">
                        <a href="{{ asset('storage/'.$journal->attachment_path) }}" target="_blank">Lihat Lampiran Saat Ini</a>
                    </div>
                @endif
            </div>
            <div class="col-12 d-grid d-md-flex gap-2">
                <button class="btn btn-primary btn-action-lg" type="submit">{{ $journal ? 'Update Jurnal' : 'Simpan Jurnal' }}</button>
                <a href="{{ route('teacher.journals.index') }}" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </form>
    </x-panel>
</x-layouts.app>



