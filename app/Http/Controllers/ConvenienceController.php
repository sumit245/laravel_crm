<?php

namespace App\Http\Controllers;

use App\Models\Conveyance;
use App\Models\Tada;
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
        $cons = Conveyance::get();
        return view('billing.convenience', compact('cons'));
    }

    // Tada view
    public function tadaView(){
        $tadas = Tada::get();
        return view('billing.tada', compact('tadas'));
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

    $id = $request->input('user_id'); // Vehicle ID from the hidden input field

    // Validate the required fields: 'category' and 'rate'
    $validatedData = $request->validate([
        'vehicle_name' => 'nullable|string|max:100',  // Optional field
        'category' => 'required|string',  // Category is required as a string
        'subcategory' => 'nullable|string|max:50',  // Optional field
        'rate' => 'required|numeric',  // Rate is required and must be numeric
    ]);

    try {
        // Find the vehicle by its ID
        $vehicle = Vehicle::findOrFail($id);

        // Update the fields using the update method
        $vehicle->update([
            'vehicle_name' => $validatedData['vehicle_name'] ?? $vehicle->vehicle_name, // Update only if provided
            'category' => $validatedData['category'],  // Directly assign the incoming category value
            'sub_category' => $validatedData['subcategory'],  // Nullable field
            'rate' => $validatedData['rate'],  // Required field
        ]);

        // Redirect to the settings page with a success message
        return redirect()->route('billing.settings')->with('success', 'Vehicle updated successfully!');
    } catch (\Exception $e) {
        // Log the error message
        \Log::error('Error updating vehicle: ' . $e->getMessage());

        // Return to the same page with an error message
        return redirect()->back()->withErrors(['error' => 'An error occurred while updating the vehicle. Please try again.']);
    }
}

    

    // User Edit
    public function editUser(Request $request){
        $ue = User::find($request->id);
        $uc = UserCategory::get();
        return view('billing.editUser', compact('ue', 'uc'));
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
// Add Category
    public function addCategory(Request $request)
    {
        \Log::info('Request Data: Add category', $request->all());
        $validatedData = $request->validate([
            'category' => 'required|string|max:255', // category_code
            'vehicle_id' => 'required|integer', // single vehicle ID
        ]);

        // Save the new category
        $category = new UserCategory(); // Assuming your model is UserCategory
        $category->category_code = $validatedData['category'];
        $category->allowed_vehicles = $validatedData['vehicle_id']; // Directly save single vehicle id
        $category->save();

        return redirect()->back()->with('success', 'Category added successfully.');
    }

    public function editCategory(Request $request){
        $uc = UserCategory::find($request->id);
        $uv = Vehicle::get();
        return view('billing.editCategory', compact('uc', 'uv'));
    }

    public function updateCategory(Request $request)
    {
        try {
            \Log::info('Request Data: Update category', $request->all());

            $validatedData = $request->validate([
                'category_code' => 'required|string|max:255',
                'vehicle_id' => 'required|integer',
            ]);

            $category = UserCategory::find($request->category_id);

            if (!$category) {
                return redirect()->back()->with('error', 'Category not found.');
            }

            $category->category_code = $validatedData['category_code'];
            $category->allowed_vehicles = $validatedData['vehicle_id'];

            $category->save();

            return redirect()->route('billing.settings')->with('success', 'Category updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Error updating category: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while updating the category.');
        }
    }


// Delete Category
public function deleteCategory(Request $request){
    $dc = UserCategory::find($request->id);
    $dc->delete();
    return redirect()->back()->with('success', 'Category deleted successfully!');
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