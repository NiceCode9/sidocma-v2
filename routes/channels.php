<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// User private channel for notifications
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel untuk surat masuk per user
Broadcast::channel('suratmasuk.{userId}', function ($user, $userId) {
    // Pastikan user yang mengakses adalah user yang sama atau memiliki role tertentu
    return (int) $user->id === (int) $userId || $user->hasRole('super admin');
});

Broadcast::channel('surat-readed.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('surat-readed', function ($user) {
    return $user->hasRole('super admin');
});

// Channel untuk disposisi per user
Broadcast::channel('disposisi.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('disposisi', function ($user) {
    return $user->hasAnyRole(['super admin', 'direktur']);
});
