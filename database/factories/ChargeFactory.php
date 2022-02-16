<?php

use Faker\Generator as Faker;

$factory->define(App\Charge::class, function (Faker $faker) {
    return [
        'type_id' => 'f0dcc499-aa11-45ea-98bb-c9f4896c6a2f',
        'company_id'=>'0a327e34-9361-45f7-8574-d99056d1c321',
        'amount' => $faker->numberBetween($min = 100, $max = 3000),
    ];
});
