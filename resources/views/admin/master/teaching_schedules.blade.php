<x-layouts.app :title="'Jadwal Mengajar'" :pageTitle="'Jadwal Mengajar'" :breadcrumb="'Admin / Master Data / Jadwal Mengajar'">
    <x-panel title="Filter Jadwal" class="mb-3">
        <form method="GET" action="{{ route('admin.teaching-schedules.index') }}" class="row g-2">
            <div class="col-md-3">
                <select class="form-select" name="schedule_profile_id">
                    @foreach($profiles as $profile)
                        <option value="{{ $profile->id }}" @selected($profileId == $profile->id)>
                            {{ $profile->name }} @if($activeProfile->id === $profile->id) (Aktif) @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="teacher_id">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(request('teacher_id') == $teacher->id)>{{ $teacher->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="class_id">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="day_of_week">
                    <option value="">Semua Hari</option>
                    @foreach($dayOptions as $key => $label)
                        <option value="{{ $key }}" @selected(request('day_of_week') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filter</button></div>
            <div class="col-md-2"><a class="btn btn-outline-success w-100" href="{{ route('admin.teaching-schedules.export', request()->query()) }}">CSV</a></div>
            <div class="col-md-2"><a class="btn btn-outline-secondary w-100" href="{{ route('admin.teaching-schedules.profiles') }}">Profil Jadwal</a></div>
        </form>
    </x-panel>

    <x-panel title="Tambah Jadwal" class="mb-3">
        <form method="POST" action="{{ route('admin.teaching-schedules.store') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-lg-3 col-md-4">
                <select class="form-select" name="schedule_profile_id" required>
                    @foreach($profiles as $profile)
                        <option value="{{ $profile->id }}" @selected($profileId == $profile->id)>{{ $profile->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 col-md-4">
                <select class="form-select" name="teacher_id" required>
                    <option value="">Guru</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <select class="form-select" name="class_id" required>
                    <option value="">Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <select class="form-select" name="subject_id" required>
                    <option value="">Mapel</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <select class="form-select" name="day_of_week" required>
                    <option value="">Hari</option>
                    @foreach($dayOptions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-1 col-md-2"><input type="number" class="form-control" name="jam_ke" min="1" max="12" placeholder="Jam" required></div>
            <div class="col-lg-2 col-md-3"><input type="time" class="form-control" name="start_time"></div>
            <div class="col-lg-2 col-md-3"><input type="time" class="form-control" name="end_time"></div>
            <div class="col-lg-2 col-md-3 d-flex align-items-center">
                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input" name="is_active" value="1" checked>
                    <label class="form-check-label">Aktif</label>
                </div>
            </div>
            <div class="col-lg-3 col-md-4"><button class="btn btn-primary w-100">Simpan</button></div>
        </form>
    </x-panel>

    <x-panel>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Profil</th>
                        <th>Guru</th>
                        <th>Kelas</th>
                        <th>Mapel</th>
                        <th>Hari</th>
                        <th>Jam Ke</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th style="width: 140px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>
                            {{ $item->scheduleProfile?->name ?? '-' }}
                            @if($item->schedule_profile_id === $activeProfile->id)
                                <span class="badge text-bg-success">Aktif</span>
                            @endif
                        </td>
                        <td>{{ $item->teacher->user->name }}</td>
                        <td>{{ $item->class->name }}</td>
                        <td>{{ $item->subject->name }}</td>
                        <td>{{ $dayOptions[$item->day_of_week] ?? $item->day_of_week }}</td>
                        <td>{{ $item->jam_ke }}</td>
                        <td>{{ $item->start_time ?: '-' }} - {{ $item->end_time ?: '-' }}</td>
                        <td>{!! $item->is_active ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>' !!}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary w-100"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#editSchedule{{ $item->id }}"
                                    aria-expanded="false"
                                    aria-controls="editSchedule{{ $item->id }}">
                                Edit
                            </button>
                        </td>
                    </tr>
                    <tr class="collapse" id="editSchedule{{ $item->id }}">
                        <td colspan="9" class="bg-light-subtle">
                            <form method="POST" action="{{ route('admin.teaching-schedules.update', $item) }}" class="row g-2 align-items-end">
                                @csrf
                                @method('PUT')

                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label small mb-1">Profil</label>
                                    <select class="form-select form-select-sm" name="schedule_profile_id" required>
                                        @foreach($profiles as $profile)
                                            <option value="{{ $profile->id }}" @selected($item->schedule_profile_id === $profile->id)>{{ $profile->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-3 col-md-6">
                                    <label class="form-label small mb-1">Guru</label>
                                    <select class="form-select form-select-sm" name="teacher_id" required>
                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->id }}" @selected($item->teacher_id === $teacher->id)>{{ $teacher->user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-1 col-md-2">
                                    <label class="form-label small mb-1">Kelas</label>
                                    <select class="form-select form-select-sm" name="class_id" required>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" @selected($item->class_id === $class->id)>{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label small mb-1">Mapel</label>
                                    <select class="form-select form-select-sm" name="subject_id" required>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}" @selected($item->subject_id === $subject->id)>{{ $subject->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label small mb-1">Hari</label>
                                    <select class="form-select form-select-sm" name="day_of_week" required>
                                        @foreach($dayOptions as $key => $label)
                                            <option value="{{ $key }}" @selected($item->day_of_week === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-1 col-md-2">
                                    <label class="form-label small mb-1">Jam Ke</label>
                                    <input type="number" class="form-control form-control-sm" name="jam_ke" min="1" max="12" value="{{ $item->jam_ke }}" required>
                                </div>
                                <div class="col-lg-1 col-md-3">
                                    <label class="form-label small mb-1">Mulai</label>
                                    <input type="time" class="form-control form-control-sm" name="start_time" value="{{ $item->start_time }}">
                                </div>
                                <div class="col-lg-1 col-md-3">
                                    <label class="form-label small mb-1">Selesai</label>
                                    <input type="time" class="form-control form-control-sm" name="end_time" value="{{ $item->end_time }}">
                                </div>
                                <div class="col-lg-1 col-md-3 d-flex align-items-center">
                                    <div class="form-check mt-3">
                                        <input type="checkbox" class="form-check-input" name="is_active" value="1" @checked($item->is_active)>
                                        <label class="form-check-label small">Aktif</label>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-md-4">
                                    <button class="btn btn-sm btn-primary w-100">Simpan Update</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-secondary">Belum ada jadwal.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
