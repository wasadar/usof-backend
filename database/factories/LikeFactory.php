<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use App\Models\Posts;
use App\Models\Like;
use Illuminate\Database\Eloquent\Factories\Factory;

class LikeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Like::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $type = rand(0,1);
        $grade = rand(0,1);
        return [
            'type' => ($grade) ? "+" : "-",
            'author' => User::inRandomOrder()->first()->id,
            'post_id' => ($type) ? (Posts::inRandomOrder()->first()->id) : null,
            'comment_id' => ($type) ? null : (Comment::inRandomOrder()->first()->id),
        ];
    }
}
