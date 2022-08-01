<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{

    public $timestamps = false;

    protected $fillable = [
        'type',
        'notifiable',
        'data',
    ];

}
