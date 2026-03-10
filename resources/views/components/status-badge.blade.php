@props(['status'])

@php
    $key = strtolower((string) $status);
    $map = [
        'hadir' => 'hadir',
        'izin' => 'izin',
        'sakit' => 'sakit',
        'alpa' => 'alpa',
        'pending' => 'pending',
        'submitted' => 'pending',
        'approved' => 'approved',
        'rejected' => 'rejected',
        'belum jurnal' => 'belum-jurnal',
        'sudah jurnal' => 'sudah-jurnal',
        'belum absensi kelas' => 'belum-absensi-kelas',
        'sudah absensi kelas' => 'hadir',
    ];
    $class = $map[$key] ?? 'pending';
@endphp

<span {{ $attributes->merge(['class' => 'badge sim-badge '.$class]) }}>{{ $slot->isEmpty() ? ucfirst($status) : $slot }}</span>
