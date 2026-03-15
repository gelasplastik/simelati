<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDutyTeacherAssignmentRequest;
use App\Models\DutyTeacherAssignment;
use App\Models\Teacher;
use Illuminate\Http\Request;

class DutyTeacherAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = DutyTeacherAssignment::query()->with(['teacher.user', 'assignedBy']);

        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->input('day_of_week'));
        }

        $items = $query->orderBy('day_of_week')->orderBy('teacher_id')->paginate(25)->withQueryString();
        $teachers = Teacher::query()->with('user')->orderBy('id')->get();
        $dayOptions = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
        ];

        return view('admin.duty.assignments.index', compact('items', 'teachers', 'dayOptions'));
    }

    public function store(StoreDutyTeacherAssignmentRequest $request)
    {
        DutyTeacherAssignment::query()->create([
            'date' => null,
            'day_of_week' => $request->string('day_of_week')->toString(),
            'teacher_id' => $request->integer('teacher_id'),
            'notes' => $request->input('notes'),
            'assigned_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Penugasan guru piket mingguan berhasil ditambahkan.');
    }

    public function destroy(DutyTeacherAssignment $assignment)
    {
        $assignment->delete();

        return back()->with('success', 'Penugasan guru piket berhasil dihapus.');
    }
}

