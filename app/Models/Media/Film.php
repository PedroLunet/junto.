<?php

namespace App\Models\Media;

use Illuminate\Database\Eloquent\Model;

class Film extends Model
{
    protected $table = 'film';
    protected $primaryKey = 'mediaid';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = ['mediaid'];
}
