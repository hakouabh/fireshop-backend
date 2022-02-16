<?php

use Faker\Generator as Faker;
//userID
$factory->define(App\Customer::class, function (Faker $faker) {
    return [
        'full_name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'company_name' => $faker->name,
        'company_id'=>'0a327e34-9361-45f7-8574-d99056d1c321'
    ];
});
