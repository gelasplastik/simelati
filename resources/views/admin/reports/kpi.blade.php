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
        <table class="table table-sm">
            <thead><tr><th>Rank</th><th>Guru</th><th>Kehadiran</th><th>Jurnal</th><th>Ketepatan</th><th>Total</th></tr></thead>
            <tbody>
            @foreach($items as $i => $item)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item['teacher']->user->name }}</td>
                    <td>{{ $item['attendance_score'] }}</td>
                    <td>{{ $item['journal_score'] }}</td>
                    <td>{{ $item['punctuality_score'] }}</td>
                    <td><strong>{{ $item['total_score'] }}</strong></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-panel>
</x-layouts.app>
