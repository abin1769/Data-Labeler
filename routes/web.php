<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DatasetController;

// User routes
Route::get('/', [DatasetController::class, 'index'])->name('home');
Route::post('/nickname', [DatasetController::class, 'setNickname'])->name('nickname.set');
Route::post('/logout-nickname', [DatasetController::class, 'logoutNickname'])->name('nickname.logout');
Route::get('/dataset-image/{filename}', [DatasetController::class, 'serveImage'])->name('images.show');

// User API routes
Route::get('/api/next-image', [DatasetController::class, 'getNextImage'])->name('api.next-image');
Route::post('/api/submit-label', [DatasetController::class, 'submitLabel'])->name('api.submit-label');
Route::get('/api/leaderboard', [DatasetController::class, 'getLeaderboard'])->name('api.leaderboard');

// Admin routes
Route::get('/admin', [DatasetController::class, 'adminView'])->name('admin');
Route::post('/admin/login', [DatasetController::class, 'adminLogin'])->name('admin.login');
Route::post('/admin/logout', [DatasetController::class, 'adminLogout'])->name('admin.logout');
Route::post('/admin/sync', [DatasetController::class, 'syncDataset'])->name('admin.sync');
Route::post('/admin/approve/{id}', [DatasetController::class, 'approveLabel'])->name('admin.approve');
Route::post('/admin/reject/{id}', [DatasetController::class, 'rejectLabel'])->name('admin.reject');
Route::post('/admin/update/{id}', [DatasetController::class, 'updateLabel'])->name('admin.update');
Route::get('/admin/download', [DatasetController::class, 'downloadCsv'])->name('admin.download');
Route::post('/admin/upload-dataset', [DatasetController::class, 'uploadDataset'])->name('admin.upload-dataset');
Route::post('/admin/upload-examples', [DatasetController::class, 'uploadExamples'])->name('admin.upload-examples');
Route::post('/admin/delete-example', [DatasetController::class, 'deleteExample'])->name('admin.delete-example');
