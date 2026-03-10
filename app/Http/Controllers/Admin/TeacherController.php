<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index()
    {
        $items = Teacher::query()->with('user')->latest()->paginate(20);

        return view('admin.master.teachers', compact('items'));
    }

    public function store(StoreTeacherRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = User::query()->create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'teacher',
            ]);

            Teacher::query()->create([
                'user_id' => $user->id,
                'employee_code' => $request->employee_code,
            ]);
        });

        return back()->with('success', 'Guru berhasil ditambahkan.');
    }

    public function update(StoreTeacherRequest $request, Teacher $teacher)
    {
        DB::transaction(function () use ($request, $teacher) {
            $teacher->user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->filled('password') ? Hash::make($request->password) : $teacher->user->password,
                'role' => 'teacher',
            ]);

            $teacher->update([
                'employee_code' => $request->employee_code,
            ]);
        });

        return back()->with('success', 'Data guru berhasil diperbarui.');
    }
}
