<?php

use Faker\Generator as Faker;

$factory->define(App\Stock::class, function (Faker $faker) {
    return [
        'product_id' => '85a63ae1-f347-4043-949a-f9cceb02f524',
        'quantity' => $faker->randomDigitNotNull(),
        'company_id'=>'0a327e34-9361-45f7-8574-d99056d1c321',
        'cost' => $faker->numberBetween($min = 100, $max = 3000),
        'selling_price'=> $faker->numberBetween($min = 2000, $max = 8000)
    ];
});
