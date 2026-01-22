<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'credentialError', 'status' => 401], 401);
        }

        $tokenResult = $user->createToken('authToken');
        $accessToken = $tokenResult->plainTextToken;

        // Fetch projects associated with the user
        $projects = DB::table('project_user')
            ->join('projects', 'project_user.project_id', '=', 'projects.id')
            ->where('project_user.user_id', $user->id)
            ->select('projects.id', 'projects.project_type')
            ->get();

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $accessToken,
            'user'    => [
                'id'           => $user->id,
                'firstName'    => $user->firstName,
                'lastName'     => $user->lastName,
                'name'         => $user->name,
                'role'         => $user->role,
                'email'        => $user->email,
                'image'        => $user->image,
                'status'       => $user->status,
                'disableLogin' => $user->disableLogin,
                'address'      => $user->address,
                'contactNo'    => $user->contactNo,
                // 'lastOnline'   => $user->lastOnline,
                'created_at'   => $user->created_at,
                'updated_at'   => $user->updated_at,
            ],
            'projects' => $projects, // Include project ID and type
            'status'   => 200,
        ]);
    }
}
