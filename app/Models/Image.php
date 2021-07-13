<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $table='images';
    
    /**
     * Función que obtiene los comentarios ordenados de una imagen
     * @return
     */
    public function getComments()
    {
        return $this->hasMany('App\Models\Comment')->orderBy('id', 'desc');
    }

    /**
     * Función que obtiene los likes de una imagen
     * @return
     */
    public function getLikes()
    {
        return $this->hasMany('App\Models\Like');
    }

    /**
     * Función que obtiene el usuario que creó la imagen,
     * como es sólo 1 es "belongsTo"
     * @return
     */
    public function getUser()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
}
