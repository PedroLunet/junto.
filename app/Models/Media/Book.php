<?php

namespace App\Models\Media;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
  protected $table = 'lbaw2544.book';

  protected $primaryKey = 'mediaId';
  public $incrementing = false; // we provide it manually from the Media creation

  public $timestamps = false;

  protected $fillable = ['mediaId'];
}
