<?php

namespace App\Events;

use App\Models\Surat;
use Carbon\Carbon;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SuratRead implements ShouldBroadcast
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
            'opened_by' => $this->surat->opened_by,
            // 'read_at' => $this->surat->read_at->format('d/m/Y H:i'),
            'read_at' => Carbon::parse($this->surat->read_at)->format('d/m/Y H:i'),
            'type' => 'surat_read'
        ];
    }

    public function broadcastAs()
    {
        return 'surat.read';
    }
}
