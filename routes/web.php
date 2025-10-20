<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestNotificationController;
use App\Http\Controllers\ExportController;

Route::get('/', function () {
    return view('welcome');
});

// FCM Testing Routes
Route::prefix('test-fcm')->group(function () {
    Route::get('/status', [TestNotificationController::class, 'status']);
    Route::get('/send', [TestNotificationController::class, 'test']);
    Route::get('/add-token', [TestNotificationController::class, 'addTestToken']);
    Route::get('/test-unit', [TestNotificationController::class, 'testUnitNotification']);
    Route::get('/test-sale', [TestNotificationController::class, 'testSaleNotification']);
});

// Excel Export Routes
Route::prefix('export')->name('export.')->group(function () {
    Route::get('/sales-availability', [ExportController::class, 'exportSalesAvailability'])->name('sales-availability');
    Route::get('/units-availability', [ExportController::class, 'exportUnitsAvailability'])->name('units-availability');
    Route::get('/merged-availability', [ExportController::class, 'exportMergedAvailability'])->name('merged-availability');
});

// Excel Import Routes
Route::prefix('import')->name('import.')->group(function () {
    Route::get('/merged-availability', [ExportController::class, 'showImportForm'])->name('merged-availability.form');
    Route::post('/merged-availability', [ExportController::class, 'importMergedAvailability'])->name('merged-availability');
});

Route::get('/test-auth', function () {
    $user = auth()->user();
    if (!$user) {
        return response()->json(['authenticated' => false]);
    }
    
    $panel = Filament\Facades\Filament::getPanel('admin');
    return response()->json([
        'authenticated' => true,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ],
        'canAccessPanel' => $user->canAccessPanel($panel)
    ]);
});
