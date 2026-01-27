<?php

namespace App\Http\Controllers;

use App\Models\InventoryDispatch;
use App\Models\InventroyStreetLightModel;
use App\Models\Pole;
use App\Services\Inventory\InventoryHistoryService;
use App\Services\Inventory\InventoryService;
// TODO: Add StreetlightTask and User model also to modify vendor(installer name, billing status etc)
use App\Services\Logging\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PoleController extends Controller
{
    protected InventoryService $inventoryService;

    protected InventoryHistoryService $historyService;

    protected ActivityLogger $activityLogger;

    public function __construct(InventoryService $inventoryService, InventoryHistoryService $historyService, ActivityLogger $activityLogger)
    {
        $this->inventoryService = $inventoryService;
        $this->historyService = $historyService;
        $this->activityLogger = $activityLogger;
    }

    public function edit($id)
    {
        $pole = Pole::with(['task', 'task.streetlight'])->findOrFail($id);

        // Process images similar to show method
        $surveyImages = $this->processImagesFromJson($pole->survey_image);
        $submissionImages = $this->processImagesFromJson($pole->submission_image);

        // Get related data (same as show method)
        $latestTask = $pole->task;
        $installer = $latestTask?->vendor;
        $siteEngineer = $latestTask?->engineer;
        $projectManager = $latestTask?->manager;

        return view('poles.edit', compact('pole', 'installer', 'siteEngineer', 'projectManager', 'surveyImages', 'submissionImages'));
    }

    public function update(Request $request, $id)
    {
        $pole = Pole::findOrFail($id);

        $rules = [
            'ward_name' => 'nullable|string|max:255',
            'beneficiary' => 'nullable|string|max:255',
            'beneficiary_contact' => 'nullable|string|max:20',
            'isSurveyDone' => 'nullable|boolean',
            'isInstallationDone' => 'nullable|boolean',
            'isNetworkAvailable' => 'nullable|boolean',
            'vendor_id' => 'nullable|string|max:255',
            'luminary_qr' => 'nullable|string|max:255',
            'sim_number' => 'nullable|string|max:200',
            'panel_qr' => 'nullable|string|max:255',
            'battery_qr' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'remarks' => 'nullable|string',
            'survey_image.*' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'submission_image.*' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'existing_survey_images' => 'nullable|array',
            'existing_submission_images' => 'nullable|array',
            'deleted_survey_images' => 'nullable|array',
            'deleted_submission_images' => 'nullable|array',
            'replace_survey_image' => 'nullable|array',
            'replace_submission_image' => 'nullable|array',
            'replace_survey_image.*' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
            'replace_submission_image.*' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ];

        if (
            $request->filled('luminary_qr')
            && $request->luminary_qr !== $pole->luminary_qr
            && $request->sim_number === $pole->sim_number
        ) {
            $rules['sim_number'] = 'required|string|max:200|different:sim_number';
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // Get pole's district for validation
            $poleDistrict = null;
            if ($pole->task && $pole->task->streetlight) {
                $poleDistrict = $pole->task->streetlight->district;
            }

            // Return inventory if any QR changed
            foreach (['luminary_qr', 'panel_qr', 'battery_qr'] as $field) {
                if (! empty($validated[$field]) && $validated[$field] !== $pole->$field) {
                    // Validate new serial exists and district matches
                    $newDispatch = InventoryDispatch::where('serial_number', $validated[$field])
                        ->where('isDispatched', true)
                        ->where('is_consumed', false)
                        ->first();

                    if ($newDispatch && $poleDistrict) {
                        // Check district match
                        $projectDistricts = $this->inventoryService->getProjectDistricts($newDispatch->project_id);
                        if (! in_array($poleDistrict, $projectDistricts)) {
                            DB::rollBack();
                            $project = \App\Models\Project::find($newDispatch->project_id);

                            return redirect()->back()
                                ->withErrors([$field => "Inventory from {$project->project_name} cannot be used in district {$poleDistrict}"])
                                ->withInput();
                        }
                    }

                    $this->replaceSerialManually($pole, $field, $validated[$field], $poleDistrict);
                }
            }

            // Handle image uploads
            $updateData = [
                'ward_name' => $validated['ward_name'] ?? $pole->ward_name,
                'beneficiary' => $validated['beneficiary'] ?? $pole->beneficiary,
                'beneficiary_contact' => $validated['beneficiary_contact'] ?? $pole->beneficiary_contact,
                'remarks' => $validated['remarks'] ?? $pole->remarks,
                'luminary_qr' => $validated['luminary_qr'] ?? $pole->luminary_qr,
                'sim_number' => $validated['sim_number'] ?? $pole->sim_number,
                'panel_qr' => $validated['panel_qr'] ?? $pole->panel_qr,
                'battery_qr' => $validated['battery_qr'] ?? $pole->battery_qr,
                'lat' => $validated['lat'] ?? $pole->lat,
                'lng' => $validated['lng'] ?? $pole->lng,
                'isSurveyDone' => $validated['isSurveyDone'] ?? $pole->isSurveyDone,
                'isInstallationDone' => $validated['isInstallationDone'] ?? $pole->isInstallationDone,
                'isNetworkAvailable' => $validated['isNetworkAvailable'] ?? $pole->isNetworkAvailable,
            ];

            // Process survey images - handle deletions and new uploads
            $existingSurveyImages = $request->input('existing_survey_images', []);
            $deletedSurveyImages = $request->input('deleted_survey_images', []);

            // Remove deleted images from existing list
            $remainingSurveyImages = array_diff($existingSurveyImages, $deletedSurveyImages);

            // Add new survey images
            if ($request->hasFile('survey_image')) {
                $newSurveyImages = collect($request->file('survey_image'))->map(function ($file) use ($pole) {
                    return $this->uploadToS3($file, "streetlights/survey/{$pole->id}");
                })->toArray();
                $remainingSurveyImages = array_merge($remainingSurveyImages, $newSurveyImages);
            }

            // Handle replace survey images
            if ($request->hasFile('replace_survey_image')) {
                $replaceImages = $request->file('replace_survey_image');
                foreach ($replaceImages as $index => $file) {
                    if ($file && $file->isValid()) {
                        $newImageUrl = $this->uploadToS3($file, "streetlights/survey/{$pole->id}");
                        // Replace at the same index if exists
                        if (isset($remainingSurveyImages[$index])) {
                            $remainingSurveyImages[$index] = $newImageUrl;
                        } else {
                            $remainingSurveyImages[] = $newImageUrl;
                        }
                    }
                }
            }

            $updateData['survey_image'] = ! empty($remainingSurveyImages) ? json_encode(array_values($remainingSurveyImages)) : null;

            // Process submission/installation images - handle deletions and new uploads
            $existingSubmissionImages = $request->input('existing_submission_images', []);
            $deletedSubmissionImages = $request->input('deleted_submission_images', []);

            // Remove deleted images from existing list
            $remainingSubmissionImages = array_diff($existingSubmissionImages, $deletedSubmissionImages);

            // Add new submission images
            if ($request->hasFile('submission_image')) {
                $newSubmissionImages = collect($request->file('submission_image'))->map(function ($file) use ($pole) {
                    return $this->uploadToS3($file, "streetlights/installation/{$pole->id}");
                })->toArray();
                $remainingSubmissionImages = array_merge($remainingSubmissionImages, $newSubmissionImages);
            }

            // Handle replace submission images
            if ($request->hasFile('replace_submission_image')) {
                $replaceImages = $request->file('replace_submission_image');
                foreach ($replaceImages as $index => $file) {
                    if ($file && $file->isValid()) {
                        $newImageUrl = $this->uploadToS3($file, "streetlights/installation/{$pole->id}");
                        // Replace at the same index if exists
                        if (isset($remainingSubmissionImages[$index])) {
                            $remainingSubmissionImages[$index] = $newImageUrl;
                        } else {
                            $remainingSubmissionImages[] = $newImageUrl;
                        }
                    }
                }
            }

            $updateData['submission_image'] = ! empty($remainingSubmissionImages) ? json_encode(array_values($remainingSubmissionImages)) : null;

            // Capture diff before update for logging
            $beforeAfter = $this->activityLogger->diff($pole);

            // Update pole
            $pole->update($updateData);

            // Mark new inventory as consumed if QR codes are provided
            $newSerials = array_filter([
                $validated['luminary_qr'] ?? null,
                $validated['panel_qr'] ?? null,
                $validated['battery_qr'] ?? null,
            ]);

            if (! empty($newSerials)) {
                // Validate district for new serials before consuming
                $poleDistrict = $pole->task && $pole->task->streetlight ? $pole->task->streetlight->district : null;

                if ($poleDistrict) {
                    $dispatches = InventoryDispatch::whereIn('serial_number', $newSerials)
                        ->whereNull('streetlight_pole_id')
                        ->get();

                    foreach ($dispatches as $dispatch) {
                        $projectDistricts = $this->inventoryService->getProjectDistricts($dispatch->project_id);
                        if (! in_array($poleDistrict, $projectDistricts)) {
                            DB::rollBack();
                            $project = \App\Models\Project::find($dispatch->project_id);

                            return redirect()->back()
                                ->withErrors(['error' => "Inventory from {$project->project_name} cannot be used in district {$poleDistrict}"])
                                ->withInput();
                        }
                    }
                }

                $dispatches = InventoryDispatch::whereIn('serial_number', $newSerials)
                    ->whereNull('streetlight_pole_id')
                    ->get();

                foreach ($dispatches as $dispatch) {
                    $dispatch->update([
                        'is_consumed' => true,
                        'streetlight_pole_id' => $pole->id,
                    ]);

                    // Log history
                    $project = \App\Models\Project::find($dispatch->project_id);
                    $inventoryType = ($project && $project->project_type == 1) ? 'streetlight' : 'rooftop';
                    $this->historyService->logConsumed($dispatch, $inventoryType, $pole);
                }
            }

            DB::commit();
            Log::info('Pole Updated:', $pole->toArray());

            $this->activityLogger->log('pole', 'updated', $pole, [
                'description' => 'Pole updated via web.',
                'changes' => $beforeAfter,
            ]);

            return redirect()->route('poles.show', $pole->id)
                ->with('success', 'Pole details updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update pole', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Failed to update pole details: '.$e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $pole = Pole::findOrFail($id);

            // Count poles with same pole number
            $samePolesCount = Pole::where('complete_pole_number', $pole->pole_number)->count();

            // Get associated streetlight
            $streetlight = $pole->task->streetlight ?? null;

            // Check inventory usage
            $inventoryFields = ['luminary_qr', 'panel_qr', 'battery_qr'];
            $isInventoryUsed = false;

            foreach ($inventoryFields as $field) {
                if (! empty($pole->$field)) {
                    $dispatch = InventoryDispatch::where('serial_number', $pole->$field)
                        ->where('streetlight_pole_id', '!=', $pole->id)
                        ->first();

                    if ($dispatch) {
                        $isInventoryUsed = true;
                        break;
                    }
                }
            }

            // If multiple poles exist with same pole number
            if ($samePolesCount > 1) {
                if ($isInventoryUsed) {
                    // Inventory used in other pole → directly delete
                    $pole->delete();
                } else {
                    // Inventory not used elsewhere → return inventory + delete
                    foreach ($inventoryFields as $field) {
                        if (! empty($pole->$field)) {
                            $this->returnInventoryItem($pole->$field);
                        }
                    }
                    $pole->delete();

                    if ($streetlight) {
                        $streetlight->decrement('polecount', 1);
                    }
                }
            } else {
                // Only one pole with that number → always return inventory + delete
                foreach ($inventoryFields as $field) {
                    if (! empty($pole->$field)) {
                        $this->returnInventoryItem($pole->$field);
                    }
                }
                $pole->delete();

                if ($streetlight) {
                    $streetlight->decrement('polecount', 1);
                }
            }

            return redirect()->route('poles.index')
                ->with('success', 'Pole deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete pole', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Failed to delete pole.');
        }
    }

    private function returnInventoryItem($serialNumber)
    {
        try {
            Log::info('Returning inventory item', ['serial_number' => $serialNumber]);

            // Find inventory item
            $inventory = InventroyStreetLightModel::where('serial_number', $serialNumber)->first();

            // Find dispatch record
            $dispatch = InventoryDispatch::where('serial_number', $serialNumber)
                ->whereNotNull('streetlight_pole_id')
                ->first();

            if ($inventory) {
                $inventory->quantity = 1;
                $inventory->save();
                Log::info('Inventory quantity updated', ['serial_number' => $serialNumber]);
            }

            if ($dispatch) {
                $dispatch->delete();
                Log::info('Dispatch record updated', ['serial_number' => $serialNumber]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to return inventory item', [
                'serial_number' => $serialNumber,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function replaceSerialManually($pole, $field, $newSerial, $poleDistrict = null)
    {
        $oldSerial = $pole->$field;

        // Step 1: Get old dispatch record
        $oldDispatch = InventoryDispatch::where('serial_number', $oldSerial)->first();
        if (! $oldDispatch) {
            return;
        }

        // Step 2: Validate new serial exists in inventory
        $newInventoryItem = InventroyStreetLightModel::where('serial_number', $newSerial)->first();
        if (! $newInventoryItem) {
            throw new \Exception("Serial number {$newSerial} not found in inventory");
        }

        // Step 3: Check if new serial is already consumed
        $existingConsumed = InventoryDispatch::where('serial_number', $newSerial)
            ->where('is_consumed', true)
            ->exists();
        if ($existingConsumed) {
            throw new \Exception("Serial number {$newSerial} is already consumed");
        }

        // Step 4: Check or clone new dispatch
        $newDispatch = InventoryDispatch::where('serial_number', $newSerial)
            ->where('isDispatched', true)
            ->where('is_consumed', false)
            ->first();

        if (! $newDispatch) {
            $newDispatch = $oldDispatch->replicate();
            $newDispatch->serial_number = $newSerial;
            $newDispatch->is_consumed = false;
            $newDispatch->streetlight_pole_id = null;
            $newDispatch->save();
        } else {
            // Validate district if pole district is available
            if ($poleDistrict) {
                $projectDistricts = $this->inventoryService->getProjectDistricts($newDispatch->project_id);
                if (! in_array($poleDistrict, $projectDistricts)) {
                    $project = \App\Models\Project::find($newDispatch->project_id);
                    throw new \Exception("Inventory from {$project->project_name} cannot be used in district {$poleDistrict}");
                }
            }

            $newDispatch->fill($oldDispatch->only([
                'vendor_id',
                'total_quantity',
                'total_value',
                'rate',
                'store_id',
                'store_incharge_id',
                'project_id',
                'isDispatched',
                'is_consumed',
                'streetlight_pole_id',
            ]));
            $newDispatch->save();
        }

        // Step 5: Update inventory streetlight model - consume new, return old
        $newStreet = InventroyStreetLightModel::where('serial_number', $newSerial)->first();
        if ($newStreet) {
            $newStreet->quantity = 0; // Consume new item
            $newStreet->save();
        } else {
            $oldStreet = InventroyStreetLightModel::where('serial_number', $oldSerial)->first();
            if ($oldStreet) {
                $newStreet = $oldStreet->replicate();
                $newStreet->serial_number = $newSerial;
                $newStreet->quantity = 0; // Consume new item
                $newStreet->save();
            }
        }

        // Step 6: Return old item to stock
        $oldStreet = InventroyStreetLightModel::where('serial_number', $oldSerial)->first();
        if ($oldStreet) {
            $oldStreet->quantity = 1; // Return old item to stock
            $oldStreet->save();
        }

        // Step 7: Log history for replacement
        $project = \App\Models\Project::find($oldDispatch->project_id);
        $inventoryType = ($project && $project->project_type == 1) ? 'streetlight' : 'rooftop';

        if (isset($oldStreet) && isset($newStreet)) {
            $this->historyService->logReplaced(
                $oldStreet,
                $newStreet,
                $inventoryType,
                $oldDispatch->project_id,
                $oldDispatch->store_id,
                $pole
            );
        }

        // Step 8: Delete old dispatch
        $oldDispatch->delete();
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:streelight_poles,id',
        ]);

        try {
            DB::beginTransaction();
            $deletedCount = 0;

            foreach ($request->ids as $id) {
                $pole = Pole::find($id);
                if ($pole) {
                    // Return inventory
                    foreach (['luminary_qr', 'panel_qr', 'battery_qr'] as $field) {
                        if (! empty($pole->$field)) {
                            $this->returnInventoryItem($pole->$field);
                        }
                    }
                    $pole->delete();
                    $deletedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'message' => "Successfully deleted {$deletedCount} pole(s).",
                'deleted_count' => $deletedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk delete failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to delete poles: '.$e->getMessage(),
            ], 500);
        }
    }

    public function bulkPushRms(Request $request)
    {
        $request->validate([
            'pole_ids' => 'required|array',
            'pole_ids.*' => 'exists:streelight_poles,id',
        ]);

        try {
            $poles = Pole::whereIn('id', $request->pole_ids)
                ->with(['task.streetlight', 'task.engineer'])
                ->get();

            $successCount = 0;
            $errorCount = 0;
            $responses = [];

            foreach ($poles as $pole) {
                try {
                    $task = $pole->task;
                    $streetlight = $task ? $task->streetlight : null;

                    if (! $task || ! $streetlight || ! $task->engineer) {
                        throw new \Exception('Missing related task, streetlight, or engineer data.');
                    }

                    $approved_by = $task->engineer->firstName.' '.$task->engineer->lastName;
                    $apiResponse = \App\Helpers\RemoteApiHelper::sendPoleDataToRemoteServer($pole, $streetlight, $approved_by);

                    $status = 'error';
                    $message = 'Unknown error';

                    $responseData = $apiResponse ? $apiResponse->json() : null;
                    if ($apiResponse && $apiResponse->successful() && $responseData && isset($responseData['status']) && strtoupper((string) $responseData['status']) === 'OK') {
                        $status = 'success';
                        $message = $responseData['detail'] ?? $responseData['details'] ?? 'Successfully pushed to RMS';
                        $successCount++;
                    } else {
                        $status = 'error';
                        $message = $responseData['detail'] ?? $responseData['details'] ?? ($apiResponse ? $apiResponse->body() : 'No response from RMS API');
                        $errorCount++;
                        if (! $responseData || ! isset($responseData['status'])) {
                            $responseData = ['status' => 'ERR', 'detail' => $message];
                        }
                    }
                    Log::info('Bulk push RMS response', ['responseData' => $responseData]);

                    \App\Models\RmsPushLog::create([
                        'pole_id' => $pole->id,
                        'message' => $message,
                        'response_data' => $responseData,
                        'district' => $streetlight->district ?? null,
                        'block' => $streetlight->block ?? null,
                        'panchayat' => $streetlight->panchayat ?? null,
                        'pushed_by' => auth()->id(),
                        'pushed_at' => now(),
                    ]);

                    $responses[] = [
                        'pole_id' => $pole->id,
                        'status' => $status,
                        'message' => $message,
                    ];
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error('Failed to push pole to RMS', [
                        'pole_id' => $pole->id,
                        'error' => $e->getMessage(),
                    ]);

                    \App\Models\RmsPushLog::create([
                        'pole_id' => $pole->id,
                        'message' => $e->getMessage(),
                        'response_data' => ['status' => 'ERR', 'detail' => $e->getMessage()],
                        'district' => $pole->task?->streetlight?->district ?? null,
                        'block' => $pole->task?->streetlight?->block ?? null,
                        'panchayat' => $pole->task?->streetlight?->panchayat ?? null,
                        'pushed_by' => auth()->id(),
                        'pushed_at' => now(),
                    ]);

                    $responses[] = [
                        'pole_id' => $pole->id,
                        'status' => 'error',
                        'message' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'message' => "Pushed {$successCount} pole(s) successfully. {$errorCount} error(s).",
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'responses' => $responses,
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk push to RMS failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to push poles to RMS: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload a file to S3 storage
     */
    private function uploadToS3($file, $path)
    {
        try {
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $fileName = time().'_'.uniqid().'_'.$file->getClientOriginalName();
                $filePath = $file->storeAs($path, $fileName, 's3');

                return Storage::disk('s3')->url($filePath);
            } else {
                throw new \Exception('Invalid file format. Expected UploadedFile.');
            }
        } catch (\Exception $e) {
            Log::error('S3 Upload Error:', ['message' => $e->getMessage(), 'path' => $path]);
            throw $e;
        }
    }

    /**
     * Process images from JSON (similar to TaskController)
     */
    private function processImagesFromJson($json)
    {
        $imageUrls = [];

        if (empty($json)) {
            return $imageUrls;
        }

        $imagesArray = json_decode($json, true);

        if (! is_array($imagesArray)) {
            return $imageUrls;
        }

        // Check S3 config is present before attempting to generate URLs
        $bucket = config('filesystems.disks.s3.bucket');
        if (empty($bucket)) {
            return $imageUrls;
        }

        foreach ($imagesArray as $image) {
            if (! empty($image)) {
                try {
                    // If it's already a full URL, use it directly, otherwise generate S3 URL
                    if (filter_var($image, FILTER_VALIDATE_URL)) {
                        $imageUrls[] = $image;
                    } else {
                        $imageUrls[] = Storage::disk('s3')->url($image);
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to generate S3 URL for image: $image", ['exception' => $e->getMessage()]);
                }
            }
        }

        return $imageUrls;
    }
}
