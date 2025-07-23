<?php

use App\Http\Controllers\API\PreviewController;
use App\Http\Controllers\API\StreetlightController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\ConvenienceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\MeetController;
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

Auth::routes(['register' => false]);

Route::middleware(['auth'])->group(function () {
    // Home router

    Route::get('/vendor-data/{id}', [StaffController::class, 'vendorData'])->name('vendor.data');
    Route::get('/engineer-data/{id}', [StaffController::class, 'engineerData'])->name('engineer.data');

    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/jicr', [JICRController::class, 'index'])->name('jicr.index');
    Route::get('/jicr/blocks/{district}', [JICRController::class, 'getBlocks'])->name('jicr.blocks');
    Route::get('/jicr/panchayats/{block}', [JICRController::class, 'getPanchayats'])->name('jicr.panchayats');
    Route::get('/jicr/ward/{panchayat}', [JICRController::class, 'getWards'])->name('jicr.wards');
    Route::get('/jicr/generate', [JICRController::class, 'generatePDF'])->name('jicr.generate');
    Route::get('/export-excel', [HomeController::class, 'exportToExcel'])->name('export.excel');
    Route::get('/devices-import', [DeviceController::class, 'index'])->name('device.index');
    Route::post('/import-devices', [DeviceController::class, 'import'])->name('import.device');
    Route::post('/import-staff', [StaffController::class, 'import'])->name('import.staff');
    // Staff router
    Route::resource('staff', StaffController::class);
    Route::prefix('staff')->group(function () {
        Route::get('update-profile/{id}', [StaffController::class, 'updateProfile'])->name('staff.profile');
        Route::post('update-profile-picture', [StaffController::class, 'updateProfilePicture'])->name('staff.updateProfilePicture');
    });

    Route::get('{id}/change-password', [StaffController::class, 'changePassword'])->name('staff.change-password');
    Route::post('{id}/change-password', [StaffController::class, 'updatePassword'])->name('staff.update-password');

    //Meeting Review Extension
    // List all meetings
    Route::get('/meets', [MeetController::class, 'index'])->name('meets.index');
    // Show create form
    Route::get('/meets/create', [MeetController::class, 'create'])->name('meets.create');
    // Store new meeting
    Route::post('/meets', [MeetController::class, 'store'])->name('meets.store');
    // Show a single meeting (optional, if needed)
    Route::get('/meets/{meet}', [MeetController::class, 'show'])->name('meets.show');
    // Show edit form (optional, if you need to edit meetings)
    Route::get('/meets/{meet}/edit', [MeetController::class, 'edit'])->name('meets.edit');
    // Update a meeting (optional)
    Route::put('/meets/{meet}', [MeetController::class, 'update'])->name('meets.update');
    // Delete a meeting (optional)
    Route::delete('/meets/{meet}', [MeetController::class, 'destroy'])->name('meets.destroy');


    // vendor Router
    Route::resource('uservendors', VendorController::class);
    // Route::post('vendors-updatepassword/{id}', [VendorController::class, 'updatePassword'])->name('vendor.update-password');
    // Route::get('/vendors-change-password/{id}', [VendorController::class, 'changePassword'])->name('vendor.change-password');

    // projects Router
    Route::post('/projects/{id}/assign-users', [ProjectsController::class, 'assignUsers'])->name('projects.assignStaff');
    Route::resource('projects', ProjectsController::class);
    Route::post('/projects/{projectId}/stores', [StoreController::class, 'store'])->name('store.create');

    // site Router
    Route::get('/sites/search', [SiteController::class, 'search'])->name('sites.search');
    Route::resource('sites', SiteController::class);
    Route::post('/sites/import/{project_id}', [SiteController::class, 'import'])->name('sites.import');
Route::post('/sites/ward-poles', [SiteController::class, 'getWardPoles'])->name('sites.ward.poles');


    // Conveyance route fixed
    Route::get('/billing/convenience', [ConvenienceController::class, 'convenience'])->name('billing.convenience');
    // Tada route fixed
    Route::get('/billing/tada', [ConvenienceController::class, 'tadaView'])->name('billing.tada');
    Route::get('billing/tada-details/{id}', [ConvenienceController::class, 'viewtadaDetails'])->name('billing.tadaDetails');
    Route::get('/billing/conveyance', [ConvenienceController::class, 'convenience'])->name('billing.convenience');
    // Route::get('/billing/tada', [ConvenienceController::class, 'tadaView'])->name('billing.tada');
    // Settings Route
    Route::get('/settings', [ConvenienceController::class, 'settings'])->name('billing.settings');
    // status update
    // Route::post('/tada/update-status/{id}', [ConvenienceController::class, 'updateTadaStatus'])->name('tada.updateStatus');
    Route::post('/tada/bulk-update-status', [ConvenienceController::class, 'bulkUpdateStatus']);
    Route::get('/settings', [ConvenienceController::class, 'settings'])->name('billing.settings');
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
    Route::post('/conveyance/bulk-action', [ConvenienceController::class, 'bulkAction'])->name('conveyance.bulkAction');


    // Conveyance details
    Route::get('/convenience-details/{id}', [ConvenienceController::class, 'showdetailsconveyance'])->name('convenience.details');


    // Conveyance details
    Route::get('/convenience-details/{id}', [ConvenienceController::class, 'showdetailsconveyance'])->name('convenience.details');
    Route::delete('/settings/delete/{id}', [ConvenienceController::class, 'deleteVehicle'])->name('billing.deletevehicle');
    // Billing Edit User
    Route::get('/settings/edit-user/{id}', [ConvenienceController::class, 'editUser'])->name('billing.edituser');
    // Billing Update User
    Route::post('/settings/update-user', [ConvenienceController::class, 'updateUser'])->name('billing.updateuser');

    // Add Categories
    Route::get('/settings/add-category', [ConvenienceController::class, 'viewCategory'])->name('billing.addcategory');
    Route::post('/settings/add-category', [ConvenienceController::class, 'addCategory'])->name('billing.addcategory');
    Route::post('/settings/add-category', [ConvenienceController::class, 'addCategory'])->name('billing.addcategory');
    // Edit Categories
    Route::get('/settings/edit-category/{id}', [ConvenienceController::class, 'editCategory'])->name('billing.editcategory');
    // Update Categories
    Route::post('/settings/update-category', [ConvenienceController::class, 'updateCategory'])->name('billing.updatecategory');
    // Delete Categories
    Route::delete('/settings/delete-category/{id}', [ConvenienceController::class, 'deleteCategory'])->name('billing.deletecategory');
    // City category and Allow expense
    Route::get('/settings/edit-city-category', function () {
        return view('billing.editCityCategory');
    })->name('billing.editcitycategory');
    Route::get('/settings/allowed-expense/{id}', [ConvenienceController::class, 'editAllowedExpense'])->name('billing.allowedexpense');
    Route::post('/settings/update-allowed-expense/{id}', [ConvenienceController::class, 'updateAllowedExpense'])->name('billing.updateallowedexpense');


    //Convenience Details

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
    Route::post('/inventory/replace', [InventoryController::class, 'replaceItem'])->name('inventory.replace');

    // adding inventory data
    Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');
    // Inventory Edit
    Route::get('/inventory/edit/{id}', [InventoryController::class, 'editInventory'])->name('inventory.editInventory');
    Route::put('/inventory/update/{id}', [InventoryController::class, 'updateInventory'])->name('inventory.updateInventory');
    Route::post('/inventory/bulk-delete', [InventoryController::class, 'bulkDelete'])->name('inventory.bulkDelete');


    // Dispatch Inventory
    Route::get('/inventory/dispatch', [InventoryController::class, 'showDispatchInventory'])->name('inventory.showDispatchInventory');
    // Return Dispatch Inventory
    Route::post('/inventory/return', [InventoryController::class, 'returnInventory'])->name('inventory.return');

    // Task router
    Route::resource('tasks', TasksController::class)->except(['show']);
    Route::get('/tasks/rooftop/{id}', [TasksController::class, 'editrooftop'])->name('tasks.editrooftop');
    Route::post('/tasks/rooftop/update/{id}', [TasksController::class, 'updateRooftop'])->name('tasks.updaterooftop');
    // Greedy path
    Route::get('/tasks/{id}/{any?}', [TasksController::class, 'show'])->where('any', '.*')->name('tasks.show');

    // Projects Controller
    // Deleting target
    Route::delete('/tasks/delete/{id}', [ProjectsController::class, 'destroyTarget'])->name('tasks.destroystreetlight');



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

    // Route for hiring software HRM
    Route::get('admin-preview/{id}', [PreviewController::class, 'adminPreview'])->name('admin-preview');
    Route::post('/candidates/bulk-update', [PreviewController::class, 'bulkUpdate'])->name('candidates.bulkUpdate');
    Route::delete('/candidates/{id}', [CandidateController::class, 'destroy'])->name('candidates.destroy');
});

Route::get('apply-now/{id}', [PreviewController::class, 'applyNow'])->name('apply-now');
Route::post('/apply/store', [PreviewController::class, 'storeAndPreview'])->name('hrm.store');
Route::get('/apply/preview', [PreviewController::class, 'preview'])->name('hrm.preview');
Route::post('/apply/submit', [PreviewController::class, 'submitFinal'])->name('hrm.submit');
Route::get('/apply/success', function () {
    return view('hrm.success');
})->name('hrm.success');

// Candidate Management Routes
Route::get('/candidates', [CandidateController::class, 'index'])->name('candidates.index');
Route::post('/candidates/import', [CandidateController::class, 'importCandidates'])->name('candidates.import');
Route::post('/candidates/send-emails', [CandidateController::class, 'sendEmails'])->name('candidates.send-emails');
Route::get('/candidates/{id}/upload', [CandidateController::class, 'showUploadForm'])->name('candidates.upload-form');
Route::post('/candidates/{id}/upload', [CandidateController::class, 'uploadDocuments'])->name('candidates.upload');


// apply now


Route::get('privacy-policy', function () {
    return view('privacy');
});

Route::get('terms-and-conditions', function () {
    return view('terms');
});
Route::get('/backup', function () {
    return view('data_backup.backup');
})->name('backup.index');