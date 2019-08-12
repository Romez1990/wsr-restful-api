<?php

/** @var Factory $factory */

use App\Models\Product;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Http\UploadedFile;

$factory->define(Product::class, function (Faker $faker) {
    $title = $faker->words($faker->numberBetween(1, 4), true);
    $image = UploadedFile::fake()->image('image.jpg', 100, 100);
    $imageFileName = $title.'.jpg';
    $image = $image->storeAs('product_images', $imageFileName, 'public');
    return [
        'title' => $title,
        'manufacturer' => $faker->words(2, true),
        'text' => $faker->sentence(6),
        'image' => $image,
    ];
});
