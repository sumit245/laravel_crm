<?php

use App\Http\Controllers\API\DropdownController;
use App\Http\Controllers\API\InventoryController;
use App\Http\Controllers\InventoryController as InventoryControllers;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\SiteController;
use App\Http\Controllers\API\StaffController;
use App\Http\Controllers\API\StreetlightController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\API\VendorController;
use Illuminate\Support\Facades\Route;


Route::post('/login', [LoginController::class, 'login']);
Route::apiResource('staff', StaffController::class);
Route::prefix('staff')->group(function () {
    Route::post('/upload-avatar/{id}', [StaffController::class, 'uploadAvatar']);
    Route::get('/get-performance/{user_id}', [StaffController::class, 'getStaffPerformance']);
});

Route::prefix('vendor')->group(function () {
    Route::get('/', [VendorController::class, 'index']); //View all vendors
    Route::post('/', [VendorController::class, 'create']); // Create vendor
    // Route::post('/upload-avatar/{id}', [VendorController::class, 'uploadAvatar']);
    Route::get('{id}', [VendorController::class, 'show']); // View a specific vendor
    Route::get('{id}/edit', [VendorController::class, 'edit']); // Edit vendor (optional)
    Route::put('{id}', [VendorController::class, 'update']); // Update vendor
    Route::delete('{id}', [VendorController::class, 'destroy']); // Delete vendor
    Route::get('{vendorId}/sites', [TaskController::class, 'getSitesForVendor']);
});

Route::get('/vendors/{vendorId}/sites', [TaskController::class, 'getSitesForVendor']);

Route::apiResource('projects', ProjectController::class);
Route::apiResource('site', SiteController::class);

Route::apiResource('task', TaskController::class);
Route::post('/tasks/{id}/approve', [TaskController::class, 'approveTask']);
Route::get('/get_installable_pole/{ward}', [TaskController::class, 'getInstallablePoles']);
Route::post('streetlight/tasks/update', [TaskController::class, 'submitStreetlightTasks']);
Route::get('streetlight/tasks/engineers', [StreetlightController::class, 'getEngineerTasks']);
Route::get('streetlight/tasks/vendors', [StreetlightController::class, 'getVendorTasks']);
Route::apiResource('streetlight', StreetlightController::class);
Route::post('send-to-rms', [TaskController::class, 'sendDataToRMS']);

Route::apiResource('inventories', InventoryController::class);
Route::post('inventory/dispatch/vendor', [InventoryController::class, 'dispatchInventory']);
Route::get('get-inventory/vendor/{vendor_id}', [InventoryControllers::class, 'viewVendorInventory']);

Route::post('/pole-details', [TaskController::class, 'getPoleDetails']);
Route::get('/installed-poles/site-engineer/{engineer_id}', [TaskController::class, 'getInstalledPolesForSiteEngineer']);
Route::get('/installed-poles/vendor/{vendor_id}', [TaskController::class, 'getInstalledPolesForVendor']);
Route::get('/installed-poles/project-manager/{manager_id}', [TaskController::class, 'getInstalledPolesForProjectManager']);
Route::get('/export-poles/vendor/{id}', [TaskController::class, 'exportPoles'])->name('poles.export');

Route::post('fetch-states', [DropdownController::class, 'fetchState']);
Route::post('fetch-cities', [DropdownController::class, 'fetchCity']);
