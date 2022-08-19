<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\DB;
use App\Models\System\Client;
use App\Models\System\User;
use App\Http\Resources\System\MessageCollection;
use Notification;
use Illuminate\Support\Facades\Notification as SendNotification;
use App\Notifications\System\Message;
use App\Models\System\Notification as ModelNotification;
use App\Models\System\Message as ModelMessage;
use App\Models\System\MessageDescription;
use App\Http\Resources\System\MessageNotificationCollection;

class MessageController extends Controller
{
    public function index()
    {
        return view('system.message.index');
    }

    public function columns()
    {
        return [
            'number' => 'Ruc',
            'name' => 'Nombre',
            'plan' => 'Plan',
            'all' => 'Todos',
        ];
    }

    public function records(){
        $records = ModelMessage::first()
        ->get();

        return new MessageNotificationCollection($records);
    }

    public function getFilter(Request $request){

        $records = Client::where($request->column, 'like', "%{$request->value}%")
        ->get();

        return new MessageCollection($records);
    }

    public function store(Request $request){

        //dd($request->all());
        $req=$request->all();
        $id=$req['client_id'];

        $message=New ModelMessage();
        $message->message=$req['message'];
        $message->date_start=$req['date_start'];
        $message->time_start=$req['time_start'];
        $message->recurrence=$req['recurrence'];
        $message->save();

        if(!empty($req['selecteds'])){
            $ids=$req['selecteds'];
            foreach ($ids as $key => $value) {
                $description = new MessageDescription();
                $description->client_id = $value;
                $description->message_id= $message->id;
                $description->save();
            }
        }
        
        //Notification::send($idUser, new Message($id));
        
    }

    public function destroy($id)
    {
        $message = MessageDescription::findOrFail($id);
        $message->delete();

        return [
            'success' => true,
            'message' => 'Eliminado con éxito'
        ];
    }

}
