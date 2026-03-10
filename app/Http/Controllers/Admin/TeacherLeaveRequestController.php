<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TeacherLeaveReviewRequest;
use App\Models\Teacher;
use App\Models\TeacherLeaveRequest;
use Illuminate\Http\Request;

class TeacherLeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = TeacherLeaveRequest::query()->with('teacher.user');

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', (int) $request->input('teacher_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_from', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_to', '<=', $request->input('date_to'));
        }

        $items = $query->latest()->paginate(30)->withQueryString();
        $teachers = Teacher::query()->with('user')->orderBy('id')->get();

        return view('admin.teacher_leaves.index', compact('items', 'teachers'));
    }

    public function review(TeacherLeaveReviewRequest $request, TeacherLeaveRequest $teacherLeaveRequest)
    {
        if ($teacherLeaveRequest->status !== 'pending') {
            return back()->with('error', 'Permintaan ini sudah diproses.');
        }

        $teacherLeaveRequest->update([
            'status' => $request->string('status')->toString(),
            'review_notes' => $request->input('review_notes'),
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Status izin guru berhasil diperbarui.');
    }
}
