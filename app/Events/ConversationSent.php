<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;
class ConversationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    public $gymId;

    /**
     * Create a new event instance.
     *
     * @param  array  $data
     * @param $gymId
     */
    public function __construct(array $data, $gymId)
    {
        $this->data = $data;
        $this->gymId = $gymId;
    }

    /**
     * @return Channel|PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('conversation.'.$this->gymId);
    }
    public function broadcastAs()
    {
        return 'ConversationSent';
    }
    /**
     * @return array
     */
    public function broadcastWith(): array
    {
        return $this->data;
    }
}
