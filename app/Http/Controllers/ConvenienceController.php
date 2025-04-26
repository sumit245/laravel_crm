<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserCategory;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class ConvenienceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function convenience()
    {
        //
        return view('billing.convenience');
    }

    public function tada()
    {
        return  view('billing.tada');
    }

    public function settings()
    {
        $vehicles = Vehicle::get();
        $users = User::where('role', '!=', 3)->get();
        $categories = UserCategory::get();

        return view('billing.settings', compact('vehicles', 'users', 'categories'));
    }

    // Add vehicle function
    public function addVehicle(Request $request)
    {
    
        $validatedData = $request->validate([
            'vehicle_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'sub_category' => 'nullable|string|max:255', // "not required" should be "nullable"
            'rate' => 'required|numeric',
        ]);

    // Create a new vehicle record
    $vehicle = new Vehicle();
    $vehicle->vehicle_name = $validatedData['vehicle_name'];
    $vehicle->category = $validatedData['category'];
    $vehicle->sub_category = $validatedData['sub_category'] ?? null; // in case it's not filled
    $vehicle->rate = $validatedData['rate'];
    $vehicle->save();

    return redirect()->back()->with('success', 'Vehicle added successfully!');
}

    public function editVehicle(Request $request){
        $ev = Vehicle::find($request->id);
        return view('billing.editVehicle', compact('ev'));
    } 

public function updateVehicle(Request $request)
{
    \Log::info('Request Data: Edit vehicle', $request->all());

    $allowedCategories = ['Car', 'Bike', 'Public Transport'];
    $id = $request->input('vehicle_id'); // <-- your hidden input field name is 'vehicle_id', not 'id'

    $validatedData = $request->validate([
        'vehicle_name' => 'required|string|max:100',
         // <-- validate category properly
        'sub_category' => 'nullable|string|max:50',
        'rate' => 'required|numeric',
    ]);

    // Find the vehicle
    $vehicle = Vehicle::findOrFail($id);

    // Update the fields
    $vehicle->vehicle_name = $validatedData['vehicle_name'];
    // <-- update category now
    $vehicle->sub_category = $validatedData['sub_category'] ?? null; // sub_category might be null
    $vehicle->rate = $validatedData['rate'];

    $vehicle->save();

    return redirect()->route('billing.settings')->with('success', 'Vehicle updated successfully!');
}

    // User Edit
    public function editUser(Request $request){
        $ue = User::find($request->id);
        return view('billing.editUser', compact('ue'));
    }

    // Delete Vehicle
    public function deleteVehicle(Request $request){
        $dv = Vehicle::find($request->id);
        $dv->delete();
        return redirect()->back()->with('success', 'Vehicle deleted successfully!');
    }

    public function updateUser(Request $request)
{
    \Log::info('Request Data: Update user', $request->all());
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'category' => 'required|in:M1,M2,M3,M4,M5',
    ]);

    $user = User::findOrFail($request->input('user_id'));
    $user->category = $request->input('category');
    $user->save();

    return redirect()->route('billing.settings')->with('success', 'User category updated successfully!');
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
