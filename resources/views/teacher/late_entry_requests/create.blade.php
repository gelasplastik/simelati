<x-layouts.app :title="'Ajukan Permintaan Absensi/Jurnal Susulan'" :pageTitle="'Permintaan Absensi/Jurnal Susulan'" :breadcrumb="'Teacher / Permintaan Absensi/Jurnal / Create'">
    <x-panel>
        <form method="POST" action="{{ route('teacher.late-entry-requests.store') }}" class="row g-3">
            @csrf
            <div class="col-md-3"><label class="form-label">Tanggal</label><input type="date" class="form-control" name="date" value="{{ old('date', request('date')) }}" required></div>
            <div class="col-md-3">
                <label class="form-label">Kelas</label>
                <select class="form-select" name="class_id" required>
                    <option value="">Pilih Kelas</option>
                    @foreach($assignments as $assignment)
                        <option value="{{ $assignment->class_id }}" @selected((string)old('class_id', request('class_id')) === (string)$assignment->class_id)>{{ $assignment->class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Mapel</label>
                <select class="form-select" name="subject_id" required>
                    <option value="">Pilih Mapel</option>
                    @foreach($assignments as $assignment)
                        <option value="{{ $assignment->subject_id }}" @selected((string)old('subject_id', request('subject_id')) === (string)$assignment->subject_id)>{{ $assignment->subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Jam Ke</label><input type="number" class="form-control" name="jam_ke" min="1" max="12" value="{{ old('jam_ke', request('jam_ke', 1)) }}" required></div>
            <div class="col-md-4"><label class="form-label">Jenis Permintaan</label><select class="form-select" name="request_type" required><option value="attendance" @selected(old('request_type', request('request_type'))==='attendance')>Absensi</option><option value="journal" @selected(old('request_type', request('request_type'))==='journal')>Jurnal</option><option value="both" @selected(old('request_type', request('request_type','both'))==='both')>Keduanya</option></select></div>
            <div class="col-12"><label class="form-label">Alasan</label><textarea class="form-control" rows="4" name="reason" required>{{ old('reason') }}</textarea></div>
            <div class="col-12"><button class="btn btn-primary btn-action-lg">Kirim Permintaan</button></div>
        </form>
    </x-panel>
</x-layouts.app>

