<x-layouts.app :title="'Arsip Laporan Piket Harian'" :pageTitle="'Arsip Laporan Piket Harian'" :breadcrumb="'Admin / Guru Piket / Arsip'">
    <x-panel class="mb-3" title="Filter Laporan">
        <form method="GET" class="row g-2">
            <div class="col-md-3"><input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}"></div>
            <div class="col-md-3"><input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}"></div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="finalized" @selected(request('status') === 'finalized')>Finalized</option>
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Terapkan</button></div>
        </form>
    </x-panel>

    <x-panel title="Daftar Laporan">
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Guru Piket</th>
                        <th>Status</th>
                        <th>Finalisasi</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $item->date->format('d-m-Y') }}</td>
                            <td>{{ $item->dutyTeacher?->user?->name ?: '-' }}</td>
                            <td><span class="badge text-bg-{{ $item->status === 'finalized' ? 'success' : 'warning' }}">{{ strtoupper($item->status) }}</span></td>
                            <td>{{ $item->finalized_at?->format('d-m-Y H:i') ?: '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.duty-reports.show', $item) }}" class="btn btn-sm btn-primary">Detail</a>
                                <a href="{{ route('admin.duty-reports.print', $item) }}" class="btn btn-sm btn-outline-secondary" target="_blank">Print</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-secondary">Belum ada arsip laporan piket.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $items->links() }}
    </x-panel>
</x-layouts.app>
