<?php

return [
    'national_holiday_sources' => [
        'nager_api' => 'https://date.nager.at/api/v3/PublicHolidays/{year}/ID',
        'calendarific_api' => 'https://calendarific.com/api/v2/holidays',
        'calendarific_api_key' => env('CALENDARIFIC_API_KEY', ''),
    ],

    // Dataset prioritas lokal berbasis SKB 3 Menteri (dapat diperbarui tiap tahun ajaran).
    'skb_3_menteri' => [
        2025 => [
            ['date' => '2025-01-01', 'title' => 'Tahun Baru Masehi', 'entry_type' => 'national_holiday'],
            ['date' => '2025-01-27', 'title' => 'Isra Mikraj Nabi Muhammad SAW', 'entry_type' => 'national_holiday'],
            ['date' => '2025-01-29', 'title' => 'Tahun Baru Imlek 2576 Kongzili', 'entry_type' => 'national_holiday'],
            ['date' => '2025-03-29', 'title' => 'Hari Raya Nyepi', 'entry_type' => 'national_holiday'],
            ['date' => '2025-03-31', 'title' => 'Hari Raya Idul Fitri', 'entry_type' => 'national_holiday'],
            ['date' => '2025-04-01', 'title' => 'Hari Raya Idul Fitri', 'entry_type' => 'national_holiday'],
            ['date' => '2025-04-02', 'title' => 'Cuti Bersama Idul Fitri', 'entry_type' => 'collective_leave'],
            ['date' => '2025-04-03', 'title' => 'Cuti Bersama Idul Fitri', 'entry_type' => 'collective_leave'],
            ['date' => '2025-04-18', 'title' => 'Wafat Isa Almasih', 'entry_type' => 'national_holiday'],
            ['date' => '2025-05-01', 'title' => 'Hari Buruh Internasional', 'entry_type' => 'national_holiday'],
            ['date' => '2025-05-12', 'title' => 'Hari Raya Waisak', 'entry_type' => 'national_holiday'],
            ['date' => '2025-05-13', 'title' => 'Cuti Bersama Waisak', 'entry_type' => 'collective_leave'],
            ['date' => '2025-05-29', 'title' => 'Kenaikan Isa Almasih', 'entry_type' => 'national_holiday'],
            ['date' => '2025-05-30', 'title' => 'Cuti Bersama Kenaikan Isa Almasih', 'entry_type' => 'collective_leave'],
            ['date' => '2025-06-01', 'title' => 'Hari Lahir Pancasila', 'entry_type' => 'national_holiday'],
            ['date' => '2025-06-06', 'title' => 'Idul Adha', 'entry_type' => 'national_holiday'],
            ['date' => '2025-06-09', 'title' => 'Cuti Bersama Idul Adha', 'entry_type' => 'collective_leave'],
            ['date' => '2025-06-27', 'title' => 'Tahun Baru Islam 1447 H', 'entry_type' => 'national_holiday'],
            ['date' => '2025-08-17', 'title' => 'Hari Kemerdekaan Republik Indonesia', 'entry_type' => 'national_holiday'],
            ['date' => '2025-09-05', 'title' => 'Maulid Nabi Muhammad SAW', 'entry_type' => 'national_holiday'],
            ['date' => '2025-12-25', 'title' => 'Hari Raya Natal', 'entry_type' => 'national_holiday'],
            ['date' => '2025-12-26', 'title' => 'Cuti Bersama Natal', 'entry_type' => 'collective_leave'],
        ],
        2026 => [
            ['date' => '2026-01-01', 'title' => 'Tahun Baru Masehi', 'entry_type' => 'national_holiday'],
            ['date' => '2026-01-16', 'title' => 'Isra Mikraj Nabi Muhammad SAW', 'entry_type' => 'national_holiday'],
            ['date' => '2026-02-17', 'title' => 'Tahun Baru Imlek 2577 Kongzili', 'entry_type' => 'national_holiday'],
            ['date' => '2026-03-19', 'title' => 'Hari Raya Nyepi', 'entry_type' => 'national_holiday'],
            ['date' => '2026-03-20', 'title' => 'Hari Raya Idul Fitri', 'entry_type' => 'national_holiday'],
            ['date' => '2026-03-21', 'title' => 'Hari Raya Idul Fitri', 'entry_type' => 'national_holiday'],
            ['date' => '2026-03-22', 'title' => 'Cuti Bersama Idul Fitri', 'entry_type' => 'collective_leave'],
            ['date' => '2026-03-23', 'title' => 'Cuti Bersama Idul Fitri', 'entry_type' => 'collective_leave'],
            ['date' => '2026-04-03', 'title' => 'Wafat Isa Almasih', 'entry_type' => 'national_holiday'],
            ['date' => '2026-05-01', 'title' => 'Hari Buruh Internasional', 'entry_type' => 'national_holiday'],
            ['date' => '2026-05-14', 'title' => 'Kenaikan Isa Almasih', 'entry_type' => 'national_holiday'],
            ['date' => '2026-05-15', 'title' => 'Cuti Bersama Kenaikan Isa Almasih', 'entry_type' => 'collective_leave'],
            ['date' => '2026-05-28', 'title' => 'Hari Raya Waisak', 'entry_type' => 'national_holiday'],
            ['date' => '2026-05-29', 'title' => 'Cuti Bersama Waisak', 'entry_type' => 'collective_leave'],
            ['date' => '2026-06-01', 'title' => 'Hari Lahir Pancasila', 'entry_type' => 'national_holiday'],
            ['date' => '2026-06-18', 'title' => 'Idul Adha', 'entry_type' => 'national_holiday'],
            ['date' => '2026-06-19', 'title' => 'Cuti Bersama Idul Adha', 'entry_type' => 'collective_leave'],
            ['date' => '2026-07-17', 'title' => 'Tahun Baru Islam 1448 H', 'entry_type' => 'national_holiday'],
            ['date' => '2026-08-17', 'title' => 'Hari Kemerdekaan Republik Indonesia', 'entry_type' => 'national_holiday'],
            ['date' => '2026-09-24', 'title' => 'Maulid Nabi Muhammad SAW', 'entry_type' => 'national_holiday'],
            ['date' => '2026-12-25', 'title' => 'Hari Raya Natal', 'entry_type' => 'national_holiday'],
            ['date' => '2026-12-26', 'title' => 'Cuti Bersama Natal', 'entry_type' => 'collective_leave'],
        ],
    ],

    // Fallback umum jika seluruh source utama gagal.
    'fallback_dataset' => [
        2026 => [
            ['date' => '2026-01-01', 'title' => 'Tahun Baru Masehi', 'entry_type' => 'national_holiday'],
            ['date' => '2026-03-20', 'title' => 'Hari Raya Idul Fitri', 'entry_type' => 'national_holiday'],
            ['date' => '2026-03-21', 'title' => 'Hari Raya Idul Fitri', 'entry_type' => 'national_holiday'],
            ['date' => '2026-03-22', 'title' => 'Cuti Bersama Idul Fitri', 'entry_type' => 'collective_leave'],
            ['date' => '2026-05-01', 'title' => 'Hari Buruh Internasional', 'entry_type' => 'national_holiday'],
            ['date' => '2026-08-17', 'title' => 'Hari Kemerdekaan Republik Indonesia', 'entry_type' => 'national_holiday'],
            ['date' => '2026-12-25', 'title' => 'Hari Raya Natal', 'entry_type' => 'national_holiday'],
        ],
    ],
];
