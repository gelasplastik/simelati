<nav class="navbar navbar-expand topbar">
    <div class="container-fluid">
        <button class="btn btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-label="Menu">
            <i class="bi bi-list"></i>
        </button>
        <a class="navbar-brand mb-0 h5 d-flex align-items-center gap-2" href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : (auth()->user()->role === 'teacher' ? route('teacher.dashboard') : route('parent.dashboard')) }}">
            <img src="{{ asset('assets/logo/simelati-logo.png') }}" alt="SIMELATI" class="brand-logo">
            <div class="d-none d-sm-block">
                <div class="fw-bold lh-1">SIMELATI</div>
                <small class="text-secondary" style="font-size:.72rem;">Sistem Informasi Manajemen SD Plus Melati</small>
            </div>
        </a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <button type="button" class="btn btn-outline-success btn-sm d-inline-flex align-items-center gap-1" id="pwaInstallTopbar" hidden>
                <i class="bi bi-phone"></i>
                <span class="d-none d-md-inline">Install App</span>
            </button>

            @if(auth()->user()->role === 'admin' && $topbarAdminNotifications)
                @php($hasUnread = ($topbarAdminNotifications['total_unread'] ?? 0) > 0)
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi Pengajuan">
                        <i class="bi bi-bell-fill fs-4 {{ $hasUnread ? 'text-danger' : 'text-success' }}"></i>
                        @if(($topbarAdminNotifications['total_unread'] ?? 0) > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">
                                {{ $topbarAdminNotifications['total_unread'] }}
                            </span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm p-2" style="min-width: 380px; max-width: 92vw;">
                        <div class="d-flex justify-content-between align-items-center px-2 py-1">
                            <div class="small fw-semibold">Notifikasi Pengajuan</div>
                            <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
                                @csrf
                                <button type="submit" class="btn btn-link btn-sm p-0">Mark all read</button>
                            </form>
                        </div>

                        @if(($topbarAdminNotifications['total_open'] ?? 0) === 0)
                            <div class="px-2 py-2 small text-secondary">Tidak ada pengajuan pending.</div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($topbarAdminNotifications['items'] as $item)
                                    <a href="{{ $item['route'] }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-2 py-2">
                                        <div class="small d-flex align-items-center gap-2">
                                            <i class="bi {{ $item['icon'] }}"></i>
                                            <span>{{ $item['label'] }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-bg-secondary">{{ $item['count'] }}</span>
                                            @if($item['unread'] > 0)
                                                <span class="badge text-bg-danger">{{ $item['unread'] }}</span>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if(auth()->user()->role === 'teacher' && $topbarTeacherNotifications)
                @php($variant = $topbarTeacherNotifications['bell_variant'] ?? 'secondary')
                @php($moduleAttention = $topbarTeacherModuleNotifications['attention_count'] ?? 0)
                @php($taskIncomplete = ($topbarTeacherNotifications['incomplete_items'] ?? collect())->count())
                @php($dutyAttendanceAttention = $topbarTeacherDutyAttendanceAlerts['count'] ?? 0)
                @php($teacherBadgeCount = $taskIncomplete + $moduleAttention + $dutyAttendanceAttention)
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi Guru">
                        <i class="bi bi-bell-fill fs-4 text-{{ $variant }}"></i>
                        @if($teacherBadgeCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">
                                {{ $teacherBadgeCount }}
                            </span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm p-2" style="min-width: 360px; max-width: 92vw;">
                        <div class="small fw-semibold px-2 py-1">Notifikasi Mengajar Hari Ini</div>
                        @if(!empty($topbarTeacherNotifications['dropdown_message']))
                            <div class="px-2 pb-2 small text-secondary">{{ $topbarTeacherNotifications['dropdown_message'] }}</div>
                        @endif
                        @if(($topbarTeacherNotifications['incomplete_items'] ?? collect())->count() > 0)
                            <div class="list-group list-group-flush mb-2">
                                @foreach($topbarTeacherNotifications['incomplete_items'] as $item)
                                    <div class="list-group-item px-2 py-2 small">{{ $item['text'] }}</div>
                                @endforeach
                            </div>
                        @endif

                        @if(($topbarTeacherDutyAttendanceAlerts['count'] ?? 0) > 0)
                            <div class="border-top mt-1 pt-2 px-2">
                                <div class="small fw-semibold mb-1 text-danger">Pengingat Absen Guru</div>
                                <div class="small text-secondary mb-2">Anda diverifikasi hadir oleh guru piket, tetapi absensi GPS belum tercatat.</div>
                                <div class="list-group list-group-flush mb-2">
                                    @foreach($topbarTeacherDutyAttendanceAlerts['items'] as $alert)
                                        <div class="list-group-item px-2 py-2 small">
                                            <div class="fw-semibold text-danger">{{ $alert['message'] }}</div>
                                            <div class="text-secondary">Tanggal: {{ $alert['date'] ?? '-' }}</div>
                                        </div>
                                    @endforeach
                                </div>
                                <a href="{{ route('teacher.dashboard') }}" class="btn btn-sm btn-outline-danger w-100">Absen Sekarang</a>
                            </div>
                        @endif

                        @if(!empty($topbarTeacherModuleNotifications))
                            <div class="border-top mt-1 pt-2 px-2">
                                <div class="small fw-semibold mb-1">Aktivitas Modul Ajar</div>
                                <div class="small text-secondary mb-2">
                                    Submitted: {{ $topbarTeacherModuleNotifications['submitted_count'] }} |
                                    Approved: {{ $topbarTeacherModuleNotifications['approved_count'] }} |
                                    Rejected: {{ $topbarTeacherModuleNotifications['rejected_count'] }}
                                </div>
                                @if(($topbarTeacherModuleNotifications['recent_rejected'] ?? collect())->count() > 0)
                                    <div class="list-group list-group-flush mb-2">
                                        @foreach($topbarTeacherModuleNotifications['recent_rejected'] as $module)
                                            <div class="list-group-item px-2 py-2 small">
                                                <div class="fw-semibold">{{ $module->title }}</div>
                                                <div class="text-danger">Ditolak{{ $module->admin_notes ? ': '.$module->admin_notes : '' }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <a href="{{ $topbarTeacherModuleNotifications['modules_route'] }}" class="btn btn-sm btn-outline-primary w-100">Buka Modul Ajar</a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="text-end small d-none d-sm-block topbar-user-meta">
                <div class="fw-semibold">{{ auth()->user()->name }}</div>
                <span class="badge sim-badge pending text-capitalize">{{ auth()->user()->role }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-danger btn-sm d-inline-flex align-items-center gap-1" type="submit">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-md-inline">Logout</span>
                </button>
            </form>
        </div>
    </div>
</nav>

