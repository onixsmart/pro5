<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

use App\Models\System\messageDescription;

class Notification extends Model
{

    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
    ];

}
