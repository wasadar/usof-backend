<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('/auth')->group(function() {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'authenticate']);
    Route::post('/logout', [UserController::class, 'unauthenticate']);
    Route::post('/password-reset/{confirm_token}', [UserController::class, 'resetPassword']);
    Route::post('/password-reset', [UserController::class, 'getPasswordLink']);
});
Route::get("/users", [UserController::class, 'index']);
Route::get("/users/{user_id}", [UserController::class, 'show']);
Route::post("/users", [UserController::class, 'store']);
Route::post("/users/avatar", [UserController::class, 'avatar']);
Route::patch("/users/{user_id}", [UserController::class, 'update']);
Route::delete("/users/{user_id}", [UserController::class, 'destroy']);

Route::get("/posts", [UserController::class, 'indexPosts']);
Route::get("/posts/{post_id}", [UserController::class, 'showPosts']);
Route::get("/posts/{post_id}/comments", [UserController::class, 'indexPostsComments']);
Route::get("/posts/{post_id}/like", [UserController::class, 'indexPostLikes']);
Route::post("/posts/{post_id}/comments", [UserController::class, 'storeComment']);
Route::post("/posts/{post_id}/like", [UserController::class, 'storePostLike']);
Route::post("/posts", [UserController::class, 'storePosts']);
Route::patch("/posts/{post_id}", [UserController::class, 'updatePosts']);
Route::delete("/posts/{post_id}", [UserController::class, 'destroyPosts']);
Route::delete("/posts/{post_id}/like", [UserController::class, 'destroyLikePost']);

Route::get("/categories", [UserController::class, 'indexCategories']);
Route::get("/categories/{category_id}", [UserController::class, 'showCategory']);
Route::get("/categories/{category_id}/posts", [UserController::class, 'indexCategoryPosts']);
Route::post("/categories", [UserController::class, 'storeCategory']);
Route::patch("/categories/{category_id}", [UserController::class, 'updateCategory']);
Route::delete("/categories/{category_id}", [UserController::class, 'destroyCategory']);

Route::get("/comments/{comment_id}", [UserController::class, 'showComment']);
Route::get("/comments/{comment_id}/like", [UserController::class, 'indexCommentLikes']);
Route::post("/comments/{comment_id}/like", [UserController::class, 'storeCommentLike']);
Route::patch("/comments/{comment_id}", [UserController::class, 'updateComment']);
Route::delete("/comments/{comment_id}", [UserController::class, 'destroyComment']);
Route::delete("/comments/{comment_id}/line", [UserController::class, 'destroyLikeComment']);