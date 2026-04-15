<?php

namespace Database\Seeders;

use App\Models\ApostilStatikModel;
use Illuminate\Database\Seeder;

class ApostilStaticSeeder extends Seeder
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
                'name' => 'Asliga',
                'price' => 60000,
                'days' => 1,
                'group_id' => 1,
            ],
            [
                'name' => 'Notarial tasdiq',
                'price' => 85000,
                'days' => 1,
                'group_id' => 1,
            ],
            [
                'name' => 'QR kodli',
                'price' => 70000,
                'days' => 2,
                'group_id' => 2,
            ],
            [
                'name' => 'QR kodsiz',
                'price' => 50000,
                'days' => 1,
                'group_id' => 2,
            ],
        ];

        foreach ($records as $record) {
            ApostilStatikModel::updateOrCreate(
                [
                    'group_id' => $record['group_id'],
                    'name' => $record['name'],
                ],
                [
                    'price' => $record['price'],
                    'days' => $record['days'],
                ]
            );
        }
    }
}
