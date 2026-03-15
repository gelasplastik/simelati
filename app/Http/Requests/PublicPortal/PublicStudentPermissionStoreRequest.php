<?php

namespace App\Http\Requests\PublicPortal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicStudentPermissionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nisn' => ['required', 'string', 'max:50', Rule::exists('students', 'nisn')->where(fn ($query) => $query->where('is_active', true))],
            'nama_pengaju' => ['required', 'string', 'max:255'],
            'nomor_hp' => ['required', 'string', 'max:30'],
            'hubungan_dengan_siswa' => ['required', 'string', 'max:100'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'reason' => ['required', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nisn' => 'NISN',
            'nama_pengaju' => 'Nama Pengaju',
            'nomor_hp' => 'Nomor HP',
            'hubungan_dengan_siswa' => 'Hubungan dengan Siswa',
            'date_from' => 'Tanggal Mulai',
            'date_to' => 'Tanggal Selesai',
            'reason' => 'Alasan',
        ];
    }
}

