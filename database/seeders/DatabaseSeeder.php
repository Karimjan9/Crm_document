<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\CareerSeeder;
use Database\Seeders\DepartementsSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call([
            UserLevelSeeder::class,
            UserSeeder::class,
            CareerSeeder::class,
            DepartementsSeeder::class,
           
            RateOfWorkSeeder::class,
            TypeOfWorkSeeder::class,
            AcademicTitleSeeder::class,
            AcademicDegreeSeeder::class,
            BodilySeeder::class,
            UnityItemsSeeder::class,
            FirstSeeder::class,
            SecondSeeder::class,
        ]);
    }
}
