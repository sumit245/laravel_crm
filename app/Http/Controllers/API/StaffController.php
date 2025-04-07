<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StaffController extends Controller
{
    /**
     * Create a new vendor.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        // Retrieve all vendors (users with roleId of 3)
        $siteEngineers = User::where('role', 1)->get();

        if ($siteEngineers->isEmpty()) {
            return response()->json([
                'message' => 'No vendors found',
            ], 404);
        }

        return response()->json([
            'vendors' => $siteEngineers,
        ]);
    }

    public function create(Request $request) {}

    /**
     * View a specific vendor.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {}

    /**
     * Edit a specific vendor.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {}

    /**
     * Update the vendor information.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {}

    /**
     * Delete a vendor.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {}
    public function uploadAvatar(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image type & size
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Generate unique filename: username_YYYYMMDD_HHMMSS.jpg
        $timestamp = Carbon::now()->format('Ymd_His');
        $filename = "{$user->username}_{$timestamp}.jpg";

        // Upload to S3 (path: users/avatar/{filename})
        $path = $request->file('image')->storeAs('users/avatar', $filename, 's3');

        // Save image path in the database
        $user->update(['image' => Storage::disk('s3')->url($path)]);

        return response()->json([
            'message' => 'Profile picture uploaded successfully',
            'image_url' => $user->image, // Return full image URL
        ], 200);
    }

    public function getStaffPerformance($user_id)
    {
        try {
            $today = now();

            // Get logged-in user
            $loggedInUser = User::find($user_id);
            if (!$loggedInUser) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $managerId = $loggedInUser->manager_id;
            $projectId = $loggedInUser->project_id;

            // Step 1: Fetch all engineers (non-admin users)
            $engineers = User::where('role', '!=', 0)
                ->where('manager_id', $managerId)
                ->where('project_id', $projectId)
                ->get();
            Log::info($loggedInUser);

            // Step 2: Fetch all tasks once
            $allTasks = Task::all();

            // Step 3: Map performance for each engineer
            $performanceData = $engineers->map(function ($engineer) use ($allTasks, $user_id, $today) {
                $engineerTasks = $allTasks->where('engineer_id', $engineer->id);
                $total_alloted = $engineerTasks->count();
                $total_completed = $engineerTasks->where('status', 'Completed')->count();
                $total_pending = $engineerTasks->where('status', 'Pending')->count();

                // Backlogs: pending tasks with end_date < today
                $total_backlogs = $engineerTasks->filter(function ($task) use ($today) {
                    return $task->status === 'Pending' && $task->end_date < $today;
                })->count();

                $performance_percentage = $total_alloted > 0
                    ? round(($total_completed / $total_alloted) * 100, 2)
                    : 0;

                return $total_alloted > 0 ? [
                    'id' => $engineer->id,
                    'name' => $engineer->firstName . ' ' . $engineer->lastName,
                    'total_alloted' => $total_alloted,
                    'total_completed' => $total_completed,
                    'total_pending' => $total_pending,
                    'total_backlogs' => $total_backlogs,
                    'performance_percentage' => $performance_percentage,
                    'is_logged_in_user' => $engineer->id == $user_id
                ] : null;
            })->filter(); // Remove nulls

            // Step 4: Sort
            $sorted = $performanceData->sort(function ($a, $b) {
                if ($a['is_logged_in_user']) return -1;
                if ($b['is_logged_in_user']) return 1;
                return $b['performance_percentage'] <=> $a['performance_percentage'];
            })->values(); // Re-index

            return response()->json($sorted);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
