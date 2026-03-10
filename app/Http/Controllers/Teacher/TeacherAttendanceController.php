<?php

namespace App\Http\Controllers\Teacher;

use App\Domain\Attendance\AttendanceService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\TeacherCheckinRequest;
use App\Http\Requests\Teacher\TeacherCheckoutRequest;
use App\Models\Attendance;
use InvalidArgumentException;

class TeacherAttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $service)
    {
    }

    public function history()
    {
        $teacher = auth()->user()->teacher;
        $items = Attendance::query()->where('teacher_id', $teacher->id)->latest('date')->paginate(20);

        return view('teacher.attendance.history', compact('items'));
    }

    public function checkin(TeacherCheckinRequest $request)
    {
        try {
            $this->service->checkin($request->user()->teacher, $request->validated());

            return redirect()->route('teacher.dashboard')->with('success', 'Absen masuk berhasil.');
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function checkout(TeacherCheckoutRequest $request)
    {
        try {
            $this->service->checkout($request->user()->teacher, $request->validated());

            return redirect()->route('teacher.dashboard')->with('success', 'Absen pulang berhasil.');
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
