<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $table = 'images';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'image',
        'description'
    ];

    public function comments()
    {
        return $this->hasMany('App\Models\Comment', 'image_id', 'id');
    }

    public function likes()
    {
        return $this->hasMany('App\Models\Like', 'image_id', 'id');
    }
}
