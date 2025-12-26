<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{API\PreviewController, API\StreetlightController, API\TaskController, BackupController, CandidateController, ConvenienceController, DeviceController, HomeController, InventoryController, JICRController, MeetController, PerformanceController, PerformanceDebugController, PoleController, ProjectsController, RMSController, SiteController, StaffController, StoreController, TasksController, VendorController, WhiteboardController};

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
Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
Route::post('/backup/create', [BackupController::class, 'create'])->name('backup.create');
Route::get('/backup/download/{filename}', [BackupController::class, 'download'])->name('backup.download');
Route::delete('/backup/delete/{filename}', [BackupController::class, 'delete'])->name('backup.delete');

// Authenticated Routes
Route::middleware(['auth', 'restrict.meetings'])->group(function () {
    // Home
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::post('dashboard/filter', [HomeController::class, 'filterData'])->name('dashboard.filter');
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
    Route::post('/staff/bulk-delete', [StaffController::class, 'bulkDelete'])->name('staff.bulkDelete');
    Route::prefix('staff')
        ->name('staff.')
        ->group(function () {
            Route::get('update-profile/{id}', [StaffController::class, 'updateProfile'])->name('profile');
            Route::post('update-profile-picture', [StaffController::class, 'updateProfilePicture'])->name('updateProfilePicture');
            Route::post('mobile/send-otp', [StaffController::class, 'sendMobileChangeOtp'])->name('mobile.send-otp');
            Route::post('mobile/verify-otp', [StaffController::class, 'verifyMobileChangeOtp'])->name('mobile.verify-otp');
            Route::get('{id}/change-password', [StaffController::class, 'changePassword'])->name('change-password');
            Route::post('{id}/change-password', [StaffController::class, 'updatePassword'])->name('update-password');
        });
    Route::post('/staff/{id}/upload-avatar', [StaffController::class, 'uploadAvatar'])->name('staff.uploadAvatar');
    Route::post('/import-staff', action: [StaffController::class, 'import'])->name('import.staff');

    // Performance
    Route::prefix('performance')
        ->name('performance.')
        ->group(function () {
            Route::get('/', [PerformanceController::class, 'index'])->name('index');
            Route::get('/user/{userId}', [PerformanceController::class, 'show'])->name('show');
            Route::get('/subordinates/{managerId}/{type}', [PerformanceController::class, 'subordinates'])->name('subordinates');
            Route::get('/leaderboard/{role}', [PerformanceController::class, 'leaderboard'])->name('leaderboard');
            Route::get('/trends/{userId}', [PerformanceController::class, 'trends'])->name('trends');
            Route::get('/debug', [PerformanceDebugController::class, 'debug'])->name('debug');
        });

    // Meets
    Route::get('/meets/dashboard', [MeetController::class, 'dashboard'])->name('meets.dashboard');
    Route::get('/meets/details/{id}', [MeetController::class, 'details'])->name('meets.details');
    Route::resource('meets', MeetController::class);
    Route::get('/meets/{meet}/notes', [MeetController::class, 'notes'])->name('meets.notes');
    Route::put('/meets/{meet}/notes', [MeetController::class, 'updateNotes'])->name('meets.updateNotes');
    Route::get('/meets/{meet}/export/pdf', [MeetController::class, 'exportPdf'])->name('meets.exportPdf');
    // ADD THIS NEW ROUTE for storing the new discussion point/task
    Route::post('/discussion-points/store', [MeetController::class, 'storeDiscussionPoint'])->name('discussion-points.store');
    Route::post('/discussion-points/updates/store', [MeetController::class, 'storeDiscussionPointUpdate'])->name('discussion-points.updates.store');
    Route::post('/discussion-points/{point}/update-status', [MeetController::class, 'updateDiscussionPointStatus'])->name('discussion-points.update-status');
    Route::delete('/discussion-points/{point}', [MeetController::class, 'deleteDiscussionPoint'])->name('discussion-points.delete');
    Route::post('/meets/{meet}/schedule-follow-up', [MeetController::class, 'scheduleFollowUp'])->name('meets.schedule-follow-up');
    Route::delete('/meets/{meet}/attendees/{user}', [MeetController::class, 'removeAttendee'])->name('meets.attendees.remove');
    Route::delete('/follow-ups/{followUp}', [MeetController::class, 'deleteFollowUp'])->name('follow-ups.delete');
    Route::delete('/meets/{meet}', [MeetController::class, 'destroy'])->name('meets.destroy');
// Dev-only pages for E2E testing (only in local environment)
if (app()->environment('local') || config('app.debug') || env('ALLOW_DEV_TEST')) {
    Route::get('/__dev/unified-inventory-test', function () {
        return view('dev.unified_inventory_test');
    })->name('__dev.unified_inventory_test');

    Route::get('/__dev/inventory-data', function (\Illuminate\Http\Request $request) {
        // Simulate server-side DataTables response. Expect parameters: start, length, draw
        $start = intval($request->query('start', 0));
        $length = intval($request->query('length', 10));
        $total = 100; // pretend there are 100 records on server

        $data = [];
        $page = intval($start / max(1, $length));
        for ($i = 0; $i < $length; $i++) {
            $index = $start + $i + 1;
            if ($index > $total) break;
            $data[] = [
                'IT' . $index,
                'Item ' . $index,
                'SERI' . $index,
                '<span class="badge bg-success">In Stock</span>',
                'Vendor ' . $index,
                date('d/m/Y'),
                date('d/m/Y'),
                ''
            ];
        }

        return response()->json([
            'draw' => intval($request->query('draw', 1)),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ]);
    })->name('__dev.inventory_data');
}

    // optional
    Route::get('/meets/{meet}/export/excel', [MeetController::class, 'exportExcel'])->name('meets.exportExcel');
    // routes/web.php
    Route::post('/meets/{meet}/update-status', [MeetController::class, 'updateStatus'])->name('meets.updateStatus');

    Route::get('/review-meetings/{reviewMeeting}/whiteboard', [WhiteboardController::class, 'show'])->name('whiteboard.show');
    Route::post('/review-meetings/{reviewMeeting}/whiteboard', [WhiteboardController::class, 'store'])->name('whiteboard.store');
    // ...

    // Vendors
    Route::resource('uservendors', VendorController::class);
    Route::post('/uservendors/bulk-delete', [VendorController::class, 'bulkDelete'])->name('uservendors.bulkDelete');
    Route::post('/uservendors/bulk-assign-projects', [VendorController::class, 'bulkAssignProjects'])->name('uservendors.bulkAssignProjects');
    Route::post('/uservendors/{id}/upload-avatar', [VendorController::class, 'uploadAvatar'])->name('uservendors.uploadAvatar');
    Route::post('/import-vendors', [VendorController::class, 'import'])->name('import.vendors');
    Route::get('/vendors/import-format', [VendorController::class, 'importFormat'])->name('vendors.importFormat');

    // Projects
    Route::resource('projects', ProjectsController::class);
    Route::post('/projects/bulk-delete', [ProjectsController::class, 'bulkDelete'])->name('projects.bulkDelete');
    Route::post('/projects/import', [ProjectsController::class, 'import'])->name('projects.import');
    Route::get('/projects/import/format', [ProjectsController::class, 'downloadFormat'])->name('projects.importFormat');
    Route::post('/projects/{id}/assign-users', [ProjectsController::class, 'assignUsers'])->name('projects.assignStaff');
    Route::post('/projects/{id}/remove-staff', [ProjectsController::class, 'removeStaff'])->name('projects.removeStaff');
    Route::post('/projects/{id}/assign-vendors', [ProjectsController::class, 'assignUsers'])->name('projects.assignVendors');
    Route::post('/projects/{id}/remove-vendors', [ProjectsController::class, 'removeStaff'])->name('projects.removeVendors');
    Route::post('/projects/{projectId}/stores', [StoreController::class, 'store'])->name('store.create');

    // Sites
    Route::prefix('sites')->group(function () {
        Route::get('search', [SiteController::class, 'search'])->name('sites.search');
        Route::post('import/{project_id}', [SiteController::class, 'import'])->name('sites.import');
        Route::post('ward-poles', [SiteController::class, 'getWardPoles'])->name('sites.ward.poles');
        Route::post('bulk-delete', [SiteController::class, 'bulkDelete'])->name('sites.bulkDelete');
        Route::post('{siteId}/poles/import', [SiteController::class, 'importPoles'])->name('sites.poles.import');
        Route::post('poles/bulk-delete', [SiteController::class, 'bulkDeletePoles'])->name('sites.poles.bulkDelete');
        Route::get('{siteId}/poles/export-format', [SiteController::class, 'downloadPoleImportFormat'])->name('sites.poles.exportFormat');
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

            Route::get('edit-city-category', [ConvenienceController::class, 'editCityCategory'])->name('editcitycategory');
            Route::get('allowed-expense/{id}', [ConvenienceController::class, 'editAllowedExpense'])->name('allowedexpense');
            Route::post('update-allowed-expense/{id}', [ConvenienceController::class, 'updateAllowedExpense'])->name('updateallowedexpense');
        });

    Route::post('/tada/bulk-update-status', [ConvenienceController::class, 'bulkUpdateStatus']);
    Route::post('/conveyance/accept/{id}', [ConvenienceController::class, 'accept'])->name('conveyance.accept');
    Route::post('/conveyance/reject/{id}', [ConvenienceController::class, 'reject'])->name('conveyance.reject');
    Route::post('/conveyance/bulk-action', [ConvenienceController::class, 'bulkAction'])->name('conveyance.bulkAction');
    Route::get('/convenience-details/{id}', [ConvenienceController::class, 'showdetailsconveyance'])->name('convenience.details');
    Route::get('/view-bills', [ConvenienceController::class, 'viewBills'])->name('view.bills');

    // Inventory
    Route::resource('inventory', InventoryController::class)->except(['show', 'store']);
    Route::post('/inventory/store', [InventoryController::class, 'store'])->name('inventory.store');
    Route::prefix('inventory')
        ->name('inventory.')
        ->group(function () {
            Route::post('import', [InventoryController::class, 'import'])->name('import');
            Route::post('import-streetlight', [InventoryController::class, 'importStreetlight'])->name('import-streetlight');
            Route::get('download-format/{projectId}', [InventoryController::class, 'downloadImportFormat'])->name('download-format');
            Route::post('bulk-dispatch', [InventoryController::class, 'bulkDispatchFromExcel'])->name('bulk-dispatch');
            Route::post('confirm-bulk-dispatch', [InventoryController::class, 'confirmBulkDispatch'])->name('confirm-bulk-dispatch');
            Route::post('checkQR', [InventoryController::class, 'checkQR'])->name('checkQR');
            Route::post('check-serial', [InventoryController::class, 'checkSerial'])->name('checkSerial');
            Route::post('dispatchweb', [InventoryController::class, 'dispatchInventory'])->name('dispatchweb');
            Route::get('view', [InventoryController::class, 'viewInventory'])->name('view');
            Route::post('replace', [InventoryController::class, 'replaceItem'])->name('replace');
            Route::get('edit/{id}', [InventoryController::class, 'editInventory'])->name('editInventory');
            Route::put('update/{id}', [InventoryController::class, 'updateInventory'])->name('updateInventory');
            Route::post('bulk-delete', [InventoryController::class, 'bulkDelete'])->name('bulkDelete');
            Route::post('bulk-return', [InventoryController::class, 'bulkReturn'])->name('bulkReturn');
            Route::get('dispatch', [InventoryController::class, 'showDispatchInventory'])->name('showDispatchInventory');
            Route::post('return', [InventoryController::class, 'returnInventory'])->name('return');
        });
    Route::get('/store/{store}', [StoreController::class, 'show'])->name('store.show');
    Route::get('/store/{store}/inventory', [StoreController::class, 'inventory'])->name('store.inventory');
    Route::get('/store/{store}/inventory/data', [StoreController::class, 'inventoryData'])->name('store.inventory.data');
    Route::get('/store/{store}/inventory/export', [StoreController::class, 'exportInventory'])->name('store.inventory.export');
    Route::get('/store/{store}/dispatched/data', [StoreController::class, 'dispatchedData'])->name('store.dispatched.data');
    Route::delete('/store/{store}', [StoreController::class, 'destroy'])->name('store.destroy');

    // Tasks
    Route::resource('tasks', TasksController::class)->except(['show']);
    Route::get('/tasks/rooftop/{id}', [TasksController::class, 'editrooftop'])->name('tasks.editrooftop');
    Route::post('/tasks/rooftop/update/{id}', [TasksController::class, 'updateRooftop'])->name('tasks.updaterooftop');
    Route::delete('/tasks/delete/{id}', [ProjectsController::class, 'destroyTarget'])->name('tasks.destroystreetlight');
    Route::get('/tasks/export/excel', [TasksController::class, 'exportToExcel'])->name('tasks.export');
    Route::post('/tasks/bulk-delete', [ProjectsController::class, 'bulkDeleteTargets'])->name('tasks.bulkDelete');
    Route::post('/tasks/bulk-reassign', [ProjectsController::class, 'bulkReassignTargets'])->name('tasks.bulkReassign');
    Route::get('/tasks/download/import-format', [ProjectsController::class, 'downloadTargetImportFormat'])->name('tasks.importFormat');
    Route::post('/tasks/import', [ProjectsController::class, 'importTargets'])->name('tasks.import');
    Route::get('/tasks/{id}/{any?}', [TasksController::class, 'show'])
        ->where('any', '.*')
        ->name('tasks.show');

    // Surveyed/Installed Poles
    Route::get('/surveyed-poles', [TaskController::class, 'getSurveyedPoles'])->name('surveyed.poles');
    Route::get('/installed-poles', [TaskController::class, 'getInstalledPoles'])->name('installed.poles');
    Route::get('/installed-poles/data', [TaskController::class, 'getInstalledPolesData'])->name('installed.poles.data');
    Route::get('/export-poles', [TaskController::class, 'exportPoles'])->name('poles.export');
    Route::get('/poles/show/{id}', [TaskController::class, 'viewPoleDetails'])->name('poles.show');
    Route::get('/poles/{id}/edit', [PoleController::class, 'edit'])->name('poles.edit');
    Route::put('/poles/{id}', [PoleController::class, 'update'])->name('poles.update');
    Route::delete('/poles/{id}', [PoleController::class, 'destroy'])->name('poles.destroy');

    // Streetlight
    Route::get('/streetlight/search', [StreetlightController::class, 'search'])->name('streetlights.search');
    Route::get('/blocks-by-district/{district}', [StreetlightController::class, 'getBlocksByDistrict']);
    Route::get('/panchayats-by-block/{block}', [StreetlightController::class, 'getPanchayatsByBlock']);

    // Hiring (using existing candidate routes, only adding authenticated-only routes)
    Route::post('/candidates/bulk-update', [PreviewController::class, 'bulkUpdate'])->name('candidates.bulkUpdate');
    Route::delete('/candidates/{id}', [CandidateController::class, 'destroy'])->name('candidates.destroy');

    // Device Import
    Route::get('/devices-import', [DeviceController::class, 'index'])->name('device.index');
    Route::post('/import-devices', [DeviceController::class, 'import'])->name('import.device');

    // RMS Push
    Route::get('/rms-export', [RMSController::class, 'index'])->name('rms.index');
    Route::post('/rms-push', [RMSController::class, 'sendPanchayatToRMS'])->name('rms.push');
});
