<?php

namespace App\Models\Media;

use Illuminate\Database\Eloquent\Model;

class Music extends Model
{
    protected $table = 'lbaw2544.music';
    
    protected $primaryKey = 'mediaId';
    public $incrementing = false; // we provide it manually from the Media creation
    
    public $timestamps = false;

    protected $fillable = ['mediaId'];
}