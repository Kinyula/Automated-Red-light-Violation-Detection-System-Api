<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $fillable = [
        'like',
        'dislike',
        'user_id',
    ];
    protected $casts = [
        'like' => 'boolean',
        'dislike' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
