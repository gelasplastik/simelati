<x-layouts.app :title="'Absensi Kelas'" :pageTitle="'Absensi Kelas'" :breadcrumb="'Teacher / Absensi Kelas'">
    @if($selectedSchedule)
        <div class="alert alert-info">
            Mengisi dari jadwal resmi: Jam {{ $selectedSchedule->jam_ke }} - {{ $selectedSchedule->subject->name }} - Kelas {{ $selectedSchedule->class->name }}.
        </div>
    @endif

    <x-panel title="Filter Absensi" class="mb-3">
        <form class="row g-2" method="GET" action="{{ route('teacher.class-attendance.index') }}">
            <input type="hidden" name="teaching_schedule_id" value="{{ $selectedScheduleId }}">
            <div class="col-md-3"><label class="form-label">Tanggal</label><input class="form-control" type="date" name="date" value="{{ $selectedDate }}" required></div>
            <div class="col-md-3">
                <label class="form-label">Kelas</label>
                <select class="form-select" name="class_id" required>
                    <option value="">Pilih Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected((string)$selectedClass === (string)$class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Mapel</label>
                <select class="form-select" name="subject_id" required>
                    <option value="">Pilih Mapel</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((string)$selectedSubject === (string)$subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><label class="form-label">Jam Ke</label><input class="form-control" type="number" min="1" max="12" name="jam_ke" value="{{ $selectedJamKe }}" required></div>
            <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary btn-action-lg w-100">Load</button></div>
        </form>
    </x-panel>

    @if($hasOverride)
        <div class="alert alert-warning">Pengisian susulan diizinkan oleh admin.</div>
    @endif

    @if(session('saved_session_id'))
        <div class="alert alert-success d-flex justify-content-between align-items-center">
            <span>Absensi berhasil disimpan.</span>
            <a class="btn btn-sm btn-primary" href="{{ route('teacher.teaching-journals.create', session('saved_session_id')) }}">Lanjut Isi Jurnal</a>
        </div>
    @endif

    @if($students->count())
        <form method="POST" action="{{ route('teacher.class-attendance.store') }}">
            @csrf
            <input type="hidden" name="class_id" value="{{ $selectedClass }}">
            <input type="hidden" name="subject_id" value="{{ $selectedSubject }}">
            <input type="hidden" name="teaching_schedule_id" value="{{ $selectedScheduleId }}">
            <input type="hidden" name="date" value="{{ $selectedDate }}">
            <input type="hidden" name="jam_ke" value="{{ $selectedJamKe }}">

            <x-panel class="mb-3">
                <div class="row g-2 small text-secondary mb-2 session-meta">
                    <div class="col-md-3"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($selectedDate)->format('d-m-Y') }}</div>
                    <div class="col-md-3"><strong>Kelas:</strong> {{ optional($classes->firstWhere('id', $selectedClass))->name }}</div>
                    <div class="col-md-3"><strong>Mapel:</strong> {{ optional($subjects->firstWhere('id', $selectedSubject))->name }}</div>
                    <div class="col-md-3"><strong>Jam Ke:</strong> {{ $selectedJamKe }}</div>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <strong>Daftar Siswa</strong>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="setAllHadir">Set semua hadir</button>
                </div>
                <div class="table-responsive table-sticky table-responsive-mobile-cards">
                    <table class="table align-middle">
                        <thead><tr><th>Nama Siswa</th><th>Status</th><th>Note</th></tr></thead>
                        <tbody>
                        @foreach($students as $i => $student)
                            <tr class="{{ $student->is_auto ? 'auto-izin-row' : '' }}">
                                <td data-label="Nama Siswa">
                                    {{ $student->full_name }}
                                    @if($student->is_auto)
                                        <x-status-badge status="izin" class="ms-1">Auto Izin</x-status-badge>
                                    @endif
                                    <input type="hidden" name="rows[{{ $i }}][student_id]" value="{{ $student->id }}">
                                </td>
                                <td data-label="Status">
                                    <select class="form-select status-select" name="rows[{{ $i }}][status]">
                                        @foreach(['hadir','izin','sakit','alpa'] as $status)
                                            <option value="{{ $status }}" @selected($status === $student->auto_status)>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td data-label="Catatan"><input class="form-control" name="rows[{{ $i }}][note]" value="{{ $student->auto_note }}" placeholder="Catatan"></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </x-panel>

            <div class="sticky-mobile-action mt-3 rounded-3 d-flex gap-2">
                <button class="btn btn-primary btn-action-lg w-100" type="submit">Simpan Absensi</button>
            </div>
        </form>

        <script>
            document.getElementById('setAllHadir').addEventListener('click', function () {
                document.querySelectorAll('tr').forEach(function (row) {
                    if (row.classList.contains('auto-izin-row')) return;
                    const select = row.querySelector('.status-select');
                    if (select) select.value = 'hadir';
                });
            });
        </script>
    @endif
</x-layouts.app>
