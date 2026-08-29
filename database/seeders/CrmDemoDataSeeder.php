<?php

namespace Database\Seeders;

use App\Support\CrmDemoDataInstaller;
use Illuminate\Database\Seeder;

class CrmDemoDataSeeder extends Seeder
{
    /**
     * Seed the clearly identifiable sample records used by the Client Demo.
     *
     * The installer uses stable demo codes and phone numbers, so rerunning it
     * updates the sample data instead of duplicating it.
     */
    public function run(): void
    {
        app(CrmDemoDataInstaller::class)->seed();
    }
}
