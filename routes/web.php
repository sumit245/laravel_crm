<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\StaffController;
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
 Route::post('/inventory/import', [InventoryController::class, 'import'])->name('inventory.import');
 Route::resource('tasks', TasksController::class);
});

// Below routes should open only when authenticated. Also if the role of authenticated user is 0 I have to show staff management, revenue, role and some other modules. while if role is 1 these modules should not show
// Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard')->middleware('auth');
// Route::resource('staff', StaffController::class);
// Route::resource('uservendors', VendorController::class);
// Route::resource('projects', ProjectsController::class);
// Route::resource('sites', SiteController::class);
// Route::resource('inventory', InventoryController::class);
// Route::resource('tasks', TasksController::class);
