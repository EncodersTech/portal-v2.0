<?php

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Setting::create([
            'key' => Setting::PROFILE_PICTURE_MAX_SIZE,
            'type' => Setting::INTEGER_TYPE,
            'value' => 4096
        ]);

        Setting::create([
            'key' => Setting::ATHLETE_IMPORT_MAX_SIZE,
            'type' => Setting::INTEGER_TYPE,
            'value' => 8192
        ]);

        Setting::create([
            'key' => Setting::AUDIT_ENABLED,
            'type' => Setting::BOOLEAN_TYPE,
            'value' => true
        ]);

        Setting::create([
            'key' => Setting::MEET_FILE_MAX_SIZE,
            'type' => Setting::INTEGER_TYPE,
            'value' => 8192
        ]);

        Setting::create([
            'key' => Setting::MEET_FILE_MAX_COUNT,
            'type' => Setting::INTEGER_TYPE,
            'value' => 5
        ]);

        Setting::create([
            'key' => Setting::USER_BALANCE_HOLD_DURATION,
            'type' => Setting::INTEGER_TYPE,
            'value' => 6
        ]);

        Setting::create([
            'key' => Setting::DWOLLA_UNVERIFIED_TRANSFER_CAP,
            'type' => Setting::FLOAT_TYPE,
            'value' => 5000.00
        ]);

        Setting::create([
            'key' => Setting::DWOLLA_VERIFICATION_FEE,
            'type' => Setting::FLOAT_TYPE,
            'value' => 1.00
        ]);

        Setting::create([
            'key' => Setting::DWOLLA_FREE_VERIFICATION_ATTEMPTS,
            'type' => Setting::INTEGER_TYPE,
            'value' => 3
        ]);


        /* FEES =================== */

        Setting::create([
            'key' => Setting::FEE_HANDLING,
            'type' => Setting::FLOAT_TYPE,
            'value' => 3.00                     // Percentage
        ]);

        Setting::create([
            'key' => Setting::FEE_BALANCE,
            'type' => Setting::FLOAT_TYPE,
            'value' => 0                         // Flat
        ]);

        Setting::create([
            'key' => Setting::FEE_CC,
            'type' => Setting::FLOAT_TYPE,
            'value' => 3.25                     // Percentage
        ]);

        Setting::create([
            'key' => Setting::FEE_PAYPAL,
            'type' => Setting::FLOAT_TYPE,
            'value' => 3.00                     // Percentage
        ]);

        Setting::create([
            'key' => Setting::FEE_ACH,
            'type' => Setting::FLOAT_TYPE,
            'value' => 10.00                    // Flat fee
        ]);

        Setting::create([
            'key' => Setting::FEE_CHECK,
            'type' => Setting::FLOAT_TYPE,
            'value' => 0.00                     // Flat fee
        ]);

        Setting::create([
            'key' => Setting::WITHDRAWAL_FEES,
            'type' => Setting::ARRAY_TYPE,
            'value' => '{"1000":0,"3000":0}'
        ]);
    }
}
