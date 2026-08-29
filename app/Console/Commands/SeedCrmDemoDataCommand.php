<?php

namespace App\Console\Commands;

use App\Support\CrmDemoDataInstaller;
use Database\Seeders\ApostilStaticSeeder;
use Database\Seeders\ConsulSeeder;
use Database\Seeders\ConsulationTypeSeeder;
use Database\Seeders\DirectionTypeSeeder;
use Database\Seeders\DocumentTypeSeeder;
use Database\Seeders\FilialSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Command;
use Throwable;

class SeedCrmDemoDataCommand extends Command
{
    protected $signature = 'crm:demo-data
                            {--force : Allow demo data to be seeded on the designated production demo server}';

    protected $description = 'Seed idempotent CRM demo data for local/testing or an explicitly confirmed demo server';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Production is blocked. Run only on the designated demo server with --force.');

            return self::FAILURE;
        }

        if (app()->environment('production')) {
            $this->warn('Production demo mode confirmed. Existing data is not deleted; demo records are added or updated.');
        }

        $seeders = [
            FilialSeeder::class,
            UserSeeder::class,
            DocumentTypeSeeder::class,
            DirectionTypeSeeder::class,
            ConsulationTypeSeeder::class,
            ConsulSeeder::class,
            ApostilStaticSeeder::class,
        ];

        foreach ($seeders as $seeder) {
            $this->line("Running {$seeder}...");

            $exitCode = $this->call('db:seed', [
                '--class' => $seeder,
            ]);

            if ($exitCode !== self::SUCCESS) {
                $this->error("Failed while running {$seeder}.");

                return self::FAILURE;
            }
        }

        $this->line('Running CRM demo support installer...');

        try {
            app(CrmDemoDataInstaller::class)->seed();
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Demo support installer failed: ' . $exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Demo CRM data seeded successfully.');
        $this->line('Command: php artisan crm:demo-data' . (app()->environment('production') ? ' --force' : ''));

        return self::SUCCESS;
    }
}
