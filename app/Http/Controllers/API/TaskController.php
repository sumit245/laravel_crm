<?php

namespace App\Http\Controllers\API;

use App\Helpers\ExcelHelper;
use App\Helpers\RemoteApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProjectPoleController;
use App\Models\Project;
use App\Services\Inventory\InventoryService;
use App\Services\Inventory\InventoryHistoryService;
use App\Services\Rms\RmsSyncService;
use App\Models\InventoryDispatch;
use App\Models\Site;
use App\Models\Streetlight;
use App\Models\Pole;
use App\Models\StreetlightSiteWard;
use App\Models\StreetlightTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Concerns\HandlesColumnFilters;

class TaskController extends Controller
{
    use HandlesColumnFilters;
    protected InventoryService $inventoryService;
    protected InventoryHistoryService $historyService;
    private ?array $streelightPoleColumns = null;

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
                            $uploadedFiles[] = $this->uploadToS3($file, 'tasks/' . $task->id);
                        } else {
                            return response()->json(['error' => 'Invalid image format.'], 400);
                        }
                    }
                } elseif ($images instanceof \Illuminate\Http\UploadedFile) {
                    // Handle single file upload (PDF or image)
                    $extension = $images->getClientOriginalExtension();

                    if (in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'])) {
                        $uploadedFiles[] = $this->uploadToS3($images, 'tasks/' . $task->id);
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

                if (!empty($siteUpdateData)) {
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
                $fileName = time() . '_' . $file['name'];

                // Upload to S3 using the file contents
                Storage::disk('s3')->put($path . '/' . $fileName, $fileContents);

                // Return the S3 file path
                return Storage::disk('s3')->url($path . '/' . $fileName);
            } elseif ($file instanceof \Illuminate\Http\UploadedFile) {
                // Handle standard UploadedFile objects
                $fileName = time() . '_' . $file->getClientOriginalName();

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

                    if ($count < 2)
                        return null; // Ensure the format is valid
    
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
                'installable_poles' => $installablePoles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getSitesForVendor($vendorId)
    {
        $tasks = StreetlightTask::with('site')
            ->where('vendor_id', $vendorId)
            ->get();

        $sites = $tasks
            ->filter(fn ($task) => $task->site !== null)
            ->map(function ($task) {
                $site = $task->site;

                return [
                    'id' => $site->id,
                    'task_id' => $task->id,
                    'project_id' => $task->project_id,
                    'state' => $site->state,
                    'district' => $site->district,
                    'block' => $site->block,
                    'panchayat' => $site->panchayat,
                    'ward' => $task->allotted_wards,
                    'district_code' => $site->district_code,
                    'block_code' => $site->block_code,
                    'panchayat_code' => $site->panchayat_code,
                    'mukhiya_contact' => $site->mukhiya_contact,
                    'number_of_surveyed_poles' => $site->number_of_surveyed_poles,
                    'number_of_installed_poles' => $site->number_of_installed_poles,
                    'total_poles' => $site->total_poles,
                    'status' => $task->status,
                    'start_date' => optional($task->start_date)->format('d/m/Y'),
                    'end_date' => optional($task->end_date)->format('d/m/Y'),
                ];
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'vendor_id' => $vendorId,
            'sites' => $sites,
        ], 200);
    }

    public function approveTask($id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $task->update(['status' => 'Completed']);
        return response()->json([
            'message' => 'Task approved successfully',
            'task' => $task
        ]);
    }

    // Update poles survey or install
    public function submitStreetlightTasks(Request $request)
    {
        try {
            // ✅ Step 1: Validation
            $validated = $request->validate([
                'task_id' => 'required|integer',
                'complete_pole_number' => 'required|string|max:255',
                'ward_name' => 'nullable|string|max:255',
                'survey_image' => 'nullable',
                'isSurveyDone' => 'nullable',
                'isNetworkAvailable' => 'nullable',
                'isInstallationDone' => 'nullable',
                'beneficiary' => 'nullable|string|max:255',
                'beneficiary_contact' => 'nullable|string|max:20',
                'remarks' => 'nullable|string',
                'luminary_qr' => 'nullable|string|max:255',
                'sim_number' => 'nullable|string|max:200',
                'panel_qr' => 'nullable|string|max:255',
                'battery_qr' => 'nullable|string|max:255',
                'submission_image' => 'nullable',
                'lat' => 'nullable|numeric',
                'lng' => 'nullable|numeric',
            ]);

            $isSurveyDone = $this->toApiBoolean($validated['isSurveyDone'] ?? null);
            $isNetworkAvailable = $this->toApiBoolean($validated['isNetworkAvailable'] ?? null);
            $isInstallationDone = $this->toApiBoolean($validated['isInstallationDone'] ?? null);

            // ✅ Step 2: Fetch task & site
            $task = $this->resolveStreetlightSubmissionTask(
                (int) $validated['task_id'],
                $validated['complete_pole_number'],
                $validated['ward_name'] ?? null
            );

            if (!$task) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'task_id' => ['The selected task id is invalid.'],
                ]);
            }

            $validated['task_id'] = $task->id;
            $approved_by = trim(($task->engineer?->firstName ?? '') . ' ' . ($task->engineer?->lastName ?? ''));
            $streetlight = Streetlight::findOrFail($task->site_id);
            $siteWard = $this->resolveSiteWardForPole($streetlight, $validated['ward_name'] ?? null, $validated['complete_pole_number']);

            // ✅ Step 3: Create or get pole (locked to prevent race conditions)
            $pole = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $streetlight, $siteWard, $isSurveyDone, $isInstallationDone) {
                $pole = Pole::where('task_id', $validated['task_id'])
                    ->where('complete_pole_number', $validated['complete_pole_number'])
                    ->lockForUpdate()
                    ->first();

                if (!$pole) {
                    if ($isInstallationDone) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'complete_pole_number' => ['Pole not found. Survey must be submitted before installation.'],
                        ]);
                    }

                    $poleAttributes = [
                        'task_id'             => $validated['task_id'],
                        'complete_pole_number'=> $validated['complete_pole_number'],
                        'ward_name'           => $validated['ward_name'] ?? null,
                        'beneficiary'         => $validated['beneficiary'] ?? null,
                        'beneficiary_contact' => $validated['beneficiary_contact'] ?? null,
                        'remarks'             => $validated['remarks'] ?? null,
                        'isSurveyDone'        => $isSurveyDone,
                        'isInstallationDone'  => false,
                        'lat'                 => $validated['lat'] ?? null,
                        'lng'                 => $validated['lng'] ?? null,
                    ];

                    if ($this->streelightPoleHasColumn('streetlight_site_ward_id')) {
                        $poleAttributes['streetlight_site_ward_id'] = $siteWard?->id;
                    }

                    if ($this->streelightPoleHasColumn('ward_type')) {
                        $poleAttributes['ward_type'] = $siteWard?->ward_type;
                    }

                    if ($this->streelightPoleHasColumn('ward_number')) {
                        $poleAttributes['ward_number'] = $siteWard?->ward_number;
                    }

                    if ($this->streelightPoleHasColumn('pole_sequence')) {
                        $poleAttributes['pole_sequence'] = $this->extractPoleSequence($validated['complete_pole_number']);
                    }

                    $pole = Pole::create($poleAttributes);
                    if ($isSurveyDone) {
                        $streetlight->increment('number_of_surveyed_poles');
                    }
                }

                return $pole;
            });

            // ✅ Step 4: Upload images
            foreach (['survey_image' => 'survey', 'submission_image' => 'installation'] as $field => $folder) {
                if ($request->hasFile($field)) {
                    $files = $request->file($field);
                    $files = is_array($files) ? $files : [$files];
                    $images = collect($files)->filter()->map(
                        fn($img) =>
                        $this->uploadToS3($img, "streetlights/{$folder}/{$pole->id}")
                    )->values();
                    $pole->update([$field => json_encode($images)]);
                }
            }

            // ✅ Step 5: Update survey data
            if ($isSurveyDone && !$pole->isSurveyDone) {
                $pole->update([
                    'isSurveyDone' => true,
                    'surveyed_at' => now(),
                    'beneficiary' => $validated['beneficiary'] ?? null,
                    'beneficiary_contact' => $validated['beneficiary_contact'] ?? null,
                    'remarks' => $validated['remarks'] ?? null,
                    'isNetworkAvailable' => $isNetworkAvailable,
                    'lat' => $validated['lat'] ?? $pole->lat,
                    'lng' => $validated['lng'] ?? $pole->lng,
                ]);
                $streetlight->increment('number_of_surveyed_poles');
            } elseif ($isSurveyDone) {
                $pole->update([
                    'beneficiary' => $validated['beneficiary'] ?? $pole->beneficiary,
                    'beneficiary_contact' => $validated['beneficiary_contact'] ?? $pole->beneficiary_contact,
                    'remarks' => $validated['remarks'] ?? $pole->remarks,
                    'isNetworkAvailable' => $isNetworkAvailable,
                    'lat' => $validated['lat'] ?? $pole->lat,
                    'lng' => $validated['lng'] ?? $pole->lng,
                ]);
            }

            // ✅ Step 6: Update installation data
            if ($isInstallationDone && !$pole->isInstallationDone) {
                // ✅ Step 7: Mark inventory as consumed with district validation
                $serials = array_filter([
                    $validated['luminary_qr'] ?? null,
                    $validated['panel_qr'] ?? null,
                    $validated['battery_qr'] ?? null,
                ]);
                $dispatches = collect();

                if (!empty($serials)) {
                    // Get pole's district
                    $poleDistrict = $streetlight->district;

                    // Validate each serial number's dispatch district matches pole's district
                    $dispatches = InventoryDispatch::whereIn('serial_number', $serials)
                        ->where('vendor_id', $task->vendor_id)
                        ->where('isDispatched', true)
                        ->where('is_consumed', false)
                        ->get();

                    $missingSerials = array_values(array_diff($serials, $dispatches->pluck('serial_number')->all()));
                    if (!empty($missingSerials)) {
                        return response()->json([
                            'message' => 'One or more installation items were not found in dispatch (or already consumed).',
                            'error' => 'inventory_not_found',
                            'missing_serials' => $missingSerials,
                        ], 422);
                    }

                    foreach ($dispatches as $dispatch) {
                        // Get project's districts
                        $projectDistricts = $this->inventoryService->getProjectDistricts($dispatch->project_id);

                        // Check if pole's district is in project's districts
                        if (!in_array($poleDistrict, $projectDistricts)) {
                            $project = \App\Models\Project::find($dispatch->project_id);
                            return response()->json([
                                'message' => "Inventory dispatched for {$project->project_name} cannot be used in district {$poleDistrict}",
                                'error' => 'district_mismatch',
                                'pole_district' => $poleDistrict,
                                'project_name' => $project->project_name ?? 'Unknown Project',
                            ], 400);
                        }
                    }
                }

                \Illuminate\Support\Facades\DB::transaction(function () use ($pole, $streetlight, $task, $validated, $dispatches) {
                    $lockedPole = Pole::whereKey($pole->id)->lockForUpdate()->firstOrFail();

                    if ($lockedPole->isInstallationDone) {
                        return;
                    }

                    $lockedPole->update([
                        'isInstallationDone' => true,
                        'installed_at' => now(),
                        'vendor_id' => $task->vendor_id,
                        'luminary_qr' => $validated['luminary_qr'] ?? null,
                        'sim_number' => $validated['sim_number'] ?? null,
                        'panel_qr' => $validated['panel_qr'] ?? null,
                        'battery_qr' => $validated['battery_qr'] ?? null,
                    ]);

                    $streetlight->increment('number_of_installed_poles');

                    foreach ($dispatches as $dispatch) {
                        $dispatch->update([
                            'is_consumed' => true,
                            'streetlight_pole_id' => $lockedPole->id,
                        ]);

                        // Log history
                        $project = \App\Models\Project::find($dispatch->project_id);
                        $inventoryType = ($project && $project->project_type == 1) ? 'streetlight' : 'rooftop';
                        $this->historyService->logConsumed($dispatch, $inventoryType, $lockedPole);
                    }
                });

                $pole->refresh();
                RemoteApiHelper::sendPoleDataToRemoteServer($pole, $streetlight, $approved_by);
            }

            Log::info('Pole Submitted:', $pole->toArray());

            return response()->json([
                'message' => 'Pole details submitted successfully!',
                'pole' => $pole,
                'task' => $task,
            ]);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            Log::warning('Streetlight task submission validation failed', [
                'task_id' => $request->input('task_id'),
                'complete_pole_number' => $request->input('complete_pole_number'),
                'errors' => $exception->errors(),
            ]);

            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $exception->errors(),
            ], 422);
        } catch (\Throwable $exception) {
            Log::error('Streetlight task submission failed', [
                'task_id' => $request->input('task_id'),
                'complete_pole_number' => $request->input('complete_pole_number'),
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to submit pole details due to an internal error.',
            ], 500);
        }
    }

    // Controller to get details of pole by Id
    public function getPoleDetails(Request $request)
    {
        $id = $request->pole_id;
        $pole = Pole::findOrFail($id);
        return response()->json([
            'pole' => $pole
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
            'installed_poles' => $installed_poles
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
                'ward' => $pole->task?->site?->ward ?? null,
                'panchayat' => $pole->task?->site?->panchayat ?? null,
                'block' => $pole->task?->site?->block ?? null,
                'district' => $pole->task?->site?->district ?? null,
                'state' => $pole->task?->site?->state ?? null,
                'beneficiary' => $pole->beneficiary,
                'beneficiary_contact' => $pole->beneficiary_contact,
                "isSurveyed" => true,
                "isInstalled" => false,
                'installed_location' => [
                    'lat' => $pole->lat,
                    'lng' => $pole->lng,
                ],
                'remarks' => $pole->remarks,
                'survey_image' => collect(json_decode($pole->survey_image, true) ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray(),
                'submission_image' => collect(json_decode($pole->submission_image, true) ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray(),
                'site_engineer_name' => $pole->task?->engineer?->first_name ?? null,
                'project_manager_name' => $pole->task?->manager?->name ?? null,
                'assigned_date' => $pole->created_at,
                'submission_date' => $pole->updated_at,
                'status' => $pole->status
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
                "isSurveyed" => true,
                "isInstalled" => true,
                'sim_number' => $pole->sim_number,
                'panchayat' => $pole->task?->site?->panchayat ?? null,
                'block' => $pole->task?->site?->block ?? null,
                'district' => $pole->task?->site?->district ?? null,
                'state' => $pole->task?->site?->state ?? null,
                'beneficiary' => $pole->beneficiary,
                'installed_location' => [
                    'lat' => $pole->lat,
                    'lng' => $pole->lng,
                ],
                'remarks' => $pole->remarks,
                'survey_image' => collect(json_decode($pole->survey_image, true) ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray(),
                'submission_image' => collect(json_decode($pole->submission_image, true) ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray(),
                'site_engineer_name' => $pole->task?->engineer?->name ?? null,
                'project_manager_name' => $pole->task?->manager?->name ?? null,
                'status' => $pole->status
            ];
        });

        return response()->json([
            'message' => 'Installed poles for Vendor',
            'surveyed_poles' => $transformed_poles,
            'installed_poles' => $transformed__installed_poles
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
            'installed_poles' => $installed_poles
        ], 200);
    }
    // Fetch Surveyed Poles based on user role
    public function getSurveyedPoles(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        if ($request->filled('project_id')) {
            $query = $request->except(['project_id']);
            $qs = http_build_query($query);

            return redirect()->to(
                route('projects.show', $request->project_id) . ($qs ? '?' . $qs : '') . '#surveyed-poles'
            );
        }

        return redirect()
            ->route('projects.index')
            ->with('warning', 'Select a project, then open Surveyed Poles from the project overview.');
    }

    // Fetch Installed Poles based on user role
    public function getInstalledPoles(Request $request)
    {
        if ($request->filled('project_id')) {
            $query = $request->except(['project_id']);
            $qs = http_build_query($query);

            return redirect()->to(
                route('projects.show', $request->project_id) . ($qs ? '?' . $qs : '') . '#installed-poles'
            );
        }

        return redirect()
            ->route('projects.index')
            ->with('warning', 'Select a project, then open Installed Poles from the project overview.');
    }

    public function getInstalledPolesData(Request $request)
    {
        if ($request->filled('project_id')) {
            $project = Project::findOrFail($request->project_id);

            return app(ProjectPoleController::class)->installedData($project, $request);
        }

        $query = Pole::with(['task.site'])
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
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        if ($request->filled('panchayat')) {
            $query->whereHas('task.site', function ($q) use ($request) {
                $q->where('panchayat', $request->panchayat);
            });
        }

        $this->applyInstalledPolesGeoFilters($query, $request);

        if ($request->filled('ward')) {
            $query->whereHas('task.site', function ($q) use ($request) {
                $q->where('ward', 'like', '%' . $request->ward . '%');
            });
        }

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
                    ->orWhereHas('task.site', function ($sq) use ($search) {
                        $sq->where('district', 'like', "%{$search}%")
                            ->orWhere('block', 'like', "%{$search}%")
                            ->orWhere('panchayat', 'like', "%{$search}%");
                    });
            });
        }

        // ── Per-column filters from the SAP-style filter popup ──────────────
        // DOM layout (bulkDelete ON): 0=checkbox, 1=Pole Number, 2=Beneficiary,
        // 3=Beneficiary Contact, 4-6=District/Block/Panchayat (site, skipped),
        // 7=IMEI, 8=SIM, 9=Battery, 10=Panel, 11=Bill Raised (skipped), 12=RMS (skipped)
        $cfColMap = [
            1 => 'complete_pole_number',
            2 => 'beneficiary',
            3 => 'beneficiary_contact',
            7 => 'luminary_qr',
            8 => 'sim_number',
            9 => 'battery_qr',
            10 => 'panel_qr',
        ];
        $columnFilters = $this->parseColumnFilters($request);
        $this->applyColumnFilters($query, $columnFilters, $cfColMap);
        // ─────────────────────────────────────────────────────────────────────

        $filteredRecords = $query->count();

        // ordering
        $orderColumn = $request->input('order.0.column', 3);
        $orderDirection = $request->input('order.0.dir', 'asc');
        $columns = [
            'id',
            'complete_pole_number',
            'beneficiary',
            'beneficiary_contact',
            'luminary_qr',
            'sim_number',
            'battery_qr',
            'panel_qr',
            'billed',
            'rms',
        ];

        if (isset($columns[$orderColumn])) {
            if (!in_array($columns[$orderColumn], ['billed', 'rms'], true)) {
                $poleTable = (new Pole())->getTable();
                $query->orderBy($poleTable . '.' . $columns[$orderColumn], $orderDirection);
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

        $poles->load(['task.site']);

        // shape data to match DataTables columns config
        $data = $poles->map(function ($pole) {
            $poleNumber = e($pole->complete_pole_number ?? 'N/A');

            if ($pole->lat && $pole->lng) {
                $poleNumber = '<span class="text-primary" style="cursor:pointer;" onclick="locateOnMap(' . (float) $pole->lat . ', ' . (float) $pole->lng . ')">' . $poleNumber . '</span>';
            }

            $billRaised = $pole->task && $pole->task->billed
                ? '<span class="badge badge-success">Yes</span>'
                : '<span class="badge badge-readable badge-no">No</span>';

            return [
                '<input type="checkbox" class="row-checkbox" value="' . (int) $pole->id . '" />',
                $poleNumber,
                e($pole->beneficiary ?? 'N/A'),
                e($pole->beneficiary_contact ?? 'N/A'),
                e($pole->task?->site?->district ?? 'N/A'),
                e($pole->task?->site?->block ?? 'N/A'),
                e($pole->task?->site?->panchayat ?? 'N/A'),
                e($pole->luminary_qr ?? 'N/A'),
                e($pole->sim_number ?? 'N/A'),
                e($pole->battery_qr ?? 'N/A'),
                e($pole->panel_qr ?? 'N/A'),
                $billRaised,
                '<span class="badge badge-readable badge-not-pushed">Not Pushed</span>',
                '
                <a href="' . route('poles.show', $pole->id) . '" class="btn btn-icon btn-info" data-toggle="tooltip" title="View Details">
                    <i class="mdi mdi-eye"></i>
                </a>
                <a href="' . route('poles.edit', $pole->id) . '" class="btn btn-icon btn-warning">
                    <i class="mdi mdi-pencil"></i>
                </a>
                <button type="button" class="btn btn-icon btn-danger delete-pole-btn" data-toggle="tooltip"
                    title="Delete Pole" data-id="' . $pole->id . '"
                    data-name="' . ($pole->complete_pole_number ?? 'this pole') . '"
                    data-url="' . route('poles.destroy', $pole->id) . '">
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

    private function applyInstalledPolesGeoFilters($query, Request $request): void
    {
        if ($request->filled('district')) {
            $query->whereHas('task.site', fn ($q) => $q->where('district', $request->district));
        }

        if ($request->filled('block')) {
            $query->whereHas('task.site', fn ($q) => $q->where('block', $request->block));
        }
    }

    // Get Pole Details by ID 'Original'

    // Get Pole Details by ID 'Y'
    public function viewPoleDetails($id)
    {
        // Fetch the pole with the given ID along with its relationships
        $pole = Pole::with(['task.streetlight', 'task.vendor', 'task.manager', 'task.engineer'])->findOrFail($id);
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

        if (!is_array($imagesArray)) {
            return $imageUrls;
        }

        // Check S3 config is present before attempting to generate URLs
        $bucket = config('filesystems.disks.s3.bucket');
        if (empty($bucket)) {
            return $imageUrls;
        }

        foreach ($imagesArray as $image) {
            if (!empty($image)) {
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
    public function exportPoles(Request $request, $vendorId = null)
    {
        $vendorId = $vendorId ?? $request->query('vendor') ?? $request->query('vendor_id');

        if (!$vendorId) {
            return redirect()->back()->with('error', 'Please select a vendor before exporting poles.');
        }

        $vendor = User::find($vendorId);
        $vendorName = $vendor ? $vendor->name : 'Unknown';

        // Fetch surveyed poles
        $surveyedQuery = Pole::whereHas('task', function ($query) use ($vendorId, $request) {
            $query->where('vendor_id', $vendorId);

            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->filled('panchayat')) {
                $query->whereHas('site', function ($siteQuery) use ($request) {
                    $siteQuery->where('panchayat', $request->panchayat);
                });
            }
        })->where('isSurveyDone', 1)
            ->where('isInstallationDone', 0)
            ->with(['task.site', 'task.engineer', 'task.manager']);

        $surveyed_poles = $surveyedQuery->get();

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
                'Survey Image URLs' => implode(", ", collect(json_decode($pole->survey_image, true) ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray()),
                'Submission Image URLs' => implode(", ", collect($pole->submission_image ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray()),
                'Site Engineer' => $pole->task->engineer->first_name ?? null,
                'Project Manager' => $pole->task->manager->name ?? null,
                'Assigned Date' => $pole->created_at,
                'Submission Date' => $pole->updated_at,
            ];
        });

        // Fetch installed poles
        $installedQuery = Pole::whereHas('task', function ($query) use ($vendorId, $request) {
            $query->where('vendor_id', $vendorId);

            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            if ($request->filled('panchayat')) {
                $query->whereHas('site', function ($siteQuery) use ($request) {
                    $siteQuery->where('panchayat', $request->panchayat);
                });
            }
        })->where('isInstallationDone', 1)
            ->with(['task.site', 'task.engineer', 'task.manager']);

        $installed_poles = $installedQuery->get();

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
                'Survey Image URLs' => implode(", ", collect($pole->survey_image ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray()),
                'Submission Image URLs' => implode(", ", collect($pole->submission_image ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray()),
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
    public function sendDataToRMS(Request $request, RmsSyncService $rmsSyncService)
    {
        $validated = $request->validate([
            'filter' => 'nullable|string|in:all,surveyed,installed',
        ]);

        $scope = [
            'source' => 'api_task_send_to_rms',
            'filter' => $validated['filter'] ?? 'all',
        ];

        $batch = $rmsSyncService->queueSync($scope, Auth::id());

        return response()->json(
            [
                'message' => 'RMS sync queued successfully.',
                'batch_id' => $batch->id,
                'status' => $batch->status,
                'status_url' => route('api.rms.sync.status', $batch),
            ],
            202
        );
    }

    public function editPoleDetails(Request $request, $id)
    {
        // Return view to update pole
        $data = $request->all();
        return view('poles.edit', compact('data'));
    }

    private function resolveSiteWardForPole(Streetlight $site, ?string $wardName, ?string $completePoleNumber): ?StreetlightSiteWard
    {
        if (!Schema::hasTable('streetlight_site_wards')) {
            return null;
        }

        $wardType = str_contains(strtoupper((string) $wardName), 'GP') ? StreetlightSiteWard::TYPE_GP : StreetlightSiteWard::TYPE_NORMAL;
        $wardNumber = preg_replace('/\D/', '', (string) $wardName);

        if ($wardNumber === '') {
            $parts = collect(explode('/', (string) $completePoleNumber))->filter()->values();
            $wardPart = $parts->count() >= 2 ? $parts[$parts->count() - 2] : '';
            $wardType = str_contains(strtoupper($wardPart), 'GP') ? StreetlightSiteWard::TYPE_GP : $wardType;
            $wardNumber = preg_replace('/\D/', '', (string) $wardPart);
        }

        if ($wardNumber === '') {
            return null;
        }

        return StreetlightSiteWard::firstOrCreate(
            [
                'streetlight_id' => $site->id,
                'ward_type' => $wardType,
                'ward_number' => $wardNumber,
            ],
            [
                'planned_poles' => $wardType === StreetlightSiteWard::TYPE_GP ? 10 : 10,
                'source' => 'api',
            ]
        );
    }

    private function streelightPoleHasColumn(string $column): bool
    {
        if ($this->streelightPoleColumns === null) {
            $this->streelightPoleColumns = Schema::hasTable('streelight_poles')
                ? array_flip(Schema::getColumnListing('streelight_poles'))
                : [];
        }

        return isset($this->streelightPoleColumns[$column]);
    }

    private function toApiBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }

    private function resolveStreetlightSubmissionTask(int $submittedId, string $completePoleNumber, ?string $wardName): ?StreetlightTask
    {
        $task = StreetlightTask::find($submittedId);

        if ($task) {
            return $task;
        }

        $existingPole = Pole::where('complete_pole_number', $completePoleNumber)->first();

        if ($existingPole?->task) {
            return $existingPole->task;
        }

        $siteTasks = StreetlightTask::where('site_id', $submittedId)
            ->orderByRaw("FIELD(status, 'In Progress', 'Pending', 'Blocked', 'Completed')")
            ->get();

        if ($siteTasks->count() === 1) {
            return $siteTasks->first();
        }

        if ($siteTasks->isNotEmpty()) {
            $wardNumber = $this->extractWardNumber($wardName, $completePoleNumber);

            if ($wardNumber !== null) {
                $wardTask = $siteTasks->first(function (StreetlightTask $task) use ($wardNumber) {
                    $allottedWards = collect(explode(',', (string) $task->allotted_wards))
                        ->map(fn ($ward) => preg_replace('/\D/', '', trim((string) $ward)))
                        ->filter()
                        ->map(fn ($ward) => (int) $ward)
                        ->all();

                    return in_array($wardNumber, $allottedWards, true);
                });

                if ($wardTask) {
                    return $wardTask;
                }
            }

            return $siteTasks->first();
        }

        $submittedPole = Pole::find($submittedId);

        return $submittedPole?->task;
    }

    private function extractWardNumber(?string $wardName, ?string $completePoleNumber): ?int
    {
        $wardDigits = preg_replace('/\D/', '', (string) $wardName);

        if ($wardDigits !== '') {
            return (int) $wardDigits;
        }

        $parts = collect(explode('/', (string) $completePoleNumber))->filter()->values();
        $wardPart = $parts->count() >= 2 ? $parts[$parts->count() - 2] : '';
        $wardDigits = preg_replace('/\D/', '', (string) $wardPart);

        return $wardDigits === '' ? null : (int) $wardDigits;
    }

    private function extractPoleSequence(?string $completePoleNumber): ?int
    {
        $last = collect(explode('/', (string) $completePoleNumber))->filter()->last();
        $digits = preg_replace('/\D/', '', (string) $last);

        return $digits === '' ? null : (int) $digits;
    }
}
