<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class Message extends Model
{
    use HasFactory;

    public $timestamps = false; // We use sentAt manually or let DB handle it, but Laravel expects created_at/updated_at by default. 
    // Actually, the schema has `sentAt` and no `updated_at`. So I should disable timestamps and maybe map sentAt.

    protected $table = 'messages';

    protected $fillable = [
        'senderId',
        'receiverId',
        'content',
        'isRead',
        'sentAt',
    ];

    protected $casts = [
        'sentAt' => 'datetime',
        'isRead' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'senderId');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiverId');
    }
}
