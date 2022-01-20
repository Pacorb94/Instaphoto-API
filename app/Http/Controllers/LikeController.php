<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Like;

class LikeController extends Controller
{
   
    public function giveLike($imageId)
    {
        if ($imageId&&is_numeric($imageId)) {
            $image=Image::find($imageId);
            //Si existe la imagen
            if ($image) {
                $likeExists=Like::where('user_id', auth()->user()->id)
                            ->where('image_id', $imageId)->count();
                //Si no se le dió like
                if ($likeExists==0) {                 
                    $like=new Like();
                    $like->user_id=auth()->user()->id;
                    $like->image_id=$imageId;
                    $like->save();
                    return response($like, 201);
                }
                return response(['message'=>'You already liked the photo'], 500);
            }
            return response(['message'=>'No exists that image'], 404);
        }
        return response(['message'=>'Wrong image id'], 400);
    }

    public function getImagesUserLiked()
    {
        $likes=Like::where('user_id', auth()->user()->id)->orderBy('id', 'desc')->paginate(5);
        $images=[];
        //Para obtener las imágenes con like deberemos recorrer los likes y acceder a la imagen
        foreach ($likes as $like) array_push($images, $like->getImage);      
        return response(['user'=>auth()->user(), 'images'=>$images]);
    }

    public function giveDislike($imageId)
    {
        if ($imageId&&is_numeric($imageId)) {
            $image=Image::find($imageId);
            //Si existe la imagen
            if ($image) {
                $like=Like::where('user_id', auth()->user()->id)
                            ->where('image_id', $imageId)->first();
                //Si se le dió like
                if ($like) {
                    $like->delete();
                    return response(['message'=>'You dislike the photo']);
                }
                return response(['message'=>'You didnt like the photo'], 500);
            }
            return response(['message'=>'No exists that image'], 404);
        }
        return response(['message'=>'Wrong image id'], 400);
    }
}