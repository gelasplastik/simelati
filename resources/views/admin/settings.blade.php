<x-layouts.app :title="'Settings'" :pageTitle="'Settings'" :breadcrumb="'Admin / Settings'">
    <x-panel>
        <form method="POST" action="{{ route('admin.settings.update') }}" class="row g-3">
            @csrf
            <div class="col-md-6"><label class="form-label">School Latitude</label><input class="form-control" name="school_lat" value="{{ old('school_lat', $setting->school_lat) }}" required></div>
            <div class="col-md-6"><label class="form-label">School Longitude</label><input class="form-control" name="school_lng" value="{{ old('school_lng', $setting->school_lng) }}" required></div>
            <div class="col-md-4"><label class="form-label">Radius (m)</label><input class="form-control" name="radius_m" value="{{ old('radius_m', $setting->radius_m) }}" required></div>
            <div class="col-md-4"><label class="form-label">Start Time</label><input type="time" class="form-control" name="start_time" value="{{ old('start_time', substr($setting->start_time,0,5)) }}" required></div>
            <div class="col-md-4"><label class="form-label">Late Tolerance</label><input type="time" class="form-control" name="late_tolerance_time" value="{{ old('late_tolerance_time', substr($setting->late_tolerance_time,0,5)) }}" required></div>
            <div class="col-md-4"><label class="form-label">Minimal Jam Pulang</label><input type="time" class="form-control" name="min_checkout_time" value="{{ old('min_checkout_time', substr($setting->min_checkout_time ?? '12:00:00',0,5)) }}" required></div>

            <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="izin_requires_approval" value="1" @checked($setting->izin_requires_approval)><label class="form-check-label">Izin butuh approval admin</label></div></div>
            <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="journal_lock_enabled" value="1" @checked($setting->journal_lock_enabled ?? true)><label class="form-check-label">Kunci Jurnal Aktif (batasi input jurnal hanya sesuai aturan tanggal/override)</label></div></div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="attendance_journal_open_enabled" value="1" @checked($setting->attendance_journal_open_enabled ?? false)>
                    <label class="form-check-label fw-semibold">Buka Akses Absensi & Jurnal (Lintas Tanggal)</label>
                    <div class="small text-secondary">Jika aktif: guru yang sudah absen hari ini boleh isi absensi/jurnal lintas tanggal. Jika nonaktif: aturan normal (same-day + journal lock) berlaku.</div>
                </div>
            </div>

            <div class="col-12"><button class="btn btn-primary">Simpan Pengaturan</button></div>
        </form>
    </x-panel>
</x-layouts.app>
