<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;
    protected $table='likes';

    /**
     * Función que obtiene la imagen a la cual se le dió like,
     * como es sólo 1 es "belongsTo"
     * @return
     */
    public function getImage()
    {
        return $this->belongsTo('App\Models\Image', 'image_id');
    }

    /**
     * Función que obtiene el usuario que dió like a la imagen
     * @return
     */
    public function getUser()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
