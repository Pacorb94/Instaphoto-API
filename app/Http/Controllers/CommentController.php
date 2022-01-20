<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Image;
use Illuminate\Support\Facades\Validator;


class CommentController extends Controller
{

    public function create($imageId, Request $request)
    {
        if ($imageId&&is_numeric($imageId)) {
            $validator=$this->validations($request->all());
            if ($validator->fails()) return response($validator->errors(), 400);
            $image=Image::find($imageId);
            //Si existe la imagen
            if ($image) {
                $comment=new Comment();
                $comment->user_id=auth()->user()->id;
                $comment->image_id=$imageId;
                $comment->content=trim($request->input('content'));
                $comment->save();
                return response($comment, 201);
            }
            return response(['message'=>'No exists that image'], 404);
        }
        return response(['message'=>'Wrong image id'], 400);
    }

    public function delete($id)
    {
        if ($id&&is_numeric($id)) {
            $comment=Comment::find($id);
            //Si existe el comentario
            if ($comment) {
                //Si el usuario es el que creó el comentario o el que creó la imagen (publicación)
                if ($comment->user_id==auth()->user()->id
                ||$comment->getImage->user_id==auth()->user()->id) {
                    $comment->delete();
                    return response(['message'=>'Comment deleted']);
                }
                return response(['message'=>'That user is not who created the comment or the image'], 500);
            }
            return response(['message'=>'No exists that comment'], 404);
        }
        return response(['message'=>'Wrong id'], 400);
    }

    /**
     * Función que hace la validación
     * @param $request
     * @return
     */
    public function validations($request)
    {
        $validator=Validator::make(
            $request,
            [
                'content'=>'required'
            ]
        );
        return $validator;
    }
}
