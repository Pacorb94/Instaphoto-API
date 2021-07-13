<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;

class HomeController extends Controller
{
    /**
     * Función que obtiene todas las imágenes paginadas
     * @return
     */
    public function getImages()
    {
        $images=Image::orderBy('id', 'desc')->paginate(5);
        if ($images) return response($images);
        return response(['message'=>'No pictures'], 404);      
    }
}
