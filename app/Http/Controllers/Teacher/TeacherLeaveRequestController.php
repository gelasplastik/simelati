<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\TeacherLeaveStoreRequest;
use App\Models\TeacherLeaveRequest;

class TeacherLeaveRequestController extends Controller
{
    public function index()
    {
        $teacher = auth()->user()->teacher;
        $items = TeacherLeaveRequest::query()
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->paginate(20);

        return view('teacher.leave_requests.index', compact('items'));
    }

    public function create()
    {
        return view('teacher.leave_requests.create');
    }

    public function store(TeacherLeaveStoreRequest $request)
    {
        $teacher = $request->user()->teacher;
        $path = $request->file('attachment')?->store('teacher-leaves', 'public');

        TeacherLeaveRequest::query()->create([
            'teacher_id' => $teacher->id,
            'date_from' => $request->date('date_from'),
            'date_to' => $request->date('date_to'),
            'reason' => $request->string('reason')->toString(),
            'attachment_path' => $path,
            'status' => 'pending',
        ]);

        return redirect()->route('teacher.leave-requests.index')->with('success', 'Izin guru berhasil diajukan.');
    }
}
