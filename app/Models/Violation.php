<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Violation extends Model
{
    protected $fillable = [
        'user_id',
        'license_plate',
        'message',
        'original_image',

    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
