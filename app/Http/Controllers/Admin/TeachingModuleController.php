<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewTeachingModuleRequest;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingModule;
use Illuminate\Http\Request;

class TeachingModuleController extends Controller
{
    public function index(Request $request)
    {
        $query = TeachingModule::query()->with(['teacher.user', 'subject', 'class', 'approver', 'rejector']);

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', (int) $request->input('teacher_id'));
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', (int) $request->input('subject_id'));
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', (int) $request->input('class_id'));
        }
        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->input('academic_year'));
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->input('semester'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $items = $query->latest()->paginate(30)->withQueryString();
        $teachers = Teacher::query()->with('user')->orderBy('id')->get();
        $subjects = Subject::query()->orderBy('name')->get();
        $classes = SchoolClass::query()->orderBy('name')->get();

        return view('admin.teaching_modules.index', compact('items', 'teachers', 'subjects', 'classes'));
    }

    public function review(ReviewTeachingModuleRequest $request, TeachingModule $module)
    {
        if ($request->input('status') === TeachingModule::STATUS_APPROVED) {
            $module->update([
                'status' => TeachingModule::STATUS_APPROVED,
                'admin_notes' => $request->input('admin_notes'),
                'approved_at' => now(),
                'approved_by' => $request->user()->id,
                'rejected_at' => null,
                'rejected_by' => null,
            ]);

            return back()->with('success', 'Modul ajar berhasil disetujui.');
        }

        $module->update([
            'status' => TeachingModule::STATUS_REJECTED,
            'admin_notes' => $request->input('admin_notes'),
            'rejected_at' => now(),
            'rejected_by' => $request->user()->id,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return back()->with('success', 'Modul ajar ditolak.');
    }
}
