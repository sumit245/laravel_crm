<?php

use App\Http\Controllers\API\StreetlightController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\CandidateController;
use App\Models\InventroyStreetLightModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['register' => false]);

Route::middleware(['auth'])->group(function () {
    // Home router
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/export-excel', [HomeController::class, 'exportToExcel'])->name('export.excel');
    // Staff router
    Route::resource('staff', StaffController::class);
    Route::prefix('staff')->group(function () {
        Route::get('update-profile/{id}', [StaffController::class, 'updateProfile'])->name('staff.profile');
        Route::post('update-profile-picture', [StaffController::class, 'updateProfilePicture'])->name('staff.updateProfilePicture');
        Route::get('{id}/change-password', [StaffController::class, 'changePassword'])->name('staff.change-password');
        Route::post('{id}/change-password', [StaffController::class, 'updatePassword'])->name('staff.update-password');
    });


    // vendor Router
    Route::resource('uservendors', VendorController::class);

    // projects Router
    Route::post('/projects/{id}/assign-users', [ProjectsController::class, 'assignUsers'])->name('projects.assignStaff');
    Route::resource('projects', ProjectsController::class);
    Route::post('/projects/{projectId}/stores', [StoreController::class, 'store'])->name('store.create');

    // site Router
    Route::get('/sites/search', [SiteController::class, 'search'])->name('sites.search');
    Route::resource('sites', SiteController::class);
    Route::post('/sites/import/{project_id}', [SiteController::class, 'import'])->name('sites.import');

    // Inventory router
    Route::delete('/store/{store}', [StoreController::class, 'destroy'])->name('store.destroy');
    Route::get('/store/{store}/inventory', [StoreController::class, 'inventory'])->name('store.inventory');
    Route::resource('inventory', InventoryController::class)->except(['show', 'store']);
    Route::post('/inventory/import', [InventoryController::class, 'import'])->name('inventory.import');
    Route::post('/inventory/import-streetlight', [InventoryController::class, 'importStreetlight'])->name('inventory.import-streetlight');
    Route::post('/inventory/checkQR', function (Request $request) {
        $exists = InventroyStreetLightModel::where('serial_number', $request->qr_code)->exists();
        return response()->json(['exists' => $exists]);
    })->name('inventory.checkQR');
    Route::get('/inventory/dispatch', [InventoryController::class, 'dispatch'])->name('inventory.dispatch');
    Route::get('/inventory/view', [InventoryController::class, 'viewInventory'])->name('inventory.view');
    Route::get('/inventory/edit/{id}', [InventoryController::class, 'edit'])->name('inventory.edit');


    // Task router
    Route::resource('tasks', TasksController::class)->except(['show']);
    Route::get('/tasks/{id}/{any?}', [TasksController::class, 'show'])->where('any', '.*')->name('tasks.show');

    // Route for Surveyed Poles
    Route::get('/surveyed-poles', [TaskController::class, 'getSurveyedPoles'])->name('surveyed.poles');
    Route::get('/export-poles', [TaskController::class, 'exportPoles'])->name('poles.export');
    Route::get('/poles/show/{id}', [TaskController::class, 'viewPoleDetails'])->name('poles.show');

    // Route for Installed Poles
    Route::get('/installed-poles', [TaskController::class, 'getInstalledPoles'])->name('installed.poles');
    Route::get('/streetlight/search', [StreetlightController::class, 'search'])->name('streetlights.search');
    Route::get('/blocks-by-district/{district}', [StreetlightController::class, 'getBlocksByDistrict']);
    Route::get('/panchayats-by-block/{block}', [StreetlightController::class, 'getPanchayatsByBlock']);

    // Route for hiring software
    Route::post('/import-candidates', [CandidateController::class, 'importCandidates'])->name('import.candidates');
    Route::post('/send-emails', [CandidateController::class, 'sendEmails'])->name('send.emails');
    Route::get('/upload-documents/{id}', [CandidateController::class, 'showUploadForm']);
    Route::post('/upload-documents/{id}', [CandidateController::class, 'uploadDocuments'])->name('upload.documents');
    Route::get('/hirings', [CandidateController::class, 'index'])->name('hiring.index');
});
