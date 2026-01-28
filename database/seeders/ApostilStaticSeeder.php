<?php

namespace Database\Seeders;

use App\Models\ApostilStatikModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
       ApostilStatikModel::insert([
        [
            'name' => 'Asliga',
            'price' => 0,
            'days' => 0,
            'group_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Notarial tasdiq',
            'price' => 0,
            'days' => 0,
            'group_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'QR kodli',
            'price' => 0,
            'days' => 0,
            'group_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
         [
            'name' => 'QR kodsiz',
            'price' => 0,
            'days' => 0,
            'group_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ],
       ]); 
    }
}
