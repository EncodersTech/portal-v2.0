<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class ChangeHandlingFeeValue extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::where('key','fee_handling')->update(['value'=>'3.25']);
    }
}
