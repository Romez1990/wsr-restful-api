<?php

use App\Models\User;
use Faker\Generator as Faker;

$factory->define(App\Models\Session::class, function (Faker $faker) {
	$user = User::all()->random();
	$ip = $faker->ipv4;
	$user_agent = $faker->userAgent;
	return [
		'user_id'    => $user,
		'ip'         => $ip,
		'user_agent' => $user_agent,
		'token'      => md5($user->password . $ip . $user_agent)
	];
});
