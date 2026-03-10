<x-layouts.app :title="'Form Jurnal Mengajar'" :pageTitle="'Jurnal Mengajar'" :breadcrumb="'Teacher / Jurnal Mengajar / Form'">
    @if($hasOverride)
        <div class="alert alert-warning">Pengisian susulan diizinkan oleh admin.</div>
    @endif
    <x-panel class="mb-3" title="Data Sesi Jurnal">
        <div class="row g-2 small session-meta">
            <div class="col-md-3"><strong>Hari/Tanggal:</strong><br>{{ $session->date->translatedFormat('l, d-m-Y') }}</div>
            <div class="col-md-2"><strong>Jam Ke:</strong><br>{{ $session->jam_ke }}</div>
            <div class="col-md-3"><strong>Kelas:</strong><br>{{ $session->class->name }}</div>
            <div class="col-md-4"><strong>Mapel:</strong><br>{{ $session->subject->name }}</div>
        </div>
    </x-panel>

    <x-panel>
        <form method="POST" action="{{ $mode === 'edit' ? route('teacher.teaching-journals.update', $journal) : route('teacher.teaching-journals.store') }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            @if($mode === 'edit')
                @method('PUT')
            @else
                <input type="hidden" name="class_attendance_session_id" value="{{ $session->id }}">
            @endif

            <div class="col-md-4">
                <label class="form-label">Pertemuan Ke</label>
                <input type="number" min="1" class="form-control" name="pertemuan_ke" value="{{ old('pertemuan_ke', $journal?->pertemuan_ke) }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">Kompetensi Dasar / Materi Pembelajaran</label>
                <textarea class="form-control" rows="3" name="materi" required>{{ old('materi', $journal?->materi) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Peserta Didik yang Tidak Hadir (Auto)</label>
                <textarea class="form-control readonly-note" rows="2" readonly>{{ $absentText }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Catatan Peserta Didik</label>
                <textarea class="form-control" rows="3" name="student_notes">{{ old('student_notes', $journal?->student_notes) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Ketuntasan Materi</label>
                <textarea class="form-control" rows="3" name="mastery_notes">{{ old('mastery_notes', $journal?->mastery_notes) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Lampiran / Foto / Dokumen Pendukung</label>
                <input type="file" class="form-control" name="attachment" accept="image/*,application/pdf" capture="environment">
                <small class="text-secondary">Bisa foto langsung dari HP atau upload dari galeri/dokumen. Opsional.</small>
                @if($journal?->attachment_path)
                    <div class="mt-2">
                        @if(preg_match('/\.(jpg|jpeg|png|webp)$/i', $journal->attachment_path))
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset('storage/'.$journal->attachment_path) }}" alt="Lampiran" class="rounded border" style="width:48px;height:48px;object-fit:cover;">
                                <a href="{{ asset('storage/'.$journal->attachment_path) }}" target="_blank">Lihat Lampiran</a>
                            </div>
                        @else
                            <a href="{{ asset('storage/'.$journal->attachment_path) }}" target="_blank">Lihat Lampiran</a>
                        @endif
                    </div>
                @endif
            </div>
            <div class="col-12 d-grid d-md-flex gap-2">
                <button class="btn btn-primary btn-action-lg" type="submit">{{ $mode === 'edit' ? 'Update Jurnal' : 'Simpan Jurnal' }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('teacher.teaching-journals.history') }}">Riwayat Jurnal</a>
            </div>
        </form>
    </x-panel>
</x-layouts.app>
