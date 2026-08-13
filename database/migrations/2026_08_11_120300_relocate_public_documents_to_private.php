<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $public = Storage::disk('public');
        $private = Storage::disk('private');

        foreach (['documents', 'files'] as $prefix) {
            foreach ($public->allFiles($prefix) as $sourcePath) {
                $directory = dirname($sourcePath);
                if ($directory !== '.') {
                    $private->makeDirectory($directory);
                }

                $this->moveBetweenDisks($public, $private, $sourcePath, $sourcePath);
            }
        }
    }

    public function down(): void
    {
        $public = Storage::disk('public');
        $private = Storage::disk('private');

        foreach ($private->allFiles('documents') as $sourcePath) {
            if (!str_starts_with($sourcePath, 'documents/legacy/')) {
                continue;
            }

            $directory = dirname($sourcePath);
            if ($directory !== '.') {
                $public->makeDirectory($directory);
            }

            $this->moveBetweenDisks($private, $public, $sourcePath, $sourcePath);
        }

        foreach ($private->allFiles('files') as $sourcePath) {
            $directory = dirname($sourcePath);
            if ($directory !== '.') {
                $public->makeDirectory($directory);
            }

            $this->moveBetweenDisks($private, $public, $sourcePath, $sourcePath);
        }
    }

    protected function moveBetweenDisks($sourceDisk, $destinationDisk, string $sourcePath, string $destinationPath): bool
    {
        if ($destinationDisk->exists($destinationPath)) {
            throw new RuntimeException("Storage migration conflict at {$destinationPath}.");
        }

        $stream = $sourceDisk->readStream($sourcePath);
        if (!is_resource($stream)) {
            return false;
        }

        try {
            $written = $destinationDisk->put($destinationPath, $stream);
        } finally {
            fclose($stream);
        }

        return $written && $sourceDisk->delete($sourcePath);
    }
};
