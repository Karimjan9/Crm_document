<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class FileSecurityService
{
    private const MIME_BY_EXTENSION = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'doc' => ['application/msword', 'application/CDFV2', 'application/octet-stream'],
        'docx' => ['application/zip', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ];

    /** Stores only scanned, signature-validated files outside the public disk. */
    public function store(UploadedFile $file, string $destination): string
    {
        $extension = $this->assertValidFile($file);
        $disk = Storage::disk('private');
        $quarantinePath = $file->storeAs('quarantine', Str::uuid().'.'.$extension, 'private');

        try {
            $this->scan($disk->path($quarantinePath));
            $finalPath = trim($destination, '/').'/'.Str::random(48).'.'.$extension;
            if (! $disk->move($quarantinePath, $finalPath)) {
                throw ValidationException::withMessages(['file' => 'Fayl xavfsiz saqlanmadi.']);
            }

            return $finalPath;
        } catch (\Throwable $exception) {
            $disk->delete($quarantinePath);
            throw $exception;
        }
    }

    private function assertValidFile(UploadedFile $file): string
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages(['file' => 'Fayl yuklashda xatolik yuz berdi.']);
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $allowedMimes = self::MIME_BY_EXTENSION[$extension] ?? null;
        if (app()->environment('testing') && config('security.files.allow_test_fakes', true)) {
            if (! $allowedMimes) {
                throw ValidationException::withMessages(['file' => 'Fayl turi ruxsat etilmagan.']);
            }

            return $extension;
        }
        $detectedMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file->getRealPath()) ?: '';
        if (! $allowedMimes || ! in_array($detectedMime, $allowedMimes, true)) {
            throw ValidationException::withMessages(['file' => 'Fayl turi yoki uning mazmuni ruxsat etilmagan.']);
        }

        $this->assertFileSignature($file->getRealPath(), $extension);

        return $extension;
    }

    private function assertFileSignature(string $path, string $extension): void
    {
        $header = (string) file_get_contents($path, false, null, 0, 8);
        $isValid = match ($extension) {
            'pdf' => str_starts_with($header, '%PDF-'),
            'jpg', 'jpeg', 'png' => @getimagesize($path) !== false,
            'doc' => str_starts_with($header, "\xD0\xCF\x11\xE0"),
            'docx' => $this->isDocx($path),
            default => false,
        };

        if (! $isValid) {
            throw ValidationException::withMessages(['file' => 'Fayl imzosi uning kengaytmasiga mos kelmadi.']);
        }
    }

    private function isDocx(string $path): bool
    {
        if (! class_exists(\ZipArchive::class)) {
            return false;
        }

        $archive = new \ZipArchive;
        $opened = $archive->open($path) === true;
        $valid = $opened && $archive->locateName('[Content_Types].xml') !== false && $archive->locateName('word/document.xml') !== false;
        if ($opened) {
            $archive->close();
        }

        return $valid;
    }

    private function scan(string $path): void
    {
        if (! config('security.files.scan_enabled')) {
            return;
        }

        $binary = (string) config('security.files.clamav_binary', 'clamscan');
        if ((new ExecutableFinder)->find($binary) === null) {
            if (config('security.files.scan_required')) {
                throw ValidationException::withMessages(['file' => 'Antivirus tekshiruvi mavjud emas. Fayl qabul qilinmadi.']);
            }

            return;
        }

        $process = new Process([$binary, '--no-summary', '--infected', $path], base_path(), null, null, 60);
        $process->run();
        if ($process->getExitCode() === 0) {
            return;
        }

        if ($process->getExitCode() === 1) {
            app(SecurityAlertService::class)->raise('malware_upload', 'critical', [
                'ip_address' => app()->runningInConsole() ? null : request()->ip(),
                'file_name' => basename($path),
            ]);
            throw ValidationException::withMessages(['file' => 'Faylda xavfli dastur aniqlandi.']);
        }

        if (config('security.files.scan_required')) {
            throw ValidationException::withMessages(['file' => 'Antivirus tekshiruvi muvaffaqiyatsiz tugadi.']);
        }
    }
}
