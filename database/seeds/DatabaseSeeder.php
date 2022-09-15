<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SettingTableSeeder::class);
        $this->call(StateTableSeeder::class);
        $this->call(CountryTableSeeder::class);
        $this->call(ErrorCodeCategoryTableSeeder::class);
        $this->call(ErrorCodeTableSeeder::class);
        $this->call(AuditEventCategoryTableSeeder::class);
        $this->call(AuditEventTypeTableSeeder::class);
        $this->call(ClothingSizeChartTableSeeder::class);
        $this->call(ClothingSizeTableSeeder::class);
        $this->call(SanctioningBodyTableSeeder::class);
        $this->call(LevelCategoryTableSeeder::class);
        $this->call(AthleteLevelTableSeeder::class);
        $this->call(AthleteSpecialistEventTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(MeetCompetitionFormatTableSeeder::class);
        $this->call(AddAthleteLevelAbbreviationTableSeeder::class);
        $this->call(AddNGASanctioningBodyTableSeeder::class);
        $this->call(AddNGAAthleteLevelTableSeeder::class);
        $this->call(AddNewClothingSizeofDefualtTshirt::class);
        $this->call(UpdateNGAAthleteLevelName::class);
        $this->call(ChangeHandlingFeeValue::class);
        $this->call(UpdateCCFeeSettingTableSeeder::class);
        $this->call(AddAllTimeWithdrawnCreditFeeTableSeeder::class);
        $this->call(CountHandlingAndProcessorFeeSeeder::class);
        $this->call(CountStripeFeeChargeSeeder::class);
        $this->call(AddNGAMemberLevelSeeder::class);
        $this->call(AddFeatureMeetFeeTableSeeder::class);
        $this->call(AddNGASilverLevelSeeder::class);
    }
}
