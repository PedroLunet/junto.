<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StandardPost extends Model
{
    protected $table = 'standard_post';
    protected $primaryKey = 'postid';
    public $timestamps = false;
    
    protected $fillable = ['text', 'imageurl'];
}