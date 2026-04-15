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
    protected $signature = 'crm:demo-data';

    protected $description = 'Seed demo CRM data for local/testing environments only';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('This command is blocked in production environment.');

            return self::FAILURE;
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
        $this->line('Command: php artisan crm:demo-data');

        return self::SUCCESS;
    }
}
