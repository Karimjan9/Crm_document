<?php

namespace Database\Seeders;

use App\Models\FilialModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        FilialModel::create([
            'name' => 'Buxoro Filiali',
            'code' => 'BR001',
            'description' => 'The primary branch located in the city center.',
        ]);
        FilialModel::create([
            'name' => 'Samarqand Filiali',
            'code' => 'SM002',
            'description' => 'A branch serving the Samarqand region.',
        ]);
        FilialModel::create([
            'name' => 'Toshkent Filiali',
            'code' => 'TK003',
            'description' => 'Main branch in the capital city.',
        ]);
    }
}
