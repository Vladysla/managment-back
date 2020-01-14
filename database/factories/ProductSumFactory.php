<?php

use Faker\Generator as Faker;

$factory->define(App\ProductSum::class, function (Faker $faker) {
    return [
        'product_id' => rand(70, 569),
        'color_id' => rand(1, 6),
        'size_id' => rand(1, 10),
        'place_id' => rand(1, 2),
        'sold' => 0,
    ];
});
