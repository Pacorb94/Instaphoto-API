<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $table='comments';

    /**
     * Función que obtiene la imagen de la que se está comentando,
     * como es sólo 1 es "belongsTo"
     * @return
     */
    public function getImage()
    {
        return $this->belongsTo('App\Models\Image', 'image_id');
    }

    /**
     * Función que obtiene el usuario que creó el comentario
     * @return
     */
    public function getUser()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
