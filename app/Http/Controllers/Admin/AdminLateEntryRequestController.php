<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Attendance\LateEntryRequestService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LateEntryRequestReviewRequest;
use App\Models\LateEntryRequest;
use App\Models\Teacher;
use Symfony\Component\HttpFoundation\StreamedResponse;
use InvalidArgumentException;

class AdminLateEntryRequestController extends Controller
{
    public function __construct(private readonly LateEntryRequestService $service)
    {
    }

    public function index()
    {
        $query = LateEntryRequest::query()->with(['teacher.user', 'class', 'subject', 'reviewer']);

        if (request('teacher_id')) {
            $query->where('teacher_id', request('teacher_id'));
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        if (request('date_from')) {
            $query->whereDate('date', '>=', request('date_from'));
        }

        if (request('date_to')) {
            $query->whereDate('date', '<=', request('date_to'));
        }

        $items = $query->latest()->paginate(25)->withQueryString();
        $teachers = Teacher::query()->with('user')->get();

        return view('admin.late_entry_requests.index', compact('items', 'teachers'));
    }

    public function approve(LateEntryRequestReviewRequest $request, LateEntryRequest $lateEntryRequest)
    {
        try {
            $this->service->approve($lateEntryRequest, $request->user(), $request->review_notes);
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Permintaan berhasil disetujui. Override sesi aktif 24 jam.');
    }

    public function reject(LateEntryRequestReviewRequest $request, LateEntryRequest $lateEntryRequest)
    {
        try {
            $this->service->reject($lateEntryRequest, $request->user(), $request->review_notes);
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Permintaan berhasil ditolak.');
    }

    public function exportCsv(): StreamedResponse
    {
        $rows = LateEntryRequest::query()->with(['teacher.user', 'class', 'subject'])->latest()->get();

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Guru', 'Tanggal', 'Kelas', 'Mapel', 'Jam Ke', 'Jenis', 'Alasan', 'Status', 'Review Notes']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->teacher->user->name,
                    $row->date->format('Y-m-d'),
                    $row->class->name,
                    $row->subject->name,
                    $row->jam_ke,
                    $row->request_type,
                    $row->reason,
                    $row->status,
                    $row->review_notes,
                ]);
            }
            fclose($handle);
        }, 'late-entry-requests.csv', ['Content-Type' => 'text/csv']);
    }
}
