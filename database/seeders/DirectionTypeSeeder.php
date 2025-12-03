<?php

namespace Database\Seeders;

use App\Models\DirectionTypeModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        DirectionTypeModel::create([
            'name' => "Oliy ta'lim",
            'description' => 'Goods coming into the country',
        ]);
        DirectionTypeModel::create([
            'name' => "Sog'liqni saqlash",
            'description' => 'Services related to health and medical care',
        ]);
        DirectionTypeModel::create([
            'name' => "Adliya",
            'description' => 'Employment and job placement services',
        ]);
            DirectionTypeModel::create([
            'name' => "Oliy sud",
            'description' => 'Services related to legal matters and judiciary',
        ]);
         DirectionTypeModel::create([
            'name' => "Tashqi ishlar",
            'description' => 'Services related to legal matters and judiciary',
        ]);
            DirectionTypeModel::create([
            'name' => "Boshqa",
            'description' => 'Other services not categorized',
        ]);
    }
}
