<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherSubstituteAssignment;

class TeacherSubstituteTaskController extends Controller
{
    public function index()
    {
        $teacher = auth()->user()->teacher;

        $todayTasks = TeacherSubstituteAssignment::query()
            ->with(['class', 'subject', 'originalTeacher.user'])
            ->where('substitute_teacher_id', $teacher->id)
            ->whereDate('date', now()->toDateString())
            ->whereIn('status', ['assigned', 'completed'])
            ->orderBy('jam_ke')
            ->get();

        $upcomingTasks = TeacherSubstituteAssignment::query()
            ->with(['class', 'subject', 'originalTeacher.user'])
            ->where('substitute_teacher_id', $teacher->id)
            ->whereDate('date', '>', now()->toDateString())
            ->whereIn('status', ['assigned', 'completed'])
            ->orderBy('date')
            ->orderBy('jam_ke')
            ->paginate(20);

        return view('teacher.substitute_tasks.index', compact('todayTasks', 'upcomingTasks'));
    }
}
