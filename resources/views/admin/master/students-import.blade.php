<x-layouts.app :title="'Import Siswa'" :pageTitle="'Import Siswa'" :breadcrumb="'Admin / Master Data / Import Siswa'">
    <x-panel class="mb-3">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <div>
                <div class="fw-semibold">Petunjuk Import</div>
                <div class="small text-secondary">File mendukung format CSV atau XLSX dengan kolom: NISN, Nama Siswa, Kelas, Status Aktif (opsional).</div>
            </div>
            <a href="{{ route('admin.students.import.template') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-download me-1"></i>Download Template CSV
            </a>
        </div>
    </x-panel>

    <x-panel class="mb-3" title="Upload File Import">
        <form method="POST" action="{{ route('admin.students.import.store') }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-8">
                <label class="form-label">File CSV / XLSX</label>
                <input type="file" name="file" class="form-control" accept=".csv,.xlsx" required>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button class="btn btn-success w-100" type="submit"><i class="bi bi-upload me-1"></i>Proses Import</button>
            </div>
        </form>
    </x-panel>

    @if(session('import_summary'))
        @php($summary = session('import_summary'))
        <x-panel title="Ringkasan Hasil Import">
            <div class="row g-3 mb-3">
                <div class="col-md-3"><div class="border rounded-3 p-3"><div class="small text-secondary">Total Baris</div><div class="h5 mb-0">{{ $summary['total_rows'] }}</div></div></div>
                <div class="col-md-3"><div class="border rounded-3 p-3"><div class="small text-secondary">Inserted</div><div class="h5 mb-0 text-success">{{ $summary['inserted'] }}</div></div></div>
                <div class="col-md-3"><div class="border rounded-3 p-3"><div class="small text-secondary">Updated</div><div class="h5 mb-0 text-primary">{{ $summary['updated'] }}</div></div></div>
                <div class="col-md-3"><div class="border rounded-3 p-3"><div class="small text-secondary">Skipped/Error</div><div class="h5 mb-0 text-danger">{{ $summary['skipped'] }}</div></div></div>
            </div>

            @if(!empty($summary['errors']))
                <div class="alert alert-warning mb-0">
                    <div class="fw-semibold mb-1">Detail Baris Bermasalah</div>
                    <ul class="mb-0">
                        @foreach($summary['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-panel>
    @endif
</x-layouts.app>
