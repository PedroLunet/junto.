<?php

namespace App\Models\Post;

use App\Http\Controllers\FileController;
use Illuminate\Database\Eloquent\Model;

class StandardPost extends Model
{
    protected $table = 'lbaw2544.standard_post';

    protected $primaryKey = 'postid';

    public $timestamps = false;

    protected $fillable = ['postid', 'text', 'imageurl'];

    public function getImageUrl()
    {
        return FileController::get('post', $this->postid);
    }
}
