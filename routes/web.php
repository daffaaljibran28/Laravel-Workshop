<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MailController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('products', ProductController::class);

        Route::get('/upload', [FileController::class, 'index']);
        Route::post('/upload', [FileController::class, 'upload'])->name('file.upload');
        Route::get('/laporan-pdf', [LaporanController::class, 'generatePDF']);
        Route::get('/upload-gambar', [ImageController::class, 'index']);
        Route::post('/upload-gambar', [ImageController::class, 'uploadAndResize'])->name('image.upload');
        Route::get('/send-email', [MailController::class, 'sendEmail']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {

    // Dashboard Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
            ->name('admin.dashboard');
    });

    // Dashboard Kasir
    Route::middleware(['role:kasir'])->group(function () {
        Route::get('/kasir', [KasirController::class, 'dashboard'])
            ->name('kasir.index');
        Route::get('/kasir/dashboard', [KasirController::class, 'dashboard'])
            ->name('kasir.dashboard');
        Route::post('/kasir/transaksi', [KasirController::class, 'store'])
            ->name('kasir.transaksi.store');
    });

});

require __DIR__.'/auth.php';