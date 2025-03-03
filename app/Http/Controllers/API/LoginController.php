<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    //
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

        $token = $user->createToken('authToken')->accessToken;

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
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
                'lastOnline'   => $user->lastOnline,
                'created_at'   => $user->created_at,
                'updated_at'   => $user->updated_at,
            ],
            'status'  => 200,
        ]);
    }
}
