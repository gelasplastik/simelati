<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'SIMELATI' }}</title>
    <link rel="icon" type="image/png" href="/assets/logo/simelati-logo.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0F8EA8">
    <meta name="application-name" content="SIMELATI">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="SIMELATI">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="apple-touch-icon" sizes="192x192" href="/assets/pwa/icon-192.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-wrap">
<div class="container py-4 py-md-5">
    <div class="row justify-content-center align-items-center" style="min-height: 88vh;">
        <div class="col-lg-6 col-md-8">
            <div class="card login-card border-0">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-3">
                        <img src="{{ asset('assets/logo/simelati-logo.png') }}" alt="Logo SIMELATI" class="login-logo">
                        <h1 class="h3 mb-1 fw-bold" style="color:#1f5d25;">SIMELATI</h1>
                        <p class="text-secondary mb-1">Sistem Informasi Manajemen SD Plus Melati</p>
                        <div class="small" style="color:#0f8ea8;">Portal Administrasi Sekolah</div>
                    </div>
                    @include('partials.flash')
                    @include('partials.errors')
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>

<div id="pwaInstallPrompt" class="pwa-install" hidden>
    <div class="small">Install SIMELATI di perangkat Anda untuk akses lebih cepat.</div>
    <div class="d-flex gap-2 mt-2">
        <button type="button" class="btn btn-success btn-sm" id="pwaInstallBtn">Install</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="pwaInstallDismiss">Nanti</button>
    </div>
</div>

<div id="pwaUpdatePrompt" class="pwa-update" hidden>
    <div class="small">Versi baru SIMELATI tersedia.</div>
    <div class="d-flex gap-2 mt-2">
        <button type="button" class="btn btn-primary btn-sm" id="pwaUpdateBtn">Perbarui</button>
    </div>
</div>
</body>
</html>

