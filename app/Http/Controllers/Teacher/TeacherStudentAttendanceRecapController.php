<?php

namespace App\Http\Controllers\Teacher;

use App\Domain\Reporting\StudentAttendanceRecapService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shared\StudentAttendanceRecapFilterRequest;
use App\Models\SchoolClass;

class TeacherStudentAttendanceRecapController extends Controller
{
    public function __invoke(StudentAttendanceRecapFilterRequest $request, StudentAttendanceRecapService $service)
    {
        $teacher = auth()->user()->teacher;
        $class = SchoolClass::query()
            ->where('homeroom_teacher_id', $teacher->id)
            ->where('is_active', true)
            ->firstOrFail();

        $month = (int) ($request->input('month') ?: now()->month);
        $year = (int) ($request->input('year') ?: now()->year);
        $recap = $service->build($class->id, $month, $year);

        return view('teacher.student_attendance_recap', compact('class', 'month', 'year', 'recap'));
    }
}
