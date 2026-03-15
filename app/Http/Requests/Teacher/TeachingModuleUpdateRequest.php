<?php

namespace App\Http\Requests\Teacher;

use App\Models\Assignment;
use App\Models\TeachingModule;
use Illuminate\Foundation\Http\FormRequest;

class TeachingModuleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var TeachingModule|null $module */
        $module = $this->route('module');

        return $this->user()?->role === 'teacher'
            && $module
            && $this->user()?->teacher
            && (int) $module->teacher_id === (int) $this->user()->teacher->id
            && $module->canTeacherEdit();
    }

    public function rules(): array
    {
        return [
            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $teacher = $this->user()?->teacher;
                    if (! $teacher) {
                        $fail('Data guru tidak ditemukan.');
                        return;
                    }

                    $isAssigned = Assignment::query()
                        ->where('teacher_id', $teacher->id)
                        ->where('subject_id', (int) $value)
                        ->where('class_id', (int) $this->input('class_id'))
                        ->exists();

                    if (! $isAssigned) {
                        $fail('Mapel dan kelas harus sesuai assignment guru Anda.');
                    }
                },
            ],
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'academic_year' => ['required', 'in:2021/2022,2022/2023,2023/2024,2024/2025,2025/2026,2026/2027,2027/2028,2028/2029,2029/2030,2030/2031'],
            'semester' => ['required', 'in:ganjil,genap'],
            'title' => ['required', 'string', 'max:255'],
            'teacher_notes' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,docx', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year.in' => 'Tahun ajaran harus dipilih dari daftar 2021/2022 sampai 2030/2031.',
            'file.mimes' => 'File modul harus berupa PDF atau DOCX.',
            'file.max' => 'Ukuran file modul maksimal 10MB.',
        ];
    }
}
