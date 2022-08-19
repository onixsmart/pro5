<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use App\Models\System\Client;
use App\Models\System\Message;

class MessageDescription extends Model
{

    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'message_id',
    ];

    /* public function payments()
    {
        return $this->hasMany(ClientPayment::class);
    } */

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function scopeClientGet($query){
        return $query->client->notifications->first()->updated_at;
    }
}
