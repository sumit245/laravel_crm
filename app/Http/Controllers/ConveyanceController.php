<?php

namespace App\Http\Controllers;

use App\Models\Conveyance;
use App\Models\Tada;
use App\Models\User;
use App\Models\UserCategory;
use App\Models\Vehicle;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConveyanceController extends Controller
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
        $appliedAmount = Conveyance::sum('amount');
        $disbursedAmount = Conveyance::where('status', 1)->sum('amount');
        $rejectedAmount = Conveyance::where('status', 0)->sum('amount');
        

        return view('billing.convenience', compact('cons', 'appliedAmount', 'disbursedAmount', 'rejectedAmount'));
    }

    public function showdetailsconveyance($id){
        
        $details = Conveyance::with(['user', 'vehicle'])->where('user_id', $id)->get();
        $appliedAmount = Conveyance::where('user_id', $id)->sum('amount');
        $disbursedAmount = Conveyance::where('user_id', $id)->where('status', 1)->sum('amount');
        $rejectedAmount = Conveyance::where('user_id', $id)->where('status', 0)->sum('amount'); 
        $dueclaimAmount = $appliedAmount-$disbursedAmount;
        // dd($details);
        return view('billing.conveyanceDetails', compact('details', 'appliedAmount', 'disbursedAmount', 'rejectedAmount', 'dueclaimAmount'));
    }

    public function accept($id){
        Conveyance::where('id', $id)->update(['status' => 1]);

        return back()->with('success', 'Status updated to Accepted.');
    }

    public function reject($id){
        Conveyance::where('id', $id)->update(['status' => 0]);

        return back()->with('success', 'Status updated to Accepted.');
    }

    // Tada view
    public function tadaView()
    {
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

    public function getAllVehicles(Request $request)
    {
        try {
            //code...
            $vehicles = Vehicle::get();
            return response()->json($vehicles, 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'msg' => $e->getMessage()
            ], 500);
        }
    }

    public function getVehicleDetail($id)
    {
        try {
            //code...
            $vehicle = Vehicle::find($id);
            return response()->json($vehicle, 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'msg' => $e->getMessage()
            ], 500);
        }
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

    public function editVehicle(Request $request)
    {
        $ev = Vehicle::find($request->id);
        return view('billing.editVehicle', compact('ev'));
    }

    public function updateVehicle(Request $request)
    {
<<<<<<< HEAD:app/Http/Controllers/ConvenienceController.php
    
    $id = $request->input('user_id'); // Vehicle ID from the hidden input field
=======
        Log::info('Request Data: Edit vehicle', $request->all());

        $id = $request->input('user_id'); // Vehicle ID from the hidden input field
>>>>>>> 89c3125ce5a81b57e9efbba12838dae88d21b5cf:app/Http/Controllers/ConveyanceController.php

        // Validate the required fields: 'category' and 'rate'
        $validatedData = $request->validate([
            'vehicle_name' => 'nullable|string|max:100',  // Optional field
            'category' => 'required|string',  // Category is required as a string
            'subcategory' => 'nullable|string|max:50',  // Optional field
            'icon' => 'nullable|string|max:50',  // Optional field
            'rate' => 'required|numeric',  // Rate is required and must be numeric
        ]);

<<<<<<< HEAD:app/Http/Controllers/ConvenienceController.php
        // Redirect to the settings page with a success message
        return redirect()->route('billing.settings')->with('success', 'Vehicle updated successfully!');
    } catch (\Exception $e) {
        // Log the error message
        // Return to the same page with an error message
        return redirect()->back()->withErrors(['error' => 'An error occurred while updating the vehicle. Please try again.']);
=======
        try {
            // Find the vehicle by its ID
            $vehicle = Vehicle::findOrFail($id);

            // Update the fields using the update method
            $vehicle->update($validatedData);

            // Redirect to the settings page with a success message
            return redirect()->route('billing.settings')->with('success', 'Vehicle updated successfully!');
        } catch (\Exception $e) {
            // Log the error message
            Log::error('Error updating vehicle: ' . $e->getMessage());

            // Return to the same page with an error message
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the vehicle. Please try again.']);
        }
>>>>>>> 89c3125ce5a81b57e9efbba12838dae88d21b5cf:app/Http/Controllers/ConveyanceController.php
    }



    // User Edit
    public function editUser(Request $request)
    {
        $ue = User::find($request->id);
        $uc = UserCategory::get();
        return view('billing.editUser', compact('ue', 'uc'));
    }

    // Delete Vehicle
    public function deleteVehicle(Request $request)
    {
        $dv = Vehicle::find($request->id);
        $dv->delete();
        return redirect()->back()->with('success', 'Vehicle deleted successfully!');
    }

    public function updateUser(Request $request)
<<<<<<< HEAD:app/Http/Controllers/ConvenienceController.php
{
    
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'category' => 'required|in:M1,M2,M3,M4,M5',
    ]);
=======
    {
        Log::info('Request Data: Update user', $request->all());
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'category' => 'required|in:M1,M2,M3,M4,M5',
        ]);
>>>>>>> 89c3125ce5a81b57e9efbba12838dae88d21b5cf:app/Http/Controllers/ConveyanceController.php

        $user = User::findOrFail($request->input('user_id'));
        $user->category = $request->input('category');
        $user->save();

        return redirect()->route('billing.settings')->with('success', 'User category updated successfully!');
    }
    // Add Category
    public function addCategory(Request $request)
    {
        Log::info('Request Data: Add category', $request->all());
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

    public function editCategory(Request $request)
    {
        $uc = UserCategory::find($request->id);
        $uv = Vehicle::get();
        return view('billing.editCategory', compact('uc', 'uv'));
    }

    public function updateCategory(Request $request)
    {
        try {
            Log::info('Request Data: Update category', $request->all());

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
            Log::error('Error updating category: ' . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while updating the category.');
        }
    }


    // Delete Category
    public function deleteCategory(Request $request)
    {
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
