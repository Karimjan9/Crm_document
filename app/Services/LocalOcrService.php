<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocalOcrService
{
    public function extract(string $path, bool $pdf): array
    {
        $source = Storage::disk('private')->path($path);
        $pages = max(1, min(5, (int) config('services.ocr.max_pages', 3)));
        if ($pdf) {
            $text = trim($this->run(['pdftotext', '-f', '1', '-l', (string) $pages, '-enc', 'UTF-8', $source, '-']));
            if ($text !== '') {
                return ['text' => mb_substr($text, 0, 50000), 'page_limit' => $pages];
            }
        }

        $directory = 'ocr-temporary/'.Str::uuid();
        Storage::disk('private')->makeDirectory($directory);
        $absolute = Storage::disk('private')->path($directory);
        @chmod($absolute, 0700);
        try {
            $images = [$source];
            if ($pdf) {
                $this->run(['pdftoppm', '-f', '1', '-l', (string) $pages, '-scale-to', '2200', '-png', $source, $absolute.'/page']);
                $images = glob($absolute.'/page-*.png') ?: [];
                natsort($images);
            }
            $text = '';
            foreach ($images as $image) {
                $text .= $this->run(['tesseract', $image, 'stdout', '-l', (string) config('services.ocr.languages', 'eng+rus+uzb')])."\n";
            }
            if (trim($text) === '') {
                throw new \RuntimeException('No readable text in the uploaded file.');
            }
            return ['text' => mb_substr(trim($text), 0, 50000), 'page_limit' => $pdf ? $pages : 1];
        } finally {
            Storage::disk('private')->deleteDirectory($directory);
        }
    }

    private function run(array $command): string
    {
        return Process::timeout(25)->env(['OMP_THREAD_LIMIT' => '1'])->run($command)->throw()->output();
    }
}
