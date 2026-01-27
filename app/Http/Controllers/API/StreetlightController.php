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
        
        // Get sites that are either never allotted or partially allotted
        $usedSiteIds = StreetlightTask::pluck('site_id')->toArray();
        $sites = $query->get();
        
        // Filter panchayats that are either:
        // 1. Never allotted (no StreetlightTask exists), OR
        // 2. Partially allotted (some wards allotted but not all)
        $availablePanchayats = [];
        
        foreach ($sites as $site) {
            $task = StreetlightTask::where('site_id', $site->id)->first();
            
            if (!$task) {
                // Never allotted - include it
                $availablePanchayats[] = [
                    'id' => $site->id,
                    'text' => $site->panchayat
                ];
            } else {
                // Check if partially allotted
                $siteWards = $site->ward ? array_map('trim', explode(',', $site->ward)) : [];
                $allottedWards = Pole::where('task_id', $task->id)
                    ->whereNotNull('ward_name')
                    ->distinct()
                    ->pluck('ward_name')
                    ->map(function($ward) {
                        return trim($ward);
                    })
                    ->toArray();
                
                // Check if all wards are allotted
                $allWardsAllotted = !empty($siteWards) && 
                    count($siteWards) === count(array_intersect($siteWards, $allottedWards));
                
                if (!$allWardsAllotted) {
                    // Partially allotted - include it
                    $availablePanchayats[] = [
                        'id' => $site->id,
                        'text' => $site->panchayat . ' (Partially Allotted)'
                    ];
                }
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

    // 1. Get tasks assigned to the logged-in Site Engineer
    public function getEngineerTasks(Request $request)
    {
        $tasks = StreetlightTask::where('engineer_id', $request->id)->with('site')->get();
        return response()->json($tasks);
    }

    // 2. Get tasks assigned to the logged-in Vendor
    public function getVendorTasks(Request $request)
    {
        $tasks = StreetlightTask::where('vendor_id', $request->id)->with('site')->get();
        return response()->json($tasks);
    }

    // 3. Vendor submits a task, making it visible to succeeding roles
    public function submitTask(Request $request, $taskId)
    {

        $task = StreetlightTask::findOrFail($taskId);

        $task->update([
            'status' => 'Submitted',
            'completion_details' => $request->input('completion_details'),
        ]);

        return response()->json(['message' => 'Task submitted successfully']);
    }
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
            $task = StreetlightTask::where('site_id', $site->id)->first();
            
            if (!$task) {
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
                // Check if partially allotted
                $siteWards = $site->ward ? array_map('trim', explode(',', $site->ward)) : [];
                $allottedWards = Pole::where('task_id', $task->id)
                    ->whereNotNull('ward_name')
                    ->distinct()
                    ->pluck('ward_name')
                    ->map(function($ward) {
                        return trim($ward);
                    })
                    ->toArray();
                
                // Check if all wards are allotted
                $allWardsAllotted = !empty($siteWards) && 
                    count($siteWards) === count(array_intersect($siteWards, $allottedWards));
                
                if (!$allWardsAllotted) {
                    // Partially allotted - include it
                    $availablePanchayats[] = [
                        'id' => $site->id,
                        'panchayat' => $site->panchayat,
                        'district' => $site->district,
                        'block' => $site->block,
                        'ward' => $site->ward,
                        'status' => 'partially_allotted',
                        'allotted_wards' => $allottedWards,
                        'total_wards' => $siteWards
                    ];
                }
            }
        }
        
        // Remove duplicates by panchayat name (in case multiple sites have same panchayat)
        $uniquePanchayats = collect($availablePanchayats)
            ->unique('panchayat')
            ->values()
            ->toArray();
        
        return response()->json($uniquePanchayats);
    }

    public function getWardsBySite($siteId)
    {
        $streetlight = Streetlight::find($siteId);
        
        if (!$streetlight || empty($streetlight->ward)) {
            return response()->json([]);
        }

        $allWards = array_map('trim', explode(',', $streetlight->ward));
        $allWards = array_filter($allWards); // Remove empty values
        
        // Check if there's an existing task for this site
        $task = StreetlightTask::where('site_id', $siteId)->first();
        
        if ($task) {
            // Get already allotted wards from poles
            $allottedWards = Pole::where('task_id', $task->id)
                ->whereNotNull('ward_name')
                ->distinct()
                ->pluck('ward_name')
                ->map(function($ward) {
                    return trim($ward);
                })
                ->toArray();
            
            // Filter out already allotted wards
            $availableWards = array_diff($allWards, $allottedWards);
            
            // Return only unallotted wards
            return response()->json(array_values($availableWards));
        }
        
        // No task exists, return all wards
        return response()->json(array_values($allWards));
    }
}
