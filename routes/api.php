<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompoundController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FinishSpecController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\ShareLinkController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\SavedSearchController;
use App\Http\Controllers\UnitTypeController;
use App\Http\Controllers\UnitAreaController;
use App\Http\Controllers\Api\FCMTokenController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Admin\UnitAdminController;
use App\Http\Controllers\Admin\SaleAdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Laravel Real Estate API - All API routes with Token Authentication
| Base URL: http://127.0.0.1:8001/api
|
| Public Routes: /register, /login
| Protected Routes: All other endpoints require Bearer token
|
*/

// ============================================================
// PUBLIC ROUTES (No Authentication Required)
// ============================================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public access to companies and compounds for website
Route::get('/companies', [CompanyController::class, 'index']);
Route::get('/companies/{id}', [CompanyController::class, 'show']);
Route::get('/compounds', [CompoundController::class, 'index']);
Route::get('/compounds/{id}', [CompoundController::class, 'show']);
Route::get('/sales', [SalesController::class, 'index']);
Route::get('/sales/{id}', [SalesController::class, 'show']);


// ============================================================
// PROTECTED ROUTES (Require Bearer Token)
// ============================================================
Route::middleware('auth:sanctum')->group(function () {

    // AUTH - Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // SEARCH
    Route::get('/search', [SearchController::class, 'search']);

    // COMPANIES (authenticated)
    Route::get('/companies-with-sales', [SalesController::class, 'getCompaniesWithSales']);

    // UNITS
    Route::get('/units', [UnitController::class, 'index']);
    Route::get('/units/{id}', [UnitController::class, 'show']);
    Route::post('/filter-units', [UnitController::class, 'filter']);
    Route::get('/filter-units', [UnitController::class, 'filter']);

    // STAGES
    Route::get('/stages', [StageController::class, 'index']);
    Route::get('/stages/{id}', [StageController::class, 'show']);
    Route::post('/stages', [StageController::class, 'store']);
    Route::put('/stages/{id}', [StageController::class, 'update']);
    Route::delete('/stages/{id}', [StageController::class, 'destroy']);

    // SHARE LINKS
    Route::get('/share-link', [ShareLinkController::class, 'getShareData']);

    // STATISTICS
    Route::get('/statistics', [StatisticsController::class, 'index']);

    // SAVED SEARCHES
    Route::get('/saved-searches', [SavedSearchController::class, 'index']);
    Route::get('/saved-searches/{id}', [SavedSearchController::class, 'show']);
    Route::post('/saved-searches', [SavedSearchController::class, 'store']);
    Route::put('/saved-searches/{id}', [SavedSearchController::class, 'update']);
    Route::delete('/saved-searches/{id}', [SavedSearchController::class, 'destroy']);

    // UNIT TYPES
    Route::get('/unit-types', [UnitTypeController::class, 'index']);
    Route::get('/unit-types/{id}', [UnitTypeController::class, 'show']);
    Route::post('/unit-types', [UnitTypeController::class, 'store']);
    Route::put('/unit-types/{id}', [UnitTypeController::class, 'update']);
    Route::delete('/unit-types/{id}', [UnitTypeController::class, 'destroy']);

    // UNIT AREAS
    Route::get('/unit-areas', [UnitAreaController::class, 'show']);
    Route::post('/unit-areas', [UnitAreaController::class, 'store']);
    Route::put('/unit-areas', [UnitAreaController::class, 'update']);
    Route::delete('/unit-areas', [UnitAreaController::class, 'destroy']);

    // FINISH SPECIFICATIONS
    Route::get('/finish-specs', [FinishSpecController::class, 'index']);
    Route::get('/finish-specs/{id}', [FinishSpecController::class, 'show']);
    Route::post('/finish-specs', [FinishSpecController::class, 'store']);
    Route::put('/finish-specs/{id}', [FinishSpecController::class, 'update']);
    Route::delete('/finish-specs/{id}', [FinishSpecController::class, 'destroy']);

    // USER PROFILE
    Route::get('/user-by-email', [UserController::class, 'getUserByEmail']);
    Route::get('/profile', [UserController::class, 'getProfile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::post('/change-password', [UserController::class, 'changePassword']);

    // SALESPEOPLE
    Route::get('/salespeople-by-compound', [UserController::class, 'getSalespeopleByCompound']);

    // FAVORITES
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites', [FavoriteController::class, 'destroy']);

    // FCM TOKEN (Push Notifications)
    Route::post('/fcm-token', [FCMTokenController::class, 'store']);
    Route::delete('/fcm-token', [FCMTokenController::class, 'destroy']);

    // MANUAL NOTIFICATIONS (Optional - for admin/testing)
    Route::post('/notifications/send-all', [NotificationController::class, 'sendToAll']);
    Route::post('/notifications/send-role', [NotificationController::class, 'sendToRole']);
    Route::post('/notifications/send-topic', [NotificationController::class, 'sendToTopic']);

    // ============================================================
    // ADMIN ROUTES - Units & Sales Management (Auto-send FCM notifications)
    // ============================================================
    Route::prefix('admin')->group(function () {
        // UNITS - Create/Update/Delete (Auto-sends notifications to buyers)
        Route::post('/units', [UnitAdminController::class, 'store']);
        Route::put('/units/{id}', [UnitAdminController::class, 'update']);
        Route::delete('/units/{id}', [UnitAdminController::class, 'destroy']);

        // SALES - Create/Update/Delete (Auto-sends notifications to buyers)
        Route::post('/sales', [SaleAdminController::class, 'store']);
        Route::put('/sales/{id}', [SaleAdminController::class, 'update']);
        Route::delete('/sales/{id}', [SaleAdminController::class, 'destroy']);
    });
});
