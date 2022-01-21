<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateOrUpdateImageRequest;
use App\Http\Resources\ImageCollection;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use Illuminate\Http\Response;
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
        $image->description = trim($data['description']);
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

    public function getImage($imageName)
    {
        $exists = Storage::disk('images')->exists($imageName);
        if ($exists) {
            $image = Storage::disk('images')->get($imageName);
            return new Response($image);
        }
        return response(['message' => 'No exists an image with that name'], 404);
    }

    public function getImages()
    {
        $images = Image::orderBy('id', 'desc')->paginate(5);
        return response(new ImageCollection($images));
    }

    public function delete(Image $image)
    {
        //TODO: VER SI SE BORRAN CON ON CASCADE
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