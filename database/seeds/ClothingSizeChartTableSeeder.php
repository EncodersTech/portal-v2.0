<?php

use Illuminate\Database\Seeder;
use App\Models\ClothingSizeChart;

class ClothingSizeChartTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ClothingSizeChart::create([
            'id' => ClothingSizeChart::CHART_DEFAULT_TSHIRT,
            'name' => 'Default (T-shirt)',
            'is_leo' => false,
            'is_default' => true
        ]);

        ClothingSizeChart::create([
            'id' => ClothingSizeChart::CHART_DEFAULT_LEO,
            'name' => 'Default (Leotard)',
            'is_leo' => true,
            'is_default' => true
        ]);

        ClothingSizeChart::create([
            'id' => ClothingSizeChart::CHART_LEO_GK,
            'name' => 'GK (Leotard)',
            'is_leo' => true
        ]);

        ClothingSizeChart::create([
            'id' => ClothingSizeChart::CHART_LEO_ALPHA_FACTOR,
            'name' => 'Alpha Factor (Leotard)',
            'is_leo' => true
        ]);

        ClothingSizeChart::create([
            'id' => ClothingSizeChart::CHART_LEO_SNOW_FLAKE,
            'name' => 'Snow Flake (Leotard)',
            'is_leo' => true
        ]);

        ClothingSizeChart::create([
            'id' => ClothingSizeChart::CHART_LEO_DESTIRA,
            'name' => 'Destira (Leotard)',
            'is_leo' => true
        ]);
    }
}
