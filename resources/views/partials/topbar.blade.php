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
            @if(auth()->user()->role === 'teacher' && $topbarTeacherNotifications)
                @php($variant = $topbarTeacherNotifications['bell_variant'] ?? 'secondary')
                <div class="dropdown">
                    <button class="btn btn-link text-decoration-none p-0 position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifikasi Jurnal">
                        <i class="bi bi-bell-fill fs-4 text-{{ $variant }}"></i>
                        @if(($topbarTeacherNotifications['incomplete_items'] ?? collect())->count() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger">
                                {{ ($topbarTeacherNotifications['incomplete_items'] ?? collect())->count() }}
                            </span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-sm p-2" style="min-width: 360px; max-width: 92vw;">
                        <div class="small fw-semibold px-2 py-1">Notifikasi Mengajar Hari Ini</div>
                        @if(!empty($topbarTeacherNotifications['dropdown_message']))
                            <div class="px-2 pb-2 small text-secondary">{{ $topbarTeacherNotifications['dropdown_message'] }}</div>
                        @endif
                        @if(($topbarTeacherNotifications['incomplete_items'] ?? collect())->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($topbarTeacherNotifications['incomplete_items'] as $item)
                                    <div class="list-group-item px-2 py-2 small">{{ $item['text'] }}</div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="text-end small d-none d-sm-block">
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
