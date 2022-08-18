<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant\Task;
use App\Models\System\Message;
use Carbon\Carbon;
use Artisan;

class Systemnotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:run';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecucion de Notificaciones para los tenants';
    
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
        foreach (Message::where('time_start', Carbon::now()->format('H:i').':00')->get() as $task) {
            try {

                $this->info('Ejecutando a la hora del mensaje');

                /* Artisan::call($task->class);
                
                $task->output = Artisan::output();
                $task->save(); */
            }
            catch (\Exception $e) {
                $this->info('Fallo ejecucion');
            }
        };
    }
}
