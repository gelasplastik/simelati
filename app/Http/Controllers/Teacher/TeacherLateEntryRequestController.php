<?php

namespace App\Http\Controllers\Teacher;

use App\Domain\Attendance\LateEntryRequestService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\LateEntryRequestStoreRequest;
use App\Models\Assignment;
use App\Models\LateEntryRequest;
use InvalidArgumentException;

class TeacherLateEntryRequestController extends Controller
{
    public function __construct(private readonly LateEntryRequestService $service)
    {
    }

    public function index()
    {
        $teacher = auth()->user()->teacher;
        $items = LateEntryRequest::query()
            ->with(['class', 'subject', 'reviewer'])
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->paginate(20);

        return view('teacher.late_entry_requests.index', compact('items'));
    }

    public function create()
    {
        $teacher = auth()->user()->teacher;
        $assignments = Assignment::query()
            ->with(['class', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->get();

        return view('teacher.late_entry_requests.create', compact('assignments'));
    }

    public function store(LateEntryRequestStoreRequest $request)
    {
        try {
            $this->service->create($request->user()->teacher, $request->validated());

            return redirect()->route('teacher.late-entry-requests.index')->with('success', 'Permintaan izin pengisian susulan berhasil diajukan.');
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }
    }
}
