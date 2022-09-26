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
use Carbon\Carbon;

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

        switch ($request->column) {
            case 'number':
                $records = Client::where($request->column, 'like', "%{$request->value}%")
        ->get();
                break;
            case 'name':
                $records = Client::where($request->column, 'like', "%{$request->value}%")
        ->get();
                break;

            case 'plan':
                $records=Client::whereHas('plan', function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->value}%");
                })->get();
                break;

            default:
            $records = Client::all();
                break;
        }

        return new MessageCollection($records);
    }

    public function store(Request $request){

        //dd($request->all());

        $messages=ModelMessage::all();

        $req=$request->all();
        $id=$req['client_id'];
        if ($id!=null||$id) {
            ModelMessage::where('id',$id)->update([
                'message' => $req['message'],
                'date_start' => $req['date_start'],
                'time_start' => $req['time_start']
            ]);
        } else {
            $message=New ModelMessage();
            $message->message=$req['message'];
            $message->date_start=$req['date_start'];
            $message->time_start=$req['time_start'];
            $message->recurrence=$req['recurrence'];
            $message->save();
    
            $ids=$req['selecteds'];
            foreach ($ids as $key => $value) {
                $description = new MessageDescription();
                $description->client_id = $value;
                $description->message_id= $message->id;
                $description->save();
            }
        }
        return [
            'success' => true,
            'message' => ($req['client_id'])?'Mensaje editado con exito':'Nuevo mensaje creado con exito'
        ];
        
    }

    public function destroy($id)
    {
        
        $message_info = MessageDescription::where('message_id',$id)->delete();
        $message = ModelMessage::where('id',$id)->delete();

        return [
            'success' => true,
            'message' => 'Eliminado con éxito'
        ];
    }

    public function record($id){
        $data=[];
        $message_info=ModelMessage::where('id',$id)->get();
        return new MessageNotificationCollection($message_info);
        
    }

}
