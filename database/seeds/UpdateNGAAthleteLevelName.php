<?php

use App\Models\AthleteLevel;
use Illuminate\Database\Seeder;

class UpdateNGAAthleteLevelName extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AthleteLevel::where('id',AthleteLevel::NGA_WOMEN_GYMNASTICS_GOLD)->update(['name' => 'Gold']);
        AthleteLevel::where('id',AthleteLevel::NGA_WOMEN_GYMNASTICS_PLATINUM)->update(['name' => 'Platinum']);
        AthleteLevel::where('id',AthleteLevel::NGA_WOMEN_GYMNASTICS_DIAMOND)->update(['name' => 'Diamond']);
    }
}
