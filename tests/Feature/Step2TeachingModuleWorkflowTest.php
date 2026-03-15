<?php

namespace Tests\Feature;

use App\Domain\Reporting\TeachingModuleProgressService;
use App\Models\Assignment;
use App\Models\TeachingModule;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Step2TeachingModuleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('public');
    }

    public function test_teacher_can_upload_valid_module_and_file_is_stored(): void
    {
        $teacherUser = User::query()->where('role', 'teacher')->firstOrFail();
        $teacher = $teacherUser->teacher;
        $assignment = Assignment::query()->where('teacher_id', $teacher->id)->firstOrFail();

        $this->actingAs($teacherUser)
            ->post(route('teacher.modules.store'), [
                'subject_id' => $assignment->subject_id,
                'class_id' => $assignment->class_id,
                'academic_year' => '2025/2026',
                'semester' => 'genap',
                'title' => 'Modul Uji 1',
                'teacher_notes' => 'Catatan uji',
                'file' => UploadedFile::fake()->create('modul.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect(route('teacher.modules.index'));

        $module = TeachingModule::query()->where('teacher_id', $teacher->id)->firstOrFail();
        $this->assertSame('submitted', $module->status);
        $this->assertNotNull($module->file_path);
        Storage::disk('public')->assertExists($module->file_path);
    }

    public function test_teacher_cannot_upload_module_for_unassigned_subject_class(): void
    {
        $teacherUser = User::query()->where('role', 'teacher')->firstOrFail();
        $teacher = $teacherUser->teacher;

        $otherAssignment = Assignment::query()->where('teacher_id', '!=', $teacher->id)->firstOrFail();

        $this->actingAs($teacherUser)
            ->post(route('teacher.modules.store'), [
                'subject_id' => $otherAssignment->subject_id,
                'class_id' => $otherAssignment->class_id,
                'academic_year' => '2025/2026',
                'semester' => 'genap',
                'title' => 'Modul Tidak Valid',
                'file' => UploadedFile::fake()->create('modul.pdf', 200, 'application/pdf'),
            ])
            ->assertSessionHasErrors('subject_id');
    }

    public function test_invalid_file_type_and_oversized_file_are_rejected(): void
    {
        $teacherUser = User::query()->where('role', 'teacher')->firstOrFail();
        $assignment = Assignment::query()->where('teacher_id', $teacherUser->teacher->id)->firstOrFail();

        $this->actingAs($teacherUser)
            ->post(route('teacher.modules.store'), [
                'subject_id' => $assignment->subject_id,
                'class_id' => $assignment->class_id,
                'academic_year' => '2025/2026',
                'semester' => 'genap',
                'title' => 'Modul TXT',
                'file' => UploadedFile::fake()->create('modul.txt', 100, 'text/plain'),
            ])
            ->assertSessionHasErrors('file');

        $this->actingAs($teacherUser)
            ->post(route('teacher.modules.store'), [
                'subject_id' => $assignment->subject_id,
                'class_id' => $assignment->class_id,
                'academic_year' => '2025/2026',
                'semester' => 'genap',
                'title' => 'Modul Besar',
                'file' => UploadedFile::fake()->create('modul.pdf', 12000, 'application/pdf'),
            ])
            ->assertSessionHasErrors('file');
    }

    public function test_admin_can_approve_and_reject_module_with_notes(): void
    {
        $teacherUser = User::query()->where('role', 'teacher')->firstOrFail();
        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $assignment = Assignment::query()->where('teacher_id', $teacherUser->teacher->id)->firstOrFail();

        $this->actingAs($teacherUser)->post(route('teacher.modules.store'), [
            'subject_id' => $assignment->subject_id,
            'class_id' => $assignment->class_id,
            'academic_year' => '2025/2026',
            'semester' => 'ganjil',
            'title' => 'Modul Review',
            'file' => UploadedFile::fake()->create('review.pdf', 120, 'application/pdf'),
        ]);

        $module = TeachingModule::query()->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.teaching-modules.review', $module), [
                'status' => 'approved',
                'admin_notes' => 'Sudah sesuai.',
            ])
            ->assertRedirect();

        $module->refresh();
        $this->assertSame('approved', $module->status);
        $this->assertNotNull($module->approved_at);
        $this->assertSame($admin->id, $module->approved_by);

        $this->actingAs($admin)
            ->patch(route('admin.teaching-modules.review', $module), [
                'status' => 'rejected',
                'admin_notes' => 'Perbaiki format.',
            ])
            ->assertRedirect();

        $module->refresh();
        $this->assertSame('rejected', $module->status);
        $this->assertSame('Perbaiki format.', $module->admin_notes);
        $this->assertNotNull($module->rejected_at);
        $this->assertSame($admin->id, $module->rejected_by);
    }

    public function test_approved_module_cannot_be_edited_but_rejected_can_be_resubmitted(): void
    {
        $teacherUser = User::query()->where('role', 'teacher')->firstOrFail();
        $admin = User::query()->where('role', 'admin')->firstOrFail();
        $assignment = Assignment::query()->where('teacher_id', $teacherUser->teacher->id)->firstOrFail();

        $this->actingAs($teacherUser)->post(route('teacher.modules.store'), [
            'subject_id' => $assignment->subject_id,
            'class_id' => $assignment->class_id,
            'academic_year' => '2026/2027',
            'semester' => 'ganjil',
            'title' => 'Modul Lock',
            'file' => UploadedFile::fake()->create('lock.pdf', 120, 'application/pdf'),
        ]);

        $module = TeachingModule::query()->firstOrFail();

        $this->actingAs($admin)->patch(route('admin.teaching-modules.review', $module), [
            'status' => 'approved',
            'admin_notes' => 'approved',
        ]);

        $this->actingAs($teacherUser)
            ->get(route('teacher.modules.edit', $module))
            ->assertRedirect(route('teacher.modules.index'));

        $this->actingAs($teacherUser)
            ->put(route('teacher.modules.update', $module), [
                'subject_id' => $assignment->subject_id,
                'class_id' => $assignment->class_id,
                'academic_year' => '2026/2027',
                'semester' => 'ganjil',
                'title' => 'Edited',
            ])
            ->assertForbidden();

        $this->actingAs($admin)->patch(route('admin.teaching-modules.review', $module), [
            'status' => 'rejected',
            'admin_notes' => 'revisi dulu',
        ]);

        $this->actingAs($teacherUser)
            ->post(route('teacher.modules.resubmit', $module))
            ->assertRedirect();

        $this->assertSame('submitted', $module->fresh()->status);
    }

    public function test_duplicate_module_scope_is_prevented_and_kpi_progress_is_queryable(): void
    {
        $teacherUser = User::query()->where('role', 'teacher')->firstOrFail();
        $teacher = $teacherUser->teacher;
        $assignment = Assignment::query()->where('teacher_id', $teacher->id)->firstOrFail();

        $payload = [
            'subject_id' => $assignment->subject_id,
            'class_id' => $assignment->class_id,
            'academic_year' => '2027/2028',
            'semester' => 'genap',
            'title' => 'Modul Duplicate',
            'file' => UploadedFile::fake()->create('dup.pdf', 100, 'application/pdf'),
        ];

        $this->actingAs($teacherUser)->post(route('teacher.modules.store'), $payload)->assertRedirect();
        $this->actingAs($teacherUser)->post(route('teacher.modules.store'), $payload)
            ->assertRedirect();

        $this->assertSame(1, TeachingModule::query()
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $assignment->subject_id)
            ->where('class_id', $assignment->class_id)
            ->where('academic_year', '2027/2028')
            ->where('semester', 'genap')
            ->count());

        $summary = app(TeachingModuleProgressService::class)->summaryForTeacher($teacher);

        $this->assertArrayHasKey('total_assigned', $summary);
        $this->assertArrayHasKey('uploaded_count', $summary);
        $this->assertArrayHasKey('approved_count', $summary);
        $this->assertArrayHasKey('completion_count', $summary);
        $this->assertArrayHasKey('completion_percentage', $summary);
        $this->assertGreaterThanOrEqual(0, $summary['completion_percentage']);
    }
}
