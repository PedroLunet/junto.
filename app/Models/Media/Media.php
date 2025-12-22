<?php

namespace App\Models\Media;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'lbaw2544.media';
    
    protected $fillable = [
        'title',
        'creator', 
        'releaseyear',
        'coverimage'
    ];
    
    public $timestamps = false;
}