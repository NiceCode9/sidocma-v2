<?php

namespace App\Events;

use App\Models\Surat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuratCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

public $surat;

    public function __construct(Surat $surat)
    {
        $this->surat = $surat;
    }

    public function broadcastOn()
    {
        return [
            new Channel('hospital-notifications'),
            new PrivateChannel('user.' . $this->surat->user_id)
        ];
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->surat->id,
            'no_surat' => $this->surat->no_surat,
            'perihal' => $this->surat->perihal,
            'keterangan' => $this->surat->keterangan,
            'user' => $this->surat->user->name,
            'created_at' => $this->surat->created_at->format('d/m/Y H:i'),
            'type' => 'surat_created'
        ];
    }

    public function broadcastAs()
    {
        return 'surat.created';
    }
}
