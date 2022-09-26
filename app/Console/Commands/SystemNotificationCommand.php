<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant\Task;
use App\Models\System\Message;
use Carbon\Carbon;
use Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\System\Client;
use App\Notifications\System\Message as MessageNotification;

class SystemNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message:run-notification';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execution of Notices to tenants';
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        foreach (Message::where('time_start', Carbon::now()->format('H:i').':00')->get() as $message) {
            try {
                $message_text=$message->message;
                $message_info=$message->messageDescription;
                $message_date=$message->date_start;
                $message_day=Carbon::parse($message_date)->format('d');
                $message_month=Carbon::parse($message_date)->format('m');
                $message_year=Carbon::parse($message_date)->format('Y');
                $tenant_id=null;
                $this->info($message_info);
                $this->info($message_month);
                $this->info(Carbon::now()->format('m'));
                if ($message->recurrence==1) {
                    if(Carbon::now()->format('m')==$message_month){
                        $this->info('paso mes');
                        if(Carbon::now()->format('d')==$message_day){
                            if (count($message_info)>0) {
                                $this->info('paso dia');
                                $this->info($message_info);
                                Log::info('dentro del if ' . $message_info . ' Message success');
                                foreach ($message_info as $value) {
                                    $id_client=$value->client_id;
                                    $tenant_id = Client::where('id', $id_client)->first();
                                    //Envio de notificacion al tenant
                                    $tenant_id->notify(new MessageNotification($message_text));
                                    //Log::info('id con value' . $value . ' tenant');
                                }
                                $message_month=Carbon::parse($message_date)->addMonth(1);
                                Message::where('id',$message->id)->update([
                                    'date_start' => $message_month
                                ]);
                            }
                        }
                    }
                } 

                if ($message->recurrence==0) {
                    if(Carbon::now()->format('Y')==$message_year){
                        if(Carbon::now()->format('m')==$message_month){
                            if(Carbon::now()->format('d')==$message_day){
                                if (count($message_info)>0) {
                                    foreach ($message_info as $value) {
                                        $id_client=$value->client_id;
                                        $tenant_id = Client::where('id', $id_client)->get();
                                        //Envio de notificacion al tenant
                                        $tenant_id->notify(new MessageNotification($message_text));
         
                                    }
                                    $message_month=Carbon::parse($message_date)->addYear(1);
                                    Message::where('id',$message->id)->update([
                                            'date_start' => $message_month
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                
            
                
                Log::info('Notification for ' . $tenant_id . ' Message success');
                $this->info('Ejecutando mensaje');

            }
            catch (\Exception $e) {
                Log::error("Backup failed -- Line: {$e->getLine()} - Message: {$e->getMessage()} - File: {$e->getFile()}");
                $this->info('Fallo ejecucion');
            }
        };
    }
}
