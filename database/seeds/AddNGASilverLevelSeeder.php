<?php

use App\Models\AthleteLevel;
use App\Models\LevelCategory;
use App\Models\SanctioningBody;
use Illuminate\Database\Seeder;


/**
 * Class AddNGASilverLevelSeeder
 */
class AddNGASilverLevelSeeder extends Seeder
{
    public function run()
    {
        $body = SanctioningBody::find(SanctioningBody::NGA);
        $category = LevelCategory::find(LevelCategory::GYMNASTICS_WOMEN);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_SILVER,
            'name' => 'Silver',
            'abbreviation' => 'SL',
            'level_category_id' => $category->id
        ]);
    }
}
