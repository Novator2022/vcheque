<?php

namespace App\Listeners;

use App\Log;
use App\Events\LogEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogEventListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  LogEvent  $event
     * @return void
     */
    public function handle(LogEvent $event)
    {
        switch($event->type){
            case "organisation": {$event->data->load(['expense_type']);break;}
            case "cheque": {$event->data->load(['organisation','user']);break;}
        }
        
        $object = [
            "action"=>$event->action,
            "type"=>$event->type,
            "object"=>$event->data->toArray()
        ];
        Log::create([
            "user_id"=>$event->user->id,
            "type"=>$event->type,
            "data"=>$object
        ]);
    }
}
