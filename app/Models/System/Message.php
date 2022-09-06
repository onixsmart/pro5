<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

use App\Models\System\MessageDescription;

class Message extends Model
{

    public $timestamps = false;

    protected $fillable = [
        'message',
        'date_start',
        'time_start',
        'recurrence',
    ];

    public function messageDescription()
    {
        return $this->hasMany(MessageDescription::class);
    }
}
