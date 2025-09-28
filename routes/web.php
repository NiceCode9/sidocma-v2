<?php

use App\Http\Controllers\ManagementSuratController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('units', \App\Http\Controllers\UnitController::class);
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::resource('document-categories', \App\Http\Controllers\DocumentCategoryController::class);

    Route::get('/folders', [\App\Http\Controllers\FolderController::class, 'index'])->name('folders.index');
    // AJAX routes for folder operations
    Route::get('/folders/browse/{folder?}', [\App\Http\Controllers\FolderController::class, 'browse'])->name('folders.browse');
    Route::post('/folders', [\App\Http\Controllers\FolderController::class, 'store'])->name('folders.store');
    Route::delete('/folders/{folder}', [\App\Http\Controllers\FolderController::class, 'destroy'])->name('folders.destroy');
    Route::get('/folders/search', [\App\Http\Controllers\FolderController::class, 'search'])->name('folders.search');
    Route::get('/folders/get-units', [\App\Http\Controllers\FolderController::class, 'getUnits'])->name('folders.get-units');
    Route::get('/folders/get-roles', [\App\Http\Controllers\FolderController::class, 'getRoles'])->name('folders.get-roles');
    Route::get('/folders/get-users', [\App\Http\Controllers\FolderController::class, 'getUsers'])->name('folders.get-users');
    Route::get('/folders/get-permission', [\App\Http\Controllers\FolderController::class, 'getPermissionFolder'])->name('folders.get-permission');
    Route::post('/folders/set-permission', [\App\Http\Controllers\FolderController::class, 'setPermissionFolder'])->name('folders.set-permission');
    Route::get('/folders/info/{folder}', [\App\Http\Controllers\FolderController::class, 'getFolderInfo'])->name('folders.info');

    // Document routes
    Route::post('/documents', [\App\Http\Controllers\DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/download', [\App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [\App\Http\Controllers\DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/documents/share/{document}', [\App\Http\Controllers\DocumentController::class, 'share'])->name('documents.share');

    // Management Surat Routes
    Route::prefix('management-surat')->name('management-surat.')->group(function () {
        Route::get('/', [ManagementSuratController::class, 'index'])->name('index');

        // Surat Masuk Routes
        Route::get('/surat-masuk/data', [ManagementSuratController::class, 'suratMasukData'])->name('surat-masuk.data');
        Route::get('/surat-masuk/stats', [ManagementSuratController::class, 'suratMasukStats'])->name('surat-masuk.stats');

        // Surat Keluar Routes
        Route::get('/surat-keluar/data', [ManagementSuratController::class, 'suratKeluarData'])->name('surat-keluar.data');
        Route::get('/surat-keluar/stats', [ManagementSuratController::class, 'suratKeluarStats'])->name('surat-keluar.stats');
    });

    Route::prefix('kirim-surat')->group(function () {
        Route::get('/', [ManagementSuratController::class, 'kirimSurat'])->name('kirim-surat.index');
        Route::get('/data', [ManagementSuratController::class, 'getData'])->name('kirim-surat.data');
        Route::post('/', [ManagementSuratController::class, 'store'])->name('kirim-surat.store');
        Route::get('/{id}', [ManagementSuratController::class, 'show'])->name('kirim-surat.show');
        Route::put('/{id}', [ManagementSuratController::class, 'update'])->name('kirim-surat.update');
        Route::delete('/{id}', [ManagementSuratController::class, 'destroy'])->name('kirim-surat.destroy');
        Route::get('/{id}/download', [ManagementSuratController::class, 'download'])->name('kirim-surat.download');
    });

    // Notification routes
    Route::get('/notifications/unread-count', [ManagementSuratController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/list', [ManagementSuratController::class, 'getNotifications'])->name('notifications.list');
    Route::post('/notifications/{id}/mark-read', [ManagementSuratController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [ManagementSuratController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    Route::get('/surat/{id}/view', [ManagementSuratController::class, 'viewFile'])->name('surat.view');
    Route::get('/surat/{id}/stream', [ManagementSuratController::class, 'streamFile'])->name('surat.stream');

    Route::get('/surat/{id}/docx-html', [ManagementSuratController::class, 'viewDocxHtml'])->name('surat.docx-html');
});

require __DIR__ . '/auth.php';
