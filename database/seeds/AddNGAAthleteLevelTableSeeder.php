<?php

use App\Models\AthleteLevel;
use App\Models\LevelCategory;
use App\Models\SanctioningBody;
use Illuminate\Database\Seeder;

class AddNGAAthleteLevelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedNGAWomenGymnasticsLevels();
        $this->seedNGAMenGymnasticsLevels();
    }

    public function seedNGAWomenGymnasticsLevels()
    {
        $body = SanctioningBody::find(SanctioningBody::NGA);
        $category = LevelCategory::find(LevelCategory::GYMNASTICS_WOMEN);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_LEVEL_1,
            'name' => 'Level 1',
            'abbreviation' => '1',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_LEVEL_2,
            'name' => 'Level 2',
            'abbreviation' => '2',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_LEVEL_3,
            'name' => 'Level 3',
            'abbreviation' => '3',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_LEVEL_4,
            'name' => 'Level 4',
            'abbreviation' => '4',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_LEVEL_5,
            'name' => 'Level 5',
            'abbreviation' => '5',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_LEVEL_6,
            'name' => 'Level 6',
            'abbreviation' => '6',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_LEVEL_7,
            'name' => 'Level 7',
            'abbreviation' => '7',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_LEVEL_8,
            'name' => 'Level 8',
            'abbreviation' => '8',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_LEVEL_9,
            'name' => 'Level 9',
            'abbreviation' => '9',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_LEVEL_0,
            'name' => 'Level 10',
            'abbreviation' => '10',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_GOLD,
            'name' => 'Gold',
            'abbreviation' => 'XG',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_PLATINUM,
            'name' => 'Platinum',
            'abbreviation' => 'XP',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_DIAMOND,
            'name' => 'Diamond',
            'abbreviation' => 'XD',
            'level_category_id' => $category->id
        ]);
    }

    public function seedNGAMenGymnasticsLevels()
    {
        $body = SanctioningBody::find(SanctioningBody::NGA);
        $category = LevelCategory::find(LevelCategory::GYMNASTICS_MEN);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_MEN_GYMNASTICS_LEVEL_1,
            'name' => 'Level 1',
            'abbreviation' => '1',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_MEN_GYMNASTICS_LEVEL_2,
            'name' => 'Level 2',
            'abbreviation' => '2',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_MEN_GYMNASTICS_LEVEL_3,
            'name' => 'Level 3',
            'abbreviation' => '3',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_MEN_GYMNASTICS_LEVEL_4,
            'name' => 'Level 4',
            'abbreviation' => '4',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_MEN_GYMNASTICS_LEVEL_5,
            'name' => 'Level 5',
            'abbreviation' => '5',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_MEN_GYMNASTICS_LEVEL_6,
            'name' => 'Level 6',
            'abbreviation' => '6',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_MEN_GYMNASTICS_LEVEL_7,
            'name' => 'Level 7',
            'abbreviation' => '7',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_MEN_GYMNASTICS_LEVEL_8,
            'name' => 'Level 8',
            'abbreviation' => '8',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_MEN_GYMNASTICS_LEVEL_9,
            'name' => 'Level 9',
            'abbreviation' => '9',
            'level_category_id' => $category->id
        ]);


        $body->levels()->create([
            'id' => AthleteLevel::NGA_MEN_GYMNASTICS_LEVEL_10,
            'name' => 'Level 10',
            'abbreviation' => '10',
            'level_category_id' => $category->id
        ]);
    }
}
