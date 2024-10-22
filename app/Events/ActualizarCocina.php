<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ActualizarCocina implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    //propiedades
    public $confirmar;
    public $mesa;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($confirmar, $mesa)
    {
        $this->confirmar = $confirmar;
        $this->mesa = $mesa;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('my-channel');
    }

    public function broadcastAs()
    {
        return 'actualizar-cocina';
    }
}
