<?php

namespace Database\Seeders;

use App\Models\ConsulationTypeModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        ConsulationTypeModel::create([
            'name' => "BAA konsullik xizmati",
            'description' => 'Consular services related to the United Arab Emirates',
        ]);
        ConsulationTypeModel::create([
            'name' => "AQSh konsullik xizmati",
            'description' => 'Consular services related to the United States of America',
        ]);
        ConsulationTypeModel::create([
            'name' => "Buyuk Britaniya konsullik xizmati",
            'description' => 'Consular services related to the United Kingdom',
        ]);
          ConsulationTypeModel::create([
            'name' => "Turkiya konsullik xizmati",
            'description' => 'Consular services related to Turkey',
        ]);
          ConsulationTypeModel::create([
            'name' => "Rossiya konsullik xizmati",
            'description' => 'Consular services related to Russia',
        ]);
          ConsulationTypeModel::create([
            'name' => "Boshqa konsullik xizmatlari",
            'description' => 'Other consular services not categorized',
        ]);
    }
}
