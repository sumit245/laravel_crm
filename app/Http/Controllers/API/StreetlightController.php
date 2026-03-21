<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use App\Models\Pole;
use App\Models\Task;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Streetlight Site Data API — provides panchayat/ward/pole data for the mobile app's task
 * execution flow. Engineers use this to fetch their assigned sites, view target pole counts, and
 * navigate to specific GPS locations for survey work.
 *
 * Data Flow:
 *   GET /api/streetlights → Filter by project + engineer → Return site list with pole
 *   counts → GET /api/streetlights/{id}/poles → Return pole list with survey status
 *
 * @depends-on Streetlight, StreetlightTask, Pole, User, Project
 * @business-domain Mobile API
 * @package App\Http\Controllers\API
 */
class StreetlightController extends Controller
{
    //

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Streetlight::get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $streetlight = Streetlight::create($request->all());

            // Save to streetlight table

            return response()->json([
                'message' => 'Task created successfully',
                'task'    => $streetlight,
            ]);
        } catch (\Exception $e) {
            Log::info($e);
            return response()->json([
                'message' => 'Error creating task',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $task = Streetlight::findOrFail($id);

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
            $task = Streetlight::findOrFail($id);

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
                    Log::warning('Unexpected format for "image" input:', ['image' => $images]);
                    return response()->json(['error' => 'Invalid "image" input format.'], 400);
                }

                // Update the task's `image` field in the database
                $task->update(['image' => json_encode($uploadedFiles)]);
            }

            // Save task and return success response
            return response()->json([
                'message'        => 'Task updated successfully.',
                'task'           => $task,
                'uploaded_files' => $uploadedFiles,
            ]);
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Error updating task:', ['error' => $e->getMessage()]);

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
            Log::error('S3 Upload Error:', ['message' => $e->getMessage()]);

            // Return error response
            return response()->json([
                'error'   => 'An error occurred while uploading the file.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search for matching records.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
    public function search(Request $request)
    {
        $search = $request->input('search');
        $district = $request->input('district');
        $block = $request->input('block');
        $projectId = $request->input('project_id');
        
        $query = Streetlight::where('panchayat', 'LIKE', "%{$search}%");
        
        // Filter by district if provided
        if ($district) {
            $query->where('district', $district);
        }
        
        // Filter by block if provided
        if ($block) {
            $query->where('block', $block);
        }
        
        // Filter by project if provided
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        $sites = $query->get();
        
        // Filter panchayats that are either:
        // 1. Never allotted (no StreetlightTask exists), OR
        // 2. Partially allotted (some wards allotted but not all)
        $availablePanchayats = [];
        
        foreach ($sites as $site) {
            $siteWards = $site->ward ? array_map('trim', explode(',', $site->ward)) : [];
            $siteWards = array_filter($siteWards);
            
            // Aggregate allotted_wards across ALL tasks for this site
            $existingTasks = StreetlightTask::where('site_id', $site->id)->get();
            $allottedWards = [];
            foreach ($existingTasks as $task) {
                if (!empty($task->allotted_wards)) {
                    $taskWards = array_map('trim', explode(',', $task->allotted_wards));
                    $allottedWards = array_merge($allottedWards, $taskWards);
                }
            }
            $allottedWards = array_unique($allottedWards);
            
            if ($existingTasks->isEmpty()) {
                // Never allotted - include it
                $availablePanchayats[] = [
                    'id' => $site->id,
                    'text' => $site->panchayat
                ];
            } else {
                // Check if all wards are allotted
                $allWardsAllotted = !empty($siteWards) && 
                    empty(array_diff($siteWards, $allottedWards));
                
                if (!$allWardsAllotted) {
                    // Partially allotted - include it
                    $availablePanchayats[] = [
                        'id' => $site->id,
                        'text' => $site->panchayat . ' (Partially Allotted)'
                    ];
                }
                // Fully allotted — excluded from results
            }
        }
        
        // Remove duplicates by panchayat name and limit results
        $uniquePanchayats = collect($availablePanchayats)
            ->unique('text')
            ->take(10)
            ->values()
            ->toArray();
        
        return response()->json($uniquePanchayats);
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

    /**
     * 1. Get tasks assigned to the logged-in Site Engineer
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
    public function getEngineerTasks(Request $request)
    {
        $tasks = StreetlightTask::where('engineer_id', $request->id)->with('site')->get();
        return response()->json($tasks);
    }

    /**
     * 2. Get tasks assigned to the logged-in Vendor
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
    public function getVendorTasks(Request $request)
    {
        $tasks = StreetlightTask::where('vendor_id', $request->id)->with('site')->get();
        return response()->json($tasks);
    }

    /**
     * 3. Vendor submits a task, making it visible to succeeding roles
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  mixed  $taskId  The task identifier
     * @return void  
     */
    public function submitTask(Request $request, $taskId)
    {

        $task = StreetlightTask::findOrFail($taskId);

        $task->update([
            'status' => 'Submitted',
            'completion_details' => $request->input('completion_details'),
        ]);

        return response()->json(['message' => 'Task submitted successfully']);
    }
    /**
     * Get the blocks by district.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  mixed  $district  The district identifier or name
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
    public function getBlocksByDistrict($district, Request $request)
    {
        try {
            $projectId = $request->input('project_id');
            
            // URL decode the district name in case it's encoded
            $district = urldecode($district);
            
            // First try with project_id filter
            $query = Streetlight::where('district', $district);
            
            if ($projectId) {
                $query->where('project_id', $projectId);
            }
            
            $blocks = $query->select('block')
                ->distinct()
                ->get()
                ->map(function($item) {
                    return ['block' => $item->block];
                })
                ->filter(function($item) {
                    return !empty($item['block']) && trim($item['block']) !== '';
                })
                ->values();
            
            // If no blocks found with project filter, try without project filter
            // (This handles cases where districts exist but no streetlights for that project yet)
            if ($blocks->isEmpty() && $projectId) {
                $blocks = Streetlight::where('district', $district)
                    ->select('block')
                    ->distinct()
                    ->get()
                    ->map(function($item) {
                        return ['block' => $item->block];
                    })
                    ->filter(function($item) {
                        return !empty($item['block']) && trim($item['block']) !== '';
                    })
                    ->values();
            }

            $blocksArray = $blocks->toArray();

            \Log::info('Blocks for district', [
                'district' => $district,
                'project_id' => $projectId,
                'blocks_count' => count($blocksArray),
                'blocks' => $blocksArray
            ]);

            return response()->json($blocksArray);
        } catch (\Exception $e) {
            \Log::error('Error fetching blocks', [
                'district' => $district ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to fetch blocks',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the panchayats by block.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  mixed  $block  The block identifier or name
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
    public function getPanchayatsByBlock($block, Request $request)
    {
        $projectId = $request->input('project_id');
        
        // Get all streetlight sites for this block
        $query = Streetlight::where('block', $block);
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        $sites = $query->get();
        
        // Filter panchayats that are either:
        // 1. Never allotted (no StreetlightTask exists), OR
        // 2. Partially allotted (some wards allotted but not all)
        $availablePanchayats = [];
        
        foreach ($sites as $site) {
            $siteWards = $site->ward ? array_map('trim', explode(',', $site->ward)) : [];
            $siteWards = array_filter($siteWards);
            
            // Aggregate allotted_wards across ALL tasks for this site
            $existingTasks = StreetlightTask::where('site_id', $site->id)->get();
            $allottedWards = [];
            foreach ($existingTasks as $task) {
                if (!empty($task->allotted_wards)) {
                    $taskWards = array_map('trim', explode(',', $task->allotted_wards));
                    $allottedWards = array_merge($allottedWards, $taskWards);
                }
            }
            $allottedWards = array_unique($allottedWards);
            
            if ($existingTasks->isEmpty()) {
                // Never allotted - include it
                $availablePanchayats[] = [
                    'id' => $site->id,
                    'panchayat' => $site->panchayat,
                    'district' => $site->district,
                    'block' => $site->block,
                    'ward' => $site->ward,
                    'status' => 'unallotted'
                ];
            } else {
                // Check if all wards are allotted
                $allWardsAllotted = !empty($siteWards) && 
                    empty(array_diff($siteWards, $allottedWards));
                
                if (!$allWardsAllotted) {
                    // Partially allotted - include it
                    $availablePanchayats[] = [
                        'id' => $site->id,
                        'panchayat' => $site->panchayat,
                        'district' => $site->district,
                        'block' => $site->block,
                        'ward' => $site->ward,
                        'status' => 'partially_allotted',
                        'allotted_wards' => array_values($allottedWards),
                        'total_wards' => $siteWards
                    ];
                }
                // Fully allotted — excluded from results
            }
        }
        
        // Remove duplicates by panchayat name (in case multiple sites have same panchayat)
        $uniquePanchayats = collect($availablePanchayats)
            ->unique('panchayat')
            ->values()
            ->toArray();
        
        return response()->json($uniquePanchayats);
    }

    /**
     * Get the wards by site.
     *
     * @param  mixed  $siteId  The site identifier
     * @return void  
     */
    public function getWardsBySite($siteId)
    {
        $streetlight = Streetlight::find($siteId);
        
        if (!$streetlight || empty($streetlight->ward)) {
            return response()->json([]);
        }

        $allWards = array_map('trim', explode(',', $streetlight->ward));
        $allWards = array_filter($allWards); // Remove empty values
        
        // Aggregate allotted_wards across ALL tasks for this site
        $existingTasks = StreetlightTask::where('site_id', $siteId)->get();
        $allottedWards = [];
        foreach ($existingTasks as $task) {
            if (!empty($task->allotted_wards)) {
                $taskWards = array_map('trim', explode(',', $task->allotted_wards));
                $allottedWards = array_merge($allottedWards, $taskWards);
            }
        }
        $allottedWards = array_unique($allottedWards);
        
        // Filter out already allotted wards
        $availableWards = array_diff($allWards, $allottedWards);
        
        // Return only unallotted wards
        return response()->json(array_values($availableWards));
    }

    /**
     * Get wards for a specific site, intended for the Task Edit view.
     * Includes unallotted wards AND wards currently allotted to the specified task.
     *
     * @param int $siteId
     * @param int $taskId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWardsForEdit($siteId, $taskId)
    {
        try {
            $site = Streetlight::find($siteId);
            
            if (!$site || empty($site->ward)) {
                return response()->json([]);
            }
            
            $siteWards = $site->ward ? explode(',', $site->ward) : [];
            $siteWards = array_filter(array_map('trim', $siteWards));
            
            // Aggregate allotted_wards across ALL OTHER tasks for this site
            $otherTasks = StreetlightTask::where('site_id', $siteId)
                                         ->where('id', '!=', $taskId)
                                         ->get();
            $allottedWards = [];
            foreach ($otherTasks as $task) {
                if (!empty($task->allotted_wards)) {
                    $taskWards = array_map('trim', explode(',', $task->allotted_wards));
                    $allottedWards = array_merge($allottedWards, $taskWards);
                }
            }
            $allottedWards = array_unique($allottedWards);
            
            // Get wards allotted to THIS specific task
            $thisTask = StreetlightTask::find($taskId);
            $thisTaskWards = [];
            if ($thisTask && !empty($thisTask->allotted_wards)) {
                $thisTaskWards = array_map('trim', explode(',', $thisTask->allotted_wards));
            }
            
            $availableWards = [];
            
            foreach ($siteWards as $ward) {
                $wardValue = trim($ward);
                if (empty($wardValue)) continue;
                
                // Return wards that are NOT allotted to OTHER tasks
                if (!in_array($wardValue, $allottedWards)) {
                    $availableWards[] = [
                        'ward' => $wardValue,
                        'is_currently_allotted' => in_array($wardValue, $thisTaskWards)
                    ];
                }
            }
            
            return response()->json($availableWards);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching wards for edit: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching wards.'], 500);
        }
    }
}
