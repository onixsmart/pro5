<?php

namespace App\Http\Resources\System;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\System\Client;

class MessageNotificationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->collection->transform(function($row, $key) {
            $recurrence='';
            if ($row->recurrence!=null) {
                if ($row->recurrence==0) {
                    $recurrence='Anual';
                } else {
                    $recurrence='Mensual';
                }
            }
            //dd($recurrence);

            return [
                'message' => $row->message,
                'update' => '',
                'recurrence' => $recurrence,
            ];
        });

    }
    
}
