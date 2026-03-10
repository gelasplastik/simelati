<x-layouts.app :title="'Jurnal Mengajar'" :pageTitle="'Jurnal Mengajar'" :breadcrumb="'Teacher / Jurnal Mengajar'">
    <x-panel class="mb-3" title="Jadwal Hari Ini">
        <div class="small text-secondary">
            Profil jadwal aktif: <strong>{{ $activeProfile->name }}</strong>
        </div>
    </x-panel>

    @if($schedules->isEmpty())
        <x-panel>
            <div class="text-secondary">Tidak ada jadwal mengajar hari ini.</div>
        </x-panel>
    @else
        <div class="row g-3">
            @foreach($schedules as $item)
                @php($schedule = $item['schedule'])
                @php($journal = $item['journal'])
                @php($startTime = $schedule->start_time ? \Carbon\Carbon::createFromFormat('H:i:s', $schedule->start_time)->format('H:i') : '-')
                @php($endTime = $schedule->end_time ? \Carbon\Carbon::createFromFormat('H:i:s', $schedule->end_time)->format('H:i') : '-')
                <div class="col-12 col-md-6">
                    <x-panel class="h-100">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <div class="fw-semibold">{{ $startTime }} - {{ $endTime }}</div>
                                <div class="small text-secondary">Jam ke {{ $schedule->jam_ke }}</div>
                            </div>
                            @if($journal)
                                <span class="badge text-bg-success">Jurnal sudah diisi</span>
                            @else
                                <span class="badge text-bg-warning">Belum diisi</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <div class="fw-semibold">Kelas {{ $schedule->class->name }}</div>
                            <div class="text-secondary">{{ $schedule->subject->name }}</div>
                        </div>

                        @if($journal)
                            <button class="btn btn-outline-success w-100" disabled>Jurnal sudah diisi</button>
                        @else
                            <a href="{{ route('teacher.journals.create', $schedule) }}" class="btn btn-primary w-100">Isi Jurnal</a>
                        @endif
                    </x-panel>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
