<x-layouts.app :title="'Ajukan Izin Guru'" :pageTitle="'Ajukan Izin Guru'" :breadcrumb="'Teacher / Izin Guru / Create'">
    <x-panel>
        <form method="POST" action="{{ route('teacher.leave-requests.store') }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-3">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="date_from" class="form-control" value="{{ old('date_from') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="date_to" class="form-control" value="{{ old('date_to') }}" required>
            </div>
            <div class="col-12">
                <label class="form-label">Alasan Izin</label>
                <textarea name="reason" class="form-control" rows="4" required>{{ old('reason') }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label">Lampiran / Foto Pendukung</label>
                <input type="file" name="attachment" class="form-control" accept="image/*,application/pdf" capture="environment">
                <small class="text-secondary">Bisa foto langsung dari HP atau upload dari galeri/dokumen. Opsional.</small>
            </div>
            <div class="col-12">
                <button class="btn btn-primary btn-action-lg" type="submit">Kirim Izin Guru</button>
                <a class="btn btn-outline-secondary" href="{{ route('teacher.leave-requests.index') }}">Kembali</a>
            </div>
        </form>
    </x-panel>
</x-layouts.app>
