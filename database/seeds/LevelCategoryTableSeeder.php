<?php

use Illuminate\Database\Seeder;
use App\Models\LevelCategory;

class LevelCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        LevelCategory::create([
            'id' => LevelCategory::GYMNASTICS_WOMEN,
            'name' => 'Women\'s Artistic Gymnastics',
            'male' => false
        ]);

        LevelCategory::create([
            'id' => LevelCategory::GYMNASTICS_MEN,
            'name' => 'Men\'s Artistic Gymnastics',
            'female' => false
        ]);

        LevelCategory::create([
            'id' => LevelCategory::TRAMPOLINE_TUMBLING,
            'name' => 'Trampoline & Tumbling'
        ]);
        
        LevelCategory::create([
            'id' => LevelCategory::RHYTHMIC,
            'name' => 'Rhythmic Gymnastics'
        ]);
        
        LevelCategory::create([
            'id' => LevelCategory::ACROBATIC,
            'name' => 'Acrobatic Gymnastics'
        ]);
        
        LevelCategory::create([
            'id' => LevelCategory::GYMNASTICS_FOR_ALL,
            'name' => 'Gymnastics for All'
        ]);
        
        LevelCategory::create([
            'id' => LevelCategory::TUMBLING,
            'name' => 'Tumbling'
        ]);
    }
}
