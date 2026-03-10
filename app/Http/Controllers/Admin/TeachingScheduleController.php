<?php

namespace App\Http\Controllers\Admin;

use App\Domain\MasterData\ScheduleProfileService;
use App\Domain\MasterData\TeachingScheduleService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeachingScheduleStoreRequest;
use App\Http\Requests\Admin\TeachingScheduleUpdateRequest;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use App\Models\ScheduleProfile;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeachingScheduleController extends Controller
{
    public function __construct(
        private readonly TeachingScheduleService $service,
        private readonly ScheduleProfileService $profileService,
    ) {
    }

    public function index(Request $request)
    {
        $query = TeachingSchedule::query()->with(['teacher.user', 'class', 'subject', 'scheduleProfile']);
        $activeProfile = $this->profileService->active();

        $profileId = $request->filled('schedule_profile_id')
            ? (int) $request->input('schedule_profile_id')
            : $activeProfile->id;

        $query->where('schedule_profile_id', $profileId);

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', (int) $request->input('teacher_id'));
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', (int) $request->input('class_id'));
        }

        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->input('day_of_week'));
        }

        $items = $query->orderBy('day_of_week')->orderBy('jam_ke')->paginate(30)->withQueryString();
        $teachers = Teacher::query()->with('user')->orderBy('id')->get();
        $classes = SchoolClass::query()->orderBy('name')->get();
        $subjects = Subject::query()->orderBy('name')->get();
        $profiles = $this->profileService->all();
        $dayOptions = $this->service->dayOptions();

        return view('admin.master.teaching_schedules', compact('items', 'teachers', 'classes', 'subjects', 'dayOptions', 'profiles', 'activeProfile', 'profileId'));
    }

    public function store(TeachingScheduleStoreRequest $request)
    {
        try {
            $payload = $request->validated();
            $this->service->ensureAssignmentMatches(
                $payload['teacher_id'],
                $payload['class_id'],
                $payload['subject_id'],
            );
            $this->service->ensureNoDuplicate($payload);

            TeachingSchedule::query()->create([
                ...$payload,
                'is_active' => $request->boolean('is_active'),
            ]);
        } catch (\InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        return back()->with('success', 'Jadwal mengajar berhasil ditambahkan.');
    }

    public function update(TeachingScheduleUpdateRequest $request, TeachingSchedule $schedule)
    {
        try {
            $payload = $request->validated();
            $this->service->ensureAssignmentMatches(
                $payload['teacher_id'],
                $payload['class_id'],
                $payload['subject_id'],
            );
            $this->service->ensureNoDuplicate($payload, $schedule->id);

            $schedule->update([
                ...$payload,
                'is_active' => $request->boolean('is_active'),
            ]);
        } catch (\InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        return back()->with('success', 'Jadwal mengajar berhasil diperbarui.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = TeachingSchedule::query()->with(['teacher.user', 'class', 'subject', 'scheduleProfile']);
        $activeProfile = $this->profileService->active();
        $profileId = $request->filled('schedule_profile_id')
            ? (int) $request->input('schedule_profile_id')
            : $activeProfile->id;
        $query->where('schedule_profile_id', $profileId);

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', (int) $request->input('teacher_id'));
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', (int) $request->input('class_id'));
        }

        if ($request->filled('day_of_week')) {
            $query->where('day_of_week', $request->input('day_of_week'));
        }

        $rows = $query->orderBy('day_of_week')->orderBy('jam_ke')->get();
        $dayOptions = $this->service->dayOptions();

        return response()->streamDownload(function () use ($rows, $dayOptions) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Profil', 'Guru', 'Kelas', 'Mapel', 'Hari', 'Jam Ke', 'Start', 'End', 'Status']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->scheduleProfile?->name ?? '-',
                    $row->teacher->user->name,
                    $row->class->name,
                    $row->subject->name,
                    $dayOptions[$row->day_of_week] ?? $row->day_of_week,
                    $row->jam_ke,
                    $row->start_time,
                    $row->end_time,
                    $row->is_active ? 'aktif' : 'nonaktif',
                ]);
            }
            fclose($handle);
        }, 'teaching-schedules.csv', ['Content-Type' => 'text/csv']);
    }

    public function profiles()
    {
        $profiles = $this->profileService->all();
        $activeProfile = $this->profileService->active();

        return view('admin.master.schedule_profiles', compact('profiles', 'activeProfile'));
    }

    public function activateProfile(ScheduleProfile $profile)
    {
        $this->profileService->activate($profile);

        return back()->with('success', 'Profil jadwal aktif berhasil diperbarui ke '.$profile->name.'.');
    }
}
