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

class SuratReaded implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $surat;
    protected $adminUser;

    /**
     * Create a new event instance.
     */
    public function __construct($surat, $adminUser)
    {
        $this->surat = $surat;
        $this->adminUser = $adminUser;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('surat-readed.' . $this->surat->user_id),
            new Channel('surat-readed'),
        ];
    }

    public function broadcastAs()
    {
        return 'surat-readed';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => 'surat_read', // Tipe event untuk admin
            'surat' => [
                'id' => $this->surat->id,
                'no_surat' => $this->surat->no_surat,
                'perihal' => $this->surat->perihal,
                'user_name' => $this->surat->user->name,
                'read_at' => $this->surat->read_at,
                'is_read' => true,
            ],
            'opened_by' => $this->adminUser->name,
            'admin_id' => $this->adminUser->id,
            'message' => "Surat {$this->surat->no_surat} telah dibaca oleh {$this->adminUser->name}",
            'timestamp' => now()->toISOString(),
            'action' => 'update_table', // Instruksi untuk admin
        ];
    }
}
