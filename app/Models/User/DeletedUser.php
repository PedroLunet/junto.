<?php

namespace App\Models\User;

use App\Http\Controllers\FileController;
use Illuminate\Database\Eloquent\Model;

class DeletedUser extends Model
{
    protected $table = 'users';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'username',
        'email',
        'isdeleted',
    ];

    protected $casts = [
        'isdeleted' => 'boolean',
    ];

    public static function getDeletedUserPlaceholder()
    {
        return new self([
            'id' => null,
            'name' => 'Deleted User',
            'username' => 'deleted_user',
            'email' => 'deleted@anon.com',
            'isdeleted' => true,
        ]);
    }
}
