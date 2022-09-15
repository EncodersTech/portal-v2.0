<?php

use App\Models\Setting;
use Illuminate\Database\Seeder;

class AddAllTimeWithdrawnCreditFeeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create(['key' => 'all_time_withdrawn_credit_fee', 'type' => 'float', 'value' => 0]);
    }
}
