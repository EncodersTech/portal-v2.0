<?php

use App\Models\AthleteLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddAthleteLevelAbbreviationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $athleteLevels = AthleteLevel::all();

        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_BRONZE)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'XB']);

        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_BRONZE)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'XB']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_SILVER)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'XS']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_GOLD)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'XG']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_DIAMOND)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'XD']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_PLATINUM)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'XP']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_1)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'1']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_2)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'2']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_3)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'3']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_4)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'4']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_5)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'5']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_6)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'6']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_7)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'7']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_8)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'8']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_9)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'9']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_LEVEL_10)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'10']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_OPEN)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'WO']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_ELITE)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'WE']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_TOPS)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'WT']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_EXHIB)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'WEX']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_HOPES)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'WH']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_WOMEN_HUGS)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'WHU']);


        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_BRONZE)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'XB']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_SILVER)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'XS']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_GOLD)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'XG']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_1)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'1']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_2)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'2']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_3)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'3']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_4_DIVISION_1)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'4D1']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_5_DIVISION_1)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'5D1']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_6_DIVISION_1)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'6D1']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_7_DIVISION_1)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'7D1']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_4_DIVISION_2)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'4D2']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_5_DIVISION_2)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'5D2']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_6_DIVISION_2)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'6D2']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_7_DIVISION_2)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'7D2']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_8)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'8']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_9)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'9']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_LEVEL_10)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'10']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_JUNIOR_DEVELOPMENT_DIVISION_1)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'JDD1']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_JUNIOR_DEVELOPMENT_DIVISION_2)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'JDD2']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_EXHIB)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'MEX']);
        DB::table('athlete_levels')->where('id', AthleteLevel::USAG_GYMNASTICS_MEN_HUGS)->whereNull('abbreviation')->orWhere('abbreviation', '')->update(['abbreviation'=>'MHU']);
    }
}
