<?php

namespace App\Http\Controllers\Teacher;

use App\Domain\Attendance\SessionAccessService;
use App\Domain\ClassAttendance\TeachingJournalService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\TeachingJournalStoreRequest;
use App\Http\Requests\Teacher\TeachingJournalUpdateRequest;
use App\Models\ClassAttendanceSession;
use App\Models\TeachingJournal;
use InvalidArgumentException;
use Illuminate\Support\Facades\Storage;

class TeacherTeachingJournalController extends Controller
{
    public function __construct(
        private readonly TeachingJournalService $service,
        private readonly SessionAccessService $accessService,
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
            ->with(['class', 'subject', 'teachingJournal'])
            ->where('teacher_id', $teacher->id)
            ->latest('date')
            ->paginate(20);

        return view('teacher.teaching_journals.index', compact('sessions'));
    }

    public function create(ClassAttendanceSession $session)
    {
        $teacher = auth()->user()->teacher;
        abort_unless($session->teacher_id === $teacher->id, 403);

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

        abort_if($session->attendances()->count() === 0, 422, 'Silakan isi absensi kelas terlebih dahulu sebelum mengisi jurnal mengajar.');

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
            abort_unless($session->teacher_id === $teacher->id, 403);
            $this->accessService->ensureJournalAccessAllowed($session);

            if ($session->attendances()->count() === 0) {
                throw new InvalidArgumentException('Silakan isi absensi kelas terlebih dahulu sebelum mengisi jurnal mengajar.');
            }

            $payload = $request->validated();
            if ($request->hasFile('attachment')) {
                $payload['attachment_path'] = $request->file('attachment')->store('teaching-journals', 'public');
            }

            $journal = $this->service->createOrUpdate($session, $teacher->id, $payload);

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

            $this->accessService->ensureTeacherCheckedInToday($teacher);
            $this->accessService->ensureJournalAccessAllowed($journal->classAttendanceSession);

            $payload = $request->validated();
            if ($request->hasFile('attachment')) {
                $newPath = $request->file('attachment')->store('teaching-journals', 'public');
                if ($journal->attachment_path) {
                    Storage::disk('public')->delete($journal->attachment_path);
                }
                $payload['attachment_path'] = $newPath;
            }

            $this->service->createOrUpdate($journal->classAttendanceSession, $teacher->id, $payload);

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
