<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(App\Product::class, function (Faker $faker) {
    return [
        'brand' => $faker->word,
        'model' => str_random(3),
        'price_arrival' => rand(1000, 2000),
        'price_sell' => rand(2000, 3000),
        'type_id' => rand(1, 4)
    ];
});
