<x-layouts.app :title="'Laporan Jurnal Mengajar'" :pageTitle="'Report: Teaching Journals'" :breadcrumb="'Admin / Reports / Teaching Journals'">
    <x-panel class="mb-3">
        <form class="row g-2" method="GET" action="{{ route('admin.reports.journals') }}">
            <div class="col-md-2">
                <select class="form-select" name="teacher_id">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(request('teacher_id') == $teacher->id)>{{ $teacher->user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="class_id">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="subject_id">
                    <option value="">Semua Mapel</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2"><input class="form-control" type="date" name="date_from" value="{{ request('date_from') }}"></div>
            <div class="col-md-2"><input class="form-control" type="date" name="date_to" value="{{ request('date_to') }}"></div>
            <div class="col-md-2">
                <input class="form-control" type="number" min="2020" max="2100" name="year" value="{{ request('year', $chartYear) }}" placeholder="Tahun Grafik">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-primary w-100">Filter</button>
                <a class="btn btn-outline-success" href="{{ route('admin.reports.journals.export', request()->query()) }}">CSV</a>
            </div>
        </form>
    </x-panel>

    <div class="row g-3 mb-3">
        <div class="col-md-3 col-6">
            <x-stat-card label="Total Jurnal" :value="$totalJournals" icon="bi-journal-text" variant="primary" />
        </div>
        <div class="col-md-3 col-6">
            <x-stat-card label="Guru Aktif Mengisi" :value="$activeTeachers" icon="bi-person-check" variant="success" />
        </div>
        <div class="col-md-3 col-6">
            <x-stat-card label="Kelas Tercakup" :value="$activeClasses" icon="bi-grid" variant="info" />
        </div>
        <div class="col-md-3 col-6">
            <x-stat-card label="Rata-rata / Hari" :value="$avgPerDay" icon="bi-graph-up-arrow" variant="warning" />
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-8">
            <x-panel :title="'Grafik Jurnal Bulanan ('.$chartYear.')'">
                <div style="height:260px;">
                    <canvas id="journalMonthlyChart"></canvas>
                </div>
            </x-panel>
        </div>
        <div class="col-lg-4">
            <x-panel title="Rekap Jurnal per Guru (Top 10)">
                <div class="table-responsive table-responsive-mobile-cards">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Guru</th><th>Total Jurnal</th></tr></thead>
                        <tbody>
                        @forelse($teacherRecap as $row)
                            <tr>
                                <td data-label="Guru">{{ $row->teacher?->user?->name ?? '-' }}</td>
                                <td data-label="Total Jurnal"><strong>{{ $row->total }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-secondary">Belum ada data.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </x-panel>
        </div>
    </div>

    <x-panel>
        <div class="table-responsive table-responsive-mobile-cards">
        <table class="table table-sm align-middle">
            <thead><tr><th>Tanggal</th><th>Guru</th><th>Kelas</th><th>Mapel</th><th>Jam</th><th>Pertemuan</th><th>Materi</th><th>Tidak Hadir</th><th>Lampiran</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                @php($path = $item->attachment_path)
                @php($isImage = $path && preg_match('/\.(jpg|jpeg|png|webp)$/i', $path))
                <tr>
                    <td data-label="Tanggal">{{ $item->date->format('d-m-Y') }}</td>
                    <td data-label="Guru">{{ $item->teacher->user->name }}</td>
                    <td data-label="Kelas">{{ $item->class->name }}</td>
                    <td data-label="Mapel">{{ $item->subject->name }}</td>
                    <td data-label="Jam">{{ $item->jam_ke }}</td>
                    <td data-label="Pertemuan">{{ $item->pertemuan_ke }}</td>
                    <td data-label="Materi">{{ \Illuminate\Support\Str::limit($item->materi, 60) }}</td>
                    <td data-label="Tidak Hadir">{{ $item->absent_students_text }}</td>
                    <td data-label="Lampiran">
                        @if($path)
                            @if($isImage)
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ asset('storage/'.$path) }}" alt="Lampiran" style="width:36px;height:36px;object-fit:cover;" class="rounded border">
                                    <a href="{{ asset('storage/'.$path) }}" target="_blank">Lihat Lampiran</a>
                                </div>
                            @else
                                <a href="{{ asset('storage/'.$path) }}" target="_blank">Lihat Lampiran</a>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-secondary">Tidak ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
        {{ $items->links() }}
    </x-panel>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const monthlyCtx = document.getElementById('journalMonthlyChart');
        if (monthlyCtx) {
            new Chart(monthlyCtx, {
                type: 'bar',
                data: {
                    labels: @json($monthLabels),
                    datasets: [{
                        label: 'Total Jurnal',
                        data: @json($monthData),
                        backgroundColor: '#2E7D32'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    </script>
</x-layouts.app>
