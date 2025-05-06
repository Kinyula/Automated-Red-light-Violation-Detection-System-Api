<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoliceOfficer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'gender',
        'police_post'
    ];
}
