<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Asset;
use Faker\Generator as Faker;

$factory->define(Asset::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'status' => $faker->randomElement(['active','not_active']),
        'description' => $faker->sentence
    ];
});
