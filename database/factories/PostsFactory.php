<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Posts;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Posts::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => Str::random(15),
            'content' => Str::random(100),
            'status' => 'active',
            'author' => User::inRandomOrder()->first()->id,
            'category' => Category::inRandomOrder()->first()->id,
            'rating' => 0
        ];
    }
}
