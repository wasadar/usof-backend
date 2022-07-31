<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use App\Models\Posts;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'content' => Str::random(100),
            'author' => User::inRandomOrder()->first()->id,
            'post' => Posts::inRandomOrder()->first()->id,
            'rating' => 0
        ];
    }
}
