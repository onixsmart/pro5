<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

use App\Models\System\messageDescription;

class Message extends Model
{

    public $timestamps = false;

    protected $fillable = [
        'message',
    ];

    public function messageDescription()
    {
        return $this->hasMany(MessageDescription::class);
    }
}
