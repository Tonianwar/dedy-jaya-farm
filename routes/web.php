<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminCowController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminHealthRecordController;
use App\Http\Controllers\AdminLivestockTagController;
use App\Http\Controllers\AdminQrCodeController;
use App\Http\Controllers\AdminSettingsController;
use App\Http\Controllers\AdminWeightLogController;
use App\Http\Controllers\PublicCatalogController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\PublicTagController;
use Illuminate\Support\Facades\Route;

Route::get('/', PublicHomeController::class)->name('home');
Route::get('/katalog', [PublicCatalogController::class, 'index'])->name('catalog');
Route::get('/qr/{tag:token}', [PublicTagController::class, 'show'])->name('tags.show');
Route::get('/sapi/{slug}', [PublicCatalogController::class, 'show'])->name('cows.show');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AdminAuthController::class, 'store'])->middleware('throttle:6,1');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'destroy'])->name('logout');
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/tags', [AdminLivestockTagController::class, 'index'])->name('tags.index');
    Route::post('/tags', [AdminLivestockTagController::class, 'store'])->name('tags.store');
    Route::resource('cows', AdminCowController::class)->except(['destroy']);
    Route::patch('/cows/{cow}/sold', [AdminCowController::class, 'markSold'])->name('cows.sold');
    Route::post('/cows/{cow}/tag', [AdminCowController::class, 'assignTag'])->name('cows.tag.assign');
    Route::get('/tags/{tag:code}/qr.svg', [AdminQrCodeController::class, 'svg'])->name('tags.qr.svg');
    Route::get('/tags/{tag:code}/qr.png', [AdminQrCodeController::class, 'png'])->name('tags.qr.png');
    Route::post('/cows/{cow}/weights', [AdminWeightLogController::class, 'store'])->name('cows.weights.store');
    Route::delete('/cows/{cow}/weights/{weightLog}', [AdminWeightLogController::class, 'destroy'])->name('cows.weights.destroy');
    Route::post('/cows/{cow}/health-records', [AdminHealthRecordController::class, 'store'])->name('cows.health-records.store');
    Route::delete('/cows/{cow}/health-records/{healthRecord}', [AdminHealthRecordController::class, 'destroy'])->name('cows.health-records.destroy');
    Route::get('/settings', [AdminSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
});
