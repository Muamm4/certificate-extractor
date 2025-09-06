<?php

use App\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;

// Route for the main page and processing
Route::get('/', [CertificateController::class, 'index'])->name('extractor.index');
Route::post('/upload', [CertificateController::class, 'upload'])->name('extractor.upload');

// Route for downloading the generated files
Route::get('/download/{id}/{type}', [CertificateController::class, 'download'])->name('extractor.download');

