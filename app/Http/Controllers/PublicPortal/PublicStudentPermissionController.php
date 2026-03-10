<?php

namespace App\Http\Controllers\PublicPortal;

use App\Domain\Permissions\PublicStudentPermissionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\PublicPortal\PublicStudentPermissionStoreRequest;
use App\Models\Student;
use InvalidArgumentException;

class PublicStudentPermissionController extends Controller
{
    public function __construct(private readonly PublicStudentPermissionService $service)
    {
    }

    public function create()
    {
        $nisn = trim((string) request('nisn'));
        $student = null;

        if ($nisn !== '') {
            $student = Student::query()
                ->with('class')
                ->where('nisn', $nisn)
                ->where('is_active', true)
                ->first();

            if (! $student) {
                session()->flash('error', 'Siswa dengan NISN tersebut tidak ditemukan atau tidak aktif.');
            }
        }

        return view('public.permissions.create', compact('nisn', 'student'));
    }

    public function store(PublicStudentPermissionStoreRequest $request)
    {
        try {
            $this->service->submit(
                $request->validated(),
                $request->file('attachment'),
                $request->ip(),
                $request->userAgent()
            );
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage())->withInput();
        }

        return redirect()->route('public.permissions.create')->with('success', 'Pengajuan izin berhasil dikirim.');
    }
}
