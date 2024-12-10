<?php

use App\Http\Controllers\API\InventoryController;
use App\Http\Controllers\API\LoginController;
use App\Http\Controllers\API\ProjectController;
use App\Http\Controllers\API\SiteController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\API\VendorController;
use App\Http\Controllers\API\DropdownController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
 */

Route::post('/login', [LoginController::class, 'login']);

Route::prefix('vendor')->group(function () {
    Route::get('/', [VendorController::class, 'index']); //View all vendors
    Route::post('/', [VendorController::class, 'create']); // Create vendor
    Route::get('{id}', [VendorController::class, 'show']); // View a specific vendor
    Route::get('{id}/edit', [VendorController::class, 'edit']); // Edit vendor (optional)
    Route::put('{id}', [VendorController::class, 'update']); // Update vendor
    Route::delete('{id}', [VendorController::class, 'destroy']); // Delete vendor
    // Allot Site
    // Allot Task
    // Allot Inventory
    // Update Task
});

Route::prefix('projects')->group(function () {
    Route::get('/', [ProjectController::class, 'index']); //View all vendors
    Route::post('/', [ProjectController::class, 'create']); // Create vendor
    Route::get('{id}', [ProjectController::class, 'show']); // View a specific vendor
    Route::get('{id}/edit', [ProjectController::class, 'edit']); // Edit vendor (optional)
    Route::put('{id}', [ProjectController::class, 'update']); // Update vendor
    Route::delete('{id}', [ProjectController::class, 'destroy']); // Delete vendor
    // Allot Site
    // Allot Task
    // Allot Inventory
    // Update Task
});

// Route::middleware('api')->group(function () {
//     Route::apiResource('projects', ProjectController::class);
// });
// Route::apiResource('projects', ProjectController::class);
Route::apiResource('sites', SiteController::class);
Route::apiResource('tasks', TaskController::class);
Route::apiResource('inventories', InventoryController::class);


Route::post('fetch-states', [DropdownController::class, 'fetchState']);
Route::post('fetch-cities', [DropdownController::class, 'fetchCity']);
