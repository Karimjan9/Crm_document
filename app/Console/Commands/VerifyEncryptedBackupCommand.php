<?php

namespace App\Console\Commands;

use App\Services\BackupEncryptionService;
use App\Services\SecurityAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class VerifyEncryptedBackupCommand extends Command
{
    protected $signature = 'backup:verify {--path= : Exact encrypted backup file to restore-test}';

    protected $description = 'Decrypts and restores the latest backup into the dedicated disposable restore-test database.';

    public function handle(BackupEncryptionService $encryption): int
    {
        $database = config('database.connections.'.config('database.default'));
        $restoreDatabase = (string) config('security.backups.restore_test_database');
        $productionDatabase = (string) ($database['database'] ?? '');
        if (! preg_match('/^[A-Za-z0-9_]+$/', $restoreDatabase) || $restoreDatabase === '' || $restoreDatabase === $productionDatabase) {
            app(SecurityAlertService::class)->raise('backup_restore_test_unconfigured', 'critical');
            $this->error('BACKUP_RESTORE_TEST_DATABASE must be a dedicated, non-production database name.');

            return self::FAILURE;
        }

        $backup = $this->backupPath();
        if (! $backup) {
            app(SecurityAlertService::class)->raise('backup_missing', 'critical');
            $this->error('No encrypted backup was found.');

            return self::FAILURE;
        }

        $temporary = storage_path('app/private/restore-test-'.bin2hex(random_bytes(8)).'.sql');
        try {
            $encryption->decryptFile($backup, $temporary);
            if (! str_contains((string) file_get_contents($temporary), 'CREATE TABLE')) {
                throw new \RuntimeException('Decrypted backup does not contain a database schema.');
            }

            $client = (string) config('database.client_binary', 'mysql');
            $base = [$client, '--host='.($database['host'] ?? '127.0.0.1'), '--port='.($database['port'] ?? 3306), '--user='.($database['username'] ?? '')];
            $environment = ['MYSQL_PWD' => (string) ($database['password'] ?? '')];
            $this->executeProcess([...$base, '--execute=DROP DATABASE IF EXISTS `'.$restoreDatabase.'`; CREATE DATABASE `'.$restoreDatabase.'`;'], $environment);
            $this->executeProcess([...$base, $restoreDatabase], $environment, (string) file_get_contents($temporary));
            $this->executeProcess([...$base, $restoreDatabase, '--execute=SHOW TABLES;'], $environment);
            $this->executeProcess([...$base, '--execute=DROP DATABASE IF EXISTS `'.$restoreDatabase.'`;'], $environment);
        } catch (\Throwable $exception) {
            app(SecurityAlertService::class)->raise('backup_restore_test_failed', 'critical', ['message' => $exception->getMessage()]);
            report($exception);
            $this->error('Backup restore test failed.');

            return self::FAILURE;
        } finally {
            File::delete($temporary);
        }

        $this->info('Encrypted backup restore test passed.');

        return self::SUCCESS;
    }

    private function backupPath(): ?string
    {
        $requested = $this->option('path');
        if ($requested) {
            return is_file($requested) && str_ends_with($requested, '.enc') ? $requested : null;
        }

        return collect(File::files(storage_path('app/backups')))
            ->filter(fn ($file) => str_ends_with($file->getFilename(), '.enc'))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->first()?->getPathname();
    }

    private function executeProcess(array $command, array $environment, ?string $input = null): void
    {
        $process = new Process($command, base_path(), $environment, $input, 300);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput()) ?: 'MySQL restore-test command failed.');
        }
    }
}
