<?php

use Illuminate\Http\Request;
use App\Http\Middleware\ApiAuthMiddleware;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Usuarios
Route::post('/register', 'UserController@register');
Route::post('/login', 'UserController@login');
Route::middleware(ApiAuthMiddleware::class)->put('/user/update', 'UserController@update');
Route::middleware(ApiAuthMiddleware::class)->post('/user/upload', 'UserController@upload');
Route::get('/user/avatar/{filename}', 'UserController@getImage');
Route::get('/user/detail/{id}', 'UserController@detail');

// Categorías
Route::resource('/category', 'CategoryController');

// Post
Route::post('/post/upload', 'PostController@upload');
Route::get('/post/image/{filename}', 'PostController@getImage');
Route::get('/post/category/{id}', 'PostController@getPostByCategory');
Route::get('/post/user/{id}', 'PostController@getPostByUser');
Route::resource('/post', 'PostController');