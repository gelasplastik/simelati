<?php

namespace App\Domain\Attendance;

use App\Models\Assignment;
use App\Models\ClassAttendanceSession;
use App\Models\LateEntryRequest;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LateEntryRequestService
{
    public function create(Teacher $teacher, array $payload): LateEntryRequest
    {
        $isAssigned = Assignment::query()
            ->where('teacher_id', $teacher->id)
            ->where('class_id', $payload['class_id'])
            ->where('subject_id', $payload['subject_id'])
            ->exists();

        if (! $isAssigned) {
            throw new InvalidArgumentException('Kelas dan mapel tidak sesuai assignment guru.');
        }

        $duplicatePending = LateEntryRequest::query()
            ->where('teacher_id', $teacher->id)
            ->whereDate('date', $payload['date'])
            ->where('class_id', $payload['class_id'])
            ->where('subject_id', $payload['subject_id'])
            ->where('jam_ke', $payload['jam_ke'])
            ->where('request_type', $payload['request_type'])
            ->where('status', 'pending')
            ->exists();

        if ($duplicatePending) {
            throw new InvalidArgumentException('Permintaan pending untuk sesi yang sama sudah ada.');
        }

        return LateEntryRequest::query()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $payload['class_id'],
            'subject_id' => $payload['subject_id'],
            'date' => $payload['date'],
            'jam_ke' => $payload['jam_ke'],
            'request_type' => $payload['request_type'],
            'reason' => $payload['reason'],
            'status' => 'pending',
        ]);
    }

    public function approve(LateEntryRequest $request, User $admin, ?string $reviewNotes = null): LateEntryRequest
    {
        if ($request->status !== 'pending') {
            throw new InvalidArgumentException('Permintaan ini sudah diproses.');
        }

        return DB::transaction(function () use ($request, $admin, $reviewNotes) {
            $session = ClassAttendanceSession::query()->firstOrCreate([
                'teacher_id' => $request->teacher_id,
                'class_id' => $request->class_id,
                'subject_id' => $request->subject_id,
                'date' => $request->date->toDateString(),
                'jam_ke' => $request->jam_ke,
            ], [
                'override_allowed' => false,
            ]);

            $session->update([
                'override_allowed' => true,
                'override_reason' => $request->reason,
                'override_allowed_by' => $admin->id,
                'override_expires_at' => now()->addDay(),
            ]);

            $request->update([
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'review_notes' => $reviewNotes,
            ]);

            return $request->fresh(['teacher.user', 'class', 'subject']);
        });
    }

    public function reject(LateEntryRequest $request, User $admin, ?string $reviewNotes = null): LateEntryRequest
    {
        if ($request->status !== 'pending') {
            throw new InvalidArgumentException('Permintaan ini sudah diproses.');
        }

        $request->update([
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'review_notes' => $reviewNotes,
        ]);

        return $request->fresh(['teacher.user', 'class', 'subject']);
    }
}
