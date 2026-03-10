<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'SIMELATI' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/simelati-logo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#2E7D32">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="SIMELATI">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('assets/pwa/icon-192.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@php($user = auth()->user())
<div class="d-flex content-wrapper">
    <aside class="sidebar d-none d-lg-block p-3 position-fixed top-0 start-0">
        @include('partials.sidebar')
    </aside>

    <div class="main-content flex-grow-1">
        @include('partials.topbar')

        <div class="container-fluid p-3 p-lg-4">
            <div class="mb-3 page-head">
                <div>
                    <h4 class="mb-1">{{ $pageTitle ?? 'Dashboard' }}</h4>
                    <small class="text-secondary">{{ $breadcrumb ?? 'SIMELATI' }}</small>
                </div>
                <div class="d-flex align-items-center gap-2 small text-secondary">
                    <i class="bi bi-building"></i>
                    SD Plus Melati
                </div>
            </div>

            @include('partials.flash')
            @include('partials.errors')

            {{ $slot }}
        </div>

        @include('partials.footer')
    </div>
</div>

<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title d-flex align-items-center gap-2" id="mobileSidebarLabel">
            <img src="{{ asset('assets/logo/simelati-logo.png') }}" alt="SIMELATI" class="brand-logo">
            <span>SIMELATI</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-3">
        @include('partials.sidebar')
    </div>
</div>

@if($user)
    <nav class="mobile-bottom-nav d-md-none">
        @if($user->role === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-house"></i>Dashboard</a>
            <a href="{{ route('admin.reports.attendance') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"><i class="bi bi-graph-up"></i>Laporan</a>
            <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><i class="bi bi-gear"></i>Setting</a>
        @elseif($user->role === 'teacher')
            <a href="{{ route('teacher.dashboard') }}" class="{{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}"><i class="bi bi-house"></i>Dashboard</a>
            <a href="{{ route('teacher.class-attendance.index') }}" class="{{ request()->routeIs('teacher.class-attendance.*') ? 'active' : '' }}"><i class="bi bi-clipboard-check"></i>Absensi</a>
            <a href="{{ route('teacher.journals.index') }}" class="{{ request()->routeIs('teacher.journals.*') ? 'active' : '' }}"><i class="bi bi-journal-text"></i>Jurnal</a>
        @else
            <a href="{{ route('parent.dashboard') }}" class="{{ request()->routeIs('parent.dashboard') ? 'active' : '' }}"><i class="bi bi-house"></i>Dashboard</a>
            <a href="{{ route('parent.permissions.index') }}" class="{{ request()->routeIs('parent.permissions.index') ? 'active' : '' }}"><i class="bi bi-clock-history"></i>Riwayat</a>
            <a href="{{ route('parent.permissions.create') }}" class="{{ request()->routeIs('parent.permissions.create') ? 'active' : '' }}"><i class="bi bi-send-plus"></i>Ajukan</a>
        @endif
    </nav>
@endif

<div id="pwaInstallPrompt" class="pwa-install shadow-sm" hidden>
    <div class="small">Install SIMELATI di perangkat Anda untuk akses lebih cepat.</div>
    <div class="d-flex gap-2 mt-2">
        <button type="button" class="btn btn-success btn-sm" id="pwaInstallBtn">Install</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="pwaInstallDismiss">Nanti</button>
    </div>
</div>
</body>
</html>
