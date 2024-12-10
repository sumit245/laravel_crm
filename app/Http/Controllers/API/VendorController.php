<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VendorController extends Controller
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
        $vendors = User::where('role', 3)->get();

        if ($vendors->isEmpty()) {
            return response()->json([
                'message' => 'No vendors found',
            ], 404);
        }

        return response()->json([
            'vendors' => $vendors,
        ]);
    }

    public function create(Request $request)
    {
        try {
            // Create new vendor
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password), // Encrypt password
                'username' => $request->username,
                'firstName' => $request->firstName,
                'lastName' => $request->lastName,
                'image' => $request->image,
                'status' => 'active', // Default status
                'address' => $request->address,
                'contactNo' => $request->contactNo,
                'role' => 3, // Vendor role
                'disableLogin' => 0, // Default
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

        if (!$user || $user->role != 3) {
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

        if (!$user || $user->role != 3) {
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
            'email' => 'nullable|email|max:255',
            'password' => 'nullable|string|min:8', // Add more password rules if needed
            'contactNo' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        // Find the vendor
        $user = User::find($id);

        if (!$user || $user->role != 3) {
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

        if (!$user || $user->role != 3) {
            return response()->json([
                'message' => 'Vendor not found or invalid role',
            ], 404);
        }

        // Delete vendor
        $user->delete();

        return response()->json([
            'message' => 'Vendor deleted successfully',
        ]);
    }
}
