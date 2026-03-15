<x-layouts.app :title="'Ajukan Izin Guru'" :pageTitle="'Ajukan Izin Guru'" :breadcrumb="'Teacher / Izin Guru / Create'">
    <x-panel>
        <form method="POST" action="{{ route('teacher.leave-requests.store') }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="date_from" class="form-control" value="{{ old('date_from') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="date_to" class="form-control" value="{{ old('date_to') }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">Alasan Izin</label>
                <textarea name="reason" class="form-control" rows="4" required>{{ old('reason') }}</textarea>
            </div>

            <div class="col-md-4">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" value="1" id="affects_schedule" name="affects_teaching_schedule" @checked(old('affects_teaching_schedule'))>
                    <label class="form-check-label" for="affects_schedule">
                        Affects Teaching Schedule
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Usulan Guru Pengganti (Opsional)</label>
                <select class="form-select" name="proposed_substitute_teacher_id">
                    <option value="">-- Tidak ada usulan --</option>
                    @foreach($substituteTeachers as $subTeacher)
                        <option value="{{ $subTeacher->id }}" @selected((string)old('proposed_substitute_teacher_id') === (string)$subTeacher->id)>{{ $subTeacher->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Catatan Coverage</label>
                <input type="text" name="coverage_notes" class="form-control" value="{{ old('coverage_notes') }}" placeholder="Catatan tambahan pengganti">
            </div>

            <div class="col-12">
                <label class="form-label">Lampiran / Foto Pendukung</label>
                <input type="file" name="attachment" class="form-control" accept=".png,.jpg,.jpeg,application/pdf" capture="environment">
                <small class="text-secondary">Bisa foto langsung dari HP atau upload dari galeri/dokumen. Maks 10MB. Opsional.</small>
            </div>
            <div class="col-12">
                <button class="btn btn-primary btn-action-lg" type="submit">Kirim Izin Guru</button>
                <a class="btn btn-outline-secondary" href="{{ route('teacher.leave-requests.index') }}">Kembali</a>
            </div>
        </form>
    </x-panel>
</x-layouts.app>


