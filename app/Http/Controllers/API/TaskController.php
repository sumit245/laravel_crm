<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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
        Log::info("Requested Task ID: " . $id);
        Log::info("Project Type: " . $request->query('project_type'));
        // Get the project_type from the query parameter
        $projectType = $request->query('project_type');
        Log::info($projectType);
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

    public function submitStreetlightTasks(Request $request)
    {
        // Step 1: Validate Request
        $validator = Validator::make($request->all(), [
            'task_id'        => 'required|exists:streetlight_tasks,id',
            'complete_pole_number'         => 'required|string|max:255',
            'isSurveyDone'      => 'nullable|boolean',
            'survey_image'        => 'nullable|array',
            'isNetworkAvailable'  => 'nullable|boolean',
            'beneficiary'         => 'nullable|string|max:255',
            'remarks'             => 'nullable|string',
            'isInstallationDone' => 'nullable|boolean',
            'luminary_qr'         => 'nullable|string|max:255',
            'sim_number'    => 'nullable|string|max:200',
            'panel_qr'            => 'nullable|string|max:255',
            'battery_qr'          => 'nullable|string|max:255',
            'submission_image'    => 'nullable|array',
            'lat'            => 'nullable|numeric',
            'lng'           => 'nullable|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        //Step 2: Fetch Task
        $task = StreetlightTask::findOrFail($request->task_id);

        $streetlight = Streetlight::findOrFail($task->site_id);

        // ✅ Step 4: Check if Pole Already Exists
        $pole = Pole::where('task_id', $request->task_id)
            ->where('complete_pole_number', $request->complete_pole_number)
            ->first();

        // If pole does not exist, create a new one
        if (!$pole) {
            $pole = Pole::create([
                'task_id'              => $request->task_id,
                'complete_pole_number' => $request->complete_pole_number,
                'isSurveyDone'         => true,
                'isInstallationDone'   => false,
                'beneficiary'          => $request->beneficiary,
                'remarks'              => $request->beneficiary,
                'luminary_qr'          => null,
                'panel_qr'             => null,
                'battery_qr'           => null,
                'lat'             => $request->lat,
                'lng'            => $request->lng,
            ]);

            // Increment surveyed poles count in `streetlight_tasks`
            $streetlight->increment('number_of_surveyed_poles');
        }
        // ✅ Step 5: Upload Images (If Any)
        if ($request->hasFile('survey_image')) {
            $uploadedSurveyImages = [];
            foreach ($request->file('survey_image') as $image) {
                $uploadedSurveyImages[] = $this->uploadToS3($image, "streetlights/survey/{$pole->id}");
            }
            $pole->update(['survey_image' => json_encode($uploadedSurveyImages)]);
        }
        if ($request->hasFile('submission_image')) {
            $uploadedSubmissionImages = [];
            foreach ($request->file('submission_image') as $image) {
                $uploadedSubmissionImages[] = $this->uploadToS3($image, "streetlights/installation/{$pole->id}");
            }
            $pole->update(['submission_image' => json_encode($uploadedSubmissionImages)]);
        }
        // ✅ Step 6: Update Survey Data
        if ($request->isSurveyDone) {
            $pole->update([
                'isSurveyDone'     => true,
                'beneficiary'      => $request->beneficiary,
                'remarks'          => $request->remarks,
                'isNetworkAvailable' => $request->isNetworkAvailable,
            ]);
        }
        // ✅ Step 7: Update Installation Data
        if ($request->isInstallationDone) {
            $pole->update([
                'isInstallationDone' => true,
                'luminary_qr'       => $request->luminary_qr,
                'sim_number'        => $request->sim_number,
                'panel_qr'          => $request->panel_qr,
                'battery_qr'        => $request->battery_qr,
                'latitude'          => $request->latitude,
                'longitude'         => $request->longitude,
            ]);

            // Increment installed poles count in `streetlight_tasks`
            $streetlight->increment('number_of_installed_poles');
        }
        return response()->json([
            'message' => 'Pole details submitted successfully!',
            'pole'    => $pole,
            'task'    => $task,
        ], 200);
    }
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
                'installed_location' => [
                    'lat' => $pole->lat,
                    'lng' => $pole->lng,
                ],
                'remarks' => $pole->remarks,
                'survey_image' => collect($pole->survey_image ?? [])->map(fn($image) => Storage::url($image))->toArray(),
                'submission_image' => collect($pole->submission_image ?? [])->map(fn($image) => Storage::url($image))->toArray(),
                'site_engineer_name' => $pole->task->engineer->name ?? null, // Assuming 'name' is the field for engineer's name
                'project_manager_name' => $pole->task->manager->name ?? null, // Assuming 'name' is the field for manager's name
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
                'ward' => $pole->task->site->ward ?? null,
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
                'survey_image' => collect($pole->survey_image ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray(),
                'submission_image' => collect($pole->submission_image ?? [])->map(fn($image) => Storage::disk('s3')->url($image))->toArray(),
                'site_engineer_name' => $pole->task->engineer->name ?? null, // Assuming 'name' is the field for engineer's name
                'project_manager_name' => $pole->task->manager->name ?? null, // Assuming 'name' is the field for manager's name
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
        $poles = $query->paginate(25);
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
        $poles = $query->paginate(25);
        $totalInstalled = $query->count();
        return view('poles.installed', compact('poles', 'totalInstalled', 'districts', 'blocks', 'panchayats'));
    }

    public function viewPoleDetails($id)
    {
        // Fetch the pole with the given ID along with its relationships
        $pole = Pole::with(['streetlight', 'task', 'tasks'])->findOrFail($id);

        // Get full S3 URLs for survey images
        $surveyImages = [];
        if (!empty($pole->survey_image)) {
            foreach ($pole->survey_image as $image) {
                $surveyImages[] = Storage::disk('s3')->url($image);
            }
        }

        // Fetch related users (Installer, Project Manager, Site Engineer) from the latest task
        $latestTask = $pole->tasks()->latest()->first(); // Get latest assigned task
        Log::info($latestTask);

        $installer = $latestTask?->vendor;    // Installer
        $projectManager = $latestTask?->manager; // Project Manager
        $siteEngineer = $latestTask?->engineer;  // Site Engineer

        // Return the view with the pole details
        return view('poles.show', compact('pole', 'surveyImages', 'installer', 'projectManager', 'siteEngineer'));
    }
}
