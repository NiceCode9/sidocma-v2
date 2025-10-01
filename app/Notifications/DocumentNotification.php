<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class DocumentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $document;
    protected $type;
    protected $actor;
    protected $message;

    /**
     * Notification types:
     * - document_uploaded
     * - document_shared
     * - document_updated
     * - document_deleted
     * - document_permission_granted
     * - document_expiring_soon
     * - document_approved
     */
    public function __construct(Document $document, string $type, User $actor = null, string $customMessage = null)
    {
        $this->document = $document;
        $this->type = $type;
        $this->actor = $actor;
        $this->message = $customMessage ?? $this->getDefaultMessage();
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => $this->type ?? 'document_uploaded',
            'document_id' => $this->document->id,
            'document_title' => $this->document->title,
            'document_number' => $this->document->document_number,
            'document_file_name' => $this->document->file_name,
            'message' => $this->message,
            'actor_id' => $this->actor?->id,
            'actor_name' => $this->actor?->name,
            'folder_id' => $this->document->folder_id,
            'folder_name' => $this->document->folder?->name,
            'action_url' => route('documents.view-file', $this->document->id),
            'time_ago' => $this->document->created_at->diffForHumans(),
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => $this->type ?? 'document_uploaded',
            'document_id' => $this->document->id,
            'document_title' => $this->document->title,
            'document_number' => $this->document->document_number,
            'document_file_name' => $this->document->file_name,
            'message' => $this->message,
            'actor_name' => $this->actor?->name,
            'folder_name' => $this->document->folder?->name,
            'time_ago' => $this->document->created_at->diffForHumans(),
            'is_read' => false,
        ]);
    }

    protected function getDefaultMessage()
    {
        $actorName = $this->actor ? $this->actor->name : 'Sistem';

        return match ($this->type) {
            'document_uploaded' => "{$actorName} mengunggah dokumen baru: {$this->document->title}",
            'document_shared' => "{$actorName} membagikan dokumen: {$this->document->title}",
            'document_updated' => "{$actorName} memperbarui dokumen: {$this->document->title}",
            'document_deleted' => "{$actorName} menghapus dokumen: {$this->document->title}",
            'document_permission_granted' => "Anda mendapat akses ke dokumen: {$this->document->title}",
            'document_expiring_soon' => "Dokumen akan segera kadaluarsa: {$this->document->title}",
            'document_approved' => "{$actorName} menyetujui dokumen: {$this->document->title}",
            default => "Notifikasi dokumen: {$this->document->title}",
        };
    }
}
