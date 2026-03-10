<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Http\Requests\ParentPortal\StorePermissionRequest;
use App\Models\Setting;
use App\Models\StudentPermission;

class PermissionController extends Controller
{
    public function index()
    {
        $parent = auth()->user()->parentProfile;
        $items = StudentPermission::query()->with('student.class')->where('parent_id', $parent->id)->latest()->paginate(20);

        return view('parent.permissions.index', compact('items'));
    }

    public function create()
    {
        $parent = auth()->user()->parentProfile;
        $students = $parent->students()->where('is_active', true)->orderBy('full_name')->get();

        return view('parent.permissions.create', compact('students'));
    }

    public function store(StorePermissionRequest $request)
    {
        $parent = $request->user()->parentProfile;
        abort_unless($parent->students()->where('students.id', $request->student_id)->exists(), 403);

        $setting = Setting::active();
        $status = $setting->izin_requires_approval ? 'submitted' : 'approved';
        $path = $request->hasFile('attachment') ? $request->file('attachment')->store('student-permissions', 'public') : null;

        StudentPermission::query()->create([
            'student_id' => $request->student_id,
            'parent_id' => $parent->id,
            'submitter_name' => $request->user()->name,
            'submitter_phone' => $parent->phone,
            'submitter_relationship' => 'Orang Tua',
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'reason' => $request->reason,
            'attachment' => $path,
            'attachment_path' => $path,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => $status,
        ]);

        return redirect()->route('parent.permissions.index')->with('success', 'Izin berhasil diajukan.');
    }
}
