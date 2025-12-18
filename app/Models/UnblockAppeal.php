<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\User;

class UnblockAppeal extends Model
{
    protected $table = 'lbaw2544.unblock_appeal';

    protected $fillable = [
        'userid',
        'reason',
        'status',
        'adminnotes',
    ];

    protected $casts = [
        'createdat' => 'datetime',
        'updatedat' => 'datetime',
    ];

    public $timestamps = true;

    const CREATED_AT = 'createdat';
    const UPDATED_AT = 'updatedat';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->createdat) {
                $model->createdat = now();
            }
            if (!$model->updatedat) {
                $model->updatedat = now();
            }
        });

        static::updating(function ($model) {
            $model->updatedat = now();
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
