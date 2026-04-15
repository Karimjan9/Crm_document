<?php

namespace Database\Seeders;

use App\Models\ConsulModel;
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
        $records = [
            [
                'name' => "Ta'lim va Adliya",
                'amount' => 150000,
                'day' => 3,
            ],
            [
                'name' => 'Qolganlar',
                'amount' => 250000,
                'day' => 5,
            ],
            [
                'name' => 'Chet el fuqarolari uchun',
                'amount' => 100000,
                'day' => 2,
            ],
            [
                'name' => 'Boshqalar',
                'amount' => 120000,
                'day' => 3,
            ],
        ];

        foreach ($records as $record) {
            ConsulModel::updateOrCreate(
                ['name' => $record['name']],
                [
                    'amount' => $record['amount'],
                    'day' => $record['day'],
                ]
            );
        }
    }
}
