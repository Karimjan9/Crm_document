<?php

namespace Database\Seeders;

use App\Models\DirectionTypeModel;
use Illuminate\Database\Seeder;

class DirectionTypeSeeder extends Seeder
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
                'name' => "Oliy ta'lim",
                'description' => "Ta'lim yo'nalishidagi hujjatlar",
            ],
            [
                'name' => "Sog'liqni saqlash",
                'description' => 'Medical and healthcare related workflows',
            ],
            [
                'name' => 'Adliya',
                'description' => 'Legal and justice workflow direction',
            ],
            [
                'name' => 'Oliy sud',
                'description' => 'High court related document direction',
            ],
            [
                'name' => 'Tashqi ishlar',
                'description' => 'Foreign affairs related process direction',
            ],
            [
                'name' => 'Boshqa',
                'description' => 'Other directions not categorized',
            ],
        ];

        foreach ($records as $record) {
            DirectionTypeModel::updateOrCreate(
                ['name' => $record['name']],
                ['description' => $record['description']]
            );
        }
    }
}
