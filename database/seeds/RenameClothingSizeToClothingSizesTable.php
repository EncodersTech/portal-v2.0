<?php

use App\Models\ClothingSize;
use Illuminate\Database\Seeder;

class RenameClothingSizeToClothingSizesTable extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $clothingSize = ClothingSize::where('size', 'AX')->first();

        $clothingSize->update([
            'size' => 'AS',
        ]);
    }
}
