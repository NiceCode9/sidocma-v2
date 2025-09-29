<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentShare;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    public function uploadDocument(
        UploadedFile $file,
        Folder $folder,
        User $uploader,
        ?string $description = null,
        bool $is_later = false,
        ?string $document_number = null,
        ?int $category = null,
    ): Document {
        $fileName = $this->generateUniqueName($file);
        $filePath = $file->storeAs('documents', $fileName, 'public');

        $document = Document::create([
            'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'description' => $description,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'file_extension' => $file->getClientOriginalExtension(),
            'folder_id' => $folder->id,
            'category_id' => $category,
            'created_by' => $uploader->id,
            'document_number' => $document_number,
            'version' => 1,
            'is_active' => true,
            'is_latter' => $is_later,
        ]);

        $this->createDocumentShare($document, $uploader);

        return $document;
    }

    public function generateUniqueName(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);
        return "{$timestamp}_{$random}.{$extension}";
    }

    public function createDocumentShare(
        Document $document,
        User $creator,
        ?string $password = null,
        ?\DateTime $expiresAt = null,
    ): DocumentShare {
        return DocumentShare::create([
            'uuid' => Str::uuid(),
            'document_id' => $document->id,
            'created_by' => $creator->id,
            'password' => $password,
            'expires_at' => $expiresAt,
        ]);
    }

    public function downloadDocument(Document $document): string
    {
        // $this->logActivity($downloader, ActivityLog::ACTION_DOWNLOAD, $document);
        return Storage::disk('public')->path($document->file_path);
    }

    public function deleteDocument(Document $document, User $deleter): bool
    {
        // Soft delete
        $document->update(['is_active' => false]);

        // Log activity

        return true;
    }

    // public function
}
