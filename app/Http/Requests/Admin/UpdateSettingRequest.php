<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'school_lat' => ['required', 'numeric', 'between:-90,90'],
            'school_lng' => ['required', 'numeric', 'between:-180,180'],
            'radius_m' => ['required', 'integer', 'min:1'],
            'start_time' => ['required', 'date_format:H:i'],
            'late_tolerance_time' => ['required', 'date_format:H:i'],
            'min_checkout_time' => ['required', 'date_format:H:i', 'after:late_tolerance_time'],
            'izin_requires_approval' => ['nullable', 'boolean'],
            'journal_lock_enabled' => ['nullable', 'boolean'],
        ];
    }
}
