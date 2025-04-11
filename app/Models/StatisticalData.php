<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatisticalData extends Model
{
    protected $fillable = ['user_id', 'statistical_data'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
