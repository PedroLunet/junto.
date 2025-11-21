<?php

namespace App\Models;

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
}