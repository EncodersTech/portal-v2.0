<?php

use App\Models\AthleteLevel;
use App\Models\LevelCategory;
use App\Models\SanctioningBody;
use Illuminate\Database\Seeder;

class AddNGAMemberLevelSeeder extends Seeder
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
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_MEMBER,
            'name' => 'Member',
            'abbreviation' => 'ME',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_WOMEN_GYMNASTICS_NON_MEMBER,
            'name' => 'Non-Member',
            'abbreviation' => 'NM',
            'level_category_id' => $category->id
        ]);
    }

    public function seedNGAMenGymnasticsLevels()
    {
        $body = SanctioningBody::find(SanctioningBody::NGA);
        $category = LevelCategory::find(LevelCategory::GYMNASTICS_MEN);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_MEN_GYMNASTICS_LEVEL_MEMBER,
            'name' => 'Member',
            'abbreviation' => 'ME',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::NGA_MEN_GYMNASTICS_LEVEL_NON_MEMBER,
            'name' => 'Non-Member',
            'abbreviation' => 'NM',
            'level_category_id' => $category->id
        ]);
    }
}
