<?php

namespace Database\Seeders;

use App\Models\FilialModel;
use Illuminate\Database\Seeder;

class FilialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FilialModel::firstOrCreate([
            'name' => 'Buxoro Filiali',
            'code' => 'BR001',
        ], [
            'description' => 'The primary branch located in the city center.',
        ]);
        FilialModel::firstOrCreate([
            'name' => 'Samarqand Filiali',
            'code' => 'SM002',
        ], [
            'description' => 'A branch serving the Samarqand region.',
        ]);
        FilialModel::firstOrCreate([
            'name' => 'Toshkent Filiali',
            'code' => 'TK003',
        ], [
            'description' => 'Main branch in the capital city.',
        ]);
    }
}
