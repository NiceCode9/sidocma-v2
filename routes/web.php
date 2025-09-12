<?php

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

    // Document routes
    Route::post('/documents', [\App\Http\Controllers\DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{document}/download', [\App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
    Route::delete('/documents/{document}', [\App\Http\Controllers\DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('/documents/share/{document}', [\App\Http\Controllers\DocumentController::class, 'share'])->name('documents.share');
});

require __DIR__ . '/auth.php';
