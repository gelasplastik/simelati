<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Attendance\LeaveCoverageService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeacherLeaveReviewRequest;
use App\Http\Requests\Admin\TeacherLeaveSubstituteUpdateRequest;
use App\Models\Teacher;
use App\Models\TeacherLeaveRequest;
use App\Models\TeacherSubstituteAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TeacherLeaveRequestController extends Controller
{
    public function __construct(
        private readonly LeaveCoverageService $coverageService,
    ) {
    }

    public function index(Request $request)
    {
        $query = TeacherLeaveRequest::query()->with([
            'teacher.user',
            'proposedSubstituteTeacher.user',
            'substituteAssignments.class',
            'substituteAssignments.subject',
            'substituteAssignments.originalTeacher.user',
            'substituteAssignments.substituteTeacher.user',
        ]);

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', (int) $request->input('teacher_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_from', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_to', '<=', $request->input('date_to'));
        }

        $items = $query->latest()->paginate(30)->withQueryString();
        $teachers = Teacher::query()->with('user')->orderBy('id')->get();
        $substituteTeachers = Teacher::query()->with('user')->orderBy('id')->get();

        return view('admin.teacher_leaves.index', compact('items', 'teachers', 'substituteTeachers'));
    }

    public function review(TeacherLeaveReviewRequest $request, TeacherLeaveRequest $teacherLeaveRequest)
    {
        if ($teacherLeaveRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        DB::transaction(function () use ($request, $teacherLeaveRequest) {
            $teacherLeaveRequest->update([
                'status' => $request->string('status')->toString(),
                'review_notes' => $request->input('review_notes'),
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            if ($teacherLeaveRequest->status === 'approved' && $teacherLeaveRequest->affects_teaching_schedule) {
                $this->coverageService->syncDetectedSessions($teacherLeaveRequest);
                $teacherLeaveRequest->load('substituteAssignments');

                if ($teacherLeaveRequest->proposed_substitute_teacher_id) {
                    $substitute = Teacher::query()->find($teacherLeaveRequest->proposed_substitute_teacher_id);
                    if ($substitute) {
                        $teacherLeaveRequest->substituteAssignments->each(function (TeacherSubstituteAssignment $assignment) use ($substitute, $request) {
                            try {
                                $this->coverageService->assignSubstitute($assignment, $substitute, $request->user()->id, $assignment->notes);
                            } catch (InvalidArgumentException) {
                                // Keep pending when proposed substitute conflicts.
                            }
                        });
                    }
                }
            }
        });

        return back()->with('success', 'Status izin guru berhasil diperbarui.');
    }

    public function assignSubstitute(TeacherLeaveSubstituteUpdateRequest $request, TeacherSubstituteAssignment $assignment)
    {
        try {
            if ($request->filled('substitute_teacher_id')) {
                $substitute = Teacher::query()->findOrFail($request->integer('substitute_teacher_id'));
                $this->coverageService->assignSubstitute($assignment, $substitute, $request->user()->id, $request->input('notes'));
                return back()->with('success', 'Guru pengganti berhasil ditetapkan.');
            }

            $this->coverageService->clearSubstitute($assignment, $request->user()->id, $request->input('notes'));

            return back()->with('success', 'Guru pengganti berhasil dikosongkan.');
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
