<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PostsRequest;
use App\Http\Requests\CreatePostsRequest;
use App\Models\Posts;
use App\Models\User;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Controllers\UserController;

class PostsController extends Controller
{
    public function indexPosts(){
        return Posts::paginate(50);
    }

    public function showPosts($id){
        return Posts::find($id);
    }

    public function storePosts(CreatePostsRequest $request){
        // return UserController::getAuthenticatedUser();
        if (JWTAuth::parseToken()->authenticate()){
            $validated = $request->validated();
            Posts::create([
                'title' => $validated->title,
                'content' => $validated->content,
                /*'category' => $validated->category,*/
                'status' => 'active',
                'author' => JWTAuth::parseToken()->authenticate()->id
            ]);
            return response()->json(['message' => 'Created'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }  
    }

    public function updatePosts(PostsRequest $request, $id)
    {
        if (UserController::getAuthenticatedUser()['id'] == Posts::find($id)->toArray()['author'] || UserController::getAuthenticatedUser()['role'] == 'admin'){
            $validated = $request->validated();
            Posts::find($id)->update($validated);
            return response()->json(['message' => 'Updated'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
    }

    public function destroyPosts($id){
        if (UserController::getAuthenticatedUser()['id'] == Posts::find($id)->toArray()['author'] || UserController::getAuthenticatedUser()['role'] == 'admin'){
            Posts::find($id)->delete();
            return response()->json(['message' => 'Deleted'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
    }
}
