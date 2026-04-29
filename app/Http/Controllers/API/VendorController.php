<?php

namespace App\Http\Controllers\API;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\Logging\ActivityLogger;

/**
 * Vendor Management — handles vendor-specific operations like viewing assigned inventory,
 * tracking dispatched vs consumed items, and vendor performance summaries.
 *
 * Data Flow:
 *   List vendors for project → View vendor inventory (dispatched items) → Track
 *   consumption against poles
 *
 * @depends-on User, InventoryDispatch, Project
 * @business-domain Vendor Management
 * @package App\Http\Controllers\API
 */
class VendorController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {
    }

    /**
     * Create a new vendor.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        // Retrieve all vendors (users with vendor role)
        $vendors = User::where('role', UserRole::VENDOR->value)->get();

        if ($vendors->isEmpty()) {
            return response()->json([
                'message' => 'No vendors found',
            ], 404);
        }

        return response()->json([
            'vendors' => $vendors,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * Data flow: HTTP Request → Database Query → Blade View
     *
     * @param  Request  $request  The incoming HTTP request
     * @return void  
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'firstName' => 'required|string|max:255',
            'lastName'  => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'username'  => 'required|string|max:50|unique:users,username',
            'contactNo' => 'nullable|string|max:20',
            'address'   => 'nullable|string|max:255',
            'image'     => 'nullable|url',
        ]);

        try {
            $validated['password']     = bcrypt($validated['password']);
            $validated['status']       = 'active';
            $validated['role']         = UserRole::VENDOR->value;
            $validated['disableLogin'] = 0;

            $user = User::create($validated);

            $this->activityLogger->log('vendor', 'created', $user, [
                'description' => "Created vendor {$user->firstName} {$user->lastName} via API"
            ]);

            return response()->json([
                'message' => 'Vendor created successfully',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "There is an error creating Vendor",
                "error" => $e->getMessage(),
            ]);
        }
    }

    /**
     * View a specific vendor.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user || $user->role !== UserRole::VENDOR->value) {
            return response()->json([
                'message' => 'Vendor not found or invalid role',
            ], 404);
        }

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Edit a specific vendor.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);

        if (!$user || $user->role !== UserRole::VENDOR->value) {
            return response()->json([
                'message' => 'Vendor not found or invalid role',
            ], 404);
        }

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Update the vendor information.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate incoming request data (you can expand validation as needed)
        $validatedData = $request->validate([
            'firstName' => 'nullable|string|max:255',
            'lastName' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8', // Add more password rules if needed
            'contactNo' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        // Find the vendor
        $user = User::find($id);

        if (!$user || $user->role !== UserRole::VENDOR->value) {
            return response()->json([
                'message' => 'Vendor not found or invalid role',
            ], 404);
        }

        // Prepare data for update, only including fields that are present in the request
        $updateData = array_filter($validatedData, function ($value) {
            return $value !== null; // Exclude null values
        });

        // Handle password separately, ensuring it is hashed if provided
        if (isset($updateData['password'])) {
            $updateData['password'] = bcrypt($updateData['password']);
        }

        // Update vendor data
        $user->update($updateData);

        $this->activityLogger->log('vendor', 'updated', $user, [
            'description' => "Updated vendor {$user->name} via API"
        ]);

        return response()->json([
            'message' => 'Vendor updated successfully',
            'user' => $user,
        ]);
    }






    /**
     * Delete a vendor.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user || $user->role !== UserRole::VENDOR->value) {
            return response()->json([
                'message' => 'Vendor not found or invalid role',
            ], 404);
        }

        // Delete vendor
        $vendorName = $user->name;
        $user->delete();

        $this->activityLogger->log('vendor', 'deleted', null, [
            'description' => "Deleted vendor {$vendorName} via API"
        ]);

        return response()->json([
            'message' => 'Vendor deleted successfully',
        ]);
    }

    /**
     * Upload avatar.
     *
     * Data flow: HTTP Request → Processing → Response
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  mixed  $id  The resource identifier
     * @return void  
     */
    public function uploadAvatar(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image type & size
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Generate unique filename: username_YYYYMMDD_HHMMSS.jpg
        $timestamp = Carbon::now()->format('Ymd_His');
        $filename = "{$user->username}_{$timestamp}.jpg";

        // Upload to S3 (path: users/avatar/{filename})
        $path = $request->file('image')->storeAs('users/avatar', $filename, 's3');

        // Save image path in the database
        $user->update(['image' => Storage::disk('s3')->url($path)]);

        $this->activityLogger->log('vendor', 'updated', $user, [
            'description' => "Updated avatar for vendor {$user->username} via API"
        ]);

        return response()->json([
            'message' => 'Profile picture uploaded successfully',
            'image_url' => $user->image, // Return full image URL
        ], 200);
    }
}
