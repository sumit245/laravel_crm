<?php

use App\Http\Controllers\API\StreetlightController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['register' => false]);

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard'); // Redirect authenticated users
    }
    return redirect()->route('login'); // Redirect guests to login
});

Route::middleware(['auth'])->group(function () {
    Route::resource('staff', StaffController::class);
    Route::get('staff/{id}/change-password', [StaffController::class, 'changePassword'])->name('staff.change-password');
    Route::post('staff/{id}/change-password', [StaffController::class, 'updatePassword'])->name('staff.update-password');
    Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::resource('uservendors', VendorController::class);
    Route::post('/projects/{id}/assign-users', [ProjectsController::class, 'assignUsers'])->name('projects.assignStaff');
    Route::resource('projects', ProjectsController::class);
    Route::get('/sites/search', [SiteController::class, 'search'])->name('sites.search');
    Route::resource('sites', SiteController::class);
    Route::post('/sites/import/{project_id}', [SiteController::class, 'import'])->name('sites.import');
    Route::resource('inventory', InventoryController::class)->except(['show', 'store']);
    Route::post('/inventory/import', [InventoryController::class, 'import'])->name('inventory.import');
    Route::get('/inventory/dispatch', [InventoryController::class, 'dispatch'])->name('inventory.dispatch');
    Route::get('/inventory/view', [InventoryController::class, 'viewInventory'])->name('inventory.view');
    Route::post('/projects/{projectId}/stores', [StoreController::class, 'store'])->name('store.create');
    Route::delete('/store/{store}', [StoreController::class, 'destroy'])->name('store.destroy');
    Route::get('/store/{store}/inventory', [StoreController::class, 'inventory'])->name('store.inventory');

    // routes/web.php
    Route::resource('tasks', TasksController::class)->except(['show']);
    Route::get('/tasks/{id}/{any?}', [TasksController::class, 'show'])->where('any', '.*')->name('tasks.show');
    Route::get('/streetlight/search', [StreetlightController::class, 'search'])->name('streetlights.search');
    Route::get('/blocks-by-district/{district}', [StreetlightController::class, 'getBlocksByDistrict']);
    Route::get('/panchayats-by-block/{block}', [StreetlightController::class, 'getPanchayatsByBlock']);
});
