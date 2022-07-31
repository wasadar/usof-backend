<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UserRequest;
use App\Http\Requests\AdminUserRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests\LikeRequest;
use App\Http\Requests\PostsRequest;
use App\Http\Requests\AvatarRequest;
use App\Http\Requests\CategoryRequest;
use App\Http\Requests\CreateCategoryRequest;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\CreatePostsRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\Posts;
use App\Models\Comment;
use App\Models\Category;
use App\Models\Like;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\RecoveryMail;
use Illuminate\Support\Facades\DB;

class UserController extends Controller{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(){
        return User::paginate(50);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminUserRequest $request){
        if ($this->getAuthenticatedUser()['role'] == 'admin'){
            $validated = $request->validated();
            User::create([
                'login' => $validated['login'],
                'email' => $validated['email'],
                'password' => $validated['password']
            ]);
            return response()->json(['message' => 'Created'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
        
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        return User::find($id);
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, $id)
    {
        if ($this->getAuthenticatedUser()['id'] == $id || $this->getAuthenticatedUser()['role'] == 'admin'){
            $validated = $request->validated();
            User::find($id)->update($validated);
            return response()->json(['message' => 'Updated'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
//    public function destroy($id)
//    {
//        //
//    }

    public function authenticate(Request $request){
        $credentials = $request->only(/*'email'*/'login', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(compact('token'));
    }

    public function unauthenticate(Request $request){
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'success unauthenticate'], 200);
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        }
    }

    public function register(RegisterRequest $request){
            /*$validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
        }*/

        $user = User::create([
            'login' => $request->get('login'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user','token'),201);
    }

    public static function getAuthenticatedUser(){
        try {

            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

                return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

                return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

                return response()->json(['token_absent'], $e->getStatusCode());

        }

        return /*response()->json(compact('user'))*/ $user->toArray();
    }

    public function destroy($id){
        if ($this->getAuthenticatedUser()['id'] == $id || $this->getAuthenticatedUser()['role'] == 'admin'){
            User::find($id)->delete();
            return response()->json(['message' => 'Deleted'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
    }

    public function indexPosts(){
        return Posts::paginate(50);
    }

    public function indexCategoryPosts($id){
        return Posts::where('category',$id)->paginate(50);
    }

    public function showPosts($id){
        return Posts::find($id);
    }

    public function storePosts(CreatePostsRequest $request){
        if ($this->getAuthenticatedUser()['id']){
            $validated = $request->validated();
            $category = Category::where('title',$validated['category'])->first();
            if ($category){
                Posts::create([
                    'title' => $validated['title'],
                    'content' => $validated['content'],
                    'category' => $category->id,
                    'status' => 'active',
                    'author' => $this->getAuthenticatedUser()['id']
                ]);
                return response()->json(['message' => 'Created'], 200);
            }
            else {
                return response()->json(['error' => 'wrong category'], 400);
            }
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }  
    }

    public function updatePosts(PostsRequest $request, $id)
    {
        if ($this->getAuthenticatedUser()['id'] == Posts::find($id)->toArray()['author'] || $this->getAuthenticatedUser()['role'] == 'admin'){
            $validated = $request->validated();
            if (array_key_exists('category',$validated)){
                $category = Category::where('title',$validated->category)->first();
                if ($category){
                    $validated['category'] = $category->id;
                }
                else {
                    return response()->json(['error' => 'wrong category'], 400);
                }
            }
            Posts::find($id)->update($validated);
            return response()->json(['message' => 'Updated'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
    }

    public function destroyPosts($id){
        if ($this->getAuthenticatedUser()['id'] == Posts::find($id)->toArray()['author'] || $this->getAuthenticatedUser()['role'] == 'admin'){
            Posts::find($id)->delete();
            return response()->json(['message' => 'Deleted'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
    }

    public function indexCategories(){
        return Category::paginate(50);
    }

    public function showCategory($id){
        return Category::find($id);
    }

    public function storeCategory(CreateCategoryRequest $request){
        if ($this->getAuthenticatedUser()['role'] == 'admin'){
            $validated = $request->validated();
            Category::create([
                'title' => $validated['title'],
                'description' => $validated['description']
            ]);
            return response()->json(['message' => 'Created'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }  
    }

    public function updateCategory(CategoryRequest $request, $id)
    {
        if ($this->getAuthenticatedUser()['role'] == 'admin'){
            $validated = $request->validated();
            Category::find($id)->update($validated);
            return response()->json(['message' => 'Updated'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
    }

    public function destroyCategory($id){
        if ($this->getAuthenticatedUser()['role'] == 'admin'){
            Category::find($id)->delete();
            return response()->json(['message' => 'Deleted'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
    }

    public function indexPostsComments($id){
        return Comment::where('post',$id)->paginate(50);
    }

    public function showComment($id){
        return Comment::find($id);
    }

    public function storeComment(CommentRequest $request, $id){
        if ($this->getAuthenticatedUser()['id']){
            //$validated = $request->validated();
            Comment::create([
                'content' => /*$validated*/$request->content/*['content']*/,
                'author' => $this->getAuthenticatedUser()['id'],
                'post' => $id
            ]);
            return response()->json(['message' => 'Created'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }  
    }

    public function updateComment(CommentRequest $request, $id)
    {
        if ($this->getAuthenticatedUser()['id'] == Comment::find($id)->toArray()['author'] || $this->getAuthenticatedUser()['role'] == 'admin'){
            $validated = $request->validated();
            Comment::find($id)->update($validated);
            return response()->json(['message' => 'Updated'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
    }

    public function destroyComment($id){
        if ($this->getAuthenticatedUser()['id'] == Comment::find($id)->toArray()['author']  || $this->getAuthenticatedUser()['role'] == 'admin'){
            Comment::find($id)->delete();
            return response()->json(['message' => 'Deleted'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
    }

    public function storeCommentLike(LikeRequest $request, $id){
        if ($this->getAuthenticatedUser()['id']){
            $validated = $request->validated();
            $like = Like::where('author',$this->getAuthenticatedUser()['id'])->where('comment_id',$id)->firstOrCreate();
            $check = !isset($like->type);
            if ($check){
                $like->type = $validated['type'];
                $like->author = $this->getAuthenticatedUser()['id'];
                $like->comment_id = $id;
                $like->save();
                $this->getUserRating(Comment::find($id)->author);
                return response()->json(['message' => 'Created'], 200);
            }
            else {
                return response()->json(['error' => 'like on this comment alredy exists'], 409);
            }
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
    }

    public function storePostLike(LikeRequest $request, $id){
        if ($this->getAuthenticatedUser()['id']){
            $validated = $request->validated();
            $like = Like::where('author',$this->getAuthenticatedUser()['id'])->where('post_id',$id)->firstOrCreate();
            $check = !isset($like->type);
            if ($check){
                $like->type = $validated['type'];
                $like->author = $this->getAuthenticatedUser()['id'];
                $like->post_id = $id;
                $like->save();
                $this->getUserRating(Posts::find($id)->author);
                return response()->json(['message' => 'Created'], 200);
            }
            else {
                return response()->json(['error' => 'like on this post alredy exists'], 409);
            }
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
    }

    public function destroyLikeComment($id){
        if ($this->getAuthenticatedUser()['id']){
            try {
                Like::where('author',$this->getAuthenticatedUser()['id'])->where('comment_id',$id)->first()->delete();
            } catch (Exception $e){
                return response()->json(['error' => 'you don\'t have any likes under this comment'], 409);
            }
            //Like::where('author',$this->getAuthenticatedUser()['id'])->where('comment_id',$id)->first()->delete();
            $this->getUserRating(Comment::find($id)->author);
            return response()->json(['message' => 'Deleted'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
    }

    public function destroyLikePost($id){
        if ($this->getAuthenticatedUser()['id']){
            try {
                Like::where('author',$this->getAuthenticatedUser()['id'])->where('post_id',$id)->first()->delete();
            } catch (Exception $e) {
                return response()->json(['error' => $e->getMessage()], 409);
            }
            //Like::where('author',$this->getAuthenticatedUser()['id'])->where('post_id',$id)->first()->delete();
            $this->getUserRating(Posts::find($id)->author);
            return response()->json(['message' => 'Deleted'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
    }

    public function indexCommentLikes($id){
        return Like::where('comment_id',$id)->paginate(50);
    }

    public function indexPostLikes($id){
        return Like::where('post_id',$id)->paginate(50);
    }

    public function avatar(AvatarRequest $request){
        if ($this->getAuthenticatedUser()['id']){
            $path = $request->file('avatar')->storeAs('images',$this->getAuthenticatedUser()['id']);
            $user = User::find($this->getAuthenticatedUser()['id']);
            $user->picture = $path;
            $user->save();
            return response()->json(['message' => 'Saved'], 200);
        }
        else {
            return response()->json(['error' => 'forbidden'], 403);
        }
    }

    public function getPasswordLink(Request $request){
        $login = $request['login'];
        $mail = User::where('login',$login)->first()->email;
        $token = Str::random($length = 20);
        DB::table('password_resets')->insert(array(
            'user' => User::where('login',$login)->first()->id,
            'token' => $token
        ));
        Mail::to($mail)->send(new RecoveryMail('Use this link to recover your password on usof-backend-service: http://127.0.0.1:8000/api/auth/password-reset/' . $token));
        return response()->json(['message' => 'Link is send to your mail'], 200);
    }

    public function resetPassword(ResetPasswordRequest $request, $token){
        $user = User::find(DB::table('password_resets')->where('token',$token)->first()->user);
        $validated = $request->validated();
        $user->password = Hash::make($validated['password']);
        $user->save();
        DB::table('password_resets')->where('token',$token)->delete();
        return response()->json(['message' => 'Password is changed'], 200);
    }

    public static function getPostRating($id){
        $post = Posts::find($id);
        $likes = Like::where('post_id',$id)->get();
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
        return $rating;
    }

    public static function getCommentRating($id){
        $comment = Comment::find($id);
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
        return $rating;
    }

    public static function getUserRating($id){
        $user = User::find($id);
        $rating = 0;
        $posts = Posts::where('author',$id)->get();
        foreach($posts as $post){
            $rating += UserController::getPostRating($post->id);
        }
        $comments = Comment::where('author',$id)->get();
        foreach($comments as $comment){
            $rating += UserController::getCommentRating($comment->id);
        }
        $user->rating = $rating;
        $user->save();
        return $rating;
    }
}
