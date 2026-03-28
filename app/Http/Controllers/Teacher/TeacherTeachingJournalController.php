<?php

namespace App\Http\Controllers\Teacher;

use App\Domain\Attendance\LeaveCoverageService;
use App\Domain\Attendance\SessionAccessService;
use App\Domain\ClassAttendance\TeachingJournalService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\TeachingJournalStoreRequest;
use App\Http\Requests\Teacher\TeachingJournalUpdateRequest;
use App\Models\ClassAttendanceSession;
use App\Models\Setting;
use App\Models\TeachingJournal;
use App\Support\SafeFileUpload;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class TeacherTeachingJournalController extends Controller
{
    public function __construct(
        private readonly TeachingJournalService $service,
        private readonly SessionAccessService $accessService,
        private readonly LeaveCoverageService $coverageService,
    ) {
    }

    public function index()
    {
        $teacher = auth()->user()->teacher;

        try {
            $this->accessService->ensureTeacherCheckedInToday($teacher);
        } catch (InvalidArgumentException $exception) {
            return redirect()->route('teacher.dashboard')->with('error', $exception->getMessage());
        }

        $sessions = ClassAttendanceSession::query()
            ->with(['class', 'subject', 'teachingJournal', 'originalTeacher.user', 'executingTeacher.user'])
            ->where(function ($q) use ($teacher) {
                $q->where('teacher_id', $teacher->id)
                    ->orWhere('executing_teacher_id', $teacher->id);
            })
            ->latest('date')
            ->paginate(20);

        return view('teacher.teaching_journals.index', compact('sessions'));
    }

    public function create(ClassAttendanceSession $session)
    {
        $teacher = auth()->user()->teacher;

        if (! $this->coverageService->canTeacherExecuteSession($teacher, $session)) {
            abort(403);
        }

        try {
            $this->accessService->ensureTeacherCheckedInToday($teacher);
            $this->accessService->ensureJournalAccessAllowed($session);
        } catch (InvalidArgumentException $exception) {
            $requestUrl = route('teacher.late-entry-requests.create', [
                'date' => $session->date->toDateString(),
                'class_id' => $session->class_id,
                'subject_id' => $session->subject_id,
                'jam_ke' => $session->jam_ke,
                'request_type' => 'journal',
            ]);

            return redirect()->route('teacher.teaching-journals.index')->with('error', $exception->getMessage())->with('late_entry_request_url', $requestUrl);
        }

        abort_if(! $this->isAttendanceJournalOpenEnabled() && $session->attendances()->count() === 0, 422, 'Silakan isi absensi kelas terlebih dahulu sebelum mengisi jurnal mengajar.');

        $session->load(['class', 'subject', 'attendances.student']);
        $payload = $this->service->preloadForForm($session);

        return view('teacher.teaching_journals.form', [
            'mode' => 'create',
            'session' => $payload['session'],
            'journal' => $payload['journal'],
            'absentText' => $payload['absent_students_text'],
            'hasOverride' => $session->hasValidOverride(),
        ]);
    }

    public function store(TeachingJournalStoreRequest $request)
    {
        try {
            $teacher = $request->user()->teacher;
            $this->accessService->ensureTeacherCheckedInToday($teacher);

            $session = ClassAttendanceSession::query()->findOrFail($request->integer('class_attendance_session_id'));

            if (! $this->coverageService->canTeacherExecuteSession($teacher, $session)) {
                abort(403);
            }

            $this->accessService->ensureJournalAccessAllowed($session);

            if (! $this->isAttendanceJournalOpenEnabled() && $session->attendances()->count() === 0) {
                throw new InvalidArgumentException('Silakan isi absensi kelas terlebih dahulu sebelum mengisi jurnal mengajar.');
            }

            $payload = $request->validated();
            $payload['attachment_path'] = SafeFileUpload::storePublic($request->file('attachment'), 'teaching-journals');

            $journal = $this->service->createOrUpdate($session, $teacher->id, $payload);
            $this->coverageService->markCompletedForSession($session);

            return redirect()->route('teacher.teaching-journals.edit', $journal)->with('success', 'Jurnal mengajar berhasil disimpan.');
        } catch (InvalidArgumentException $exception) {
            $requestUrl = isset($session)
                ? route('teacher.late-entry-requests.create', [
                    'date' => $session->date->toDateString(),
                    'class_id' => $session->class_id,
                    'subject_id' => $session->subject_id,
                    'jam_ke' => $session->jam_ke,
                    'request_type' => 'journal',
                ])
                : route('teacher.late-entry-requests.create');

            return back()->with('error', $exception->getMessage())->with('late_entry_request_url', $requestUrl)->withInput();
        }
    }

    public function edit(TeachingJournal $journal)
    {
        $teacher = auth()->user()->teacher;
        abort_unless($journal->teacher_id === $teacher->id, 403);

        $session = $journal->classAttendanceSession()->with(['class', 'subject', 'attendances.student'])->firstOrFail();

        if (! $this->coverageService->canTeacherExecuteSession($teacher, $session)) {
            abort(403);
        }

        try {
            $this->accessService->ensureTeacherCheckedInToday($teacher);
            $this->accessService->ensureJournalAccessAllowed($session);
        } catch (InvalidArgumentException $exception) {
            $requestUrl = route('teacher.late-entry-requests.create', [
                'date' => $session->date->toDateString(),
                'class_id' => $session->class_id,
                'subject_id' => $session->subject_id,
                'jam_ke' => $session->jam_ke,
                'request_type' => 'journal',
            ]);

            return redirect()->route('teacher.teaching-journals.history')->with('error', $exception->getMessage())->with('late_entry_request_url', $requestUrl);
        }

        $payload = $this->service->preloadForForm($session);

        return view('teacher.teaching_journals.form', [
            'mode' => 'edit',
            'session' => $payload['session'],
            'journal' => $payload['journal'],
            'absentText' => $payload['absent_students_text'],
            'hasOverride' => $session->hasValidOverride(),
        ]);
    }

    public function update(TeachingJournalUpdateRequest $request, TeachingJournal $journal)
    {
        try {
            $teacher = $request->user()->teacher;
            abort_unless($journal->teacher_id === $teacher->id, 403);

            $session = $journal->classAttendanceSession;
            if (! $this->coverageService->canTeacherExecuteSession($teacher, $session)) {
                abort(403);
            }

            $this->accessService->ensureTeacherCheckedInToday($teacher);
            $this->accessService->ensureJournalAccessAllowed($session);

            $payload = $request->validated();
            if ($request->hasFile('attachment')) {
                $newPath = SafeFileUpload::storePublic($request->file('attachment'), 'teaching-journals');
                if ($journal->attachment_path) {
                    Storage::disk('public')->delete($journal->attachment_path);
                }
                $payload['attachment_path'] = $newPath;
            }

            $this->service->createOrUpdate($journal->classAttendanceSession, $teacher->id, $payload);
            $this->coverageService->markCompletedForSession($session);

            return back()->with('success', 'Jurnal mengajar berhasil diperbarui.');
        } catch (InvalidArgumentException $exception) {
            $session = $journal->classAttendanceSession;
            $requestUrl = route('teacher.late-entry-requests.create', [
                'date' => $session->date->toDateString(),
                'class_id' => $session->class_id,
                'subject_id' => $session->subject_id,
                'jam_ke' => $session->jam_ke,
                'request_type' => 'journal',
            ]);

            return back()->with('error', $exception->getMessage())->with('late_entry_request_url', $requestUrl)->withInput();
        }
    }


    private function isAttendanceJournalOpenEnabled(): bool
    {
        return (bool) (Setting::active()->attendance_journal_open_enabled ?? false);
    }

    public function history()
    {
        $teacher = auth()->user()->teacher;

        try {
            $this->accessService->ensureTeacherCheckedInToday($teacher);
        } catch (InvalidArgumentException $exception) {
            return redirect()->route('teacher.dashboard')->with('error', $exception->getMessage());
        }

        $items = TeachingJournal::query()
            ->with(['class', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->latest('date')
            ->paginate(20);

        return view('teacher.teaching_journals.history', compact('items'));
    }
}


