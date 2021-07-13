<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\Like;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    /**
     * Función que sube una imagen
     * @param $request
     * @return
     */
    public function upload(Request $request)
    {
        $validator=$this->validations($request->all(), 'upload');
        if ($validator->fails()) return response($validator->errors(), 400);
        $requestImage=$request->file('file0');
        //Debemos configurar la fecha y tiempo
        date_default_timezone_set('Europe/Madrid');
        $imageName=date('d-m-Y_H-i-s').'_'.$requestImage->getClientOriginalName();
        //Almacenamos la imagen en la carpeta
        Storage::disk('images')->put($imageName, File::get($requestImage));
        $image=new Image();
        $image->user_id=auth()->user()->id;
        $image->image_path=$imageName;
        $image->description=trim($request->input('description'));
        $image->save();
        return response($image, 201);  
    }

    /**
     * Función que modifica una imagen
     * @param $id
     * @param $request
     * @return
     */
    public function update($id, Request $request)
    {
        if ($id&&is_numeric($id)) {         
            $validator=$this->validations($request->all());
            if ($validator->fails()) return response($validator->errors(), 400);
            $image=Image::find($id);
            //Si existe la imagen
            if ($image) {
                //Si el creador de la imagen es el usuario logueado
                if ($image->user_id==auth()->user()->id) {
                    $requestImage=$request->file('file0');
                    if ($requestImage) {
                        //Debemos configurar la fecha y tiempo
                        date_default_timezone_set('Europe/Madrid');
                        $imageName=date('d-m-Y_H-i-s').'_'.$requestImage->getClientOriginalName();
                        //Almacenamos la imagen en la carpeta
                        Storage::disk('images')->put($imageName, File::get($requestImage));
                    }else{
                        $imageName=$image->image_path;
                    } 
                    $description=trim($request->input('description'));
                    if (!$description) $description=$image->description;                                 
                    $image->image_path=$imageName;              
                    $image->description=$description;
                    $image->update();
                    return response($image);
                }
                return response(['message'=>'Wrong user'], 500);
            }
            return response(['message'=>'No exists an image with that id'], 404);
        }
        return response(['message'=>'Wrong id'], 400);
    }

    /**
     * Función que borra una imagen
     * @param $id
     * @return
     */
    public function delete($id)
    {
        if ($id&&is_numeric($id)) {
            $image=Image::find($id);
            //Si existe la imagen
            if ($image) {
                //Si el creador de la imagen es el usuario logueado
                if ($image->user_id==auth()->user()->id) {
                    $comments=Comment::where('image_id', $id)->get();
                    $likes=Like::where('image_id', $id)->get();
                    if (count($comments)>0) {
                        foreach ($comments as $comment) $comment->delete();         
                    }
                    if (count($likes)>0) {
                        foreach ($likes as $like) $like->delete();         
                    }
                    $image->delete();
                    //Borramos la imagen del Storage
                    Storage::disk('images')->delete($image->image_path);
                    return response(['message'=>'Image deleted']);
                }
                return response(['message'=>'Wrong user'], 500);
            }
            return response(['message'=>'No exists an image with that id'], 404);
        }
        return response(['message'=>'Wrong id'], 400);
    }

    /**
     * Función que hace la validación
     * @param $request
     * @param $functionName
     * @return
     */
    public function validations($request, $functionName='')
    {
        if ($functionName=='upload') {
            $validator=Validator::make(
                $request,
                [
                    'file0'=>'image|mimes:jpg,jpeg,png,gif',
                    'description'=>'required'
                ]
            );  
        }else{
            $validator=Validator::make(
                $request,
                [
                    'file0'=>'image|mimes:jpg,jpeg,png,gif'
                ]
            );  
        }             
        return $validator;
    }
}
