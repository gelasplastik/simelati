<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Harian Guru Piket</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; margin: 24px; }
        h2, h3 { margin: 0 0 8px; }
        .meta { margin-bottom: 12px; }
        .meta div { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #222; padding: 6px; vertical-align: top; }
        th { background: #f3f3f3; }
        .text-center { text-align: center; }
        .signature { margin-top: 30px; width: 320px; margin-left: auto; text-align: center; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
<div class="no-print" style="margin-bottom: 12px;">
    <button onclick="window.print()">Print</button>
</div>

<h2>LAPORAN HARIAN GURU PIKET</h2>
<div class="meta">
    <div><strong>Sekolah:</strong> SD Plus Melati</div>
    <div><strong>Tanggal:</strong> {{ $report->date->translatedFormat('l, d F Y') }}</div>
    <div><strong>Guru Piket:</strong> {{ $report->dutyTeacher?->user?->name ?: '-' }}</div>
    <div><strong>Status:</strong> {{ strtoupper($report->status) }}</div>
</div>

<h3>A. Kehadiran Guru</h3>
<table>
    <thead>
        <tr>
            <th class="text-center">No</th>
            <th>Nama Guru</th>
            <th>Mapel</th>
            <th class="text-center">Hadir</th>
            <th class="text-center">Tidak Hadir</th>
            <th>Alasan</th>
            <th>Guru Pengganti</th>
        </tr>
    </thead>
    <tbody>
    @foreach($report->teacherRows as $row)
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td>{{ $row->teacher_name }}</td>
            <td>{{ $row->subject_label ?: '-' }}</td>
            <td class="text-center">{{ ($row->verified_status ?? $row->attendance_status) === 'present' ? 'v' : '-' }}</td>
            <td class="text-center">{{ in_array(($row->verified_status ?? $row->attendance_status), ['leave', 'absent']) ? 'v' : '-' }}</td>
            <td>{{ $row->reason ?: '-' }}</td>
            <td>{{ $row->substitute_teacher_name ?: '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h3>B. Rekap Kehadiran Siswa</h3>
<table>
    <thead>
        <tr>
            <th class="text-center">No</th>
            <th>Kelas</th>
            <th class="text-center">Total Siswa</th>
            <th class="text-center">Hadir</th>
            <th class="text-center">Sakit</th>
            <th class="text-center">Izin</th>
            <th class="text-center">Alpa</th>
            <th class="text-center">Terlambat</th>
        </tr>
    </thead>
    <tbody>
    @foreach($report->studentRows as $row)
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td>{{ $row->class_name }}</td>
            <td class="text-center">{{ $row->total_students }}</td>
            <td class="text-center">{{ $row->present_count }}</td>
            <td class="text-center">{{ $row->sick_count }}</td>
            <td class="text-center">{{ $row->izin_count }}</td>
            <td class="text-center">{{ $row->alpa_count }}</td>
            <td class="text-center">{{ $row->late_count }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div><strong>Catatan Harian:</strong> {{ $report->notes ?: '-' }}</div>

<div class="signature">
    <div>{{ now()->translatedFormat('d F Y') }}</div>
    <div>Guru Piket,</div>
    <br><br><br>
    <div><strong>{{ $report->dutyTeacher?->user?->name ?: '................................' }}</strong></div>
</div>
</body>
</html>
