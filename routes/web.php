<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DatasetController;
use App\Http\Controllers\AuditController;

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
Route::post('/admin/approve-all', [DatasetController::class, 'approveAll'])->name('admin.approve-all');
Route::post('/admin/reject-all', [DatasetController::class, 'rejectAll'])->name('admin.reject-all');
Route::post('/admin/reject-all-pending', [DatasetController::class, 'rejectAllPending'])->name('admin.reject-all-pending');
Route::get('/admin/download', [DatasetController::class, 'downloadCsv'])->name('admin.download');
Route::post('/admin/upload-dataset', [DatasetController::class, 'uploadDataset'])->name('admin.upload-dataset');
Route::post('/admin/upload-examples', [DatasetController::class, 'uploadExamples'])->name('admin.upload-examples');
Route::post('/admin/delete-example', [DatasetController::class, 'deleteExample'])->name('admin.delete-example');
Route::post('/admin/workspace/settings', [DatasetController::class, 'updateWorkspaceSettings'])->name('admin.workspace-settings');
Route::post('/admin/workspace/passkey', [DatasetController::class, 'regenerateWorkspacePasskey'])->name('admin.workspace-passkey');

// Audit routes -- putaran review kandidat salah-label (hasil scripts/audit_data.py)
Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
Route::get('/audit/relabel', [AuditController::class, 'relabelIndex'])->name('audit.relabel');
Route::get('/audit-image/{label}/{filename}', [AuditController::class, 'serveImage'])->name('audit.image');

Route::get('/api/audit/next', [AuditController::class, 'getNextCandidate'])->name('api.audit.next');
Route::post('/api/audit/submit', [AuditController::class, 'submitDecision'])->name('api.audit.submit');
Route::get('/api/audit/relabel/next', [AuditController::class, 'getNextRelabel'])->name('api.audit.relabel.next');
Route::post('/api/audit/relabel/submit', [AuditController::class, 'submitRelabel'])->name('api.audit.relabel.submit');

// Audit admin routes
Route::get('/admin/audit', [AuditController::class, 'adminView'])->name('admin.audit');
Route::post('/admin/audit/upload-csv', [AuditController::class, 'uploadCandidatesCsv'])->name('admin.audit.upload-csv');
Route::post('/admin/audit/upload-train-zip', [AuditController::class, 'uploadTrainZip'])->name('admin.audit.upload-train-zip');
Route::get('/admin/audit/download/round1', [AuditController::class, 'downloadRound1Csv'])->name('admin.audit.download-round1');
Route::get('/admin/audit/download/round2', [AuditController::class, 'downloadRound2Csv'])->name('admin.audit.download-round2');
