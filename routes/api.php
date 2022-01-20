<?php

use Illuminate\Support\Facades\Route;

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

Route::post('/register', 'App\Http\Controllers\UserController@register');
Route::post('/login', 'App\Http\Controllers\UserController@login');

Route::middleware('auth:sanctum')->group(function () {
    //User
    Route::post('/profile-image', 'App\Http\Controllers\UserController@uploadProfileImage');
    Route::get(
        '/profile-images/{imageName}',
        'App\Http\Controllers\UserController@getProfileImage'
    );
    Route::get('/users/{user}', 'App\Http\Controllers\UserController@getUser');
    Route::get('/users/search/{nick}', 'App\Http\Controllers\UserController@searchUsersByNick');
    Route::put('/users/{user}', 'App\Http\Controllers\UserController@update');

    //Image
    Route::post('/image', 'App\Http\Controllers\ImageController@upload');
    Route::get('/images', 'App\Http\Controllers\ImageController@getImages');
    Route::put('/images/{image}', 'App\Http\Controllers\ImageController@update');
    Route::delete('/images/{image}', 'App\Http\Controllers\ImageController@delete');

    //Comment
    Route::post('/images/{image}/comment', 'App\Http\Controllers\CommentController@create');
    Route::delete('/comments/{comment}', 'App\Http\Controllers\CommentController@delete');

    //Like
    Route::post('/images/{image}/like', 'App\Http\Controllers\LikeController@giveLike');
    Route::get('/likes', 'App\Http\Controllers\LikeController@getImagesUserLiked');
    Route::delete('/images/{image}/dislike', 'App\Http\Controllers\LikeController@giveDislike');
});