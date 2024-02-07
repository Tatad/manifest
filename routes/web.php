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
    Route::group(['middleware' => ['web', 'DropboxAuthenticated']], function(){
        Route::get('dropbox', function(){
            //dd("!23");
            return Dropbox::post('users/get_current_account');
        });
    });

    Route::get('dropbox/connect', function(){
        return Dropbox::connect();
    });

    Route::get('/', [ManifestController::class, 'manifest'])->name('home');
    Route::get('/manifest', [ManifestController::class, 'manifest'])->name('manifest');
    Route::get('/manifest-grouped', [ManifestController::class, 'manifestGroupedView'])->name('manifestGroupedView');

    Route::get('/manifest-sent-list', [ManifestController::class, 'manifestSentList'])->name('manifestSentList');

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

    Route::post('/restore-image', [ManifestController::class, 'restoreImage']);

    Route::get('/test', [ManifestController::class, 'test']);
});

Route::get('/barcode_qr_reader', 'App\Http\Controllers\ImageUploadController@page');
Route::post('/barcode_qr_reader/upload', 'App\Http\Controllers\ImageUploadController@upload')->name('image.upload');


Route::get('/scanner', 'App\Http\Controllers\ScannerController@index')->name('scanner');
Route::get('/lookup', 'App\Http\Controllers\ScannerController@lookup')->name('lookup');
Route::get('/upc-lookup', 'App\Http\Controllers\ScannerController@UpcLookup')->name('UpcLookup');
Route::post('/lookupItem', 'App\Http\Controllers\ScannerController@lookupItem')->name('lookupItem');
Route::get('/scanned-list', 'App\Http\Controllers\ScannerController@scannedList')->name('scannedList');
Route::post('/scan', 'App\Http\Controllers\ScannerController@scan')->name('scan');
Route::post('/add-item-number', 'App\Http\Controllers\ScannerController@addItemNumber')->name('addItemNumber');
Route::post('/add-item', 'App\Http\Controllers\ScannerController@addItem')->name('addItem');
Route::post('/scan-upc-code', 'App\Http\Controllers\ScannerController@scanUpcCode')->name('scanUpcCode');
Route::post('/lookup-upc-code', 'App\Http\Controllers\ScannerController@lookupUpcCode')->name('lookupUpcCode');
Route::post('/add-item-scanned-list', 'App\Http\Controllers\ScannerController@addItemViaScannedList')->name('addItemViaScannedList');



require __DIR__.'/auth.php';
