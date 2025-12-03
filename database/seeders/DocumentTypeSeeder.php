<?php

namespace Database\Seeders;

use App\Models\DocumentTypeModel;
use Dom\Document;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        DocumentTypeModel::create([
            'name' => 'Passport',
            'description' => 'International travel document',
        ]);
        DocumentTypeModel::create([
            'name' => 'Driver License',
            'description' => 'Official driving permit',
        ]);
        DocumentTypeModel::create([
            'name' => 'ID Card',
            'description' => 'National identification document',
        ]);
          DocumentTypeModel::create([
            'name' => "Ma'lumotnoma",
            'description' => 'National identification document',
        ]);
          DocumentTypeModel::create([
            'name' => "Boshqa",
            'description' => 'National identification document',
        ]);
    }
}
