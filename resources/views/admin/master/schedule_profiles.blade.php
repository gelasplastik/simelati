<x-layouts.app :title="'Profil Jadwal'" :pageTitle="'Profil Jadwal'" :breadcrumb="'Admin / Master Data / Jadwal Mengajar / Profil Jadwal'">
    <x-panel title="Profil Jadwal Aktif" class="mb-3">
        <div class="alert alert-info mb-0">
            Profil aktif saat ini:
            <strong>{{ $activeProfile->name }}</strong>
            ({{ strtoupper($activeProfile->code) }})
        </div>
    </x-panel>

    <x-panel>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama Profil</th>
                        <th>Kode</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($profiles as $profile)
                        <tr>
                            <td class="fw-semibold">{{ $profile->name }}</td>
                            <td>{{ strtoupper($profile->code) }}</td>
                            <td>{{ $profile->description ?: '-' }}</td>
                            <td>
                                @if($profile->is_active)
                                    <span class="badge text-bg-success">Aktif</span>
                                @else
                                    <span class="badge text-bg-secondary">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>
                                @if(! $profile->is_active)
                                    <form method="POST" action="{{ route('admin.teaching-schedules.profiles.activate', $profile) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-primary w-100">Aktifkan</button>
                                    </form>
                                @else
                                    <span class="text-success small">Sedang aktif</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-panel>
</x-layouts.app>
