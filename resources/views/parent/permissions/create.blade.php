<x-layouts.app :title="'Ajukan Izin'" :pageTitle="'Ajukan Izin'" :breadcrumb="'Parent / Permissions / Create'">
    <x-panel>
        <form method="POST" action="{{ route('parent.permissions.store') }}" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label class="form-label">Siswa</label>
                <select class="form-select" name="student_id" required>
                    <option value="">Pilih Siswa</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->full_name }} (NISN: {{ $student->nisn }}) - {{ $student->class?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><label class="form-label">Dari</label><input type="date" class="form-control" name="date_from" value="{{ old('date_from') }}" required></div>
            <div class="col-md-3"><label class="form-label">Sampai</label><input type="date" class="form-control" name="date_to" value="{{ old('date_to') }}" required></div>
            <div class="col-12"><label class="form-label">Alasan</label><textarea class="form-control" name="reason" rows="3" required>{{ old('reason') }}</textarea></div>
            <div class="col-12">
                <label class="form-label">Lampiran / Foto Pendukung</label>
                <input type="file" class="form-control" name="attachment" accept=".png,.jpg,.jpeg,application/pdf" capture="environment">
                <small class="text-secondary">Bisa foto langsung dari HP atau upload dari galeri/dokumen. Maks 10MB. Opsional.</small>
            </div>
            <div class="col-12"><button class="btn btn-primary btn-action-lg">Kirim Izin</button></div>
        </form>
    </x-panel>
</x-layouts.app>


