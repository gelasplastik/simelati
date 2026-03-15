<x-layouts.app :title="'Laporan KPI'" :pageTitle="'Report: KPI Guru'" :breadcrumb="'Admin / Reports / KPI'">
    <x-panel class="mb-3">
        <form class="row g-2" method="GET" action="{{ route('admin.reports.kpi') }}">
            <div class="col-md-3"><input class="form-control" type="number" name="month" min="1" max="12" value="{{ $month }}"></div>
            <div class="col-md-3"><input class="form-control" type="number" name="year" min="2020" max="2100" value="{{ $year }}"></div>
            <div class="col-md-3"><button class="btn btn-primary">Tampilkan</button></div>
            <div class="col-md-3 text-end"><a class="btn btn-outline-success" href="{{ route('admin.reports.kpi.export', ['month' => $month, 'year' => $year]) }}">Export CSV</a></div>
        </form>
    </x-panel>

    <x-panel>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                <tr>
                    <th>Rank</th>
                    <th>Guru</th>
                    <th>Kehadiran</th>
                    <th>Jurnal</th>
                    <th>Ketepatan</th>
                    <th>Verif Piket Tanpa Absen</th>
                    <th>Modul Ajar</th>
                    <th>Progress Modul</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                @forelse($items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $item['teacher']->user->name }}</td>
                        <td>{{ $item['attendance_score'] }}</td>
                        <td>{{ $item['journal_score'] }}</td>
                        <td>{{ $item['punctuality_score'] }}</td>
                        <td>
                            @if(($item['duty_verification_gap_days'] ?? 0) > 0)
                                <span class="badge text-bg-danger">{{ $item['duty_verification_gap_days'] }} hari</span>
                            @else
                                <span class="badge text-bg-success">0</span>
                            @endif
                        </td>
                        <td>
                            <span class="small">
                                {{ $item['module_completion_count'] ?? 0 }}/{{ $item['module_total_assignments'] ?? 0 }}
                                <span class="text-secondary">(A: {{ $item['module_approved_count'] ?? 0 }})</span>
                            </span>
                        </td>
                        <td>
                            @php($percent = $item['module_completion_percentage'] ?? 0)
                            <div class="d-flex align-items-center gap-2" style="min-width: 140px;">
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ min(100, max(0, $percent)) }}%"></div>
                                </div>
                                <span class="small">{{ $percent }}%</span>
                            </div>
                        </td>
                        <td><strong>{{ $item['total_score'] }}</strong></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-secondary">Belum ada data KPI.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-panel>
</x-layouts.app>
