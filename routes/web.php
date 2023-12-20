<?php

use App\Http\Controllers\ManifestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

// Route::get('/', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/account', [RegistrationController::class, 'create'])->name('account');
Route::post('/account', [RegistrationController::class, 'store'])->name('account.store');
Route::post('/authenticate', [RegistrationController::class, 'authenticate'])->name('authenticate');

Route::middleware('auth')->group(function () {
    Route::get('/', [ManifestController::class, 'manifest'])->name('home');
    Route::get('/manifest', [ManifestController::class, 'manifest'])->name('manifest');
    Route::get('/manifest-grouped', [ManifestController::class, 'manifestGroupedView'])->name('manifestGroupedView');
    Route::get('/manifest-sent', [ManifestController::class, 'manifestSent'])->name('manifest.sent');
    Route::post('/manifest-download-batch-pdf', [ManifestController::class, 'batchDownloadPdf'])->name('manifest.batchPdf');
    Route::post('/manifest-download-batch-csv', [ManifestController::class, 'batchDownloadCsv'])->name('manifest.batchCsv');
    Route::post('/manifest-add-name', [ManifestController::class, 'addManifestName'])->name('manifest.addManifestName');


    Route::post('/manifest-download-pdf', [ManifestController::class, 'pdfManifest'])->name('manifest.pdf');

    Route::get('/manifest-data', [ManifestController::class, 'manifestData'])->name('manifestData');
    Route::get('/pallets', [ManifestController::class, 'pallets'])->name('pallets');
    Route::post('/manifest-upload', [ManifestController::class, 'updload'])->name('manifest.upload');
    Route::patch('/manifest', [ManifestController::class, 'update'])->name('manifest.update');
    Route::post('/manifest', [ManifestController::class, 'send'])->name('manifest.send');
    Route::post('/manifest-restore', [ManifestController::class, 'restore'])->name('manifest.restore');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/manifest-upload-test', [ManifestController::class, 'read'])->name('read');
});

Route::get('/barcode_qr_reader', 'App\Http\Controllers\ImageUploadController@page');
Route::post('/barcode_qr_reader/upload', 'App\Http\Controllers\ImageUploadController@upload')->name('image.upload');

require __DIR__.'/auth.php';
