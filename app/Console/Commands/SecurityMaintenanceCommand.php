<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SecurityMaintenanceCommand extends Command
{
    protected $signature = 'security:purge-temporary-files';

    protected $description = 'Removes expired exports and files left in upload quarantine.';

    public function handle(): int
    {
        $disk = Storage::disk('private');
        $deleted = 0;
        foreach (['exports' => 120, 'quarantine' => 1440] as $directory => $minutes) {
            foreach ($disk->files($directory) as $path) {
                $modifiedAt = $disk->lastModified($path);
                if ($modifiedAt !== false && $modifiedAt < now()->subMinutes($minutes)->getTimestamp()) {
                    $disk->delete($path);
                    $deleted++;
                }
            }
        }

        $this->info("Deleted {$deleted} expired temporary files.");

        return self::SUCCESS;
    }
}
