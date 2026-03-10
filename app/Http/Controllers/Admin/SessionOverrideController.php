<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSessionOverrideRequest;
use App\Models\ClassAttendanceSession;
use App\Models\SchoolClass;
use App\Models\Teacher;

class SessionOverrideController extends Controller
{
    public function index()
    {
        $query = ClassAttendanceSession::query()->with(['teacher.user', 'class', 'subject', 'overrideAllowedBy']);

        if (request('teacher_id')) {
            $query->where('teacher_id', request('teacher_id'));
        }

        if (request('class_id')) {
            $query->where('class_id', request('class_id'));
        }

        if (request('date')) {
            $query->whereDate('date', request('date'));
        }

        $items = $query->latest('date')->paginate(20)->withQueryString();
        $teachers = Teacher::query()->with('user')->get();
        $classes = SchoolClass::query()->orderBy('name')->get();

        return view('admin.overrides.index', compact('items', 'teachers', 'classes'));
    }

    public function update(UpdateSessionOverrideRequest $request, ClassAttendanceSession $session)
    {
        if ($request->boolean('override_allowed')) {
            $session->update([
                'override_allowed' => true,
                'override_reason' => $request->override_reason,
                'override_allowed_by' => $request->user()->id,
                'override_expires_at' => $request->override_expires_at,
            ]);
        } else {
            $session->update([
                'override_allowed' => false,
                'override_reason' => null,
                'override_allowed_by' => null,
                'override_expires_at' => null,
            ]);
        }

        return back()->with('success', 'Override sesi berhasil diperbarui.');
    }
}
