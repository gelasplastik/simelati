<x-layouts.app :title="'Parent Dashboard'" :pageTitle="'Dashboard Orang Tua'" :breadcrumb="'Parent / Dashboard'">
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <x-panel title="Data Anak" class="h-100">
                <div class="vstack gap-2">
                    @forelse($children as $child)
                        <div class="d-flex justify-content-between align-items-center border rounded-3 p-2">
                            <div>
                                <div class="fw-semibold">{{ $child->full_name }}</div>
                                <div class="small text-secondary">NISN: {{ $child->nisn }}</div>
                            </div>
                            <span class="badge sim-badge hadir">{{ $child->class?->name ?? '-' }}</span>
                        </div>
                    @empty
                        <div class="text-secondary">Belum ada data anak.</div>
                    @endforelse
                </div>
            </x-panel>
        </div>
        <div class="col-md-6">
            <x-panel title="Aksi Cepat" class="h-100">
                <div class="d-grid gap-2">
                    <a class="btn btn-primary btn-action-lg" href="{{ route('parent.permissions.create') }}">Ajukan Izin Siswa</a>
                    <a class="btn btn-outline-secondary btn-action-lg" href="{{ route('parent.permissions.index') }}">Lihat Riwayat Izin</a>
                </div>
            </x-panel>
        </div>
    </div>

    <x-panel title="Riwayat Izin Terbaru">
        <a class="btn btn-outline-primary btn-sm" href="{{ route('parent.permissions.index') }}">Buka Halaman Riwayat</a>
    </x-panel>
</x-layouts.app>
