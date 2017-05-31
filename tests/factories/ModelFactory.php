<?php

use App\Post;
use App\Comment;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(Post::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->sentence,
        'content' => $faker->text
    ];
});

$factory->define(Comment::class, function (Faker\Generator $faker) {
    return [
        'username' => $faker->userName,
        'content' => $faker->text
    ];
});
