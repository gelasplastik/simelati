<?php

namespace App\Http\Controllers\Admin;

use App\Domain\MasterData\StudentImportService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentImportRequest;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStudentImportController extends Controller
{
    public function __construct(private readonly StudentImportService $service)
    {
    }

    public function index()
    {
        return view('admin.master.students-import');
    }

    public function store(StudentImportRequest $request)
    {
        try {
            $summary = $this->service->import($request->file('file'));

            return back()->with('success', 'Import siswa selesai diproses.')->with('import_summary', $summary);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function template(): StreamedResponse
    {
        $rows = [
            ['NISN', 'Nama Siswa', 'Kelas', 'Status Aktif'],
            ['00620260001', 'Siswa Contoh 1', '1', '1'],
            ['00620260002', 'Siswa Contoh 2', '2A', '1'],
        ];

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'template-import-siswa.csv', ['Content-Type' => 'text/csv']);
    }
}
