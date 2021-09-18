<?php

use Faker\Generator as Faker;
use HttpOz\Roles\Models\Role;

$factory->define(Role::class, function (Faker $faker) {
    return [
        'name'        => $faker->word(),
        'slug'        => $faker->slug(),
        'description' => $faker->sentence(),
        'group'       => 'default',
    ];
});
