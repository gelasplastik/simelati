<x-layouts.app :title="'Izin Guru'" :pageTitle="'Izin Guru'" :breadcrumb="'Admin / Izin Guru'">
    <x-panel class="mb-3">
        <form method="GET" action="{{ route('admin.teacher-leave-requests.index') }}" class="row g-2">
            <div class="col-md-3">
                <select class="form-select" name="teacher_id">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(request('teacher_id') == $teacher->id)>{{ $teacher->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">Semua Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>PENDING</option>
                    <option value="approved" @selected(request('status') === 'approved')>APPROVED</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>REJECTED</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}"></div>
            <div class="col-md-2"><input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
        </form>
    </x-panel>

    <x-panel>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                <tr>
                    <th>Guru</th>
                    <th>Tanggal</th>
                    <th>Alasan</th>
                    <th>Coverage</th>
                    <th>Status</th>
                    <th>Lampiran</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tbody>
                @forelse($items as $item)
                    @php($path = $item->attachment_path)
                    @php($isImage = $path && preg_match('/\.(jpg|jpeg|png|webp)$/i', $path))
                    <tr>
                        <td>{{ $item->teacher->user->name }}</td>
                        <td>{{ $item->date_from->format('d-m-Y') }} s/d {{ $item->date_to->format('d-m-Y') }}</td>
                        <td>{{ $item->reason }}</td>
                        <td>
                            @if($item->affects_teaching_schedule)
                                <span class="badge text-bg-warning">Affects Schedule</span>
                                <div class="small text-secondary mt-1">Usulan: {{ $item->proposedSubstituteTeacher?->user?->name ?? '-' }}</div>
                                @if($item->coverage_notes)
                                    <div class="small">Catatan: {{ $item->coverage_notes }}</div>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td><span class="badge text-bg-secondary text-uppercase">{{ $item->status }}</span></td>
                        <td>
                            @if($path)
                                @if($isImage)
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ asset('storage/'.$path) }}" alt="Lampiran" style="width:40px;height:40px;object-fit:cover;" class="rounded border">
                                        <a href="{{ asset('storage/'.$path) }}" target="_blank">Lihat Lampiran</a>
                                    </div>
                                @else
                                    <a href="{{ asset('storage/'.$path) }}" target="_blank">Lihat Lampiran</a>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($item->status === 'pending')
                                <div class="d-flex gap-1 mb-2">
                                    <form method="POST" action="{{ route('admin.teacher-leave-requests.review', $item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button class="btn btn-sm btn-success">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.teacher-leave-requests.review', $item) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="btn btn-sm btn-danger">Tolak</button>
                                    </form>
                                </div>
                            @else
                                <span class="text-secondary small d-block mb-2">Sudah diproses</span>
                            @endif

                            @if($item->affects_teaching_schedule && $item->status === 'approved')
                                <details>
                                    <summary class="small">Kelola Guru Pengganti ({{ $item->substituteAssignments->count() }})</summary>
                                    <div class="mt-2">
                                        @forelse($item->substituteAssignments->sortBy(['date', 'jam_ke']) as $coverage)
                                            <div class="border rounded p-2 mb-2">
                                                <div class="small mb-1">
                                                    <strong>{{ $coverage->date->format('d-m-Y') }}</strong> | Jam {{ $coverage->jam_ke }} | {{ $coverage->class->name }} - {{ $coverage->subject->name }}
                                                </div>
                                                <div class="small mb-2">
                                                    Guru asli: {{ $coverage->originalTeacher->user->name }}<br>
                                                    Guru pengganti: <strong>{{ $coverage->substituteTeacher?->user?->name ?? '-' }}</strong>
                                                    <span class="badge text-bg-{{ $coverage->status === 'completed' ? 'success' : ($coverage->status === 'assigned' ? 'warning' : 'secondary') }} text-uppercase">{{ $coverage->status }}</span>
                                                </div>
                                                <form method="POST" action="{{ route('admin.teacher-leave-requests.substitutes.assign', $coverage) }}" class="row g-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="col-md-5">
                                                        <select class="form-select form-select-sm" name="substitute_teacher_id">
                                                            <option value="">-- Belum ditetapkan --</option>
                                                            @foreach($substituteTeachers as $subTeacher)
                                                                <option value="{{ $subTeacher->id }}" @selected($coverage->substitute_teacher_id === $subTeacher->id)>{{ $subTeacher->user->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-5"><input type="text" name="notes" class="form-control form-control-sm" value="{{ $coverage->notes }}" placeholder="Catatan coverage"></div>
                                                    <div class="col-md-2"><button class="btn btn-sm btn-primary w-100">Simpan</button></div>
                                                </form>
                                            </div>
                                        @empty
                                            <div class="small text-secondary">Tidak ada sesi terdampak pada jadwal aktif.</div>
                                        @endforelse
                                    </div>
                                </details>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-secondary">Belum ada data izin guru.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
