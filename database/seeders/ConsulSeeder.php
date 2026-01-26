<?php

namespace Database\Seeders;

use App\Models\ConsulModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConsulSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ConsulModel::create([
            'name' => 'Main',
            'amount' => 0,
            'day' => 0,
        ]);
    }
}
