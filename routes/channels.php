<?php

use Illuminate\Support\Facades\Broadcast;

// Channel untuk surat masuk per user
Broadcast::channel('suratmasuk.{userId}', function ($user, $userId) {
    // Pastikan user yang mengakses adalah user yang sama atau memiliki role tertentu
    return (int) $user->id === (int) $userId || $user->hasRole('super admin');
});

// Optional: Channel publik untuk semua user yang memiliki akses
Broadcast::channel('suratmasuk', function ($user) {
    // Hanya user dengan role tertentu yang bisa akses channel publik
    return $user->hasRole(['super admin', 'admin']);
});
