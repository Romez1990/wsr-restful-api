<?php

use Faker\Generator as Faker;

$factory->define(App\Models\User::class, function (Faker $faker) {
	return [
		'login'      => $faker->unique()->userName,
		'password'   => md5($faker->password),
		'created_at' => now(),
		'updated_at' => now()
	];
});
