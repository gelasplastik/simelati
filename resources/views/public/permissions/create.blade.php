<x-layouts.guest :title="'Izin Siswa - SIMELATI'">
    <div class="mb-4">
        <h2 class="h5 mb-1">Form Izin Siswa</h2>
        <p class="text-secondary mb-0">Isi NISN siswa terlebih dahulu, lalu kirim pengajuan izin tanpa perlu login.</p>
    </div>

    <form method="GET" action="{{ route('public.permissions.create') }}" class="row g-2 mb-4">
        <div class="col-12">
            <label class="form-label">NISN</label>
            <input type="text" class="form-control" name="nisn" value="{{ old('nisn', $nisn) }}" placeholder="Contoh: 00620260001" required>
        </div>
        <div class="col-12 d-flex align-items-end">
            <button class="btn btn-outline-primary w-100" type="submit">Cari Siswa</button>
        </div>
    </form>

    @if($student)
        <div class="alert alert-info shadow-sm">
            <div><strong>Nama Siswa:</strong> {{ $student->full_name }}</div>
            <div><strong>NISN:</strong> {{ $student->nisn }}</div>
            <div><strong>Kelas:</strong> {{ $student->class?->name ?? '-' }}</div>
        </div>

        <form method="POST" action="{{ route('public.permissions.store') }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            <input type="hidden" name="nisn" value="{{ $student->nisn }}">
            <input type="text" name="website" class="d-none" tabindex="-1" autocomplete="off">

            <div class="col-12">
                <label class="form-label">Nama Pengaju</label>
                <input type="text" class="form-control" name="nama_pengaju" value="{{ old('nama_pengaju') }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">Nomor HP</label>
                <input type="text" class="form-control" name="nomor_hp" value="{{ old('nomor_hp') }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">Hubungan dengan Siswa</label>
                <input type="text" class="form-control" name="hubungan_dengan_siswa" value="{{ old('hubungan_dengan_siswa') }}" placeholder="Ayah / Ibu / Wali" required>
            </div>
            <div class="col-12">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" class="form-control" name="date_from" value="{{ old('date_from') }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" class="form-control" name="date_to" value="{{ old('date_to') }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">Alasan</label>
                <textarea class="form-control" name="reason" rows="3" required>{{ old('reason') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Lampiran / Foto Pendukung</label>
                <input type="file" class="form-control" name="attachment" accept="image/*,application/pdf" capture="environment">
                <small class="text-secondary">Bisa foto langsung dari HP atau upload dari galeri/dokumen. Lampiran opsional.</small>
            </div>
            <div class="col-12">
                <button class="btn btn-primary btn-action-lg w-100" type="submit">Kirim Pengajuan Izin</button>
            </div>
        </form>
    @endif
</x-layouts.guest>
