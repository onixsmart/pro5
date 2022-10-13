<?php

namespace App\Http\Resources\System;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\System\Client;
use App\Models\System\MessageDescription;
use Carbon\Carbon;

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
            $array_select=[];
            $selecteds = MessageDescription::where('message_id',$row->id)->select('client_id')->get();
            foreach ($selecteds as $value) {
                $array_select[] = $value->client_id;
            }
            //dd($array_select);
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
                'id'=>$row->id,
                'message' => $row->message,
                'updated' => Carbon::parse($row->date_update)->format('Y:m:d'),
                'recurrence' => $recurrence,
                'date_start' => $row->date_start,
                'time_start' => $row->time_start,
                'selecteds' => $array_select,
            ];
        });

    }
    
}
