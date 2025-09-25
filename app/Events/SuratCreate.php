<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuratCreate implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $surat;
    public $users;

    /**
     * Create a new event instance.
     */
    public function __construct($surat, $users)
    {
        $this->surat = $surat;
        $this->users = $users;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // Broadcast ke setiap user yang memiliki role super admin
        foreach ($this->users as $user) {
            $channels[] = new PrivateChannel('suratmasuk.' . $user->id);
        }

        // Tambahkan channel publik jika diperlukan
        $channels[] = new Channel('suratmasuk');

        return $channels;
    }

    public function broadcastAs()
    {
        return 'surat-masuk';
    }

    public function broadcastWith(): array
    {
        return [
            'surat' => $this->surat,
            'message' => 'Surat baru telah diterima',
            'timestamp' => now()
        ];
    }
}
