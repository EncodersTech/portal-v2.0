<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Athlete;
use Faker\Generator as Faker;
use App\Models\Gym;

$factory->define(Athlete::class, function (Faker $faker) {
    $gender_arr = ['male', 'female'];
    $gender = $gender_arr[array_rand($gender_arr)];
    $gym =  Gym::first();
    return [
        'gym_id' => '1',
        'first_name' => $faker->firstName($gender),
        'last_name' => $faker->lastName,
        'gender' => $gender,
        'dob' => $faker->dateTimeThisCentury(),
        'is_us_citizen' => $faker->boolean(),

        'tshirt_size_id' => rand(1, 8),
        'leo_size_id' => rand(9, 16),

        'usag_no' => rand(1, 99999),
        'usag_level_id' => rand(1001, 1021),
        'usag_active' => $faker->boolean(),

        'usaigc_no' => rand(1, 99999),
        'usaigc_level_id' => rand(2001, 2008),
        'usaigc_active' => $faker->boolean(),

        'aau_no' => rand(1, 99999),
        'aau_level_id' => rand(3001, 3014),
        'aau_active' => $faker->boolean(),
    ];

    //factory(App\Models\Athlete::class, 1500)->create()
});
