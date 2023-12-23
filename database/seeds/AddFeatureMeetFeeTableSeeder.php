<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;

/**
 * Class AddFeatureMeetFeeTableSeeder
 */
class AddFeatureMeetFeeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create([
            'key' => Setting::FEATURED_MEET_FEE,
            'type' => Setting::FLOAT_TYPE,
            'value' => '100.25',
        ]);

        Setting::create([
            'key' => Setting::ENABLED_FEATURED_MEET_FEE,
            'type' => Setting::BOOLEAN_TYPE,
            'value' => false,
        ]);

        Setting::create([
            'key' => Setting::TERMS_SERVICE_LINK,
            'type' => Setting::STRING_TYPE,
            'value' => 'javascript:void(0)',
        ]);
    }
}
