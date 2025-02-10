<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Streetlight;
use App\Models\StreetlightTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            Log::info('Request received:', $request->all());

            if ($request->hasFile('image')) {
                $images = $request->file('image'); // Input format for multiple files in JSON

                if (is_array($images)) {
                    // Handle multiple images
                    foreach ($images as $file) {
                        if ($file->isValid()) {
                            // Upload each image to S3
                            $uploadedFiles[] = $this->uploadToS3($file, 'tasks/' . $task->id);
                        } else {
                            Log::warning('Invalid image format:', $file);
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
        $sites = Streetlight::where('panchayat', 'LIKE', "%{$search}%")
            ->limit(10) // Limit results to improve performance
            ->get(['id', 'panchayat']);
        return response()->json($sites->map(function ($site) {
            return [
                'id' => $site->id,
                'text' => $site->panchayat
            ];
        }));
    }

    /**
     * Remove the specified resource from storage.
     */
    //  public function destroy($id)
    //  {
    //   $task = Task::findOrFail($id);
    //   $task->delete();
    //   return response()->json(['message' => 'Task deleted']);
    //  }

    //  public function getSitesForVendor($vendorId)
    //  {
    //   // Fetch tasks where the given vendor_id matches and eager load the site relationship
    //   $tasks = Task::with('site')
    //    ->where('vendor_id', $vendorId)
    //    ->get();

    //   // Extract unique sites from the tasks
    //   $sites = $tasks->pluck('site')->unique('id')->values();

    //   // Return the response
    //   return response()->json([
    //    'status'    => 'success',
    //    'vendor_id' => $vendorId,
    //    'sites'     => $sites,
    //   ], 200);
    //  }

    // 1. Get tasks assigned to the logged-in Site Engineer
    public function getEngineerTasks(Request $request)
    {
        $tasks = StreetlightTask::where('engineer_id', $request->id)->with('site')->get();
        Log::info($tasks);
        return response()->json($tasks);
    }

    // 2. Get tasks assigned to the logged-in Vendor
    public function getVendorTasks(Request $request)
    {
        // $user = Auth::user();

        // if ($user->role !== 3) { // Role 3 = Vendor
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

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
}
