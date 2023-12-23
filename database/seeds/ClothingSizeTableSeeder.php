<?php

use Illuminate\Database\Seeder;
use App\Models\ClothingSizeChart;

class ClothingSizeTableSeeder extends Seeder
{

    private const DEFAULT_SIZES_TSHIRT = [
        'XS',
        'S',
        'M',
        'L',
        'XL',
        '2XL',
        '3XL',
        '4XL',
    ];

    private const DEFAULT_SIZES_LEO= [
        'CXS',
        'CS',
        'CM',
        'CL',
        'AXS',
        'AS',
        'AM',
        'AL',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $chart = ClothingSizeChart::find(ClothingSizeChart::CHART_DEFAULT_TSHIRT);
        foreach (self::DEFAULT_SIZES_TSHIRT as $size) {
            $chart->sizes()->create([
                'size' => $size
            ]);
        }

        $chart = ClothingSizeChart::find(ClothingSizeChart::CHART_DEFAULT_LEO);
        foreach (self::DEFAULT_SIZES_LEO as $size) {
            $chart->sizes()->create([
                'size' => $size
            ]);
        }

        $this->seedGKLeos();
        $this->seedAlphaFactorLeos();
        $this->seedSnowFlakeLeos();
        $this->seedDestiraLeos();
    }

    public function seedGKLeos()
    {
        $sizes = ['CXS', 'CS', 'CM', 'CL', 'CXL', 'AXS', 'AS', 'AM', 'AL', 'AXL', '2X'];
        $chart = ClothingSizeChart::find(ClothingSizeChart::CHART_LEO_GK);
        foreach ($sizes as $size) {
            $chart->sizes()->create([
                'size' => $size
            ]);
        }
    }

    public function seedAlphaFactorLeos()
    {
        $sizes = ['CXS (2-3)', 'CSM (4-6)', 'INT (6x-7)', 'CME (8-10)', 'CLA (12-14)',
            'AXS (0-2)', 'ASM (4-6)', 'AME (8-10)', 'ALA (10-12)', 'AXL (14-16)'];
        $chart = ClothingSizeChart::find(ClothingSizeChart::CHART_LEO_ALPHA_FACTOR);
        foreach ($sizes as $size) {
            $chart->sizes()->create([
                'size' => $size
            ]);
        }
    }

    public function seedSnowFlakeLeos()
    {
        $sizes = ['CXS', 'CS', 'CM', 'CL', 'AXS', 'AS', 'AM', 'AL', 'AXL'];
        $chart = ClothingSizeChart::find(ClothingSizeChart::CHART_LEO_SNOW_FLAKE);
        foreach ($sizes as $size) {
            $chart->sizes()->create([
                'size' => $size
            ]);
        }
    }

    public function seedDestiraLeos()
    {
        $sizes = ['Child XXS (3-4)', 'Child XS (5-6)', 'Child S (6-7)', 'Child M (8-10)',
            'Child L (10-12)', 'Junior (12-14)', 'AXS', 'AS', 'AM', 'AL', 'AXL'];
        $chart = ClothingSizeChart::find(ClothingSizeChart::CHART_LEO_DESTIRA);
        foreach ($sizes as $size) {
            $chart->sizes()->create([
                'size' => $size
            ]);
        }
    }
}
