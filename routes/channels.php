<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('hospital-notifications', function ($user) {
    // Return true jika user terautentikasi
    // Bisa ditambah logic tambahan seperti cek role/department
    return $user !== null;
});

// Private channel untuk notifikasi personal user
// Hanya user yang bersangkutan yang bisa akses channel ini
Broadcast::channel('user.{userId}', function ($user, $userId) {
    // User hanya bisa akses channel milik mereka sendiri
    return $user->id === (int) $userId;
});

// Channel untuk department/bagian tertentu (opsional)
// Jika ingin implementasi notifikasi berdasarkan department
Broadcast::channel('department.{departmentId}', function ($user, $departmentId) {
    // Assuming user has department_id field
    // return $user->department_id === (int) $departmentId;

    // Atau jika menggunakan relationship
    // return $user->department && $user->department->id === (int) $departmentId;

    // Untuk saat ini, return false karena belum ada implementasi department
    return false;
});

// Channel untuk admin/supervisor (opsional)
// Untuk notifikasi khusus admin seperti laporan, monitoring, etc
Broadcast::channel('admin-notifications', function ($user) {
    // Cek apakah user adalah admin
    return $user->is_admin === true || $user->role === 'admin';
});

// Presence channel untuk tracking user yang sedang online (opsional)
// Berguna untuk fitur "sedang mengetik" atau status online
Broadcast::channel('hospital-online', function ($user) {
    if ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            // 'department' => $user->department?->name,
            'last_seen' => now()->toISOString()
        ];
    }
    return false;
});
