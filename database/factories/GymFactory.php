<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Gym;
use Faker\Generator as Faker;

$factory->define(Gym::class, function (Faker $faker) {
    return [
        'user_id' => '1',
        'name' => $faker->company,
        'short_name' => $faker->name,
        'profile_picture' => config('app.default_gym_picture'),
        'addr_1' => $faker->address,
        'city' => $faker->city,
        'state_id' => 1,
        'zipcode' => rand(10000, 99999),
        'country_id' => 1,
        'office_phone' => $faker->phoneNumber
    ];

    //factory(App\Models\Gym::class, 10)->create()
});
