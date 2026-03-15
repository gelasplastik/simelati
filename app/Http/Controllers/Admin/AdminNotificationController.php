<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Reporting\AdminNotificationService;
use App\Http\Controllers\Controller;

class AdminNotificationController extends Controller
{
    public function markAllRead(AdminNotificationService $service)
    {
        $service->markAllRead(auth()->user());

        return back()->with('success', 'Semua notifikasi pengajuan sudah ditandai dibaca.');
    }
}
