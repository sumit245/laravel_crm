<?php

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

Route::middleware(['auth', 'role:0'])->group(function () {
 Route::resource('staff', StaffController::class);
 Route::get('staff/{id}/change-password', [StaffController::class, 'changePassword'])->name('staff.change-password');
 Route::post('staff/{id}/change-password', [StaffController::class, 'updatePassword'])->name('staff.update-password');

 // Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue.index');
 // Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
});

Route::middleware(['auth'])->group(function () {
 Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
 Route::resource('uservendors', VendorController::class);
 Route::resource('projects', ProjectsController::class);
 Route::resource('sites', SiteController::class);
 Route::resource('inventory', InventoryController::class);
//  Route::resource('store', StoreController::class);
 Route::post('/projects/{projectId}/stores', [StoreController::class, 'store'])->name('store.store');

 Route::post('/inventory/import', [InventoryController::class, 'import'])->name('inventory.import');
 Route::get('/inventory/dispatch', [InventoryController::class, 'dispatch'])->name('inventory.dispatch');
 Route::delete('/store/{store}', [StoreController::class, 'destroy'])->name('store.destroy');

 Route::resource('tasks', TasksController::class);
});