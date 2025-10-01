<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentUploadedNotification extends Notification
{
    use Queueable;

    protected $document;
    protected $uploader;

    /**
     * Create a new notification instance.
     */
    public function __construct(Document $document, User $uploader)
    {
        $this->document = $document;
        $this->uploader = $uploader;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'document_uploaded',
            'document_id' => $this->document->id,
            'document_title' => $this->document->title,
            'folder_name' => $this->document->folder->name ?? 'Unknown',
            'uploader_name' => $this->uploader->name,
            'message' => "Dokumen '{$this->document->title}' telah diupload ke folder {$this->document->folder->name}",
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
