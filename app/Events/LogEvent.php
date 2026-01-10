<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class LogEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $type;
    public $user;
    public $action;

    public function __construct($data, $user, $action="create", $type="user")
    {
        $this->data = $data;
        $this->type = $type;
        $this->user = $user;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PresenceChannel('channel-logs'),
            new PrivateChannel('channel-logs.' . $this->user->id),
        ];
        // return new Channel('channel-logs');
        // return new PrivateChannel('channel-logs.' . $this->user->id);
    }

    /**
     * [broadcastWith description]
     * @return [type] [description]
     */
    public  function broadcastWith()
    {
       return [
           'data' => $this->data,
           'object' => $this->type ,
           'action' => $this->action,
       ];
    }

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function  broadcastAs()
    {
        return 'log';
    }
}
