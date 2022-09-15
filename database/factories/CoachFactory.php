<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Coach;
use Faker\Generator as Faker;
use App\Models\Gym;

$factory->define(Coach::class, function (Faker $faker) {
    $gender_arr = ['male', 'female'];
    $gender = $gender_arr[array_rand($gender_arr)];
    return [
        'gym_id' => '1',
        'first_name' => $faker->firstName($gender),
        'last_name' => $faker->lastName,
        'gender' => $gender,
        'dob' => $faker->dateTimeThisCentury(),

        'tshirt_size_id' => rand(1, 8),
        
        'usag_no' => $faker->boolean() ? rand(1, 99999) : null,
        'usag_active' => $faker->boolean(),

        'usaigc_no' => $faker->boolean() ? rand(1, 99999) : null,
        'usaigc_background_check' => $faker->boolean(),

        'aau_no' => ($faker->boolean() ? rand(1, 99999) : null)
    ];

    //factory(App\Models\Coach::class, 20)->create()
});
