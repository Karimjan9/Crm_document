<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            FilialSeeder::class,
            UserSeeder::class,
            DocumentTypeSeeder::class,
            DirectionTypeSeeder::class,
            ConsulationTypeSeeder::class,
            ConsulSeeder::class,
            ApostilStaticSeeder::class,
        ]);
    }
}
