<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PasienAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $nama;
    public $gender;
    public $umur;

    public function __construct($nama, $gender, $umur)
    {
        $this->nama = $nama;
        $this->gender = $gender;
        $this->umur = $umur;
    }

    public function broadcastOn()
    {
        return new Channel('pasien');
    }

    public function broadcastAs()
    {
        return 'pasien-added';
    }
}
