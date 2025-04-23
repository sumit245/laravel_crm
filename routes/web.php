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
use App\Http\Controllers\JICRController;
use App\Models\InventroyStreetLightModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['register' => false]);

Route::middleware(['auth'])->group(function () {
    // Home router
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/jicr', [JICRController::class, 'index'])->name('jicr.index');
    Route::get('/jicr/blocks/{district}', [JICRController::class, 'getBlocks'])->name('jicr.blocks');
    Route::get('/jicr/panchayats/{block}', [JICRController::class, 'getPanchayats'])->name('jicr.panchayats');
    Route::get('/jicr/generate', [JICRController::class, 'generatePDF'])->name('jicr.generate');
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

    // Billing management
    Route::get('/billing/tada', function () {
        return view('billing.tada');
    })->name('billing.tada');
    Route::get('/billing/convenience', function () {
        return view('billing.convenience');
    })->name('billing.convenience');
    //Convenience Details
    Route::get('/convenience-details', function () {
        return view('billing.convenienceDetails');
    })->name('convenience.details');
    // View Bills Details
    Route::get('/view-bills', function () {
    return view('billing.viewBills');
    })->name('view.bills');


    // Inventory router
    Route::delete('/store/{store}', [StoreController::class, 'destroy'])->name('store.destroy');
    Route::get('/store/{store}/inventory', [StoreController::class, 'inventory'])->name('store.inventory');
    Route::resource('inventory', InventoryController::class)->except(['show', 'store']);
    Route::post('/inventory/import', [InventoryController::class, 'import'])->name('inventory.import');
    Route::post('/inventory/import-streetlight', [InventoryController::class, 'importStreetlight'])->name('inventory.import-streetlight');
    Route::post('/inventory/checkQR', [InventoryController::class, 'checkQR'])->name('inventory.checkQR');
    Route::post('/inventory/dispatchweb', [InventoryController::class, 'dispatchInventory'])->name('inventory.dispatchweb');
    Route::get('/inventory/view', [InventoryController::class, 'viewInventory'])->name('inventory.view');
    // adding inventory data
    Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');
    // Inventory Edit

    Route::get('/inventory/edit/{id}', [InventoryController::class, 'editInventory'])->name('inventory.editInventory');
    Route::put('/inventory/update/{id}', [InventoryController::class, 'updateInventory'])->name('inventory.updateInventory');

    // Dispatch Inventory
    Route::get('/inventory/dispatch', [InventoryController::class, 'showDispatchInventory'])->name('inventory.showDispatchInventory');


    // Task router
    Route::resource('tasks', TasksController::class)->except(['show']);
    Route::get('/tasks/{id}/{any?}', [TasksController::class, 'show'])->where('any', '.*')->name('tasks.show');
    
    // Projects Controller
    // Deleting target
    Route::delete('/tasks/delete/{id}', [ProjectsController::class, 'destroyTarget'])->name('tasks.destroy');



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
