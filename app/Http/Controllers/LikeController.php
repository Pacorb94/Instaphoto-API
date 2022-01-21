<?php

namespace App\Http\Controllers;

use App\Http\Resources\LikeResource;
use App\Models\Image;
use App\Models\Like;

class LikeController extends Controller
{

    public function giveLike(Image $image)
    {
        $likeByUser = Like::where('user_id', auth()->user()->id)
            ->where('image_id', $image->id)->first();
        //Si el usuario no le dió like
        if (!$likeByUser) {
            $like = new Like();
            $like->user_id = auth()->user()->id;
            $like->image_id = $image->id;
            $like->save();
            return response(new LikeResource($like), 201);
        }
        return response(['message' => 'You already liked the photo'], 400);
    }

    public function giveDislike(Image $image)
    {
        $like = Like::where('user_id', auth()->user()->id)
            ->where('image_id', $image->id)->first();
        //Si el usuario le dió like
        if ($like) {
            $like->delete();
            return response(['message' => 'You disliked the photo']);
        }
        return response(['message' => 'You have to like the photo first'], 400);
    }
}