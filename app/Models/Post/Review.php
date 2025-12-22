<?php

namespace App\Models\Post;

use App\Models\Media\Media;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'lbaw2544.review';
    protected $primaryKey = 'postid';
    public $timestamps = false;
    
    protected $fillable = ['postid', 'content', 'rating', 'mediaid'];
    
    public function media()
    {
        return $this->belongsTo(Media::class, 'mediaid', 'id');
    }
}