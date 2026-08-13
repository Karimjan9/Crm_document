<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'backup:database
        {--path= : Directory where the SQL dump will be written}
        {--keep=7 : Number of recent SQL dumps to retain}
        {--timeout=300 : Maximum dump process time in seconds}';

    protected $description = 'Create a protected MySQL backup without exposing the database password in the process arguments.';

    public function handle(): int
    {
        $connectionName = (string) config('database.default');
        $database = config("database.connections.{$connectionName}", []);

        if (($database['driver'] ?? null) !== 'mysql') {
            $this->error('backup:database currently supports MySQL/MariaDB connections only.');

            return self::FAILURE;
        }

        $binary = (string) config('database.dump_binary', 'mysqldump');

        if (!$this->binaryExists($binary)) {
            $this->error("Database dump binary was not found: {$binary}");

            return self::FAILURE;
        }

        $backupDirectory = (string) ($this->option('path') ?: storage_path('app/backups'));
        File::ensureDirectoryExists($backupDirectory, 0700);

        if (!is_writable($backupDirectory)) {
            $this->error("Backup directory is not writable: {$backupDirectory}");

            return self::FAILURE;
        }

        $databaseName = (string) ($database['database'] ?? '');

        if ($databaseName === '') {
            $this->error('DB_DATABASE must not be empty.');

            return self::FAILURE;
        }

        $safeDatabaseName = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $databaseName) ?: 'database';
        $fileName = sprintf('%s-sql-backup-%s-%s.sql', $safeDatabaseName, now()->format('Y-m-d_H-i-s'), getmypid());
        $path = rtrim($backupDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

        $command = [
            $binary,
            '--host=' . ($database['host'] ?? '127.0.0.1'),
            '--port=' . ($database['port'] ?? 3306),
            '--user=' . ($database['username'] ?? ''),
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--routines',
            '--triggers',
            '--events',
            $databaseName,
        ];

        $environment = [
            'MYSQL_PWD' => (string) ($database['password'] ?? ''),
        ];

        $timeout = max(1, (int) $this->option('timeout'));
        $process = new Process($command, base_path(), $environment, null, $timeout);
        $errorOutput = '';
        $file = @fopen($path, 'wb');

        if ($file === false) {
            $this->error("Could not create backup file: {$path}");

            return self::FAILURE;
        }

        try {
            $process->run(function (string $type, string $buffer) use ($file, &$errorOutput): void {
                if ($type === Process::ERR) {
                    $errorOutput .= $buffer;

                    return;
                }

                fwrite($file, $buffer);
            });
        } finally {
            fclose($file);
        }

        if (!$process->isSuccessful() || !is_file($path) || filesize($path) === false || filesize($path) === 0) {
            File::delete($path);
            $message = trim($errorOutput) ?: trim($process->getErrorOutput()) ?: 'The database dump process failed.';
            $this->error($message);

            return self::FAILURE;
        }

        chmod($path, 0600);
        $this->pruneOldBackups($backupDirectory, max(1, (int) $this->option('keep')));

        $this->info("Backup created: {$path}");
        $this->line("BACKUP_PATH={$path}");

        return self::SUCCESS;
    }

    private function binaryExists(string $binary): bool
    {
        if (str_starts_with($binary, DIRECTORY_SEPARATOR) || str_starts_with($binary, './') || str_starts_with($binary, '../')) {
            return is_file($binary);
        }

        return (new ExecutableFinder())->find($binary) !== null;
    }

    private function pruneOldBackups(string $directory, int $keep): void
    {
        $files = collect(File::files($directory))
            ->filter(fn ($file): bool => strtolower($file->getExtension()) === 'sql')
            ->sortByDesc(fn ($file): int => $file->getMTime())
            ->values();

        foreach ($files->slice($keep) as $file) {
            File::delete($file->getPathname());
        }
    }
}
