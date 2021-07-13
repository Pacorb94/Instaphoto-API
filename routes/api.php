<?php

use Illuminate\Http\Request;
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
//User
Route::post('/register', 'App\Http\Controllers\UserController@register');
Route::post('/login', 'App\Http\Controllers\UserController@login');
Route::middleware('jwt')->group(function () {
    //User
    Route::put('/users/{id}/update', 'App\Http\Controllers\UserController@update');
    Route::post('/profile-image/upload', 'App\Http\Controllers\UserController@uploadProfileImage');
    Route::get('/profile-images/{imageName}', 'App\Http\Controllers\UserController@getProfileImage');
    Route::get('/users/{id}', 'App\Http\Controllers\UserController@getUser');
    Route::get('/search/users/{search?}', 'App\Http\Controllers\UserController@findUsers');

    //Image
    Route::post('/image/upload', 'App\Http\Controllers\ImageController@upload');
    Route::get('/images', 'App\Http\Controllers\HomeController@getImages');
    Route::put('/images/{id}/update', 'App\Http\Controllers\ImageController@update');
    Route::delete('/images/{id}/delete', 'App\Http\Controllers\ImageController@delete');

    //Comment
    Route::post('{imageId}/comment/create', 'App\Http\Controllers\CommentController@create');
    Route::delete('/comments/{id}/delete', 'App\Http\Controllers\CommentController@delete');

    //Like
    Route::post('/like/{imageId}', 'App\Http\Controllers\LikeController@like');
    Route::get('/likes', 'App\Http\Controllers\LikeController@getLikes');
    Route::delete('/dislike/{imageId}', 'App\Http\Controllers\LikeController@dislike');
});
