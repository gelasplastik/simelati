<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Models\SchoolClass;
use App\Models\Student;

class StudentController extends Controller
{
    public function index()
    {
        $items = Student::query()->with('class')->latest()->paginate(20);
        $classes = SchoolClass::query()->orderBy('name')->get();

        return view('admin.master.students', compact('items', 'classes'));
    }

    public function store(StoreStudentRequest $request)
    {
        Student::query()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function update(StoreStudentRequest $request, Student $student)
    {
        $student->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Siswa berhasil diperbarui.');
    }
}
