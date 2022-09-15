<?php

use Illuminate\Database\Seeder;
use App\Models\LevelCategory;
use App\Models\SanctioningBody;
use App\Models\AthleteLevel;

class AthleteLevelTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedUSAGWomenGymnasticsLevels();
        $this->seedUSAGMenGymnasticsLevels();
        $this->seedIGCGymnasticsLevels();
        $this->seedIGCTumblingLevels();
        $this->seedAAUWomenGymnasticsLevels();
        $this->seedAAUMenGymnasticsLevels();
    }

    /* USAIGC Levels */
    public function seedIGCGymnasticsLevels()
    {
        $body = SanctioningBody::find(SanctioningBody::USAIGC);
        $category = LevelCategory::find(LevelCategory::GYMNASTICS_WOMEN);

        $body->levels()->create([
            'id' => AthleteLevel::IGC_GYMNASTICS_COPPER_1,
            'name' => 'Copper 1',
            'abbreviation' => 'C1',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::IGC_GYMNASTICS_COPPER_2,
            'name' => 'Copper 2',
            'abbreviation' => 'C2',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::IGC_GYMNASTICS_BRONZE,
            'name' => 'Bronze',
            'abbreviation' => 'BR',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::IGC_GYMNASTICS_DIAMOND,
            'name' => 'Diamond',
            'abbreviation' => 'DM',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::IGC_GYMNASTICS_SILVER,
            'name' => 'Silver',
            'abbreviation' => 'SL',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::IGC_GYMNASTICS_GOLD,
            'name' => 'Gold',
            'abbreviation' => 'GD',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::IGC_GYMNASTICS_PLATINUM,
            'name' => 'Platinum',
            'abbreviation' => 'PL',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::IGC_GYMNASTICS_PREMIERE,
            'name' => 'Premier',
            'abbreviation' => 'PR',
            'level_category_id' => $category->id
        ]);
    }

    public function seedIGCTumblingLevels()
    {
        $body = SanctioningBody::find(SanctioningBody::USAIGC);
        $category = LevelCategory::find(LevelCategory::TUMBLING);

        $body->levels()->create([
            'id' => AthleteLevel::IGC_TUMBLING_1,
            'name' => 'Level 1',
            'abbreviation' => '1',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::IGC_TUMBLING_2,
            'name' => 'Level 2',
            'abbreviation' => '2',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::IGC_TUMBLING_3,
            'name' => 'Level 3',
            'abbreviation' => '3',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::IGC_TUMBLING_4,
            'name' => 'Level 4',
            'abbreviation' => '4',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::IGC_TUMBLING_5,
            'name' => 'Level 5',
            'abbreviation' => '5',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::IGC_TUMBLING_6,
            'name' => 'Level 6',
            'abbreviation' => '6',
            'level_category_id' => $category->id
        ]);
    }

    /* AAU Levels */
    public function seedAAUWomenGymnasticsLevels()
    {
        $body = SanctioningBody::find(SanctioningBody::AAU);
        $category = LevelCategory::find(LevelCategory::GYMNASTICS_WOMEN);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_WOMEN_GYMNASTICS_LEVEL_1,
            'name' => 'Level 1',
            'abbreviation' => '1',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_WOMEN_GYMNASTICS_LEVEL_2,
            'name' => 'Level 2',
            'abbreviation' => '2',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_WOMEN_GYMNASTICS_LEVEL_3,
            'name' => 'Level 3',
            'abbreviation' => '3',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_WOMEN_GYMNASTICS_LEVEL_4,
            'name' => 'Level 4',
            'abbreviation' => '4',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_WOMEN_GYMNASTICS_LEVEL_5,
            'name' => 'Level 5',
            'abbreviation' => '5',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_WOMEN_GYMNASTICS_LEVEL_6,
            'name' => 'Level 6',
            'abbreviation' => '6',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_WOMEN_GYMNASTICS_LEVEL_7,
            'name' => 'Level 7',
            'abbreviation' => '7',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_WOMEN_GYMNASTICS_LEVEL_8,
            'name' => 'Level 8',
            'abbreviation' => '8',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_WOMEN_GYMNASTICS_OPEN_OPTIONAL,
            'name' => 'Open Optional',
            'abbreviation' => 'OO',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_WOMEN_GYMNASTICS_XCEL_BRONZE,
            'name' => 'Xcel Bronze',
            'abbreviation' => 'XB',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_WOMEN_GYMNASTICS_XCEL_DIAMOND,
            'name' => 'Xcel Diamond',
            'abbreviation' => 'XD',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_WOMEN_GYMNASTICS_XCEL_SILVER,
            'name' => 'Xcel Silver',
            'abbreviation' => 'XS',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_WOMEN_GYMNASTICS_XCEL_GOLD,
            'name' => 'Xcel Gold',
            'abbreviation' => 'XG',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_WOMEN_GYMNASTICS_XCEL_PLATINUM,
            'name' => 'Xcel Platinum',
            'abbreviation' => 'XP',
            'level_category_id' => $category->id
        ]);
    }

    public function seedAAUMenGymnasticsLevels()
    {
        $body = SanctioningBody::find(SanctioningBody::AAU);
        $category = LevelCategory::find(LevelCategory::GYMNASTICS_MEN);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_MEN_GYMNASTICS_LEVEL_3,
            'name' => 'Level 3',
            'abbreviation' => '3',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_MEN_GYMNASTICS_LEVEL_4,
            'name' => 'Level 4',
            'abbreviation' => '4',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_MEN_GYMNASTICS_LEVEL_5,
            'name' => 'Level 5',
            'abbreviation' => '5',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_MEN_GYMNASTICS_LEVEL_6,
            'name' => 'Level 6',
            'abbreviation' => '6',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_MEN_GYMNASTICS_LEVEL_8,
            'name' => 'Level 8',
            'abbreviation' => '8',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::AAU_MEN_GYMNASTICS_LEVEL_9,
            'name' => 'Level 9',
            'abbreviation' => '9',
            'level_category_id' => $category->id
        ]);
    }

    public function seedUSAGWomenGymnasticsLevels()
    {
        $body = SanctioningBody::find(SanctioningBody::USAG);
        $category = LevelCategory::find(LevelCategory::GYMNASTICS_WOMEN);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_BRONZE,
            'name' => 'Xcel Bronze',
            'code' => 'WBRONZE',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_SILVER,
            'name' => 'Xcel Silver',
            'code' => 'WSILVER',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_GOLD,
            'name' => 'Xcel Gold',
            'code' => 'WGOLD',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_DIAMOND,
            'name' => 'Xcel Diamond',
            'code' => 'WDIAMOND',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_PLATINUM,
            'name' => 'Xcel Platinum',
            'code' => 'WPLATINUM',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_1,
            'name' => 'Level 1',
            'code' => 'WLEVEL01',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_2,
            'name' => 'Level 2',
            'code' => 'WLEVEL02',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_3,
            'name' => 'Level 3',
            'code' => 'WLEVEL03',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_4,
            'name' => 'Level 4',
            'code' => 'WLEVEL04',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_5,
            'name' => 'Level 5',
            'code' => 'WLEVEL05',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_6,
            'name' => 'Level 6',
            'code' => 'WLEVEL06',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_7,
            'name' => 'Level 7',
            'code' => 'WLEVEL07',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_8,
            'name' => 'Level 8',
            'code' => 'WLEVEL08',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_9,
            'name' => 'Level 9',
            'code' => 'WLEVEL09',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_10,
            'name' => 'Level 10',
            'code' => 'WLEVEL10',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_OPEN,
            'name' => 'Open',
            'code' => 'WOPEN',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_ELITE,
            'name' => 'Elite',
            'code' => 'WELITE',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_TOPS,
            'name' => 'TOPs',
            'code' => 'WTOPS',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_EXHIB,
            'name' => 'Exhibit',
            'code' => 'WEXHIB',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_HOPES,
            'name' => 'Hopes',
            'code' => 'WHOPES',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_WOMEN_HUGS,
            'name' => 'HUGS',
            'code' => 'WHUGS',
            'level_category_id' => $category->id
        ]);
    }

    public function seedUSAGMenGymnasticsLevels()
    {
        $body = SanctioningBody::find(SanctioningBody::USAG);
        $category = LevelCategory::find(LevelCategory::GYMNASTICS_MEN);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_BRONZE,
            'name' => 'Xcel Bronze',
            'code' => 'MBRONZE',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_SILVER,
            'name' => 'Xcel Silver',
            'code' => 'MSILVER',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_GOLD,
            'name' => 'Xcel Gold',
            'code' => 'MGOLD',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_1,
            'name' => 'Level 1',
            'code' => 'MLEVEL01',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_2,
            'name' => 'Level 2',
            'code' => 'MLEVEL02',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_3,
            'name' => 'Level 3',
            'code' => 'MLEVEL03',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_4_DIVISION_1,
            'name' => 'Level 4 (Div 1)',
            'code' => 'M4D1',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_5_DIVISION_1,
            'name' => 'Level 5 (Div 1)',
            'code' => 'M5D1',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_6_DIVISION_1,
            'name' => 'Level 6 (Div 1)',
            'code' => 'M6D1',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_7_DIVISION_1,
            'name' => 'Level 7 (Div 1)',
            'code' => 'M7D1',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_4_DIVISION_2,
            'name' => 'Level 4 (Div 2)',
            'code' => 'M4D2',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_5_DIVISION_2,
            'name' => 'Level 5 (Div 2)',
            'code' => 'M5D2',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_6_DIVISION_2,
            'name' => 'Level 6 (Div 2)',
            'code' => 'M6D2',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_7_DIVISION_2,
            'name' => 'Level 7 (Div 2)',
            'code' => 'M7D2',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_8,
            'name' => 'Level 8',
            'code' => 'MLEVEL08',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_9,
            'name' => 'Level 9',
            'code' => 'MLEVEL09',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_10,
            'name' => 'Level 10',
            'code' => 'MLEVEL10',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_JUNIOR_DEVELOPMENT_DIVISION_1,
            'name' => 'Junior Development (Div 1)',
            'code' => 'MJD1',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_JUNIOR_DEVELOPMENT_DIVISION_2,
            'name' => 'Junior Development (Div 2)',
            'code' => 'MJD2',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_ELITE,
            'name' => 'Elite',
            'code' => 'MELITE',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_EXHIB,
            'name' => 'Exhibit',
            'code' => 'MEXHIB',
            'level_category_id' => $category->id
        ]);

        $body->levels()->create([
            'id' => AthleteLevel::USAG_GYMNASTICS_MEN_HUGS,
            'name' => 'HUGS',
            'code' => 'MHUGS',
            'level_category_id' => $category->id
        ]);
    }
}
