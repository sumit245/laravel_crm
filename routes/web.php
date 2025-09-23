<?php

use App\Http\Controllers\API\PreviewController;
use App\Http\Controllers\API\StreetlightController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\ConvenienceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\API\HRMController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\JICRController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{API\PreviewController, API\StreetlightController, API\TaskController, CandidateController, ConvenienceController, DeviceController, HomeController, InventoryController, JICRController, MeetController, PoleController, ProjectsController, RMSController, SiteController, StaffController, StoreController, TasksController, VendorController, WhiteboardController};

Auth::routes(['register' => false]);

// Public Routes
Route::get('apply-now/{id}', [PreviewController::class, 'applyNow'])->name('apply-now');
Route::post('/apply/store', [PreviewController::class, 'storeAndPreview'])->name('hrm.store');
Route::get('/apply/preview', [PreviewController::class, 'preview'])->name('hrm.preview');
Route::post('/apply/submit', [PreviewController::class, 'submitFinal'])->name('hrm.submit');
Route::get('/apply/success', fn() => view('hrm.success'))->name('hrm.success');

Route::get('/candidates', [CandidateController::class, 'index'])->name('candidates.index');
Route::post('/candidates/import', [CandidateController::class, 'importCandidates'])->name('candidates.import');
Route::post('/candidates/send-emails', [CandidateController::class, 'sendEmails'])->name('candidates.send-emails');
Route::get('/candidates/{id}/upload', [CandidateController::class, 'showUploadForm'])->name('candidates.upload-form');
Route::post('/candidates/{id}/upload', [CandidateController::class, 'uploadDocuments'])->name('candidates.upload');

Route::get('privacy-policy', fn() => view('privacy'));
Route::get('terms-and-conditions', fn() => view('terms'));
Route::get('/backup', fn() => view('data_backup.backup'))->name('backup.index');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    // Home
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/export-excel', [HomeController::class, 'exportToExcel'])->name('export.excel');

    // JICR
    Route::prefix('jicr')
        ->name('jicr.')
        ->group(function () {
            Route::get('/', [JICRController::class, 'index'])->name('index');
            Route::get('/blocks/{district}', [JICRController::class, 'getBlocks'])->name('blocks');
            Route::get('/panchayats/{block}', [JICRController::class, 'getPanchayats'])->name('panchayats');
            Route::get('/ward/{panchayat}', [JICRController::class, 'getWards'])->name('wards');
            Route::get('/generate', [JICRController::class, 'generatePDF'])->name('generate');
        });

    // Staff
    Route::get('/vendor-data/{id}', [StaffController::class, 'vendorData'])->name('vendor.data');
    Route::get('/engineer-data/{id}', [StaffController::class, 'engineerData'])->name('engineer.data');
    Route::resource('staff', StaffController::class);
Route::prefix('staff')
        ->name('staff.')
        ->group(function () {
            Route::get('update-profile/{id}', [StaffController::class, 'updateProfile'])->name('profile');
            Route::post('update-profile-picture', [StaffController::class, 'updateProfilePicture'])->name('updateProfilePicture');
            Route::get('{id}/change-password', [StaffController::class, 'changePassword'])->name('change-password');
            Route::post('{id}/change-password', [StaffController::class, 'updatePassword'])->name('update-password');
        });
    Route::post('/import-staff', [StaffController::class, 'import'])->name('import.staff');

    // Meets
    Route::resource('meets', MeetController::class);
    Route::get('/meets/{meet}/notes', [MeetController::class, 'notes'])->name('meets.notes');
    Route::put('/meets/{meet}/notes', [MeetController::class, 'updateNotes'])->name('meets.updateNotes');
    Route::get('/meets/{meet}/export/pdf', [MeetController::class, 'exportPdf'])->name('meets.exportPdf');
    Route::get('{id}/change-password', [StaffController::class, 'changePassword'])->name('staff.change-password');
    Route::post('{id}/change-password', [StaffController::class, 'updatePassword'])->name('staff.update-password');
    // optional
    Route::get('/meets/{meet}/export/excel', [MeetController::class, 'exportExcel'])->name('meets.exportExcel');
    // routes/web.php
    Route::post('/meets/{meet}/update-status', [MeetController::class, 'updateStatus'])->name('meets.updateStatus');

    Route::get('/review-meetings/{reviewMeeting}/whiteboard', [WhiteboardController::class, 'show'])->name('whiteboard.show');
    Route::post('/review-meetings/{reviewMeeting}/whiteboard', [WhiteboardController::class, 'store'])->name('whiteboard.store');
    // ...

    // Vendors
    Route::resource('uservendors', VendorController::class);

    // Projects
    // Route::post('vendors-updatepassword/{id}', [VendorController::class, 'updatePassword'])->name('vendor.update-password');
    // Route::get('/vendors-change-password/{id}', [VendorController::class, 'changePassword'])->name('vendor.change-password');
    
    // projects Router
    Route::post('/projects/{id}/assign-users', [ProjectsController::class, 'assignUsers'])->name('projects.assignStaff');
    Route::resource('projects', ProjectsController::class);
    Route::post('/projects/{id}/assign-users', [ProjectsController::class, 'assignUsers'])->name('projects.assignStaff');
    Route::post('/projects/{projectId}/stores', [StoreController::class, 'store'])->name('store.create');

    // Sites
    Route::prefix('sites')->group(function () {
        Route::get('search', [SiteController::class, 'search'])->name('sites.search');
        Route::post('import/{project_id}', [SiteController::class, 'import'])->name('sites.import');
        Route::post('ward-poles', [SiteController::class, 'getWardPoles'])->name('sites.ward.poles');
    });
    Route::resource('sites', SiteController::class);

    // Convenience / Billing
    Route::prefix('billing')
        ->name('billing.')
        ->group(function () {
            Route::get('convenience', [ConvenienceController::class, 'convenience'])->name('convenience');
            Route::get('tada', [ConvenienceController::class, 'tadaView'])->name('tada');
            Route::get('tada-details/{id}', [ConvenienceController::class, 'viewtadaDetails'])->name('tadaDetails');
            Route::get('settings', [ConvenienceController::class, 'settings'])->name('settings');
            Route::post('settings/add', [ConvenienceController::class, 'addVehicle'])->name('addvehicle');
            Route::get('settings/edit/{id}', [ConvenienceController::class, 'editVehicle'])->name('editvehicle');
            Route::post('settings/update', [ConvenienceController::class, 'updateVehicle'])->name('updatevehicle');
            Route::delete('settings/delete/{id}', [ConvenienceController::class, 'deleteVehicle'])->name('deletevehicle');
            // User management
            Route::get('settings/edit-user/{id}', [ConvenienceController::class, 'editUser'])->name('edituser');
            Route::post('settings/update-user', [ConvenienceController::class, 'updateUser'])->name('updateuser');

            // Categories
            Route::get('settings/add-category', [ConvenienceController::class, 'viewCategory'])->name('addcategory');
            Route::post('settings/add-category', [ConvenienceController::class, 'addCategory'])->name('addcategory');
            Route::get('settings/edit-category/{id}', [ConvenienceController::class, 'editCategory'])->name('editcategory');
            Route::post('settings/update-category', [ConvenienceController::class, 'updateCategory'])->name('updatecategory');
            Route::delete('settings/delete-category/{id}', [ConvenienceController::class, 'deleteCategory'])->name('deletecategory');
    // Conveyance route fixed
    Route::get('/billing/convenience', [ConvenienceController::class, 'convenience'])->name('billing.convenience');
    // Tada route fixed
    Route::get('/billing/tada', [ConvenienceController::class, 'tadaView'])->name('billing.tada');
    Route::get('billing/tada-details/{id}', [ConvenienceController::class, 'viewtadaDetails'])->name('billing.tadaDetails');
    // Settings Route
    Route::get('/settings', [ConvenienceController::class, 'settings'])->name('billing.settings');
    // status update
    Route::post('/tada/update-status/{id}', [ConvenienceController::class, 'updateTadaStatus'])->name('tada.updateStatus');
    //Add Vehicle
    Route::post('/settings/add', [ConvenienceController::class, 'addVehicle'])->name('billing.addvehicle');
    // Edit Vehicle
    Route::get('/settings/edit/{id}', [ConvenienceController::class, 'editVehicle'])->name('billing.editvehicle');
    // Update Vehicle
    Route::post('/settings/update', [ConvenienceController::class, 'updateVehicle'])->name('billing.updatevehicle');
    // Delete Vehicle
    Route::delete('/settings/delete/{id}', [ConvenienceController::class, 'deleteVehicle'])->name('billing.deletevehicle');
    // Accept and Reject Conveyance
    Route::post('/conveyance/accept/{id}', [ConvenienceController::class, 'accept'])->name('conveyance.accept');
    Route::post('/conveyance/reject/{id}', [ConvenienceController::class, 'reject'])->name('conveyance.reject');
    
    // Conveyance details
    Route::get('/convenience-details/{id}', [ConvenienceController::class, 'showdetailsconveyance'])->name('convenience.details');
            Route::get('edit-city-category', fn() => view('billing.editCityCategory'))->name('editcitycategory');
            Route::get('allowed-expense/{id}', [ConvenienceController::class, 'editAllowedExpense'])->name('allowedexpense');
            Route::post('update-allowed-expense/{id}', [ConvenienceController::class, 'updateAllowedExpense'])->name('updateallowedexpense');
        });


    Route::post('/tada/bulk-update-status', [ConvenienceController::class, 'bulkUpdateStatus']);
    Route::post('/conveyance/accept/{id}', [ConvenienceController::class, 'accept'])->name('conveyance.accept');
    Route::post('/conveyance/reject/{id}', [ConvenienceController::class, 'reject'])->name('conveyance.reject');
    Route::post('/conveyance/bulk-action', [ConvenienceController::class, 'bulkAction'])->name('conveyance.bulkAction');
    Route::get('/convenience-details/{id}', [ConvenienceController::class, 'showdetailsconveyance'])->name('convenience.details');
    Route::get('/view-bills', fn() => view('billing.viewBills'))->name('view.bills');

    // Inventory
    // Billing Edit User
    Route::get('/settings/edit-user/{id}', [ConvenienceController::class, 'editUser'])->name('billing.edituser');
    // Billing Update User
    Route::post('/settings/update-user', [ConvenienceController::class, 'updateUser'])->name('billing.updateuser');

    // Add Categories
    Route::get('/settings/add-category', [ConvenienceController::class, 'viewCategory'])->name('billing.addcategory');
    Route::post('/settings/add-category', [ConvenienceController::class, 'addCategory'])->name('billing.addcategory');
    // Edit Categories
    Route::get('/settings/edit-category/{id}', [ConvenienceController::class, 'editCategory'])->name('billing.editcategory');
    // Update Categories
    Route::post('/settings/update-category', [ConvenienceController::class, 'updateCategory'])->name('billing.updatecategory');
    // Delete Categories
    Route::delete('/settings/delete-category/{id}', [ConvenienceController::class, 'deleteCategory'])->name('billing.deletecategory');

    //Convenience Details
    
    // View Bills Details
    Route::get('/view-bills', function () {
        return view('billing.viewBills');
    })->name('view.bills');


    // Inventory router
    Route::delete('/store/{store}', [StoreController::class, 'destroy'])->name('store.destroy');
    Route::get('/store/{store}/inventory', [StoreController::class, 'inventory'])->name('store.inventory');
//>>>>>>> 5fd7e494199d3ae2af4600437e3169f144087b5c
    Route::resource('inventory', InventoryController::class)->except(['show', 'store']);
    Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');
    Route::prefix('inventory')
        ->name('inventory.')
        ->group(function () {
            Route::post('import', [InventoryController::class, 'import'])->name('import');
            Route::post('import-streetlight', [InventoryController::class, 'importStreetlight'])->name('import-streetlight');
            Route::post('checkQR', [InventoryController::class, 'checkQR'])->name('checkQR');
            Route::post('dispatchweb', [InventoryController::class, 'dispatchInventory'])->name('dispatchweb');
            Route::get('view', [InventoryController::class, 'viewInventory'])->name('view');
            Route::post('replace', [InventoryController::class, 'replaceItem'])->name('replace');
            Route::get('edit/{id}', [InventoryController::class, 'editInventory'])->name('editInventory');
            Route::put('update/{id}', [InventoryController::class, 'updateInventory'])->name('updateInventory');
            Route::post('bulk-delete', [InventoryController::class, 'bulkDelete'])->name('bulkDelete');
            Route::get('dispatch', [InventoryController::class, 'showDispatchInventory'])->name('showDispatchInventory');
            Route::post('return', [InventoryController::class, 'returnInventory'])->name('return');
        });
    Route::get('/store/{store}/inventory', [StoreController::class, 'inventory'])->name('store.inventory');
    Route::delete('/store/{store}', [StoreController::class, 'destroy'])->name('store.destroy');

    // Tasks
    Route::resource('tasks', TasksController::class)->except(['show']);
    Route::get('/tasks/rooftop/{id}', [TasksController::class, 'editrooftop'])->name('tasks.editrooftop');
    Route::post('/tasks/rooftop/update/{id}', [TasksController::class, 'updateRooftop'])->name('tasks.updaterooftop');
//<<<<<<< HEAD
    Route::get('/tasks/{id}/{any?}', [TasksController::class, 'show'])
        ->where('any', '.*')
        ->name('tasks.show');
//=======
    // Greedy path
    Route::get('/tasks/{id}/{any?}', [TasksController::class, 'show'])->where('any', '.*')->name('tasks.show');
    
    // Projects Controller
    // Deleting target
//>>>>>>> 5fd7e494199d3ae2af4600437e3169f144087b5c
    Route::delete('/tasks/delete/{id}', [ProjectsController::class, 'destroyTarget'])->name('tasks.destroystreetlight');

    // Surveyed/Installed Poles
    Route::get('/surveyed-poles', [TaskController::class, 'getSurveyedPoles'])->name('surveyed.poles');
    Route::get('/installed-poles', [TaskController::class, 'getInstalledPoles'])->name('installed.poles');
    Route::get('/export-poles', [TaskController::class, 'exportPoles'])->name('poles.export');
    Route::get('/poles/show/{id}', [TaskController::class, 'viewPoleDetails'])->name('poles.show');
    Route::get('/poles/{id}/edit', [PoleController::class, 'edit'])->name('poles.edit');
    Route::put('/poles/{id}', [PoleController::class, 'update'])->name('poles.update');
    Route::delete('/poles/{id}', [PoleController::class, 'destroy'])->name('poles.destroy');

    // Streetlight
    Route::get('/streetlight/search', [StreetlightController::class, 'search'])->name('streetlights.search');
    Route::get('/blocks-by-district/{district}', [StreetlightController::class, 'getBlocksByDistrict']);
    Route::get('/panchayats-by-block/{block}', [StreetlightController::class, 'getPanchayatsByBlock']);

    // Hiring
    Route::get('/hirings', [CandidateController::class, 'index'])->name('hiring.index');
    Route::post('/import-candidates', [CandidateController::class, 'importCandidates'])->name('import.candidates');
    Route::post('/send-emails', [CandidateController::class, 'sendEmails'])->name('send.emails');
    Route::get('/upload-documents/{id}', [CandidateController::class, 'showUploadForm']);
    Route::post('/upload-documents/{id}', [CandidateController::class, 'uploadDocuments'])->name('upload.documents');
    Route::get('admin-preview/{id}', [PreviewController::class, 'adminPreview'])->name('admin-preview');
    Route::post('/candidates/bulk-update', [PreviewController::class, 'bulkUpdate'])->name('candidates.bulkUpdate');
    Route::delete('/candidates/{id}', [CandidateController::class, 'destroy'])->name('candidates.destroy');

//<<<<<<< HEAD
    // Device Import
    Route::get('/devices-import', [DeviceController::class, 'index'])->name('device.index');
    Route::post('/import-devices', [DeviceController::class, 'import'])->name('import.device');

    // RMS Push
    Route::get('/rms-export', [RMSController::class, 'index'])->name('rms.index');
    Route::post('/rms-push', [RMSController::class, 'sendPanchayatToRMS'])->name('rms.push');
});
//=======
    // Route for hiring software HRM
   

});
 Route::get('/apply', [PreviewController::class, 'applyNow'])->name('hrm.apply');
Route::post('/apply/store', [PreviewController::class, 'storeAndPreview'])->name('hrm.store');
Route::get('/apply/preview', [PreviewController::class, 'preview'])->name('hrm.preview');
Route::post('/apply/submit', [PreviewController::class, 'submitFinal'])->name('hrm.submit');
Route::get('/apply/success', function() {
    return view('hrm.success');
})->name('hrm.success');

// Candidate Management Routes
Route::get('/candidates', [CandidateController::class, 'index'])->name('candidates.index');
Route::post('/candidates/import', [CandidateController::class, 'importCandidates'])->name('candidates.import');
Route::post('/candidates/send-emails', [CandidateController::class, 'sendEmails'])->name('candidates.send-emails');
Route::get('/candidates/{id}/upload', [CandidateController::class, 'showUploadForm'])->name('candidates.upload-form');
Route::post('/candidates/{id}/upload', [CandidateController::class, 'uploadDocuments'])->name('candidates.upload');


// apply now
Route::get('apply-now', function () {
    return view('hrm.applyNow');
})->name('apply-now');

Route::get('admin-preview', function () {
    return view('hrm.adminPreview');
})->name('admin-preview');
//>>>>>>> 5fd7e494199d3ae2af4600437e3169f144087b5c
