<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateDutyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeacher() ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('action')) {
            $this->merge(['action' => 'save']);
        }
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:save,finalize'],
            'notes' => ['nullable', 'string'],
            'teacher_rows' => ['array'],
            'teacher_rows.*.id' => ['required', 'integer'],
            'teacher_rows.*.verified_status' => ['required', 'in:present,leave,absent'],
            'teacher_rows.*.reason' => ['nullable', 'string'],
            'teacher_rows.*.has_substitute' => ['nullable', 'boolean'],
            'teacher_rows.*.notes' => ['nullable', 'string'],
            'student_rows' => ['array'],
            'student_rows.*.id' => ['required', 'integer'],
            'student_rows.*.total_students' => ['required', 'integer', 'min:0'],
            'student_rows.*.present_count' => ['required', 'integer', 'min:0'],
            'student_rows.*.sick_count' => ['required', 'integer', 'min:0'],
            'student_rows.*.izin_count' => ['required', 'integer', 'min:0'],
            'student_rows.*.alpa_count' => ['required', 'integer', 'min:0'],
            'student_rows.*.late_count' => ['required', 'integer', 'min:0'],
            'student_rows.*.notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $rows = $this->input('teacher_rows', []);

            foreach ($rows as $index => $row) {
                $status = $row['verified_status'] ?? null;
                $reason = trim((string) ($row['reason'] ?? ''));

                if (in_array($status, ['leave', 'absent'], true) && $reason === '') {
                    $validator->errors()->add("teacher_rows.$index.reason", 'Alasan wajib diisi jika verifikasi guru adalah Izin/Sakit atau Tidak Hadir.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Aksi simpan tidak terbaca. Silakan klik tombol Simpan Draft atau Finalisasi.',
            'action.in' => 'Aksi simpan tidak valid.',
            'teacher_rows.*.id.required' => 'Data baris guru tidak lengkap.',
            'teacher_rows.*.verified_status.required' => 'Status verifikasi guru wajib dipilih.',
            'teacher_rows.*.verified_status.in' => 'Status verifikasi guru tidak valid.',
            'student_rows.*.id.required' => 'Data baris rekap siswa tidak lengkap.',
            'student_rows.*.total_students.required' => 'Total siswa wajib diisi.',
            'student_rows.*.present_count.required' => 'Jumlah hadir wajib diisi.',
            'student_rows.*.sick_count.required' => 'Jumlah sakit wajib diisi.',
            'student_rows.*.izin_count.required' => 'Jumlah izin wajib diisi.',
            'student_rows.*.alpa_count.required' => 'Jumlah alpa wajib diisi.',
            'student_rows.*.late_count.required' => 'Jumlah terlambat wajib diisi.',
            'student_rows.*.total_students.min' => 'Total siswa tidak boleh minus.',
            'student_rows.*.present_count.min' => 'Jumlah hadir tidak boleh minus.',
            'student_rows.*.sick_count.min' => 'Jumlah sakit tidak boleh minus.',
            'student_rows.*.izin_count.min' => 'Jumlah izin tidak boleh minus.',
            'student_rows.*.alpa_count.min' => 'Jumlah alpa tidak boleh minus.',
            'student_rows.*.late_count.min' => 'Jumlah terlambat tidak boleh minus.',
        ];
    }
}
