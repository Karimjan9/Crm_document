<?php

namespace Database\Seeders;

use App\Models\ConsulationTypeModel;
use Illuminate\Database\Seeder;

class ConsulationTypeSeeder extends Seeder
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
                'name' => 'BAA konsullik xizmati',
                'description' => 'Consular services related to the United Arab Emirates',
                'amount' => 220000,
                'day' => 6,
            ],
            [
                'name' => 'AQSh konsullik xizmati',
                'description' => 'Consular services related to the United States of America',
                'amount' => 260000,
                'day' => 7,
            ],
            [
                'name' => 'Buyuk Britaniya konsullik xizmati',
                'description' => 'Consular services related to the United Kingdom',
                'amount' => 280000,
                'day' => 7,
            ],
            [
                'name' => 'Turkiya konsullik xizmati',
                'description' => 'Consular services related to Turkey',
                'amount' => 170000,
                'day' => 5,
            ],
            [
                'name' => 'Rossiya konsullik xizmati',
                'description' => 'Consular services related to Russia',
                'amount' => 150000,
                'day' => 4,
            ],
            [
                'name' => 'Boshqa konsullik xizmatlari',
                'description' => 'Other consular services not categorized',
                'amount' => 190000,
                'day' => 6,
            ],
        ];

        foreach ($records as $record) {
            ConsulationTypeModel::updateOrCreate(
                ['name' => $record['name']],
                [
                    'description' => $record['description'],
                    'amount' => $record['amount'],
                    'day' => $record['day'],
                ]
            );
        }
    }
}
