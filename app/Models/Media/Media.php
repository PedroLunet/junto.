<?php

namespace App\Models\Media;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';
    
    protected $fillable = [
        'title',
        'creator', 
        'releaseyear',
        'coverimage'
    ];
    
    public $timestamps = false;

    public function book()
    {
        return $this->hasOne(Book::class, 'mediaid', 'id');
    }

    public function film()
    {
        return $this->hasOne(Film::class, 'mediaid', 'id');
    }

    public function music()
    {
        return $this->hasOne(Music::class, 'mediaid', 'id');
    }
}