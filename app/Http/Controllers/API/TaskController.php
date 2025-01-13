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

   // Update task details except `document` key
   $task->update($request->except('image'));

   $uploadedFiles = [];

   if ($request->hasFile('image')) {
    $document = $request->file('image');
    Log::info('Images coming in request:', $document);

    if (is_array($document)) {
     // Handle multiple images
     foreach ($document as $file) {
      $uploadedFiles[] = $this->uploadToS3($file, 'tasks/' . $task->id);
      Log::info('S3 Response:', $uploadedFiles);

     }
    } else {
     // Handle single PDF document
     if ($document->getClientOriginalExtension() === 'pdf' || in_array($document->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
      $uploadedFiles[] = $this->uploadToS3($document, 'tasks/' . $task->id);
     } else {
      return response()->json([
       'error' => 'Invalid file type. Only PDF or images are allowed.',
      ], 400);
     }
    }
    // Update the task's document or images field in the database
    $task->update(['image' => json_encode($uploadedFiles)]);
   }
   $site = Site::find($task->site_id);
   $site->update([
    'survey_latitude'  => $request->input('survey_lat'),
    'survey_longitude' => $request->input('survey_long'),
    'actual_latitude'  => $request->input('lat'),
    'actual_longitude' => $request->input('long'),
   ]);
   $task->save();

   // Return success response
   return response()->json([
    'message'        => 'Task updated successfully.',
    'task'           => $task,
    'uploaded_files' => $uploadedFiles,
   ]);
  } catch (\Exception $e) {
   // Handle any exceptions
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
   // Generate a unique file name
   $fileName = time() . '_' . $file->getClientOriginalName();
   // Upload the file to S3 and return its path
   return $file->storeAs($path, $fileName, 's3');
  } catch (\Exception $e) {
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
