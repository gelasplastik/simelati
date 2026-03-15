<x-layouts.app :title="'Laporan Piket Harian'" :pageTitle="'Laporan Piket Harian'" :breadcrumb="'Teacher / Guru Piket / Laporan'">
    <x-panel class="mb-3" title="Informasi Laporan">
        <div class="row g-2 small">
            <div class="col-md-3"><strong>Tanggal:</strong> {{ $report->date->translatedFormat('l, d F Y') }}</div>
            <div class="col-md-3"><strong>Guru Piket:</strong> {{ $report->dutyTeacher?->user?->name ?: '-' }}</div>
            <div class="col-md-3"><strong>Status:</strong> <span class="badge text-bg-{{ $report->status === 'finalized' ? 'success' : 'warning' }}">{{ strtoupper($report->status) }}</span></div>
            <div class="col-md-3"><strong>Finalisasi:</strong> {{ $report->finalized_at?->format('d-m-Y H:i') ?: '-' }}</div>
        </div>
    </x-panel>

    @if($report->isFinalized())
        <div class="alert alert-success">Laporan ini sudah final. Data tidak dapat diubah dari akun guru piket.</div>
    @endif

    <div class="alert alert-info">
        <strong>Petunjuk Simpan:</strong>
        <ul class="mb-0 mt-2">
            <li>Anda <strong>tidak wajib absen guru dulu</strong> untuk simpan laporan piket.</li>
            <li>Pastikan data verifikasi guru dan rekap siswa sudah terisi angka yang benar.</li>
            <li>Jika status guru <strong>Izin/Sakit</strong> atau <strong>Tidak Hadir</strong>, kolom <strong>Alasan</strong> wajib diisi.</li>
            <li>Klik <strong>Simpan Draft</strong> untuk simpan sementara.</li>
            <li>Klik <strong>Finalisasi Laporan</strong> untuk mengunci laporan. Setelah final, guru tidak bisa edit lagi.</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('teacher.duty.report.update', $report) }}" id="dutyReportForm">
        @csrf
        @method('PATCH')
        <input type="hidden" name="action" id="duty_report_action" value="save">

        <x-panel class="mb-3" title="A. Kondisi Kehadiran Guru">
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Nama Guru</th>
                            <th>Mapel</th>
                            <th>Status Sistem</th>
                            <th>Verifikasi</th>
                            <th>Alasan</th>
                            <th>Pengganti</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($report->teacherRows as $idx => $row)
                        <tr>
                            <td>{{ $row->teacher_name }}</td>
                            <td>{{ $row->subject_label ?: '-' }}</td>
                            <td>{{ ucfirst($row->attendance_status) }}</td>
                            <td>
                                <input type="hidden" name="teacher_rows[{{ $idx }}][id]" value="{{ $row->id }}">
                                <select name="teacher_rows[{{ $idx }}][verified_status]" class="form-select form-select-sm" @disabled($report->isFinalized())>
                                    <option value="present" @selected(($row->verified_status ?? $row->attendance_status) === 'present')>Hadir</option>
                                    <option value="leave" @selected(($row->verified_status ?? $row->attendance_status) === 'leave')>Izin/Sakit</option>
                                    <option value="absent" @selected(($row->verified_status ?? $row->attendance_status) === 'absent')>Tidak Hadir</option>
                                </select>
                            </td>
                            <td><input type="text" name="teacher_rows[{{ $idx }}][reason]" class="form-control form-control-sm" value="{{ $row->reason }}" @disabled($report->isFinalized())></td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="teacher_rows[{{ $idx }}][has_substitute]" value="1" @checked($row->has_substitute) @disabled($report->isFinalized())>
                                    <label class="form-check-label small">{{ $row->substitute_teacher_name ?: 'Belum ada' }}</label>
                                </div>
                            </td>
                            <td><input type="text" name="teacher_rows[{{ $idx }}][notes]" class="form-control form-control-sm" value="{{ $row->notes }}" @disabled($report->isFinalized())></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary">Belum ada data guru.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-panel>

        <x-panel class="mb-3" title="B. Rekap Kehadiran Siswa per Kelas">
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Total</th>
                            <th>Hadir</th>
                            <th>Sakit</th>
                            <th>Izin</th>
                            <th>Alpa</th>
                            <th>Terlambat</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($report->studentRows as $idx => $row)
                        <tr>
                            <td>
                                {{ $row->class_name }}
                                <input type="hidden" name="student_rows[{{ $idx }}][id]" value="{{ $row->id }}">
                            </td>
                            <td><input type="number" min="0" class="form-control form-control-sm" name="student_rows[{{ $idx }}][total_students]" value="{{ $row->total_students }}" @disabled($report->isFinalized())></td>
                            <td><input type="number" min="0" class="form-control form-control-sm" name="student_rows[{{ $idx }}][present_count]" value="{{ $row->present_count }}" @disabled($report->isFinalized())></td>
                            <td><input type="number" min="0" class="form-control form-control-sm" name="student_rows[{{ $idx }}][sick_count]" value="{{ $row->sick_count }}" @disabled($report->isFinalized())></td>
                            <td><input type="number" min="0" class="form-control form-control-sm" name="student_rows[{{ $idx }}][izin_count]" value="{{ $row->izin_count }}" @disabled($report->isFinalized())></td>
                            <td><input type="number" min="0" class="form-control form-control-sm" name="student_rows[{{ $idx }}][alpa_count]" value="{{ $row->alpa_count }}" @disabled($report->isFinalized())></td>
                            <td><input type="number" min="0" class="form-control form-control-sm" name="student_rows[{{ $idx }}][late_count]" value="{{ $row->late_count }}" @disabled($report->isFinalized())></td>
                            <td><input type="text" class="form-control form-control-sm" name="student_rows[{{ $idx }}][notes]" value="{{ $row->notes }}" @disabled($report->isFinalized())></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-secondary">Belum ada data siswa.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-panel>

        <x-panel class="mb-3" title="Catatan Harian">
            <textarea name="notes" rows="3" class="form-control" placeholder="Catatan umum guru piket..." @disabled($report->isFinalized())>{{ old('notes', $report->notes) }}</textarea>
            <div class="mt-3 d-flex gap-2 flex-wrap">
                @if(!$report->isFinalized())
                    <button type="submit" class="btn btn-success" id="btn-save-draft">Simpan Draft</button>
                    <button type="submit" class="btn btn-primary" id="btn-finalize" onclick="return confirm('Finalisasi laporan ini? Setelah final, akun guru tidak bisa mengubah lagi.')">Finalisasi Laporan</button>
                @endif
                <a href="{{ route('teacher.duty.report.print', $report) }}" target="_blank" class="btn btn-outline-secondary">Print Laporan</a>
            </div>
        </x-panel>
    </form>

    @if(!$report->isFinalized())
        <script>
            (() => {
                const actionInput = document.getElementById('duty_report_action');
                const saveBtn = document.getElementById('btn-save-draft');
                const finalizeBtn = document.getElementById('btn-finalize');

                if (saveBtn) {
                    saveBtn.addEventListener('click', () => {
                        actionInput.value = 'save';
                    });
                }

                if (finalizeBtn) {
                    finalizeBtn.addEventListener('click', () => {
                        actionInput.value = 'finalize';
                    });
                }
            })();
        </script>
    @endif
</x-layouts.app>
