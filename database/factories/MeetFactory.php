<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Meet;
use Faker\Generator as Faker;

$factory->define(Meet::class, function (Faker $faker) {

    // factory(App\Models\Meet::class, 20)->create()

    $late_registration = $faker->boolean;

    $registration_start = now()->addDays(rand(0, 49));
    $registration_end = $registration_start->addDays(rand(0, 10));

    $late_registration_start = $registration_end->addDays(rand(0, 7));
    $late_registration_end = $late_registration_start->addDays(rand(0, 10));

    $scratch_date = ($late_registration ? $late_registration_end : $registration_end )->subDays(rand(0, 3));

    $start_date = $late_registration_end->addDays(rand(1, 60));
    $end_date = $start_date->addDays(rand(1, 7));

    $meet_format = rand(1, 10);
    
    return [
        'gym_id' => '1',
        'profile_picture' => config('app.default_meet_picture'),
        'name' => $faker->company,
        'description' => $faker->text,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'website' => $faker->url,
        'equipement' => $faker->text,
        'notes' => $faker->boolean ? $faker->sentence : null,
        'special_annoucements' => $faker->boolean ? $faker->sentence : null,

        'tshirt_size_chart_id' => 1,
        'leo_size_chart_id' => 2,

        'venue_name' => $faker->streetName,
        'venue_addr_1' => $faker->streetAddress,
        'venue_addr_2' => $faker->boolean ? $faker->streetAddress : null,
        'venue_city' => $faker->city,
        'venue_state_id' => rand(1, 51),
        'venue_zipcode' => $faker->postcode,
        'venue_website' => $faker->boolean ? $faker->url : null,

        'registration_start_date' => $registration_start,
        'registration_end_date' => $registration_end,
        'registration_scratch_end_date' => $scratch_date,

        'allow_late_registration' => $late_registration,
        'late_registration_fee' => (float) rand(1, 7500) / 100.00,
        'late_registration_start_date' => $late_registration_start,
        'late_registration_end_date' => $late_registration_end,

        'athlete_limit' => $faker->boolean ? rand(1, 75) * 1000 : null,

        'accept_paypal' => $faker->boolean,
        'accept_ach' => $faker->boolean,
        'accept_mailed_check' => $faker->boolean,
        'accept_deposit' => $faker->boolean,

        'defer_handling_fees' => $faker->boolean,
        'defer_processor_fees' => $faker->boolean,

        'meet_competition_format_id' => $meet_format,
        'meet_competition_format_other' => $meet_format == 10 ? $faker->sentence : null,
        'team_format' => $faker->boolean ? $faker->sentence : null,

        'primary_contact_first_name' => $faker->firstName,
        'primary_contact_last_name' => $faker->lastName,
        'primary_contact_email' => $faker->email,
        'primary_contact_phone' => $faker->phoneNumber,
        'primary_contact_fax' => $faker->boolean ? $faker->phoneNumber : null,

        'is_published' => $faker->boolean,
        'is_archived' => $faker->boolean,
    ];
});
