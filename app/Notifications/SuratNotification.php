<?php

namespace App\Notifications;

use App\Models\Surat;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuratNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $surat;
    protected $type;
    protected $actor;
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Surat $surat, string $type, User $actor = null, string $customMessage = null)
    {
        $this->surat = $surat;
        $this->type = $type;
        $this->actor = $actor;
        $this->message = $customMessage ?? $this->getDefaultMessage();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'surat_uploaded',
            'surat_id' => $this->surat->id,
            'no_surat' => $this->surat->no_surat,
            'perihal' => $this->surat->perihal,
            'keterangan' => $this->surat->keterangan,
            'sender_id' => $this->surat->user_id,
            'sender_name' => $this->surat->user->name ?? 'Unknown',
            'has_file' => !empty($this->surat->file),
            'message' => "Surat baru dari {$this->surat->user->name} - {$this->surat->perihal}",
            'url' => route('surat.view', $this->surat->id),
            'time_ago' => $this->surat->created_at->diffForHumans(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'surat_uploaded',
            'surat_id' => $this->surat->id,
            'no_surat' => $this->surat->no_surat,
            'perihal' => $this->surat->perihal,
            'keterangan' => $this->surat->keterangan,
            'sender_id' => $this->surat->user_id,
            'sender_name' => $this->surat->user->name ?? 'Unknown',
            'has_file' => !empty($this->surat->file),
            'message' => "Surat baru dari {$this->surat->user->name} - {$this->surat->perihal}",
            'url' => route('surat.view', $this->surat->id),
            'time_ago' => $this->surat->created_at->diffForHumans(),
        ]);
    }

    protected function getDefaultMessage()
    {
        $actorName = $this->actor ? $this->actor->name : 'Sistem';

        return match ($this->type) {
            'surat_uploaded' => "{$actorName} mengirim surat baru: {($this->surat->no_surat ?? $this->surat->perihal)}",
            // 'document_shared' => "{$actorName} membagikan dokumen: {$this->document->title}",
            // 'document_updated' => "{$actorName} memperbarui dokumen: {$this->document->title}",
            // 'document_deleted' => "{$actorName} menghapus dokumen: {$this->document->title}",
            // 'document_permission_granted' => "Anda mendapat akses ke dokumen: {$this->document->title}",
            // 'document_expiring_soon' => "Dokumen akan segera kadaluarsa: {$this->document->title}",
            // 'document_approved' => "{$actorName} menyetujui dokumen: {$this->document->title}",
            default => "Notifikasi surat: {($this->surat->no_surat ?? $this->surat->perihal)}",
        };
    }
}
