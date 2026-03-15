<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use ValueError;

class SafeFileUpload
{
    public static function storePublic(?UploadedFile $file, string $directory): ?string
    {
        if (! $file) {
            return null;
        }

        $error = $file->getError();
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($error !== UPLOAD_ERR_OK || ! $file->isValid()) {
            throw new InvalidArgumentException('Upload lampiran gagal: '.$file->getErrorMessage());
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $filename = uniqid('', true).'.'.$extension;
        $targetPath = trim($directory, '/').'/'.$filename;

        try {
            Storage::disk('public')->putFileAs($directory, $file, $filename);

            return $targetPath;
        } catch (ValueError) {
            $sourcePath = $file->getPathname();
            if (! $sourcePath || ! is_file($sourcePath) || ! is_readable($sourcePath)) {
                throw new InvalidArgumentException('Lampiran tidak valid. Cek batas upload dan folder temporary PHP.');
            }

            Storage::disk('public')->put($targetPath, file_get_contents($sourcePath));

            return $targetPath;
        }
    }
}
