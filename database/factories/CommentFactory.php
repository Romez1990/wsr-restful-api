<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Comment;
use App\Models\Product;
use Faker\Generator as Faker;

$factory->define(Comment::class, function (Faker $faker) {
    return [
        'product_id' => Product::all()->random(),
        'author' => $faker->name,
        'text' => $faker->sentence(6),
    ];
});
