@php
    $firstAssignment = $assignmentOptions->first();
    $defaultPair = $firstAssignment ? ($firstAssignment->subject_id.'|'.$firstAssignment->class_id) : '';

    $selectedPair = old('assignment_pair');
    if (! $selectedPair && isset($module)) {
        $selectedPair = $module->subject_id.'|'.$module->class_id;
    }
    if (! $selectedPair) {
        $selectedPair = $defaultPair;
    }

    $academicYears = [];
    for ($year = 2021; $year <= 2030; $year++) {
        $academicYears[] = $year.'/'.($year + 1);
    }

    $currentAcademicYear = date('Y').'/'.(date('Y') + 1);
    if (! in_array($currentAcademicYear, $academicYears, true)) {
        $currentAcademicYear = $academicYears[count($academicYears) - 1];
    }

    $selectedAcademicYear = old('academic_year', $module->academic_year ?? $currentAcademicYear);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Mapel + Kelas (Sesuai Assignment)</label>
        <select class="form-select" id="assignment_pair" name="assignment_pair" required>
            @foreach($assignmentOptions as $assignment)
                @php($pair = $assignment->subject_id.'|'.$assignment->class_id)
                <option value="{{ $pair }}" @selected($selectedPair === $pair)>
                    {{ $assignment->subject?->name }} - Kelas {{ $assignment->class?->name }}
                </option>
            @endforeach
        </select>
        <input type="hidden" name="subject_id" id="subject_id" value="{{ old('subject_id', $module->subject_id ?? '') }}">
        <input type="hidden" name="class_id" id="class_id" value="{{ old('class_id', $module->class_id ?? '') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Tahun Ajaran</label>
        <select name="academic_year" class="form-select" required>
            @foreach($academicYears as $yearOption)
                <option value="{{ $yearOption }}" @selected($selectedAcademicYear === $yearOption)>{{ $yearOption }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Semester</label>
        <select name="semester" class="form-select" required>
            <option value="ganjil" @selected(old('semester', $module->semester ?? '') === 'ganjil')>Ganjil</option>
            <option value="genap" @selected(old('semester', $module->semester ?? '') === 'genap')>Genap</option>
        </select>
    </div>

    <div class="col-12">
        <label class="form-label">Judul Modul</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $module->title ?? '') }}" required>
    </div>

    <div class="col-12">
        <label class="form-label">Catatan Guru (Opsional)</label>
        <textarea name="teacher_notes" class="form-control" rows="3">{{ old('teacher_notes', $module->teacher_notes ?? '') }}</textarea>
    </div>

    <div class="col-12">
        <label class="form-label">File Modul (PDF / DOCX, maks 10MB)</label>
        <input type="file" name="file" class="form-control" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" {{ isset($module) ? '' : 'required' }}>
        <div class="form-text">Unggah dokumen modul ajar/perangkat ajar dalam format PDF atau DOCX.</div>
        @if(isset($module) && $module->file_path)
            <div class="mt-2 small">
                File saat ini: <a href="{{ asset('storage/'.$module->file_path) }}" target="_blank">Lihat file</a>
            </div>
        @endif
    </div>
</div>

<script>
    (function () {
        const pair = document.getElementById('assignment_pair');
        const subject = document.getElementById('subject_id');
        const klass = document.getElementById('class_id');

        function syncPair() {
            const value = pair.value || '';
            const parts = value.split('|');
            subject.value = parts[0] || '';
            klass.value = parts[1] || '';
        }

        syncPair();
        pair.addEventListener('change', syncPair);
    })();
</script>
