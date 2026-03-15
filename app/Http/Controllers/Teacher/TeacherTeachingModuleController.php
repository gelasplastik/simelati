<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\TeachingModuleStoreRequest;
use App\Http\Requests\Teacher\TeachingModuleUpdateRequest;
use App\Models\Assignment;
use App\Models\TeachingModule;
use App\Support\SafeFileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class TeacherTeachingModuleController extends Controller
{
    public function index()
    {
        $teacher = auth()->user()->teacher;

        $items = TeachingModule::query()
            ->with(['subject', 'class'])
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->paginate(20);

        return view('teacher.teaching_modules.index', compact('items'));
    }

    public function create()
    {
        $teacher = auth()->user()->teacher;
        $assignmentOptions = $this->assignmentOptions($teacher->id);

        return view('teacher.teaching_modules.create', compact('assignmentOptions'));
    }

    public function store(TeachingModuleStoreRequest $request)
    {
        $teacher = $request->user()->teacher;

        $existing = TeachingModule::query()
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $request->integer('subject_id'))
            ->where('class_id', $request->integer('class_id'))
            ->where('academic_year', $request->input('academic_year'))
            ->where('semester', $request->input('semester'))
            ->first();

        if ($existing) {
            return redirect()
                ->route('teacher.modules.edit', $existing)
                ->with('error', 'Modul untuk mapel/kelas/tahun ajaran/semester ini sudah ada. Silakan revisi data yang sudah ada.');
        }

        try {
            $path = SafeFileUpload::storePublic($request->file('file'), 'teaching_modules');
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        TeachingModule::query()->create([
            'teacher_id' => $teacher->id,
            'subject_id' => $request->integer('subject_id'),
            'class_id' => $request->integer('class_id'),
            'academic_year' => $request->input('academic_year'),
            'semester' => $request->input('semester'),
            'title' => $request->input('title'),
            'file_path' => $path,
            'status' => TeachingModule::STATUS_SUBMITTED,
            'teacher_notes' => $request->input('teacher_notes'),
            'uploaded_at' => now(),
            'submitted_at' => now(),
            'approved_at' => null,
            'approved_by' => null,
            'rejected_at' => null,
            'rejected_by' => null,
            'admin_notes' => null,
        ]);

        return redirect()->route('teacher.modules.index')->with('success', 'Modul ajar berhasil diunggah dan menunggu review admin.');
    }

    public function edit(TeachingModule $module)
    {
        $teacher = auth()->user()->teacher;

        if ((int) $module->teacher_id !== (int) $teacher->id) {
            abort(403);
        }

        if (! $module->canTeacherEdit()) {
            return redirect()->route('teacher.modules.index')->with('error', 'Modul yang sudah disubmit/approved tidak dapat diedit.');
        }

        $assignmentOptions = $this->assignmentOptions($teacher->id);

        return view('teacher.teaching_modules.edit', compact('module', 'assignmentOptions'));
    }

    public function update(TeachingModuleUpdateRequest $request, TeachingModule $module)
    {
        $teacher = $request->user()->teacher;

        $duplicate = TeachingModule::query()
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $request->integer('subject_id'))
            ->where('class_id', $request->integer('class_id'))
            ->where('academic_year', $request->input('academic_year'))
            ->where('semester', $request->input('semester'))
            ->where('id', '!=', $module->id)
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'subject_id' => 'Sudah ada modul lain untuk mapel/kelas/tahun ajaran/semester ini.',
            ]);
        }

        $path = $module->file_path;
        try {
            if ($request->hasFile('file')) {
                $path = SafeFileUpload::storePublic($request->file('file'), 'teaching_modules');
                if ($module->file_path) {
                    Storage::disk('public')->delete($module->file_path);
                }
            }
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        $module->update([
            'subject_id' => $request->integer('subject_id'),
            'class_id' => $request->integer('class_id'),
            'academic_year' => $request->input('academic_year'),
            'semester' => $request->input('semester'),
            'title' => $request->input('title'),
            'teacher_notes' => $request->input('teacher_notes'),
            'file_path' => $path,
            'uploaded_at' => now(),
        ]);

        return redirect()->route('teacher.modules.index')->with('success', 'Modul ajar berhasil diperbarui.');
    }

    public function resubmit(TeachingModule $module)
    {
        $teacher = auth()->user()->teacher;

        if ((int) $module->teacher_id !== (int) $teacher->id) {
            abort(403);
        }

        if (! in_array($module->status, [TeachingModule::STATUS_REJECTED, TeachingModule::STATUS_DRAFT], true)) {
            return back()->with('error', 'Modul ini tidak bisa dikirim ulang.');
        }

        $module->update([
            'status' => TeachingModule::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'rejected_at' => null,
            'rejected_by' => null,
            'admin_notes' => null,
        ]);

        return back()->with('success', 'Modul ajar berhasil dikirim ulang untuk direview admin.');
    }

    public function destroy(TeachingModule $module)
    {
        $teacher = auth()->user()->teacher;

        if ((int) $module->teacher_id !== (int) $teacher->id) {
            abort(403);
        }

        if (! $module->canTeacherEdit()) {
            return back()->with('error', 'Modul yang sudah disubmit/approved tidak dapat dihapus.');
        }

        if ($module->file_path) {
            Storage::disk('public')->delete($module->file_path);
        }

        $module->delete();

        return back()->with('success', 'Modul ajar berhasil dihapus.');
    }

    private function assignmentOptions(int $teacherId)
    {
        return Assignment::query()
            ->with(['subject', 'class'])
            ->where('teacher_id', $teacherId)
            ->orderBy('class_id')
            ->orderBy('subject_id')
            ->get();
    }
}
