<?php

namespace App\Models\Post;

use App\Models\Media\Media;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'review';
    protected $primaryKey = 'postid';
    public $timestamps = false;
    
    protected $fillable = ['content', 'rating', 'mediaid'];
    
    public function media()
    {
        return $this->belongsTo(Media::class, 'mediaid', 'id');
    }
}