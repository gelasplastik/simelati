<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAssignmentRequest;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;

class AssignmentController extends Controller
{
    public function index()
    {
        $items = Assignment::query()->with(['teacher.user', 'subject', 'class'])->latest()->paginate(30);
        $teachers = Teacher::query()->with('user')->get();
        $subjects = Subject::query()->orderBy('name')->get();
        $classes = SchoolClass::query()->orderBy('name')->get();

        return view('admin.master.assignments', compact('items', 'teachers', 'subjects', 'classes'));
    }

    public function store(StoreAssignmentRequest $request)
    {
        Assignment::query()->create($request->only(['teacher_id', 'subject_id', 'class_id']));

        return back()->with('success', 'Assignment berhasil ditambahkan.');
    }

    public function update(StoreAssignmentRequest $request, Assignment $assignment)
    {
        $assignment->update($request->only(['teacher_id', 'subject_id', 'class_id']));

        return back()->with('success', 'Assignment berhasil diperbarui.');
    }

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();

        return back()->with('success', 'Assignment berhasil dihapus.');
    }
}
