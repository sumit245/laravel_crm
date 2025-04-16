<?php

namespace App\Http\Controllers\API;

use App\Helpers\ExcelHelper;
use App\Helpers\RemoteApiHelper;
use App\Http\Controllers\Controller;
use App\Models\InventoryDispatch;
use App\Models\Site;
use App\Models\Streetlight;
use App\Models\Pole;
use App\Models\StreetlightTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
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
                'sites'       => 'required|array',
                'activity'    => 'required|string',
                'engineer_id' => 'required|exists:users,id',
                'start_date'  => 'required|date',
                'end_date'    => 'required|date|after_or_equal:start_date',
            ]);

            $task = Task::create($validated);
            return response()->json([
                'message' => 'Task created successfully',
                'task'    => $task,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
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
                'message'        => 'Task updated successfully.',
                'task'           => $task,
                'uploaded_files' => $uploadedFiles,
            ]);
        } catch (\Exception $e) {
            // Log the exception
            return response()->json([
                'error'   => 'An error occurred during the update process.',
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
                $fileName     = time() . '_' . $file['name'];

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
                'error'   => 'An error occurred while uploading the file.',
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

                    if ($count < 2) return null; // Ensure the format is valid

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
        // Fetch tasks where the given vendor_id matches and eager load the site relationship
        $tasks = Task::with('site')
            ->where('vendor_id', $vendorId)
            ->get();

        // Extract unique sites from the tasks
        $sites = $tasks->pluck('site')->unique('id')->values();

        // Return the response
        return response()->json([
            'status'    => 'success',
            'vendor_id' => $vendorId,
            'sites'     => $sites,
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
        // ✅ Step 1: Validation
        $validated = $request->validate([
            'task_id'             => 'required|exists:streetlight_tasks,id',
            'complete_pole_number' => 'required|string|max:255',
            'ward_name'           => 'nullable|string|max:255',
            'survey_image'        => 'nullable|array',
            'isSurveyDone'      => 'nullable|string|in:true,false',
            'isNetworkAvailable'  => 'nullable|string|in:true,false',
            'isInstallationDone' => 'nullable|string|in:true,false',
            'beneficiary'         => 'nullable|string|max:255',
            'beneficiary_contact' => 'nullable|string|max:20',
            'remarks'             => 'nullable|string',
            'luminary_qr'         => 'nullable|string|max:255',
            'sim_number'          => 'nullable|string|max:200',
            'panel_qr'            => 'nullable|string|max:255',
            'battery_qr'          => 'nullable|string|max:255',
            'submission_image'    => 'nullable|array',
            'lat'                 => 'nullable|numeric',
            'lng'                 => 'nullable|numeric',
        ]);

        // ✅ Step 2: Fetch task & site
        $task = StreetlightTask::findOrFail($validated['task_id']);
        $approved_by = $task->engineer->firstName . " " . $task->engineer->lastName;
        $streetlight = Streetlight::findOrFail($task->site_id);

        // ✅ Step 3: Create or get pole
        $pole = Pole::firstOrCreate(
            [
                'task_id'              => $validated['task_id'],
                'complete_pole_number' => $validated['complete_pole_number']
            ],
            [
                'ward_name'           => $validated['ward_name'] ?? null,
                'beneficiary'         => $validated['beneficiary'] ?? null,
                'beneficiary_contact' => $validated['beneficiary_contact'] ?? null,
                'remarks'             => $validated['remarks'] ?? null,
                'isSurveyDone'        => true,
                'isInstallationDone'  => false,
                'lat'                 => $validated['lat'] ?? null,
                'lng'                 => $validated['lng'] ?? null,
            ]
        );

        if ($pole->wasRecentlyCreated) {
            $streetlight->increment('number_of_surveyed_poles');
        }

        // ✅ Step 4: Upload images
        foreach (['survey_image' => 'survey', 'submission_image' => 'installation'] as $field => $folder) {
            if ($request->hasFile($field)) {
                $images = collect($request->file($field))->map(
                    fn($img) =>
                    $this->uploadToS3($img, "streetlights/{$folder}/{$pole->id}")
                );
                $pole->update([$field => json_encode($images)]);
            }
        }

        // ✅ Step 5: Update survey data
        if ($request->isSurveyDone && !$pole->isSurveyDone) {
            $pole->update([
                'isSurveyDone'        => true,
                'beneficiary'         => $validated['beneficiary'] ?? null,
                'remarks'             => $validated['remarks'] ?? null,
                'isNetworkAvailable'  => $validated['isNetworkAvailable'] ?? 0,
            ]);
            $streetlight->increment('number_of_surveyed_poles');
        }

        // ✅ Step 6: Update installation data
        if ($request->isInstallationDone && !$pole->isInstallationDone) {
            $pole->update([
                'isInstallationDone' => true,
                'luminary_qr'        => $validated['luminary_qr'] ?? null,
                'sim_number'         => $validated['sim_number'] ?? null,
                'panel_qr'           => $validated['panel_qr'] ?? null,
                'battery_qr'         => $validated['battery_qr'] ?? null,
            ]);

            $streetlight->increment('number_of_installed_poles');

            // ✅ Step 7: Mark inventory as consumed
            $serials = [
                $validated['luminary_qr'] ?? null,
                $validated['panel_qr'] ?? null,
                $validated['battery_qr'] ?? null,
            ];

            $affected = InventoryDispatch::whereIn('serial_number', array_filter($serials))->update([
                'is_consumed' => true,
                'streetlight_pole_id' => $pole->id,
            ]);
            $site = Streetlight::findOrFail($task->site_id);
            RemoteApiHelper::sendPoleDataToRemoteServer($pole, $streetlight, $approved_by);

            Log::info("InventoryDispatch updated: {$affected} rows", [
                'serials' => $serials,
                'pole_id' => $pole->id
            ]);
        }

        Log::info('Pole Submitted:', $pole->toArray());

        return response()->json([
            'message' => 'Pole details submitted successfully!',
            'pole'    => $pole,
            'task'    => $task,
        ]);
    }

    // Controller to get details of pole by Id    
    public function getPoleDetails(Request $request)
    {
        $id = $request->pole_id;
        $pole = Pole::findOrFail($id);
        if (!$pole) {
            return response()->json([
                'message' => 'No poles associated with this id'
            ], 404);
        }
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
            'surveyed_poles'    => $surveyed_poles,
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
        Log::info($surveyed_poles);
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
                "isSurveyed" => true,
                "isInstalled" => false,
                'installed_location' => [
                    'lat' => $pole->lat,
                    'lng' => $pole->lng,
                ],
                'remarks' => $pole->remarks,
                'survey_image' => collect(json_decode($pole->survey_image, true) ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray(),
                'submission_image' => collect(json_decode($pole->submission_image, true) ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray(),
                'site_engineer_name' => $pole->task->engineer->first_name ?? null, // Assuming 'name' is the field for engineer's name
                'project_manager_name' => $pole->task->manager->name ?? null, // Assuming 'name' is the field for manager's name
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
                'survey_image' => collect(json_decode($pole->survey_image, true) ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray(),
                'submission_image' => collect(json_decode($pole->submission_image, true) ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray(),
                'site_engineer_name' => $pole->task->engineer->name ?? null, // Assuming 'name' is the field for engineer's name
                'project_manager_name' => $pole->task->manager->name ?? null, // Assuming 'name' is the field for manager's name
                'status' => $pole->status
            ];
        });

        return response()->json([
            'message' => 'Installed poles for Vendor',
            'surveyed_poles'    => $transformed_poles,
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
            'surveyed_poles'    => $surveyed_poles,
            'installed_poles' => $installed_poles
        ], 200);
    }
    // Fetch Surveyed Poles based on user role
    public function getSurveyedPoles(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        $user = auth()->user();
        $query = Pole::where('isSurveyDone', 1);
        // Apply filters based on request parameters
        if ($request->has('search')) {
            $query->where('complete_pole_number', 'like', '%' . $request->search . '%');
        }
        if ($request->has('district')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('district_id', $request->district);
            });
        }

        if ($request->has('block')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('block_id', $request->block);
            });
        }
        if ($request->has('panchayat')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('panchayat_id', $request->panchayat);
            });
        }

        if ($request->has('project_manager')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('manager_id', $request->project_manager);
            });
        }

        if ($request->has('site_engineer')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('engineer_id', $request->site_engineer);
            });
        }

        if ($request->has('vendor')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('vendor_id', $request->vendor);
            });
        }
        $poles = $query->paginate(10);
        $totalSurveyed = $query->count();
        $districts = [];
        $blocks = [];
        $panchayats = [];
        return view('poles.surveyed', compact('poles', 'totalSurveyed', 'districts', 'blocks', 'panchayats'));
    }

    // Fetch Installed Poles based on user role
    public function getInstalledPoles(Request $request)
    {
        $query = Pole::where('isInstallationDone', 1);
        if ($request->has('project_manager')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('manager_id', $request->project_manager);
            });
        }

        if ($request->has('site_engineer')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('engineer_id', $request->site_engineer);
            });
        }

        if ($request->has('vendor')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('vendor_id', $request->vendor);
            });
        }
        $clonedQuery = clone $query;
        $poles = $query->paginate(25);
        $totalInstalled = $clonedQuery->count();
        return view('poles.installed', compact('poles', 'totalInstalled'));
    }

    // Get Pole Details by ID
    public function viewPoleDetails($id)
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
                'Survey Image URLs' => implode(", ", collect(json_decode($pole->survey_image, true) ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray()),
                'Submission Image URLs' => implode(", ", collect($pole->submission_image ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray()),
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
                $approved_by = $engineer->firstName . ' ' . $engineer->lastName;
                Log::info("Sending data now");
                RemoteApiHelper::sendPoleDataToRemoteServer($pole, $streetlight, $approved_by);

                $responses[] = [
                    'pole_id' => $pole->id,
                    'status' => 'success',
                ];
            } catch (\Exception $e) {
                Log::error("Failed to send pole data to RMS", [
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
}
