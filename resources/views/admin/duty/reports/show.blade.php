<x-layouts.app :title="'Detail Laporan Piket'" :pageTitle="'Detail Laporan Piket'" :breadcrumb="'Admin / Guru Piket / Detail'">
    <x-panel class="mb-3" title="Informasi Laporan">
        <div class="row g-2 small">
            <div class="col-md-3"><strong>Tanggal:</strong> {{ $report->date->translatedFormat('l, d F Y') }}</div>
            <div class="col-md-3"><strong>Guru Piket:</strong> {{ $report->dutyTeacher?->user?->name ?: '-' }}</div>
            <div class="col-md-3"><strong>Status:</strong> <span class="badge text-bg-{{ $report->status === 'finalized' ? 'success' : 'warning' }}">{{ strtoupper($report->status) }}</span></div>
            <div class="col-md-3"><strong>Finalisasi:</strong> {{ $report->finalized_at?->format('d-m-Y H:i') ?: '-' }}</div>
        </div>
    </x-panel>

    <form method="POST" action="{{ route('admin.duty-reports.update', $report) }}">
        @csrf
        @method('PATCH')

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
                            <td>
                                {{ ucfirst($row->attendance_status) }}
                                @if($row->attendance_status === 'absent' && $row->verified_status === 'present')
                                    <div class="small text-danger fw-semibold mt-1">Diverifikasi hadir oleh guru piket, tetapi belum ada absen GPS.</div>
                                @endif
                            </td>
                            <td>
                                <input type="hidden" name="teacher_rows[{{ $idx }}][id]" value="{{ $row->id }}">
                                <select name="teacher_rows[{{ $idx }}][verified_status]" class="form-select form-select-sm">
                                    <option value="present" @selected($row->verified_status === 'present')>Hadir</option>
                                    <option value="leave" @selected($row->verified_status === 'leave')>Izin/Sakit</option>
                                    <option value="absent" @selected($row->verified_status === 'absent')>Tidak Hadir</option>
                                </select>
                            </td>
                            <td><input type="text" name="teacher_rows[{{ $idx }}][reason]" class="form-control form-control-sm" value="{{ $row->reason }}"></td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="teacher_rows[{{ $idx }}][has_substitute]" value="1" @checked($row->has_substitute)>
                                    <label class="form-check-label small">{{ $row->substitute_teacher_name ?: 'Belum ada' }}</label>
                                </div>
                            </td>
                            <td><input type="text" name="teacher_rows[{{ $idx }}][notes]" class="form-control form-control-sm" value="{{ $row->notes }}"></td>
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
                            <td><input type="number" min="0" class="form-control form-control-sm" name="student_rows[{{ $idx }}][total_students]" value="{{ $row->total_students }}"></td>
                            <td><input type="number" min="0" class="form-control form-control-sm" name="student_rows[{{ $idx }}][present_count]" value="{{ $row->present_count }}"></td>
                            <td><input type="number" min="0" class="form-control form-control-sm" name="student_rows[{{ $idx }}][sick_count]" value="{{ $row->sick_count }}"></td>
                            <td><input type="number" min="0" class="form-control form-control-sm" name="student_rows[{{ $idx }}][izin_count]" value="{{ $row->izin_count }}"></td>
                            <td><input type="number" min="0" class="form-control form-control-sm" name="student_rows[{{ $idx }}][alpa_count]" value="{{ $row->alpa_count }}"></td>
                            <td><input type="number" min="0" class="form-control form-control-sm" name="student_rows[{{ $idx }}][late_count]" value="{{ $row->late_count }}"></td>
                            <td><input type="text" class="form-control form-control-sm" name="student_rows[{{ $idx }}][notes]" value="{{ $row->notes }}"></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-secondary">Belum ada data siswa.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-panel>

        <x-panel class="mb-3" title="Catatan Harian">
            <textarea name="notes" rows="3" class="form-control" placeholder="Catatan umum guru piket...">{{ old('notes', $report->notes) }}</textarea>
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                <a href="{{ route('admin.duty-reports.print', $report) }}" target="_blank" class="btn btn-outline-secondary">Print</a>
            </div>
        </x-panel>
    </form>
</x-layouts.app>
