<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class VendorController extends Controller
{
 /**
  * Display a listing of the resource.
  */
 public function index()
 {
  //
  $vendors = User::where('role', 3)->get();
  return view('uservendors.index', compact('vendors'));
 }

 /**
  * Show the form for creating a new resource.
  */
 public function create()
 {
  return view('uservendors.create');
 }

 /**
  * Generate a unique username based on the user's name.
  *
  * @param string $name
  * @return string
  */
 private function __generateUniqueUsername($name)
 {
  $baseUsername = strtolower(preg_replace('/\s+/', '', $name)); // Remove spaces and make lowercase
  $randomSuffix = mt_rand(1000, 9999); // Generate a random 4-digit number
  $username     = $baseUsername . $randomSuffix;

  // Ensure the username is unique
  while (User::where('username', $username)->exists()) {
   $randomSuffix = mt_rand(1000, 9999); // Generate a new random suffix if it exists
   $username     = $baseUsername . $randomSuffix;
  }

  return $username;
 }

 /**
  * Store a newly created resource in storage.
  */
 public function store(Request $request)
 {
  //
  // Validate the incoming data without requiring a username
  $validated = $request->validate([
   'name'          => 'required|string|max:255',
   'firstName'     => 'required|string',
   'lastName'      => 'required|string',
   'contactPerson' => 'string',
   'contactNo'     => 'string',
   'address'       => 'string|max:255',
   'aadharNumber'  => 'string|max:12',
   'panNumber'     => 'string|max:10',
   'gstNumber'     => 'nullable|string|max:15',
   'accountName'   => 'string',
   'accountNumber' => 'string',
   'ifscCode'      => 'string|max:11',
   'bankName'      => 'string',
   'branch'        => 'string',
   'email'         => 'required|email|unique:users,email',
   'password'      => 'required|string|min:6|confirmed',
  ]);

  try {
   // Generate a random unique username
   $validated['username'] = $this->__generateUniqueUsername($validated['name']);
   $validated['password'] = bcrypt($validated['password']); // Hash
   $validated['role']     = 3;
   // Create the staff user
   $vendor = User::create($validated);
   return redirect()->route('uservendors.show', $vendor->id)
    ->with('success', 'Vendor created successfully.');
  } catch (\Exception $e) {
   // Catch database or other errors
   $errorMessage = $e->getMessage();

   return redirect()->back()
    ->withErrors(['error' => $errorMessage])
    ->withInput();
  }
 }

 /**
  * Display the specified resource.
  */
 public function show(string $id)
 {
  //
  $vendor = User::find($id);
  return view('uservendors.show', compact('vendor'));
 }

 /**
  * Show the form for editing the specified resource.
  */
 public function edit(string $id)
 {
  //
  $vendor = User::find($id);
  return view('uservendors.edit', compact('vendor'));
 }

 /**
  * Update the specified resource in storage.
  */
 public function update(Request $request, string $id)
 {
  try {
   $validated = $request->validate([
    'name'          => 'required|string|max:255',
    'firstName'     => 'required|string',
    'lastName'      => 'required|string',
    'contactPerson' => 'string',
    'contactNo'     => 'string',
    'address'       => 'string|max:255',
    'aadharNumber'  => 'string|max:12',
    'panNumber'     => 'string|max:10',
    'gstNumber'     => 'nullable|string|max:15',
    'accountName'   => 'string',
    'accountNumber' => 'string',
    'ifscCode'      => 'string|max:11',
    'bankName'      => 'string',
    'branch'        => 'string',
    'email'         => 'required|email',
   ]);
   $vendor = User::find($id)->update($validated);
   return redirect()->route('uservendors.show', compact('vendor'))->with('success', 'Vendor updated successfully.');
  } catch (\Exception $e) {
   // Catch database or other errors
   $errorMessage = $e->getMessage();

   return redirect()->back()
    ->withErrors(['error' => $errorMessage])
    ->withInput();
  }

 }

 /**
  * Remove the specified resource from storage.
  */
 public function destroy(string $id)
 {
  //
  try {
   $vendor = User::findOrFail($id);
   $vendor->delete();
   return response()->json(['success' => true]);
  } catch (\Exception $e) {
   return response()->json(['success' => false, 'message' => $e->getMessage()]);
  }
 }
}
