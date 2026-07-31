<?php

namespace App\Events;

use App\Models\Disposisi;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DisposisiCreate implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $disposisi;

    public function __construct(Disposisi $disposisi)
    {
        $this->disposisi = $disposisi;
    }

    public function broadcastOn(): array
    {
        $channels = [];

        foreach ($this->disposisi->targets as $target) {
            $users = \App\Models\User::where('unit_id', $target->unit_id)->get();
            foreach ($users as $user) {
                $channels[] = new PrivateChannel('disposisi.' . $user->id);
            }
        }

        $channels[] = new Channel('disposisi');

        return $channels;
    }

    public function broadcastAs()
    {
        return 'disposisi-baru';
    }

    public function broadcastWith(): array
    {
        return [
            'disposisi_id' => $this->disposisi->id,
            'no_agenda' => $this->disposisi->no_agenda,
            'asal_naskah' => $this->disposisi->asal_naskah,
            'sifat' => $this->disposisi->sifat,
            'status' => $this->disposisi->status,
            'message' => 'Disposisi baru: ' . $this->disposisi->no_agenda,
            'timestamp' => now(),
        ];
    }
}
