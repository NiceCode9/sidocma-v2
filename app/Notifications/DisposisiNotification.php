<?php

namespace App\Notifications;

use App\Models\Disposisi;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DisposisiNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $disposisi;
    protected $type;
    protected $message;

    public function __construct(Disposisi $disposisi, string $type, ?User $actor = null, ?string $customMessage = null)
    {
        $this->disposisi = $disposisi;
        $this->type = $type;
        $this->actor = $actor;
        $this->message = $customMessage ?? $this->getDefaultMessage();
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $suratInfo = '';
        if ($this->disposisi->surat) {
            $suratInfo = $this->disposisi->surat->no_surat . ' - ' . $this->disposisi->surat->perihal;
        } elseif ($this->disposisi->document) {
            $suratInfo = $this->disposisi->document->title;
        }

        return [
            'type' => 'disposisi_' . $this->type,
            'disposisi_id' => $this->disposisi->id,
            'no_agenda' => $this->disposisi->no_agenda,
            'asal_naskah' => $this->disposisi->asal_naskah,
            'isi_informasi' => $this->disposisi->isi_informasi,
            'sifat' => $this->disposisi->sifat,
            'surat_info' => $suratInfo,
            'creator_name' => $this->disposisi->creator->name ?? 'Unknown',
            'message' => $this->message,
            'url' => route('disposisi.show', $this->disposisi->id),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    protected function getDefaultMessage(): string
    {
        $actorName = $this->actor ? $this->actor->name : 'Sistem';

        return match ($this->type) {
            'baru' => "Disposisi baru dari {$actorName}: {$this->disposisi->no_agenda}",
            'selesai' => "Disposisi {$this->disposisi->no_agenda} telah selesai oleh {$actorName}",
            default => "Notifikasi disposisi: {$this->disposisi->no_agenda}",
        };
    }
}
