<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrUpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Image;

class CommentController extends Controller
{

    
    public function create(Image $image, CreateOrUpdateCommentRequest $request)
    {
        $data = array_map('trim', $request->validated());
        $comment = new Comment($data);
        $comment->user_id = auth()->user()->id;
        $comment->image_id = $image->id;
        $comment->save();
        return response(new CommentResource($comment), 201);
    }

    public function update(Comment $comment, CreateOrUpdateCommentRequest $request)
    {
        $data = array_map('trim', $request->validated());
        //Modificamos la columna updated_at
        $comment->touch();
        //Asignamos los campos
        $comment->fill($data);
        $comment->update();
        return response(new CommentResource($comment));
    }

    public function delete(Comment $comment)
    {
        $image = Image::find($comment->image_id);
        //Si el usuario es el que creó el comentario o el que creó la imagen
        if (
            $comment->user_id == auth()->user()->id
            || $image->user_id == auth()->user()->id
        ) {
            $comment->delete();
            return response(['message' => 'Deleted comment']);
        }
        return response(['message' => 'You didn´t create the comment or the image'], 400);
    }
}