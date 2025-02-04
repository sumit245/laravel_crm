<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
<<<<<<< HEAD
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
 public function show($id)
 {
  $task = Task::with(['project', 'site', 'vendor'])->findOrFail($id);

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
   Log::info($task);
//   $request->validate([
//     'image.*' => 'file|mimes:jpeg,jpg,png,pdf|max:2048',
//   ]);
//   Log::info('Request files:', [$request->allFiles()]);
   Log::info('Request All:', $request->all());
   Log::info('Request Files:', $request->allFiles());
   Log::info('Request Keys:', $request->keys());

   $uploadedFiles = [];
   Log::info('Request received:', $request->all());

   if ($request->hasFile('image')) {
    $document = $request->file('image');
   Log::info('Images coming in request:', $document);
    $images = $request->file('image'); // Input format for multiple files i

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
=======
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Task::with(['project', 'site', 'vendor'])->get();
>>>>>>> f3f2037f20aba9163271753a7eae84ecbd11ad6e
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
    public function show($id)
    {
        $task = Task::with(['project', 'site', 'vendor'])->findOrFail($id);

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
}
