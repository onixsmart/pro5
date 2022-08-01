<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\DB;
use App\Models\System\Client;
use App\Http\Resources\System\MessageCollection;
use Notification;
use Illuminate\Support\Facades\Notification as SendNotification;
use App\Notifications\Message;
use App\Models\System\Notification as ModelNotification;

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

    public function getFilter(Request $request){

        $records = Client::where($request->column, 'like', "%{$request->value}%")
        ->get();

        return new MessageCollection($records);
    }

    public function store(Request $request){
        //dd($request->all());
        $req=$request->all();
        $id=$req['client_id'];
        $data = ModelNotification::firstOrNew(['id' => $id]);
        $data->id = $id;
        $data->type = 'message';
        $data->notifiable_type = 'message';
        $data->notifiable_id = $id;
        $data->data=$req['message'];
        $data->save();
        
    }

    public function getRecords(){

    }

}
