<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ProductoListo implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    //propiedades
    public $confirmar;
    public $mozoId;
    public $mesa;
    public $producto;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($confirmar, $mozoId, $mesa, $producto)
    {
        $this->confirmar = $confirmar;
        $this->mozoId = $mozoId;
        $this->mesa = $mesa;
        $this->producto = $producto;
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
        return 'producto-listo';
    }
}
