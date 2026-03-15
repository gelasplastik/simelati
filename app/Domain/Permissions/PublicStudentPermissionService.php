<?php

namespace App\Domain\Permissions;

use App\Models\Setting;
use App\Models\Student;
use App\Models\StudentPermission;
use App\Support\SafeFileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class PublicStudentPermissionService
{
    public function submit(array $payload, ?UploadedFile $attachment, ?string $ipAddress = null, ?string $userAgent = null): StudentPermission
    {
        $student = Student::query()
            ->where('nisn', $payload['nisn'])
            ->where('is_active', true)
            ->first();

        if (! $student) {
            throw new InvalidArgumentException('Siswa dengan NISN tersebut tidak ditemukan atau tidak aktif.');
        }

        $setting = Setting::active();
        $status = $setting->izin_requires_approval ? 'submitted' : 'approved';
        $path = SafeFileUpload::storePublic($attachment, 'student-permissions');

        return StudentPermission::query()->create([
            'student_id' => $student->id,
            'parent_id' => null,
            'submitter_name' => Arr::get($payload, 'nama_pengaju'),
            'submitter_phone' => Arr::get($payload, 'nomor_hp'),
            'submitter_relationship' => Arr::get($payload, 'hubungan_dengan_siswa'),
            'date_from' => Arr::get($payload, 'date_from'),
            'date_to' => Arr::get($payload, 'date_to'),
            'reason' => Arr::get($payload, 'reason'),
            'attachment' => $path,
            'attachment_path' => $path,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'status' => $status,
        ]);
    }
}
