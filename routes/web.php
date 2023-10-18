<?php

use App\Http\Controllers\ManifestController;
use App\Http\Controllers\ProfileController;
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

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/manifest', [ManifestController::class, 'manifest'])->name('manifest');

    Route::get('/manifest-data', [ManifestController::class, 'manifestData'])->name('manifestData');
    Route::post('/manifest-upload', [ManifestController::class, 'updload'])->name('manifest.upload');
    Route::patch('/manifest', [ManifestController::class, 'update'])->name('manifest.update');
    Route::post('/manifest', [ManifestController::class, 'send'])->name('manifest.send');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/manifest-upload-test', [ManifestController::class, 'read'])->name('read');
});

require __DIR__.'/auth.php';
