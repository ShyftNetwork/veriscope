<?php

use Faker\Generator as Faker;

$factory->define(\App\Constant::class, function (Faker $faker) {
    return [
        'name'         => $faker->word(),
        'description'  => $faker->text(rand(10,20)),
        'type'         => $faker->randomElement(['boolean', 'text']),
        'value'        => $faker->boolean(85),
    ];
});
