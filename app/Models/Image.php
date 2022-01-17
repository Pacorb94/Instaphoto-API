<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $table='images';
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'image',
        'description'
    ];

    public function getComments()
    {
        return $this->hasMany('App\Models\Comment')->orderBy('id', 'desc');
    }

    public function getLikes()
    {
        return $this->hasMany('App\Models\Like')->orderBy('id', 'desc');
    }
}
