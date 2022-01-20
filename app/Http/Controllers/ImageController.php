<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateImageRequest;
use App\Http\Requests\CreateImageRequest;
use App\Http\Resources\ImageCollection;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;


class ImageController extends Controller
{


    public function create(CreateImageRequest $request)
    {
        $data = $request->validated();
        //Debemos configurar la fecha y tiempo
        date_default_timezone_set('Europe/Madrid');
        $imageName = date('d-m-Y_H-i-s') . '_' .
            preg_replace('/\s+/', '_', $data['image']->getClientOriginalName());
        //Almacenamos la imagen en la carpeta
        Storage::disk('images')->put($imageName, File::get($data['image']));
        $image = new Image($data);
        //TODO: qRuta de la imagen de la carpeta images
        $image->image = $imageName;
        $image->user_id = auth()->user()->id;
        $image->save();
        return response(new ImageResource($image), 201);
    }

    public function getImages()
    {
        $images = Image::orderBy('id', 'desc')->paginate(5);
        return response(new ImageCollection($images));
    }

    public function update(Image $image, UpdateImageRequest $request)
    {
        $data = array_map('trim', $request->validated());
        //Modificamos la columna updated_at
        $image->touch();
        //Con tap obtenemos la imagen modificada
        $imageUpdated = tap($image)->update($data);
        return response(new ImageResource($imageUpdated));
    }

    public function delete(Image $image)
    {
        if ($image->comments() > 0) {
            foreach ($image->comments() as $comment) $comment->delete();
        }
        if ($image->likes() > 0) {
            foreach ($image->likes() as $like) $like->delete();
        }
        $image->delete();
        //Borramos la imagen del Storage
        Storage::disk('images')->delete($image->image);
        return response(['message' => 'Image deleted']);
    }
}
