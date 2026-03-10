<?php

namespace App\Domain\MasterData;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class StudentImportService
{
    public function import(UploadedFile $file): array
    {
        $rows = $this->readRows($file);

        $summary = [
            'total_rows' => count($rows),
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $seenNisn = [];
        $classMap = SchoolClass::query()->get()->mapWithKeys(function (SchoolClass $class) {
            return [Str::lower(trim($class->name)) => $class];
        });

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $nisn = trim((string) ($row['nisn'] ?? ''));
            $name = trim((string) ($row['nama siswa'] ?? $row['nama'] ?? ''));
            $className = trim((string) ($row['kelas'] ?? ''));
            $statusRaw = Str::lower(trim((string) ($row['status aktif'] ?? $row['status'] ?? $row['aktif'] ?? '1')));

            if ($nisn === '' || $name === '' || $className === '') {
                $summary['skipped']++;
                $summary['errors'][] = "Baris {$line}: kolom NISN, Nama Siswa, dan Kelas wajib diisi.";
                continue;
            }

            if (isset($seenNisn[$nisn])) {
                $summary['skipped']++;
                $summary['errors'][] = "Baris {$line}: NISN {$nisn} duplikat pada file import.";
                continue;
            }
            $seenNisn[$nisn] = true;

            $class = $classMap->get(Str::lower($className));
            if (! $class) {
                $summary['skipped']++;
                $summary['errors'][] = "Baris {$line}: kelas '{$className}' tidak ditemukan.";
                continue;
            }

            $isActive = ! in_array($statusRaw, ['0', 'false', 'nonaktif', 'tidak', 'n'], true);

            DB::transaction(function () use ($nisn, $name, $class, $isActive, &$summary) {
                $student = Student::query()->where('nisn', $nisn)->first();

                if ($student) {
                    $student->update([
                        'full_name' => $name,
                        'class_id' => $class->id,
                        'is_active' => $isActive,
                    ]);
                    $summary['updated']++;

                    return;
                }

                Student::query()->create([
                    'nisn' => $nisn,
                    'full_name' => $name,
                    'class_id' => $class->id,
                    'is_active' => $isActive,
                ]);
                $summary['inserted']++;
            });
        }

        return $summary;
    }

    private function readRows(UploadedFile $file): array
    {
        $extension = Str::lower($file->getClientOriginalExtension());

        $rows = match ($extension) {
            'csv', 'txt' => $this->readCsv($file->getRealPath()),
            'xlsx' => $this->readXlsx($file->getRealPath()),
            default => throw new RuntimeException('Format file tidak didukung.'),
        };

        if (empty($rows)) {
            throw new RuntimeException('File import kosong atau format tidak sesuai.');
        }

        return $rows;
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new RuntimeException('Gagal membaca file CSV.');
        }

        $headers = [];
        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {
            if (empty($headers)) {
                $headers = $this->normalizeHeaders($data);
                continue;
            }

            if ($this->isEmptyRow($data)) {
                continue;
            }

            $rows[] = $this->mapRow($headers, $data);
        }

        fclose($handle);

        return $rows;
    }

    private function readXlsx(string $path): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi ZIP PHP belum aktif, tidak bisa membaca XLSX.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('File XLSX tidak dapat dibuka.');
        }

        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml) {
            $shared = new SimpleXMLElement($sharedStringsXml);
            foreach ($shared->si as $si) {
                $texts = [];
                if (isset($si->t)) {
                    $texts[] = (string) $si->t;
                } else {
                    foreach ($si->r as $r) {
                        $texts[] = (string) $r->t;
                    }
                }
                $sharedStrings[] = implode('', $texts);
            }
        }

        $sheetXmlRaw = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (! $sheetXmlRaw) {
            $zip->close();
            throw new RuntimeException('Worksheet pertama pada XLSX tidak ditemukan.');
        }

        $sheetXml = new SimpleXMLElement($sheetXmlRaw);
        $sheetXml->registerXPathNamespace('x', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rowNodes = $sheetXml->xpath('//x:sheetData/x:row') ?: [];

        $headers = [];
        $rows = [];

        foreach ($rowNodes as $rowIndex => $rowNode) {
            $values = [];
            foreach ($rowNode->c as $cell) {
                $ref = (string) $cell['r'];
                $columnIndex = $this->columnIndexFromReference($ref);
                $type = (string) $cell['t'];
                $value = isset($cell->v) ? (string) $cell->v : '';
                if ($type === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                }
                $values[$columnIndex] = $value;
            }
            ksort($values);
            $values = array_values($values);

            if ($rowIndex === 0) {
                $headers = $this->normalizeHeaders($values);
                continue;
            }

            if ($this->isEmptyRow($values)) {
                continue;
            }

            $rows[] = $this->mapRow($headers, $values);
        }

        $zip->close();

        return $rows;
    }

    private function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $header = Str::of((string) $header)->lower()->trim()->replace("\xEF\xBB\xBF", '')->value();
            return preg_replace('/\s+/', ' ', $header ?? '');
        }, $headers);
    }

    private function mapRow(array $headers, array $values): array
    {
        $mapped = [];
        foreach ($headers as $index => $header) {
            if ($header === '') {
                continue;
            }
            $mapped[$header] = trim((string) ($values[$index] ?? ''));
        }

        return $mapped;
    }

    private function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function columnIndexFromReference(string $reference): int
    {
        if (! preg_match('/^([A-Z]+)/i', $reference, $matches)) {
            return 0;
        }

        $letters = strtoupper($matches[1]);
        $index = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return max(0, $index - 1);
    }
}
