<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\TeacherLeaveStoreRequest;
use App\Models\Teacher;
use App\Models\TeacherLeaveRequest;
use App\Support\SafeFileUpload;
use InvalidArgumentException;

class TeacherLeaveRequestController extends Controller
{
    public function index()
    {
        $teacher = auth()->user()->teacher;
        $items = TeacherLeaveRequest::query()
            ->with(['proposedSubstituteTeacher.user'])
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->paginate(20);

        return view('teacher.leave_requests.index', compact('items'));
    }

    public function create()
    {
        $teacher = auth()->user()->teacher;
        $substituteTeachers = Teacher::query()
            ->with('user')
            ->where('id', '!=', $teacher->id)
            ->orderBy('id')
            ->get();

        return view('teacher.leave_requests.create', compact('substituteTeachers'));
    }

    public function store(TeacherLeaveStoreRequest $request)
    {
        $teacher = $request->user()->teacher;

        try {
            $path = SafeFileUpload::storePublic($request->file('attachment'), 'teacher-leaves');
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        if ($request->filled('proposed_substitute_teacher_id') && $request->integer('proposed_substitute_teacher_id') === $teacher->id) {
            return back()->with('error', 'Guru pengganti tidak boleh sama dengan guru asli.')->withInput();
        }

        TeacherLeaveRequest::query()->create([
            'teacher_id' => $teacher->id,
            'date_from' => $request->date('date_from'),
            'date_to' => $request->date('date_to'),
            'reason' => $request->string('reason')->toString(),
            'affects_teaching_schedule' => (bool) $request->boolean('affects_teaching_schedule'),
            'proposed_substitute_teacher_id' => $request->input('proposed_substitute_teacher_id'),
            'coverage_notes' => $request->input('coverage_notes'),
            'attachment_path' => $path,
            'status' => 'pending',
        ]);

        return redirect()->route('teacher.leave-requests.index')->with('success', 'Izin guru berhasil diajukan.');
    }
}
