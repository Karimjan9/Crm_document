<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('document_files')) {
            return;
        }

        $private = Storage::disk('private');
        $public = Storage::disk('public');
        $private->makeDirectory('documents');

        DB::table('document_files')
            ->select(['id', 'file_path'])
            ->orderBy('id')
            ->get()
            ->each(function (object $file) use ($private, $public): void {
                $sourcePath = ltrim((string) $file->file_path, '/');
                if ($sourcePath === '' || !$public->exists($sourcePath)) {
                    return;
                }

                $destinationPath = str_starts_with($sourcePath, 'documents/')
                    ? $sourcePath
                    : 'documents/legacy/' . $sourcePath;

                $directory = dirname($destinationPath);
                if ($directory !== '.') {
                    $private->makeDirectory($directory);
                }

                if (!$this->moveBetweenDisks($public, $private, $sourcePath, $destinationPath)) {
                    return;
                }

                DB::table('document_files')
                    ->where('id', $file->id)
                    ->update([
                        'file_path' => $destinationPath,
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        if (!Schema::hasTable('document_files')) {
            return;
        }

        $private = Storage::disk('private');
        $public = Storage::disk('public');

        DB::table('document_files')
            ->select(['id', 'file_path'])
            ->where('file_path', 'like', 'documents/%')
            ->orderBy('id')
            ->get()
            ->each(function (object $file) use ($private, $public): void {
                $sourcePath = ltrim((string) $file->file_path, '/');
                if (!$private->exists($sourcePath)) {
                    return;
                }

                $destinationPath = str_starts_with($sourcePath, 'documents/legacy/')
                    ? substr($sourcePath, strlen('documents/legacy/'))
                    : $sourcePath;
                $directory = dirname($destinationPath);
                if ($directory !== '.') {
                    $public->makeDirectory($directory);
                }

                if (!$this->moveBetweenDisks($private, $public, $sourcePath, $destinationPath)) {
                    return;
                }

                DB::table('document_files')
                    ->where('id', $file->id)
                    ->update([
                        'file_path' => $destinationPath,
                        'updated_at' => now(),
                    ]);
            });
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
