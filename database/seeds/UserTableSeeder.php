<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Services\StripeService;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (config('app.env') == 'local') {
            $admin = User::create([
                'email' => 'admin@allgymnastics.com',
                'password' => Hash::make('00000000'),
                'first_name' => 'Bill',
                'last_name' => 'Borges',
                'office_phone' => '+1 856-234-5292',
                'job_title' => 'Founder',
                'profile_picture' => config('app.default_profile_picture'),
            ]);
            $admin->email_verified_at = now();
            $admin->is_admin = true;
            $admin->stripe_customer_id = 'cus_F6ZablEznyAoaI';
            $admin->dwolla_customer_id = '86ef5a5f-bdbf-423e-ae2c-08ed18048a8d';
            $admin->save();

            $dev = User::create([
                'email' => 'dev@allgymnastics.com',
                'password' => Hash::make('00000000'),
                'first_name' => 'Dev',
                'last_name' => 'Loper',
                'office_phone' => '+1 202-555-0142',
                'job_title' => 'Developer',
                'profile_picture' => config('app.default_profile_picture'),
            ]);
            $dev->email_verified_at = now();            
            $dev->stripe_customer_id = 'cus_F7ATPL0tTIgnqV';
            $dev->dwolla_customer_id = 'a1b65d1d-9ce0-409a-aa47-cc034b0f4653';
            $dev->save();
        } else {
            $admin = User::register([
                'email' => 'admin@allgymnastics.com',
                'password' => '00000000',
                'first_name' => 'Bill',
                'last_name' => 'Borges',
                'office_phone' => '+1 856-234-5292',
                'job_title' => 'Founder',
                'profile_picture' => config('app.default_profile_picture'),
            ]);
            $admin->email_verified_at = now();
            $admin->is_admin = true;
    
            $admin->save();
        }

    }
}
