<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrUpdateImageRequest;
use App\Http\Resources\ImageCollection;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{

    public function __construct()
    {
        date_default_timezone_set('Europe/Madrid');
    }

    public function create(CreateOrUpdateImageRequest $request)
    {
        $data = $request->validated();
        $image = new Image($data);
        $image->image = $this->moveImage($data['image']);
        $image->user_id = auth()->user()->id;
        $image->save();
        return response(new ImageResource($image), 201);
    }

    public function update(Image $image, CreateOrUpdateImageRequest $request)
    {
        $data = $request->validated();
        //Si en la petición está la clave 'description'
        if ($request->has('description')) {
            $image->description = trim($data['description']);
        }
        //Borramos la antigua imagen
        Storage::disk('images')->delete($image->image);
        $image->image = $this->moveImage($data['image']);
        //Modificamos la columna updated_at
        $image->touch();
        $image->update();
        return response(new ImageResource($image));
    }

    private function moveImage($image)
    {
        $imageName = date('d-m-Y_H-i-s') . '_' .
            preg_replace('/\s+/', '_', $image->getClientOriginalName());
        $image->move(storage_path() . '\app\images\\', $imageName);
        return $imageName;
    }

    public function getImages()
    {
        $images = Image::orderBy('id', 'desc')->paginate(5);
        return response(new ImageCollection($images));
    }

    public function delete(Image $image)
    {
        //VER SI SE BORRAN CON ON CASCADE
        // if (count($image->comments) > 0) {
        //     foreach ($image->comments as $comment) {
        //         $comment->delete();
        //     }
        // }
        // if (count($image->likes) > 0) {
        //     foreach ($image->likes as $like) {
        //         $like->delete();
        //     }
        // }
        $image->delete();
        //Borramos la imagen
        Storage::disk('images')->delete($image->image);
        return response(['message' => 'Image deleted']);
    }
}