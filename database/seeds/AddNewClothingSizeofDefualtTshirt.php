<?php

use App\Models\ClothingSize;
use App\Models\ClothingSizeChart;
use Illuminate\Database\Seeder;

class AddNewClothingSizeofDefualtTshirt extends Seeder
{
    private const DEFAULT_SIZES_TSHIRT = [
        'CXS',
        'CS',
        'CM',
        'CL',
        'CXL',
        'AXS',
        'AS',
        'AM',
        'AL',
        'AXL',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        ClothingSize::where('clothing_size_chart_id', ClothingSizeChart::CHART_DEFAULT_TSHIRT)->where('size','XS')->update(['size'=>'CXS']);
        ClothingSize::where('clothing_size_chart_id', ClothingSizeChart::CHART_DEFAULT_TSHIRT)->where('size','S')->update(['size'=>'CS']);
        ClothingSize::where('clothing_size_chart_id', ClothingSizeChart::CHART_DEFAULT_TSHIRT)->where('size','M')->update(['size'=>'CM']);
        ClothingSize::where('clothing_size_chart_id', ClothingSizeChart::CHART_DEFAULT_TSHIRT)->where('size','L')->update(['size'=>'CL']);
        ClothingSize::where('clothing_size_chart_id', ClothingSizeChart::CHART_DEFAULT_TSHIRT)->where('size','XL')->update(['size'=>'CXL']);
        ClothingSize::where('clothing_size_chart_id', ClothingSizeChart::CHART_DEFAULT_TSHIRT)->where('size','2XL')->update(['size'=>'AXS']);
        ClothingSize::where('clothing_size_chart_id', ClothingSizeChart::CHART_DEFAULT_TSHIRT)->where('size','3XL')->update(['size'=>'AX']);
        ClothingSize::where('clothing_size_chart_id', ClothingSizeChart::CHART_DEFAULT_TSHIRT)->where('size','4XL')->update(['size'=>'AM']);

        $clothing_size = ClothingSize::create([
            'clothing_size_chart_id' => ClothingSizeChart::CHART_DEFAULT_TSHIRT,
            'size' => self::DEFAULT_SIZES_TSHIRT[8],
            'is_disabled' => false,
        ]);

        $clothing_size = ClothingSize::create([
            'clothing_size_chart_id' => ClothingSizeChart::CHART_DEFAULT_TSHIRT,
            'size' => self::DEFAULT_SIZES_TSHIRT[9],
            'is_disabled' => false,
        ]);
    }
}
