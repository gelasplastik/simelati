<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassRequest;
use App\Models\SchoolClass;
use App\Models\Teacher;

class ClassController extends Controller
{
    public function index()
    {
        $items = SchoolClass::query()->with('homeroomTeacher.user')->orderBy('name')->paginate(20);
        $teachers = Teacher::query()->with('user')->orderBy('id')->get();

        return view('admin.master.classes', compact('items', 'teachers'));
    }

    public function store(StoreClassRequest $request)
    {
        SchoolClass::query()->create([
            'name' => $request->name,
            'is_active' => $request->boolean('is_active', true),
            'homeroom_teacher_id' => $request->homeroom_teacher_id,
        ]);

        return back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(StoreClassRequest $request, SchoolClass $class)
    {
        $class->update([
            'name' => $request->name,
            'is_active' => $request->boolean('is_active'),
            'homeroom_teacher_id' => $request->homeroom_teacher_id,
        ]);

        return back()->with('success', 'Kelas berhasil diperbarui.');
    }
}
