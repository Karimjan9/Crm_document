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
        ConsulModel::all()->each(function ($item) {
            $item->delete();
        });
        ConsulModel::insert([
            [
                'name' => "Ta'lim va Adliya",
                'amount' => 150,
                'day' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Qolganlar',
                'amount' => 250,
                'day' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
             [
                'name' => 'Chet el fuqarolari uchun',
                'amount' => 100,
                'day' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
             [
                'name' => 'Boshqalar',
                'amount' => 100,
                'day' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
