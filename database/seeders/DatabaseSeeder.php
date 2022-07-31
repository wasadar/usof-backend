<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\User;
use App\Models\Posts;
use App\Models\Like;

use App\Http\Controllers\UserController;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(UserSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(PostsSeeder::class);
        $this->call(CommentSeeder::class);
        $this->call(LikeSeeder::class);
        $users = User::get();
        foreach($users as $user){
            UserController::getUserRating($user->id);
        }
        /*$posts = Posts::get();
        foreach($posts as $post){
            $likes = Like::where('post_id',$post->id)->get();
            $rating = 0;
            foreach($likes as $like){
                if ($like->type == "+"){
                    $rating += 1;
                }
                else {
                    $rating -= 1;
                }
            }
            $post->rating = $rating;
            $post->save();
        }
        $comments = Comment::get();
        foreach($comments as $comment){
            $likes = Like::where('comment_id',$comment->id)->get();
            $rating = 0;
            foreach($likes as $like){
                if ($like->type == "+"){
                    $rating += 1;
                }
                else {
                    $rating -= 1;
                }
            }
            $comment->rating = $rating;
            $comment->save();
        }
        $users = User::get();
        foreach($users as $user){
            $rating = 0;
            $posts = Posts::where('author',$user->id)->get();
            foreach($posts as $post){
                $rating += $post->rating;
            }
            $comments = Comment::where('author',$user->id)->get();
            foreach($comments as $comment){
                $rating += $comment->rating;
            }
            $user->rating = $rating;
            $user->save();
        }*/
    }
}
