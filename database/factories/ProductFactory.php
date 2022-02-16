<?php

use Faker\Generator as Faker;

$factory->define(App\Product::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'type_id' => 'f675e979-8baa-471c-8a56-223a9281bdab',
        'stock' => $faker->randomDigitNotNull(),
        'company_id'=>'0a327e34-9361-45f7-8574-d99056d1c321',
        'sku' => $faker->unique()->randomNumber($nbDigits = NULL, $strict = false),
        'cost' => $faker->numberBetween($min = 100, $max = 3000),
        'selling_price'=> $faker->numberBetween($min = 2000, $max = 8000)
    ];
});
