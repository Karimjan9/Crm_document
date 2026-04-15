<?php

namespace Database\Seeders;

use App\Models\DocumentTypeModel;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
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
                'name' => 'Passport',
                'description' => 'International travel document',
            ],
            [
                'name' => 'Driver License',
                'description' => 'Official driving permit',
            ],
            [
                'name' => 'ID Card',
                'description' => 'National identification document',
            ],
            [
                'name' => "Ma'lumotnoma",
                'description' => "Turli ma'lumotnomalar uchun hujjat turi",
            ],
            [
                'name' => 'Boshqa',
                'description' => 'Other document category',
            ],
        ];

        foreach ($records as $record) {
            DocumentTypeModel::updateOrCreate(
                ['name' => $record['name']],
                ['description' => $record['description']]
            );
        }
    }
}
