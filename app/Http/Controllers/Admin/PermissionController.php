<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentPermission;

class PermissionController extends Controller
{
    public function index()
    {
        $items = StudentPermission::query()->with(['student.class', 'parent.user'])->latest()->paginate(20);

        return view('admin.permissions', compact('items'));
    }

    public function approve(StudentPermission $permission)
    {
        $permission->update(['status' => 'approved']);

        return back()->with('success', 'Izin disetujui.');
    }

    public function reject(StudentPermission $permission)
    {
        $permission->update(['status' => 'rejected']);

        return back()->with('success', 'Izin ditolak.');
    }
}
