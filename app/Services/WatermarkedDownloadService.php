<?php

namespace App\Services;

use App\Models\User;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class WatermarkedDownloadService
{
    public function download(string $path, string $downloadName, ?User $user = null)
    {
        $disk = Storage::disk('private');
        $label = $this->label($user);
        app(AuditLogger::class)->event('file.downloaded', [
            'path_hash' => hash('sha256', $path),
            'watermark' => $label,
        ]);

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($user && in_array($extension, ['jpg', 'jpeg', 'png'], true) && function_exists('imagecreatetruecolor')) {
            return $this->watermarkedImage($disk->path($path), $downloadName, $extension, $label);
        }
        if ($user && $extension === 'pdf' && $this->qpdfAvailable()) {
            return $this->watermarkedPdf($disk->path($path), $downloadName, $label);
        }

        // Non-renderable office formats retain an immutable, audited download label.
        return $disk->download($path, $downloadName, [
            'X-Content-Type-Options' => 'nosniff',
            'X-CRM-Watermark' => $label,
        ]);
    }

    private function watermarkedImage(string $path, string $name, string $extension, string $label)
    {
        $image = $extension === 'png' ? @imagecreatefrompng($path) : @imagecreatefromjpeg($path);
        if (! $image) {
            return response()->download($path, $name, ['X-Content-Type-Options' => 'nosniff']);
        }

        $color = imagecolorallocatealpha($image, 90, 0, 0, 80);
        imagestring($image, 5, 18, 18, $label, $color);
        imagestring($image, 5, max(18, imagesx($image) - min(imagesx($image) - 18, strlen($label) * 9)), max(18, imagesy($image) - 30), $label, $color);

        return response()->streamDownload(function () use ($image, $extension): void {
            $extension === 'png' ? imagepng($image) : imagejpeg($image, null, 92);
            imagedestroy($image);
        }, $name, [
            'Content-Type' => $extension === 'png' ? 'image/png' : 'image/jpeg',
            'X-Content-Type-Options' => 'nosniff',
            'X-CRM-Watermark' => $this->label(auth()->user()),
        ]);
    }

    private function watermarkedPdf(string $path, string $name, string $label)
    {
        $directory = storage_path('app/private/watermarks');
        if (! is_dir($directory)) {
            mkdir($directory, 0700, true);
        }
        $id = bin2hex(random_bytes(12));
        $overlay = $directory.'/'.$id.'-overlay.pdf';
        $output = $directory.'/'.$id.'-output.pdf';

        try {
            $dompdf = new Dompdf;
            $dompdf->loadHtml('<html><body style="margin:0"><div style="position:fixed;top:45%;left:5%;font-size:28px;color:#aa0000;opacity:.30;transform:rotate(-25deg)">'.e($label).'</div></body></html>');
            $dompdf->setPaper('a4');
            $dompdf->render();
            file_put_contents($overlay, $dompdf->output());
            (new Process([(string) config('security.watermark.qpdf_binary'), '--overlay', $overlay, '--repeat=1', '--', $path, $output], base_path(), null, null, 60))->mustRun();

            return response()->download($output, $name, ['X-Content-Type-Options' => 'nosniff', 'X-CRM-Watermark' => $label])
                ->deleteFileAfterSend(true);
        } finally {
            @unlink($overlay);
        }
    }

    private function qpdfAvailable(): bool
    {
        $binary = (string) config('security.watermark.qpdf_binary');

        return $binary !== '' && (new ExecutableFinder)->find($binary) !== null;
    }

    private function label(?User $user): string
    {
        return 'CRM | '.($user?->login ?: 'customer').' | '.now()->format('Y-m-d H:i');
    }
}
