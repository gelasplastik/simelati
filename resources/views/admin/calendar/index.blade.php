<x-layouts.app :title="'Kalender Akademik - SIMELATI'" :pageTitle="'Kalender Akademik (Kaldik)'" :breadcrumb="'Admin / Pengaturan / Kalender Akademik'">
    <div class="row g-3">
        <div class="col-12 col-xl-4">
            <x-panel title="Sync Libur Nasional & Cuti Bersama">
                <form method="POST" action="{{ route('admin.calendar.sync-national') }}" class="row g-2">
                    @csrf
                    <div class="col-8">
                        <label class="form-label">Tahun</label>
                        <select class="form-select" name="year" required>
                            @foreach($yearOptions as $year)
                                <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4 d-grid">
                        <label class="form-label d-none d-md-block">&nbsp;</label>
                        <button class="btn btn-primary" type="submit">Sync</button>
                    </div>
                </form>
                <div class="small text-secondary mt-2">Data nasional disimpan lokal, tidak dipanggil live setiap halaman.</div>

                <hr>
                <div class="table-responsive table-responsive-mobile-cards">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Judul</th>
                            <th>Tipe</th>
                            <th>Sumber</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($nationalHolidays as $holiday)
                            <tr>
                                <td data-label="Tanggal">{{ $holiday->date->format('d-m-Y') }}</td>
                                <td data-label="Judul">{{ $holiday->title }}</td>
                                <td data-label="Tipe">
                                    <x-status-badge :status="$holiday->entry_type === 'collective_leave' ? 'izin' : 'approved'">
                                        {{ $holiday->entry_type === 'collective_leave' ? 'Cuti Bersama' : 'Libur Nasional' }}
                                    </x-status-badge>
                                </td>
                                <td data-label="Sumber"><span class="badge text-bg-light border text-dark">{{ $holiday->source }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-secondary">Belum ada data kalender nasional pada tahun ini.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">{{ $nationalHolidays->links() }}</div>
            </x-panel>
        </div>

        <div class="col-12 col-xl-8">
            <x-panel title="Tambah Event Kaldik Sekolah">
                <form method="POST" action="{{ route('admin.calendar.store') }}" class="row g-3" id="calendarEventForm">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label">Judul Event</label>
                        <input class="form-control" type="text" name="title" value="{{ old('title') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipe Event</label>
                        <select class="form-select" name="event_type" id="eventType" required>
                            @foreach($eventTypes as $type => $label)
                                <option value="{{ $type }}" @selected(old('event_type') === $type)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Mode Operasional</label>
                        <select class="form-select" name="operational_mode" id="operationalMode" required>
                            @foreach($operationalModes as $mode => $label)
                                <option value="{{ $mode }}" @selected(old('operational_mode') === $mode)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Warna Event</label>
                        <input class="form-control" type="text" name="color" id="eventColor" value="{{ old('color') }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input class="form-control" type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input class="form-control" type="date" name="end_date" value="{{ old('end_date', now()->toDateString()) }}" required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_on_dashboard" value="1" @checked(old('show_on_dashboard', 1))>
                            <label class="form-check-label">Tampil di Dashboard</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="active" value="1" @checked(old('active', 1))>
                            <label class="form-check-label">Aktif</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Deskripsi</label>
                        <input class="form-control" type="text" name="description" value="{{ old('description') }}" placeholder="Opsional">
                    </div>

                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <div class="fw-semibold mb-1">Preview Efek Operasional</div>
                            <ul class="mb-0" id="operationalPreviewList"></ul>
                        </div>
                    </div>

                    <div class="col-12">
                        <a class="small text-decoration-none" data-bs-toggle="collapse" href="#advancedOptions">Advanced Options (Custom)</a>
                        <div class="collapse mt-2" id="advancedOptions">
                            <div class="row g-2">
                                <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_school_day" value="1" @checked(old('is_school_day', 1))><label class="form-check-label">Hari Sekolah</label></div></div>
                                <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="disable_teacher_attendance" value="1" @checked(old('disable_teacher_attendance'))><label class="form-check-label">Disable Absen Guru</label></div></div>
                                <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="disable_student_attendance" value="1" @checked(old('disable_student_attendance'))><label class="form-check-label">Disable Absensi Siswa</label></div></div>
                                <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="disable_journal" value="1" @checked(old('disable_journal'))><label class="form-check-label">Disable Jurnal</label></div></div>
                                <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="disable_substitute_generation" value="1" @checked(old('disable_substitute_generation'))><label class="form-check-label">Disable Generate Pengganti</label></div></div>
                                <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="disable_kpi_penalty" value="1" @checked(old('disable_kpi_penalty'))><label class="form-check-label">Disable KPI Penalty</label></div></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-success" type="submit">Simpan Event</button>
                    </div>
                </form>
            </x-panel>

            <x-panel title="Daftar Event Kalender Sekolah" class="mt-3">
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-2"><select class="form-select" name="month">@for($m=1;$m<=12;$m++)<option value="{{ $m }}" @selected($selectedMonth===$m)>{{ $m }}</option>@endfor</select></div>
                    <div class="col-md-2"><select class="form-select" name="year">@foreach($yearOptions as $year)<option value="{{ $year }}" @selected($selectedYear===$year)>{{ $year }}</option>@endforeach</select></div>
                    <div class="col-md-4"><select class="form-select" name="event_type"><option value="">Semua Tipe</option>@foreach($eventTypes as $type => $label)<option value="{{ $type }}" @selected($selectedType===$type)>{{ $label }}</option>@endforeach</select></div>
                    <div class="col-md-2 d-grid"><button class="btn btn-outline-primary" type="submit">Filter</button></div>
                </form>

                <div class="table-responsive table-responsive-mobile-cards">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Tanggal</th>
                            <th>Tipe</th>
                            <th>Mode</th>
                            <th>Efek</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($events as $event)
                            @php($flags = [
                                'disable_teacher_attendance' => $event->disable_teacher_attendance,
                                'disable_student_attendance' => $event->disable_student_attendance,
                                'disable_journal' => $event->disable_journal,
                                'disable_substitute_generation' => $event->disable_substitute_generation,
                                'disable_kpi_penalty' => $event->disable_kpi_penalty,
                            ])
                            <tr>
                                <td data-label="Judul">
                                    <div class="fw-semibold">{{ $event->title }}</div>
                                    <div class="small text-secondary">{{ $event->description ?: '-' }}</div>
                                </td>
                                <td data-label="Tanggal">{{ $event->start_date->format('d-m-Y') }} s/d {{ $event->end_date->format('d-m-Y') }}</td>
                                <td data-label="Tipe"><span class="badge text-bg-light border" style="color: {{ $event->color }}">{{ $eventTypes[$event->event_type] ?? $event->event_type }}</span></td>
                                <td data-label="Mode">{{ $operationalModes[$event->operational_mode] ?? $event->operational_mode }}</td>
                                <td data-label="Efek" class="small">
                                    @foreach(\App\Models\SchoolCalendarEvent::effectPreviewFromFlags($flags) as $line)
                                        <div>� {{ $line }}</div>
                                    @endforeach
                                </td>
                                <td data-label="Status">
                                    <span class="badge {{ $event->active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $event->active ? 'Aktif' : 'Nonaktif' }}</span>
                                </td>
                                <td data-label="Aksi" class="d-flex gap-1 flex-wrap">
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#editEvent{{ $event->id }}">Edit</button>
                                    <form method="POST" action="{{ route('admin.calendar.destroy', $event) }}" onsubmit="return confirm('Hapus event ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <tr class="collapse" id="editEvent{{ $event->id }}">
                                <td colspan="7">
                                    <form method="POST" action="{{ route('admin.calendar.update', $event) }}" class="row g-2">
                                        @csrf
                                        @method('PUT')
                                        <div class="col-md-3"><input class="form-control" name="title" value="{{ $event->title }}" required></div>
                                        <div class="col-md-2"><select class="form-select" name="event_type">@foreach($eventTypes as $type => $label)<option value="{{ $type }}" @selected($event->event_type===$type)>{{ $label }}</option>@endforeach</select></div>
                                        <div class="col-md-2"><select class="form-select" name="operational_mode">@foreach($operationalModes as $mode => $label)<option value="{{ $mode }}" @selected($event->operational_mode===$mode)>{{ $label }}</option>@endforeach</select></div>
                                        <div class="col-md-2"><input class="form-control" type="date" name="start_date" value="{{ $event->start_date->toDateString() }}" required></div>
                                        <div class="col-md-2"><input class="form-control" type="date" name="end_date" value="{{ $event->end_date->toDateString() }}" required></div>
                                        <div class="col-md-1"><input class="form-control" name="color" value="{{ $event->color }}"></div>
                                        <div class="col-md-12"><input class="form-control" name="description" value="{{ $event->description }}"></div>
                                        <div class="col-md-12 d-flex flex-wrap gap-3 small">
                                            <label><input type="checkbox" name="is_school_day" value="1" @checked($event->is_school_day)> Hari Sekolah</label>
                                            <label><input type="checkbox" name="disable_teacher_attendance" value="1" @checked($event->disable_teacher_attendance)> No Absen Guru</label>
                                            <label><input type="checkbox" name="disable_student_attendance" value="1" @checked($event->disable_student_attendance)> No Absensi Siswa</label>
                                            <label><input type="checkbox" name="disable_journal" value="1" @checked($event->disable_journal)> No Jurnal</label>
                                            <label><input type="checkbox" name="disable_substitute_generation" value="1" @checked($event->disable_substitute_generation)> No Pengganti</label>
                                            <label><input type="checkbox" name="disable_kpi_penalty" value="1" @checked($event->disable_kpi_penalty)> No KPI Penalty</label>
                                            <label><input type="checkbox" name="show_on_dashboard" value="1" @checked($event->show_on_dashboard)> Tampil Dashboard</label>
                                            <label><input type="checkbox" name="active" value="1" @checked($event->active)> Aktif</label>
                                        </div>
                                        <div class="col-md-12 d-flex justify-content-end"><button class="btn btn-sm btn-primary" type="submit">Update</button></div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-secondary">Belum ada event kalender sekolah.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">{{ $events->links() }}</div>
            </x-panel>
        </div>
    </div>

    <script>
        (() => {
            const eventType = document.getElementById('eventType');
            const operationalMode = document.getElementById('operationalMode');
            const eventColor = document.getElementById('eventColor');
            const preview = document.getElementById('operationalPreviewList');
            if (!eventType || !operationalMode || !eventColor || !preview) return;

            const colors = @json($eventColors);
            const effects = @json($operationalModeEffects);

            const render = () => {
                const type = eventType.value;
                const mode = operationalMode.value;
                eventColor.value = colors[type] || '#607D8B';

                const lines = effects[mode] || [];
                preview.innerHTML = lines.map(line => `<li>${line}</li>`).join('');
            };

            eventType.addEventListener('change', render);
            operationalMode.addEventListener('change', render);
            render();
        })();
    </script>
</x-layouts.app>
