<?php

namespace App\Http\Controllers\API;

use App\Helpers\ExcelHelper;
use App\Helpers\RemoteApiHelper;
use App\Http\Controllers\Controller;
use App\Models\InventoryDispatch;
use App\Models\Pole;
use App\Models\Site;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\Task;
use App\Models\User;
use App\Services\Inventory\InventoryHistoryService;
use App\Services\Inventory\InventoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    protected InventoryService $inventoryService;

    protected InventoryHistoryService $historyService;

    public function __construct(InventoryService $inventoryService, InventoryHistoryService $historyService)
    {
        $this->inventoryService = $inventoryService;
        $this->historyService = $historyService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Task::with(['project', 'site', 'vendor'])->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'sites' => 'required|array',
                'activity' => 'required|string',
                'engineer_id' => 'required|exists:users,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            $task = Task::create($validated);

            return response()->json([
                'message' => 'Task created successfully',
                'task' => $task,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating task',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {

        // Get the project_type from the query parameter
        $projectType = $request->query('project_type');
        // Conditionally fetch data based on project_type
        if ($projectType == 1) {
            // Fetch data from StreetlightTask model
            $task = StreetlightTask::with(['project', 'site', 'vendor'])->findOrFail($id);
        } else {
            // Default to fetching data from Task model
            $task = Task::with(['project', 'site', 'vendor'])->findOrFail($id);
        }

        // Decode the JSON image field
        $images = json_decode($task->image, true);

        // Generate full URLs
        $fullUrls = [];
        if (is_array($images)) {
            foreach ($images as $image) {
                $fullUrls[] = Storage::disk('s3')->url($image);
            }
        }

        // Add the full URLs to the image key
        $task->image = $fullUrls;

        // Include full URLs in the response
        return response()->json([
            'task' => $task,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Find the task by ID
            $task = Task::findOrFail($id);

            // Update task details except the `image` key
            $task->update($request->except('image'));

            $uploadedFiles = [];
            if ($request->hasFile('image')) {
                $images = $request->file('image'); // Input format for multiple files in JSON

                if (is_array($images)) {
                    // Handle multiple images
                    foreach ($images as $file) {
                        if ($file->isValid()) {
                            // Upload each image to S3
                            $uploadedFiles[] = $this->uploadToS3($file, 'tasks/'.$task->id);
                        } else {
                            return response()->json(['error' => 'Invalid image format.'], 400);
                        }
                    }
                } elseif ($images instanceof \Illuminate\Http\UploadedFile) {
                    // Handle single file upload (PDF or image)
                    $extension = $images->getClientOriginalExtension();

                    if (in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'])) {
                        $uploadedFiles[] = $this->uploadToS3($images, 'tasks/'.$task->id);
                    } else {
                        return response()->json(['error' => 'Invalid file type. Only PDF or images are allowed.'], 400);
                    }
                } else {
                    // Log and return an error if the `image` key format is invalid
                    return response()->json(['error' => 'Invalid "image" input format.'], 400);
                }

                // Update the task's `image` field in the database
                $task->update(['image' => json_encode($uploadedFiles)]);
            }

            // Update related site details
            $site = Site::find($task->site_id);

            if ($site) {
                $siteUpdateData = [];

                if ($request->has('survey_lat')) {
                    $siteUpdateData['survey_latitude'] = $request->input('survey_lat');
                }
                if ($request->has('survey_long')) {
                    $siteUpdateData['survey_longitude'] = $request->input('survey_long');
                }
                if ($request->has('lat')) {
                    $siteUpdateData['actual_latitude'] = $request->input('lat');
                }
                if ($request->has('long')) {
                    $siteUpdateData['actual_longitude'] = $request->input('long');
                }

                if (! empty($siteUpdateData)) {
                    $site->update($siteUpdateData);
                }
            }

            // Save task and return success response
            return response()->json([
                'message' => 'Task updated successfully.',
                'task' => $task,
                'uploaded_files' => $uploadedFiles,
            ]);
        } catch (\Exception $e) {
            // Log the exception
            return response()->json([
                'error' => 'An error occurred during the update process.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload a file to the S3 bucket.
     */
    private function uploadToS3($file, $path)
    {
        try {
            if (is_array($file) && isset($file['uri'], $file['name'])) {
                // Decode the file URI and extract the file contents
                $fileContents = file_get_contents($file['uri']);
                $fileName = time().'_'.$file['name'];

                // Upload to S3 using the file contents
                Storage::disk('s3')->put($path.'/'.$fileName, $fileContents);

                // Return the S3 file path
                return Storage::disk('s3')->url($path.'/'.$fileName);
            } elseif ($file instanceof \Illuminate\Http\UploadedFile) {
                // Handle standard UploadedFile objects
                $fileName = time().'_'.$file->getClientOriginalName();

                // Store file in S3
                return $file->storeAs($path, $fileName, 's3');
            } else {
                throw new \Exception('Invalid file format. Expected an array or UploadedFile.');
            }
        } catch (\Exception $e) {
            // Return error response
            return response()->json([
                'error' => 'An error occurred while uploading the file.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }

    public function getInstallablePoles($ward)
    {
        try {
            // Step 1: Define all possible poles (1 to 20)
            $allPoles = range(1, 20);

            // Step 2: Fetch only poles for the given ward
            $existingPoles = Pole::pluck('complete_pole_number')
                ->map(function ($poleNumber) use ($ward) {
                    $parts = explode('/', $poleNumber);
                    $count = count($parts);

                    if ($count < 2) {
                        return null;
                    } // Ensure the format is valid

                    // Extract ward and pole number
                    $poleWard = (int) $parts[$count - 2]; // Second last part is ward
                    $poleNumber = (int) $parts[$count - 1]; // Last part is pole number

                    return $poleWard === (int) $ward ? $poleNumber : null;
                })
                ->filter() // Remove null values
                ->unique() // Ensure unique poles
                ->values()
                ->toArray();

            // Step 3: Filter out existing poles for the given ward
            $installablePoles = array_values(array_diff($allPoles, $existingPoles));

            // Step 4: Return response
            return response()->json([
                'installable_poles' => $installablePoles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSitesForVendor($vendorId)
    {
        // Fetch streetlight tasks for the vendor and eager load the streetlight site
        $tasks = StreetlightTask::with('site')
            ->where('vendor_id', $vendorId)
            ->get();

        // Extract unique streetlight sites from the tasks
        $sites = $tasks->pluck('site')->filter()->unique('id')->values();

        return response()->json([
            'status' => 'success',
            'vendor_id' => $vendorId,
            'sites' => $sites,
        ], 200);
    }

    public function approveTask($id)
    {
        $task = Task::find($id);
        if (! $task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->update(['status' => 'Completed']);

        return response()->json([
            'message' => 'Task approved successfully',
            'task' => $task,
        ]);
    }

    // Update poles survey or install
    public function submitStreetlightTasks(Request $request)
    {
        try {
            // ✅ Step 1: Validation
            $validated = $request->validate([
                'task_id' => 'required|exists:streetlights,id',
                'complete_pole_number' => 'required|string|max:255',
                'ward_name' => 'nullable|string|max:255',
                'survey_image' => 'nullable|array',
                'isSurveyDone' => 'nullable|string|in:true,false',
                'isNetworkAvailable' => 'nullable|string|in:true,false',
                'isInstallationDone' => 'nullable|string|in:true,false',
                'beneficiary' => 'nullable|string|max:255',
                'beneficiary_contact' => 'nullable|string|max:20',
                'remarks' => 'nullable|string',
                'luminary_qr' => 'nullable|string|max:255',
                'sim_number' => 'nullable|string|max:200',
                'panel_qr' => 'nullable|string|max:255',
                'battery_qr' => 'nullable|string|max:255',
                'submission_image' => 'nullable|array',
                'lat' => 'nullable|numeric',
                'lng' => 'nullable|numeric',
            ]);

            // ✅ Step 2: Fetch task & site
            $task = StreetlightTask::find($validated['task_id']);
            if (! $task) {
                $this->logTaskSubmissionError($request, "Streetlight task with ID {$validated['task_id']} not found", 'task_not_found');
                
                return response()->json([
                    'message' => "Streetlight task with ID {$validated['task_id']} not found",
                    'error' => 'task_not_found',
                    'task_id' => $validated['task_id'],
                ], 404);
            }

            if (! $task->engineer) {
                $this->logTaskSubmissionError($request, "Task ID {$validated['task_id']} has no assigned engineer", 'no_engineer');
                
                return response()->json([
                    'message' => "Task ID {$validated['task_id']} has no assigned engineer",
                    'error' => 'no_engineer',
                    'task_id' => $validated['task_id'],
                ], 400);
            }

            $approved_by = $task->engineer->firstName.' '.$task->engineer->lastName;
            
            $streetlight = Streetlight::find($task->site_id);
            if (! $streetlight) {
                $this->logTaskSubmissionError($request, "Streetlight site with ID {$task->site_id} not found for task {$validated['task_id']}", 'site_not_found');
                
                return response()->json([
                    'message' => "Streetlight site with ID {$task->site_id} not found",
                    'error' => 'site_not_found',
                    'task_id' => $validated['task_id'],
                    'site_id' => $task->site_id,
                ], 404);
            }

        // ✅ Step 3: Create or get pole
        $pole = Pole::firstOrCreate(
            [
                'task_id' => $validated['task_id'],
                'complete_pole_number' => $validated['complete_pole_number'],
            ],
            [
                'ward_name' => $validated['ward_name'] ?? null,
                'beneficiary' => $validated['beneficiary'] ?? null,
                'beneficiary_contact' => $validated['beneficiary_contact'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'isSurveyDone' => true,
                'isInstallationDone' => false,
                'lat' => $validated['lat'] ?? null,
                'lng' => $validated['lng'] ?? null,
            ]
        );

        if ($pole->wasRecentlyCreated) {
            $streetlight->increment('number_of_surveyed_poles');
        }

        // ✅ Step 4: Upload images
        foreach (['survey_image' => 'survey', 'submission_image' => 'installation'] as $field => $folder) {
            if ($request->hasFile($field)) {
                $images = collect($request->file($field))->map(
                    fn ($img) => $this->uploadToS3($img, "streetlights/{$folder}/{$pole->id}")
                );
                $pole->update([$field => json_encode($images)]);
            }
        }

        // ✅ Step 5: Update survey data
        if ($request->isSurveyDone && ! $pole->isSurveyDone) {
            $pole->update([
                'isSurveyDone' => true,
                'beneficiary' => $validated['beneficiary'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'isNetworkAvailable' => $validated['isNetworkAvailable'] ?? 0,
            ]);
            $streetlight->increment('number_of_surveyed_poles');
        }

        // ✅ Step 6: Update installation data
        if ($request->isInstallationDone && ! $pole->isInstallationDone) {
            $pole->update([
                'isInstallationDone' => true,
                'vendor_id' => $task->vendor_id, // Set vendor_id from task when pole is installed
                'luminary_qr' => $validated['luminary_qr'] ?? null,
                'sim_number' => $validated['sim_number'] ?? null,
                'panel_qr' => $validated['panel_qr'] ?? null,
                'battery_qr' => $validated['battery_qr'] ?? null,
            ]);

            $streetlight->increment('number_of_installed_poles');

            // ✅ Step 7: Mark inventory as consumed with district validation
            $serials = array_filter([
                $validated['luminary_qr'] ?? null,
                $validated['panel_qr'] ?? null,
                $validated['battery_qr'] ?? null,
            ]);

            if (! empty($serials)) {
                // Get pole's district
                $poleDistrict = $streetlight->district;

                // Validate each serial number's dispatch district matches pole's district
                $dispatches = InventoryDispatch::whereIn('serial_number', $serials)
                    ->where('isDispatched', true)
                    ->where('is_consumed', false)
                    ->get();

                foreach ($dispatches as $dispatch) {
                    // Get project's districts
                    $projectDistricts = $this->inventoryService->getProjectDistricts($dispatch->project_id);

                    // Check if pole's district is in project's districts
                    if (! in_array($poleDistrict, $projectDistricts)) {
                        $project = \App\Models\Project::find($dispatch->project_id);

                        return response()->json([
                            'message' => "Inventory dispatched for {$project->project_name} cannot be used in district {$poleDistrict}",
                            'error' => 'district_mismatch',
                            'pole_district' => $poleDistrict,
                            'project_name' => $project->project_name ?? 'Unknown Project',
                        ], 400);
                    }
                }

                // All validations passed, mark as consumed
                $dispatches = InventoryDispatch::whereIn('serial_number', $serials)->get();

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
            RemoteApiHelper::sendPoleDataToRemoteServer($pole, $streetlight, $approved_by);
        }

        Log::info('Pole Submitted:', $pole->toArray());

        return response()->json([
            'message' => 'Pole details submitted successfully!',
            'pole' => $pole,
            'task' => $task,
        ]);
        } catch (ModelNotFoundException $e) {
            $this->logTaskSubmissionError($request, $e->getMessage(), 'model_not_found');
            
            return response()->json([
                'message' => 'The requested resource was not found',
                'error' => 'not_found',
                'details' => $e->getMessage(),
            ], 404);
        } catch (\Exception $e) {
            $this->logTaskSubmissionError($request, $e->getMessage(), 'exception');
            
            Log::error('Error submitting streetlight task data: '.$e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => 'An error occurred while submitting the task data',
                'error' => 'server_error',
            ], 500);
        }
    }

    /**
     * Log task submission error in human-readable format for client support
     */
    private function logTaskSubmissionError(Request $request, string $errorMessage, string $errorType = 'error'): void
    {
        $taskId = $request->task_id ?? 'N/A';
        $poleNumber = $request->complete_pole_number ?? 'N/A';
        $wardName = $request->ward_name ?? 'N/A';
        $userId = Auth::id() ?? 'N/A';
        $userName = Auth::user() ? (Auth::user()->firstName.' '.Auth::user()->lastName) : 'N/A';
        
        // Try to get task details if available
        $taskInfo = 'N/A';
        if ($taskId !== 'N/A') {
            try {
                $task = StreetlightTask::find($taskId);
                if ($task) {
                    $siteInfo = $task->site ? "{$task->site->panchayat}, {$task->site->district}" : 'N/A';
                    $taskInfo = "Task ID: {$taskId} | Site: {$siteInfo}";
                }
            } catch (\Exception $e) {
                // Ignore errors when fetching task info for logging
            }
        }
        
        $logMessage = sprintf(
            'Streetlight Task Submission Failed | %s | User: %s (ID: %s) | Pole Number: %s | Ward: %s | Error: %s',
            $taskInfo,
            $userName,
            $userId,
            $poleNumber,
            $wardName,
            $errorMessage
        );
        
        Log::error($logMessage);
    }

    // Controller to get details of pole by Id
    public function getPoleDetails(Request $request)
    {
        $id = $request->pole_id;
        $pole = Pole::findOrFail($id);
        if (! $pole) {
            return response()->json([
                'message' => 'No poles associated with this id',
            ], 404);
        }

        return response()->json([
            'pole' => $pole,
        ], 200);
    }

    // Get Installed Pole for Site Engineers
    public function getInstalledPolesForSiteEngineer($engineer_id)
    {
        $surveyed_poles = Pole::whereHas('task', function ($query) use ($engineer_id) {
            $query->where('engineer_id', $engineer_id);
        })->where('isSurveyDone', 1)
            ->with(['task.site', 'task.engineer', 'task.manager']) // Eager load relationships
            ->get();

        $installed_poles = Pole::whereHas('task', function ($query) use ($engineer_id) {
            $query->where('engineer_id', $engineer_id);
        })->where('isInstallationDone', 1)
            ->with(['task.site', 'task.engineer', 'task.manager']) // Eager load relationships
            ->get();

        return response()->json([
            'message' => 'Installed poles for Site Engineer',
            'surveyed_poles' => $surveyed_poles,
            'installed_poles' => $installed_poles,
        ], 200);
    }

    /**
     * Get all installed poles for a specific Vendor
     */
    public function getInstalledPolesForVendor($vendor_id)
    {
        $surveyed_poles = Pole::whereHas('task', function ($query) use ($vendor_id) {
            $query->where('vendor_id', $vendor_id);
        })->where('isSurveyDone', 1)
            ->where('isInstallationDone', 0)
            ->with(['task.site', 'task.engineer', 'task.manager']) // Eager load relationships
            ->get();
        // Transform the data to match the desired output structure
        $transformed_poles = $surveyed_poles->map(function ($pole) {
            return [
                'pole_id' => $pole->id,
                'complete_pole_number' => $pole->complete_pole_number,
                'ward' => $pole->task->site->ward ?? null,
                'panchayat' => $pole->task->site->panchayat ?? null,
                'block' => $pole->task->site->block ?? null,
                'district' => $pole->task->site->district ?? null,
                'state' => $pole->task->site->state ?? null,
                'beneficiary' => $pole->beneficiary,
                'beneficiary_contact' => $pole->beneficiary_contact,
                'isSurveyed' => true,
                'isInstalled' => false,
                'installed_location' => [
                    'lat' => $pole->lat,
                    'lng' => $pole->lng,
                ],
                'remarks' => $pole->remarks,
                'survey_image' => collect(json_decode($pole->survey_image, true) ?? [])->map(fn ($image) => Storage::disk('s3')->url($image))->toArray(),
                'submission_image' => collect(json_decode($pole->submission_image, true) ?? [])->map(fn ($image) => Storage::disk('s3')->url($image))->toArray(),
                'site_engineer_name' => $pole->task->engineer->first_name ?? null, // Assuming 'name' is the field for engineer's name
                'project_manager_name' => $pole->task->manager->name ?? null, // Assuming 'name' is the field for manager's name
                'assigned_date' => $pole->created_at,
                'submission_date' => $pole->updated_at,
                'status' => $pole->status,
            ];
        });

        $installed_poles = Pole::whereHas('task', function ($query) use ($vendor_id) {
            $query->where('vendor_id', $vendor_id);
        })->where('isInstallationDone', 1)
            ->with(['task.site', 'task.engineer', 'task.manager']) // Eager load relationships
            ->get();

        // Transform the data to match the desired output structure
        $transformed__installed_poles = $installed_poles->map(function ($pole) {
            return [
                'pole_id' => $pole->id,
                'complete_pole_number' => $pole->complete_pole_number,
                'ward_name' => $pole->ward_name ?? null,
                'luminary_qr' => $pole->luminary_qr,
                'battery_qr' => $pole->battery_qr,
                'panel_qr' => $pole->panel_qr,
                'beneficiary_contact' => $pole->beneficiary_contact,
                'isSurveyed' => true,
                'isInstalled' => true,
                'sim_number' => $pole->sim_number,
                'panchayat' => $pole->task->site->panchayat ?? null,
                'block' => $pole->task->site->block ?? null,
                'district' => $pole->task->site->district ?? null,
                'state' => $pole->task->site->state ?? null,
                'beneficiary' => $pole->beneficiary,
                'installed_location' => [
                    'lat' => $pole->lat,
                    'lng' => $pole->lng,
                ],
                'remarks' => $pole->remarks,
                'survey_image' => collect(json_decode($pole->survey_image, true) ?? [])->map(fn ($image) => Storage::disk('s3')->url($image))->toArray(),
                'submission_image' => collect(json_decode($pole->submission_image, true) ?? [])->map(fn ($image) => Storage::disk('s3')->url($image))->toArray(),
                'site_engineer_name' => $pole->task->engineer->name ?? null, // Assuming 'name' is the field for engineer's name
                'project_manager_name' => $pole->task->manager->name ?? null, // Assuming 'name' is the field for manager's name
                'status' => $pole->status,
            ];
        });

        return response()->json([
            'message' => 'Installed poles for Vendor',
            'surveyed_poles' => $transformed_poles,
            'installed_poles' => $transformed__installed_poles,
        ], 200);
    }

    /**
     * Get all installed poles for a specific Project Manager
     */
    public function getInstalledPolesForProjectManager($manager_id)
    {
        $surveyed_poles = Pole::whereHas('task', function ($query) use ($manager_id) {
            $query->where('manager_id', $manager_id);
        })->where('isSurveyDone', true)
            ->with(['task.site', 'task.engineer', 'task.vendor']) // Eager load relationships
            ->get();

        $installed_poles = Pole::whereHas('task', function ($query) use ($manager_id) {
            $query->where('manager_id', $manager_id);
        })->where('isInstallationDone', true)
            ->with(['task.site', 'task.engineer', 'task.vendor']) // Eager load relationships
            ->get();

        return response()->json([
            'message' => 'Installed poles for Project Manager',
            'surveyed_poles' => $surveyed_poles,
            'installed_poles' => $installed_poles,
        ], 200);
    }

    // Fetch Surveyed Poles based on user role
    public function getSurveyedPoles(Request $request)
    {
        if (! auth()->check()) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        $user = auth()->user();
        $query = Pole::with(['task.streetlight', 'task'])
            ->where('isSurveyDone', 1);

        // Apply filters based on request parameters
        if ($request->filled('search')) {
            $query->where('complete_pole_number', 'like', '%'.$request->search.'%');
        }

        // Filter by project_id through streetlight relationship
        if ($request->filled('project_id')) {
            $query->whereHas('task.streetlight', function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        // Filter by panchayat through streetlight relationship (panchayat is a string field, not ID)
        if ($request->filled('panchayat')) {
            $query->whereHas('task.streetlight', function ($q) use ($request) {
                $q->where('panchayat', $request->panchayat);
            });
        }

        // Filter by ward - use pole's ward_name field
        if ($request->filled('ward')) {
            $query->where('ward_name', $request->ward);
        }

        // Filter by district through streetlight relationship
        if ($request->filled('district')) {
            $query->whereHas('task.streetlight', function ($q) use ($request) {
                $q->where('district', $request->district);
            });
        }

        // Filter by block through streetlight relationship
        if ($request->filled('block')) {
            $query->whereHas('task.streetlight', function ($q) use ($request) {
                $q->where('block', $request->block);
            });
        }

        // Filter by project_manager through task relationship
        if ($request->filled('project_manager')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('manager_id', $request->project_manager);
            });
        }

        // Filter by site_engineer through task relationship
        if ($request->filled('site_engineer')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('engineer_id', $request->site_engineer);
            });
        }

        // Filter by vendor through task relationship
        if ($request->filled('vendor')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('vendor_id', $request->vendor);
            });
        }

        $poles = $query->with(['task.streetlight', 'task.engineer', 'task.vendor', 'task.manager'])->get();

        // Eager load RMS logs for all poles to avoid N+1 queries
        $poleIds = $poles->pluck('id');
        $rmsLogs = \App\Models\RmsPushLog::whereIn('pole_id', $poleIds)->get()->groupBy('pole_id');
        $poles->each(function ($pole) use ($rmsLogs) {
            $pole->rmsLogs = $rmsLogs->get($pole->id, collect());
        });
        $totalSurveyed = $poles->count();

        // Get unique values for filters and convert to arrays
        $districtOptions = ['' => 'All'];
        foreach ($poles->pluck('task.streetlight.district')->filter()->unique()->sort()->values() as $district) {
            if ($district) {
                $districtOptions[$district] = $district;
            }
        }

        $blockOptions = ['' => 'All'];
        foreach ($poles->pluck('task.streetlight.block')->filter()->unique()->sort()->values() as $block) {
            if ($block) {
                $blockOptions[$block] = $block;
            }
        }

        $panchayatOptions = ['' => 'All'];
        foreach ($poles->pluck('task.streetlight.panchayat')->filter()->unique()->sort()->values() as $panchayat) {
            if ($panchayat) {
                $panchayatOptions[$panchayat] = $panchayat;
            }
        }

        $wardOptions = ['' => 'All'];
        foreach ($poles->pluck('task.streetlight.ward')->filter()->unique()->sort()->values() as $ward) {
            if ($ward) {
                $wardOptions[$ward] = $ward;
            }
        }

        // Get unique project managers, site engineers, and vendors for filters
        $projectManagerOptions = ['' => 'All'];
        foreach ($poles->pluck('task.manager')->filter()->unique('id') as $manager) {
            if ($manager && $manager->id) {
                $projectManagerOptions[$manager->id] = $manager->name ?? 'N/A';
            }
        }

        $siteEngineerOptions = ['' => 'All'];
        foreach ($poles->pluck('task.engineer')->filter()->unique('id') as $engineer) {
            if ($engineer && $engineer->id) {
                $siteEngineerOptions[$engineer->id] = $engineer->name ?? 'N/A';
            }
        }

        return view('poles.surveyed', compact('poles', 'totalSurveyed', 'districtOptions', 'blockOptions', 'panchayatOptions', 'wardOptions', 'projectManagerOptions', 'siteEngineerOptions'));
    }

    // Fetch Installed Poles based on user role
    public function getInstalledPoles(Request $request)
    {
        $query = Pole::with(['task.streetlight', 'task'])
            ->where('isInstallationDone', 1);

        // Apply URL parameter filters
        if ($request->filled('project_manager')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('manager_id', $request->project_manager);
            });
        }

        if ($request->filled('site_engineer')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('engineer_id', $request->site_engineer);
            });
        }

        if ($request->filled('vendor')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('vendor_id', $request->vendor);
            });
        }

        if ($request->filled('project_id')) {
            $query->whereHas('task.streetlight', function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        if ($request->filled('panchayat')) {
            $query->whereHas('task.streetlight', function ($q) use ($request) {
                $q->where('panchayat', $request->panchayat);
            });
        }

        // Filter by ward - use pole's ward_name field
        if ($request->filled('ward')) {
            $query->where('ward_name', $request->ward);
        }

        // Apply status filters
        if ($request->filled('filter_surveyed')) {
            if ($request->filter_surveyed == '1') {
                $query->where('isSurveyDone', 1);
            } elseif ($request->filter_surveyed == '0') {
                $query->where('isSurveyDone', 0);
            }
        }

        if ($request->filled('filter_installed')) {
            if ($request->filter_installed == '1') {
                $query->where('isInstallationDone', 1);
            } elseif ($request->filter_installed == '0') {
                $query->where('isInstallationDone', 0);
            }
        }

        if ($request->filled('filter_billed')) {
            if ($request->filter_billed == '1') {
                $query->whereHas('task', function ($q) {
                    $q->where('billed', 1);
                });
            } elseif ($request->filter_billed == '0') {
                $query->whereHas('task', function ($q) {
                    $q->where('billed', 0)->orWhereNull('billed');
                });
            }
        }

        $poles = $query->orderBy('complete_pole_number', 'asc')->get();

        // Eager load RMS logs for all poles to avoid N+1 queries
        $poleIds = $poles->pluck('id');
        $rmsLogs = \App\Models\RmsPushLog::whereIn('pole_id', $poleIds)->get()->groupBy('pole_id');
        $poles->each(function ($pole) use ($rmsLogs) {
            $pole->rmsLogs = $rmsLogs->get($pole->id, collect());
        });

        return view('poles.installed', compact('poles'));
    }

    // AJAX endpoint for DataTables server-side processing
    // public function getInstalledPolesData(Request $request)
    // {
    //     $query = Pole::with(['task.streetlight'])
    //         ->where('isInstallationDone', 1);

    //     // Apply filters
    //     if ($request->filled('project_manager')) {
    //         $query->whereHas('task', function ($q) use ($request) {
    //             $q->where('manager_id', $request->project_manager);
    //         });
    //     }

    //     if ($request->filled('site_engineer')) {
    //         $query->whereHas('task', function ($q) use ($request) {
    //             $q->where('engineer_id', $request->site_engineer);
    //         });
    //     }

    //     if ($request->filled('vendor')) {
    //         $query->whereHas('task', function ($q) use ($request) {
    //             $q->where('vendor_id', $request->vendor);
    //         });
    //     }

    //     if ($request->filled('project_id')) {
    //         $query->whereHas('task.streetlight', function ($q) use ($request) {
    //             $q->where('project_id', $request->project_id);
    //         });
    //     }

    //     // Get total count before filtering
    //     $totalRecords = $query->count();

    //     // Search functionality
    //     if ($request->filled('search.value')) {
    //         $search = $request->input('search.value');
    //         $query->where(function ($q) use ($search) {
    //             $q->where('complete_pole_number', 'like', "%{$search}%")
    //                 ->orWhere('luminary_qr', 'like', "%{$search}%")
    //                 ->orWhere('sim_number', 'like', "%{$search}%")
    //                 ->orWhere('battery_qr', 'like', "%{$search}%")
    //                 ->orWhere('panel_qr', 'like', "%{$search}%")
    //                 ->orWhereHas('task.streetlight', function ($sq) use ($search) {
    //                     $sq->where('block', 'like', "%{$search}%")
    //                         ->orWhere('panchayat', 'like', "%{$search}%");
    //                 });
    //         });
    //     }

    //     // Get filtered count
    //     $filteredRecords = $query->count();

    //     // Ordering
    //     $orderColumn = $request->input('order.0.column', 3); // Default to pole number
    //     $orderDirection = $request->input('order.0.dir', 'asc');
    //     $columns = ['id', 'block', 'panchayat', 'complete_pole_number', 'luminary_qr', 'sim_number', 'battery_qr', 'panel_qr'];

    //     if (isset($columns[$orderColumn])) {
    //         if (in_array($columns[$orderColumn], ['block', 'panchayat'])) {
    //             $query->join('streetlight_tasks', 'poles.task_id', '=', 'streetlight_tasks.id')
    //                 ->join('streetlights', 'streetlight_tasks.streetlight_id', '=', 'streetlights.id')
    //                 ->orderBy('streetlights.' . $columns[$orderColumn], $orderDirection)
    //                 ->select('poles.*');
    //         } else {
    //             $query->orderBy($columns[$orderColumn], $orderDirection);
    //         }
    //     }

    //     // Pagination
    //     $start = $request->input('start', 0);
    //     $length = $request->input('length', 50);
    //     $poles = $query->skip($start)->take($length)->get();

    //     // Format data for DataTables
    //     $data = $poles->map(function ($pole) {
    //         return [
    //             'checkbox' => '<input type="checkbox" class="pole-checkbox" value="' . $pole->id . '" />',
    //             'block' => $pole->task?->streetlight?->block ?? 'N/A',
    //             'panchayat' => $pole->task?->streetlight?->panchayat ?? 'N/A',
    //             'pole_number' => '<span class="text-primary" style="cursor:pointer;" onclick="locateOnMap(' . $pole->lat . ', ' . $pole->lng . ')">' . ($pole->complete_pole_number ?? 'N/A') . '</span>',
    //             'imei' => $pole->luminary_qr ?? 'N/A',
    //             'sim_number' => $pole->sim_number ?? 'N/A',
    //             'battery' => $pole->battery_qr ?? 'N/A',
    //             'panel' => $pole->panel_qr ?? 'N/A',
    //             'bill_raised' => '0',
    //             'rms' => $pole->rms_status ?? 'N/A',
    //             'actions' => '
    //                 <a href="' . route('poles.show', $pole->id) . '" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
    //                     <i class="mdi mdi-eye"></i>
    //                 </a>
    //                 <a href="' . route('poles.edit', $pole->id) . '" class="btn btn-icon btn-warning">
    //                     <i class="mdi mdi-pencil"></i>
    //                 </a>
    //                 <button type="button" class="btn btn-icon btn-danger delete-pole-btn" data-toggle="tooltip"
    //                     title="Delete Pole" data-id="' . $pole->id . '"
    //                     data-name="' . ($pole->complete_pole_number ?? 'this pole') . '"
    //                     data-url="' . route('poles.destroy', $pole->id) . '">
    //                     <i class="mdi mdi-delete"></i>
    //                 </button>
    //             '
    //         ];
    //     });

    //     return response()->json([
    //         'draw' => intval($request->input('draw')),
    //         'recordsTotal' => $totalRecords,
    //         'recordsFiltered' => $filteredRecords,
    //         'data' => $data
    //     ]);
    // }
    public function getInstalledPolesData(Request $request)
    {
        $query = Pole::with(['task.streetlight'])
            ->where('isInstallationDone', 1);

        if ($request->filled('project_manager')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('manager_id', $request->project_manager);
            });
        }

        if ($request->filled('site_engineer')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('engineer_id', $request->site_engineer);
            });
        }

        if ($request->filled('vendor')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('vendor_id', $request->vendor);
            });
        }

        if ($request->filled('project_id')) {
            $query->whereHas('task.streetlight', function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        // total before search
        $totalRecords = $query->count();

        // search
        if ($request->filled('search.value')) {
            $search = $request->input('search.value');
            $query->where(function ($q) use ($search) {
                $q->where('complete_pole_number', 'like', "%{$search}%")
                    ->orWhere('luminary_qr', 'like', "%{$search}%")
                    ->orWhere('sim_number', 'like', "%{$search}%")
                    ->orWhere('battery_qr', 'like', "%{$search}%")
                    ->orWhere('panel_qr', 'like', "%{$search}%")
                    ->orWhereHas('task.streetlight', function ($sq) use ($search) {
                        $sq->where('block', 'like', "%{$search}%")
                            ->orWhere('panchayat', 'like', "%{$search}%");
                    });
            });
        }

        $filteredRecords = $query->count();

        // ordering
        $orderColumn = $request->input('order.0.column', 3);
        $orderDirection = $request->input('order.0.dir', 'asc');
        $columns = ['id', 'block', 'panchayat', 'complete_pole_number', 'luminary_qr', 'sim_number', 'battery_qr', 'panel_qr'];

        if (isset($columns[$orderColumn])) {
            if (in_array($columns[$orderColumn], ['block', 'panchayat'])) {
                // Join for ordering by streetlight fields
                $poleTable = (new Pole)->getTable();
                $query->join('streetlight_tasks', $poleTable.'.task_id', '=', 'streetlight_tasks.id')
                    ->join('streetlights', 'streetlight_tasks.site_id', '=', 'streetlights.id')
                    ->groupBy($poleTable.'.id') // Prevent duplicates from join
                    ->orderBy('streetlights.'.$columns[$orderColumn], $orderDirection)
                    ->select($poleTable.'.*');
            } else {
                $poleTable = (new Pole)->getTable();
                $query->orderBy($poleTable.'.'.$columns[$orderColumn], $orderDirection);
            }
        }

        // pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 50);

        // If length is -1, get all records (for export)
        if ($length == -1) {
            $poles = $query->get();
        } else {
            $poles = $query->skip($start)->take($length)->get();
        }

        // Reload relationships after join (join can interfere with eager loading)
        $poles->load(['task.streetlight']);

        // shape data to match DataTables columns config
        $data = $poles->map(function ($pole) {
            return [
                'checkbox' => '<input type="checkbox" class="pole-checkbox" value="'.$pole->id.'" />',
                'district' => $pole->task?->streetlight?->district ?? 'N/A',
                'block' => $pole->task?->streetlight?->block ?? 'N/A',
                'panchayat' => $pole->task?->streetlight?->panchayat ?? 'N/A',
                'pole_number' => '<span class="text-primary" style="cursor:pointer;" onclick="locateOnMap('.$pole->lat.', '.$pole->lng.')">'.($pole->complete_pole_number ?? 'N/A').'</span>',
                'imei' => $pole->luminary_qr ?? 'N/A',
                'sim_number' => $pole->sim_number ?? 'N/A',
                'battery' => $pole->battery_qr ?? 'N/A',
                'panel' => $pole->panel_qr ?? 'N/A',
                'bill_raised' => '0',
                'rms' => $pole->rms_status ?? 'N/A',
                'actions' => '
                <a href="'.route('poles.show', $pole->id).'" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                    <i class="mdi mdi-eye"></i>
                </a>
                <a href="'.route('poles.edit', $pole->id).'" class="btn btn-icon btn-warning">
                    <i class="mdi mdi-pencil"></i>
                </a>
                <button type="button" class="btn btn-icon btn-danger delete-pole-btn" data-toggle="tooltip"
                    title="Delete Pole" data-id="'.$pole->id.'"
                    data-name="'.($pole->complete_pole_number ?? 'this pole').'"
                    data-url="'.route('poles.destroy', $pole->id).'">
                    <i class="mdi mdi-delete"></i>
                </button>
            ',
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    // Get Pole Details by ID 'Original'

    /*public function viewPoleDetails($id)
    {
        // Fetch the pole with the given ID along with its relationships
        $pole = Pole::with(['streetlight', 'task'])->findOrFail($id);
        Log::info($pole);

        // Decode survey images JSON (ensure it's an array)
        $surveyImages = [];
        if (!empty($pole->survey_image)) {
            $surveyImagesArray = json_decode($pole->survey_image, true); // Decode JSON string into an array

            if (is_array($surveyImagesArray)) { // Ensure it's an array before looping
                foreach ($surveyImagesArray as $image) {
                    $surveyImages[] = Storage::disk('s3')->url($image);
                }
            }
        }

        // Decode survey images JSON (ensure it's an array)
        $submissionImages = [];
        if (!empty($pole->submission_image)) {
            $submissionImagesArray = json_decode($pole->submission_image, true); // Decode JSON string into an array

            if (is_array($submissionImagesArray)) { // Ensure it's an array before looping
                foreach ($submissionImagesArray as $image) {
                    $submissionImages[] = Storage::disk('s3')->url($image);
                }
            }
        }

        // Fetch related users (Installer, Project Manager, Site Engineer) from the latest task
        $latestTask = $pole->task()->first(); // Get latest assigned task
        Log::info($latestTask);

        $installer = $latestTask?->vendor;    // Installer
        $projectManager = $latestTask?->manager; // Project Manager
        $siteEngineer = $latestTask?->engineer;  // Site Engineer

        // Return the view with the pole details
        return view('poles.show', compact('pole', 'surveyImages', 'submissionImages', 'installer', 'projectManager', 'siteEngineer'));
    }
    */
    // Get Pole Details by ID 'Y'
    public function viewPoleDetails($id)
    {
        // Fetch the pole with the given ID along with its relationships
        // Note: Pole doesn't have direct streetlight relationship - access via task.streetlight
        $pole = Pole::with(['task', 'task.streetlight'])->findOrFail($id);
        $surveyImages = $this->processImagesFromJson($pole->survey_image);
        $submissionImages = $this->processImagesFromJson($pole->submission_image);
        // Fetch related users from the latest task
        $latestTask = $pole->task;
        $installer = $latestTask?->vendor;
        $projectManager = $latestTask?->manager;
        $siteEngineer = $latestTask?->engineer;

        return view('poles.show', compact('pole', 'surveyImages', 'submissionImages', 'installer', 'projectManager', 'siteEngineer'));
    }

    // 👇 Add this helper inside the same controller
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
                    $imageUrls[] = Storage::disk('s3')->url($image);
                } catch (\Exception $e) {
                    Log::error("Failed to generate S3 URL for image: $image", ['exception' => $e->getMessage()]);
                }
            }
        }

        return $imageUrls;
    }

    // Api to export poles in excel in vendor/staff app
    public function exportPoles($vendor_id)
    {
        $vendor = User::find($vendor_id);
        $vendorName = $vendor ? $vendor->name : 'Unknown';

        // Fetch surveyed poles
        $surveyed_poles = Pole::whereHas('task', function ($query) use ($vendor_id) {
            $query->where('vendor_id', $vendor_id);
        })->where('isSurveyDone', 1)
            ->where('isInstallationDone', 0)
            ->with(['task.site', 'task.engineer', 'task.manager'])
            ->get();

        $surveyed_data = $surveyed_poles->map(function ($pole) {
            return [
                'Pole ID' => $pole->id,
                'Complete Pole Number' => $pole->complete_pole_number,
                'Ward' => $pole->task->site->ward ?? null,
                'Panchayat' => $pole->task->site->panchayat ?? null,
                'Block' => $pole->task->site->block ?? null,
                'District' => $pole->task->site->district ?? null,
                'State' => $pole->task->site->state ?? null,
                'Beneficiary' => $pole->beneficiary,
                'Latitude' => $pole->lat,
                'Longitude' => $pole->lng,
                'Remarks' => $pole->remarks,
                'Survey Image URLs' => implode(', ', collect(json_decode($pole->survey_image, true) ?? [])->map(fn ($image) => Storage::disk('s3')->url($image))->toArray()),
                'Submission Image URLs' => implode(', ', collect($pole->submission_image ?? [])->map(fn ($image) => Storage::disk('s3')->url($image))->toArray()),
                'Site Engineer' => $pole->task->engineer->first_name ?? null,
                'Project Manager' => $pole->task->manager->name ?? null,
                'Assigned Date' => $pole->created_at,
                'Submission Date' => $pole->updated_at,
            ];
        });

        // Fetch installed poles
        $installed_poles = Pole::whereHas('task', function ($query) use ($vendor_id) {
            $query->where('vendor_id', $vendor_id);
        })->where('isInstallationDone', 1)
            ->with(['task.site', 'task.engineer', 'task.manager'])
            ->get();

        $installed_data = $installed_poles->map(function ($pole) {
            return [
                'Pole ID' => $pole->id,
                'Complete Pole Number' => $pole->complete_pole_number,
                'Ward' => $pole->task->site->ward ?? null,
                'Panchayat' => $pole->task->site->panchayat ?? null,
                'Block' => $pole->task->site->block ?? null,
                'District' => $pole->task->site->district ?? null,
                'State' => $pole->task->site->state ?? null,
                'Beneficiary' => $pole->beneficiary,
                'Latitude' => $pole->lat,
                'Longitude' => $pole->lng,
                'Remarks' => $pole->remarks,
                'Survey Image URLs' => implode(', ', collect($pole->survey_image ?? [])->map(fn ($image) => Storage::disk('s3')->url($image))->toArray()),
                'Submission Image URLs' => implode(', ', collect($pole->submission_image ?? [])->map(fn ($image) => Storage::disk('s3')->url($image))->toArray()),
                'Site Engineer' => $pole->task->engineer->name ?? null,
                'Project Manager' => $pole->task->manager->name ?? null,
            ];
        });

        // Use ExcelHelper
        $fileName = "survey_status_{$vendorName}.xlsx";
        $sheets = [
            'Surveyed Poles' => $surveyed_data->toArray(),
            'Installed Poles' => $installed_data->toArray(),
        ];

        return ExcelHelper::exportMultipleSheets($sheets, $fileName);
    }

    // Api to update all poles to RMS at once
    public function sendDataToRMS(Request $request)
    {
        // Optional filters if you want to limit by installed or surveyed poles
        $validated = $request->validate([
            'filter' => 'nullable|string|in:all,surveyed,installed',
        ]);

        $query = Pole::query();

        if ($validated['filter'] ?? null) {
            switch ($validated['filter']) {
                case 'surveyed':
                    $query->where('isSurveyDone', true);
                    break;
                case 'installed':
                    $query->where('isInstallationDone', true);
                    break;
                case 'all':
                default:
                    // No filter
                    break;
            }
        }

        $poles = $query->get();
        $responses = [];

        foreach ($poles as $pole) {
            try {

                $task = StreetlightTask::findOrFail($pole->task_id);
                $streetlight = Streetlight::findOrFail($task->site_id);
                $engineer = $task->engineer;
                $approved_by = $engineer->firstName.' '.$engineer->lastName;
                Log::info('Sending data now');
                RemoteApiHelper::sendPoleDataToRemoteServer($pole, $streetlight, $approved_by);

                $responses[] = [
                    'pole_id' => $pole->id,
                    'status' => 'success',
                ];
            } catch (\Exception $e) {
                Log::error('Failed to send pole data to RMS', [
                    'pole_id' => $pole->id,
                    'error' => $e->getMessage(),
                ]);

                $responses[] = [
                    'pole_id' => $pole->id,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => 'Pole data sync process completed.',
            'result' => $responses,
        ]);
    }

    public function editPoleDetails(Request $request, $id)
    {
        // Return view to update pole
        $data = $request->all();

        return view('poles.edit', compact('data'));
    }

    public function updatePoleDetails(Request $request, $id)
    {
        // Apply logic and return to show pole details
        // $id=Auth()->id();

    }
}
