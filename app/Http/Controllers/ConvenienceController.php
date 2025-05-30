<?php

namespace App\Http\Controllers;

use App\Models\Conveyance;
use App\Models\dailyfare;
use App\Models\Tada;
use App\Models\travelfare;
use App\Models\User;
use App\Models\UserCategory;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Log;

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
        $appliedAmount = Conveyance::sum('amount');
        $disbursedAmount = Conveyance::where('status', 1)->sum('amount');
        $rejectedAmount = Conveyance::where('status', 0)->sum('amount');


        return view('billing.convenience', compact('cons', 'appliedAmount', 'disbursedAmount', 'rejectedAmount'));
    }

    public function showdetailsconveyance($id)
    {

        $details = Conveyance::with(['user', 'vehicle'])->where('user_id', $id)->get();
        $appliedAmount = Conveyance::where('user_id', $id)->sum('amount');
        $disbursedAmount = Conveyance::where('user_id', $id)->where('status', 1)->sum('amount');
        $rejectedAmount = Conveyance::where('user_id', $id)->where('status', 0)->sum('amount');
        $dueclaimAmount = $appliedAmount - $disbursedAmount;
        // dd($details);
        return view('billing.conveyanceDetails', compact('details', 'appliedAmount', 'disbursedAmount', 'rejectedAmount', 'dueclaimAmount'));
    }

    public function accept($id)
    {
        Conveyance::where('id', $id)->update(['status' => 1]);
        return back()->with('success', 'Status updated to Accepted.');
    }

    public function reject($id)
    {
        Conveyance::where('id', $id)->update(['status' => 0]);

        return back()->with('success', 'Status updated to Accepted.');
    }

    // Tada view
    public function tadaView()
    {
        $tadas = Tada::get();
        $count_trip = Tada::count();
        $total_km = Tada::sum('total_km');
        $dailyamount = dailyfare::sum('amount');
        $travelfare = travelfare::sum('amount');
        $total_amount = $dailyamount + $travelfare;
        $pendingclaimcount = Tada::where('status', null)->count();
        return view('billing.tada', compact('tadas', 'count_trip', 'total_km', 'dailyamount', 'travelfare', 'total_amount', 'pendingclaimcount'));
    }

    public function viewtadaDetails(String $id)
    {
        $tadas = Tada::with('travelfare', 'dailyfare', 'user')->where('user_id', $id)->first();
        $travelfares = travelfare::where('tada_id', $tadas->id)->get();
        $dailyfares = dailyfare::where('tada_id', $tadas->id)->get();
        $dailyamount = dailyfare::sum('amount');
        $travelfare = travelfare::sum('amount');
        $conveyance = $dailyamount + $travelfare;
        return view('billing.tadaDetails', compact('tadas', 'travelfares', 'dailyfares', 'conveyance'));
    }

    public function updateTadaStatus(Request $request, $id)
    {
        try {
            // Validate request
            Log::info('Received request to update TADA status', $request->all());
            $validated = $request->validate([
                'status' => 'required|boolean',
            ]);

            // Find the TADA record
            $tada = Tada::findOrFail($id);

            // Update the status
            $tada->status = $validated['status'];
            $tada->update();

            return response()->json([
                'status' => 'success',
                'message' => 'TADA status updated successfully',
                'data' => $tada
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update TADA status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function settings()
    {
        $vehicles = Vehicle::get();
        $users = User::with('usercategory')->where('role', '!=', 3)->get();

        $categories = UserCategory::get();
        $vehicleNames = $vehicles->pluck('name', 'id')->toArray();

        return view('billing.settings', compact('vehicles', 'users', 'categories', 'vehicleNames'));
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
            // Return to the same page with an error message
            return redirect()->back()->withErrors(['error' => 'An error occurred while updating the vehicle. Please try again.']);
        }
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
    {

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'category' => 'integer',
        ]);

        $user = User::findOrFail($request->input('user_id'));
        $user->category = $request->input('category');
        $user->update();

        return redirect()->route('billing.settings')->with('success', 'User category updated successfully!');
    }

    public function viewCategory()
    {
        $vehicles = Vehicle::get();
        return view('billing.addCategory', compact('vehicles'));
    }
    // Add Category
    public function addCategory(Request $request)
    {
        \Log::info('Request Data: Add category', $request->all());
        $validatedData = $request->validate([
            'category' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'room_min_price' => 'nullable|numeric|min:0',
            'room_max_price' => 'nullable|numeric|min:0',
            'vehicle_id' => 'required|array',
            'vehicle_id.*' => 'exists:vehicles,id',
        ]);

        // Validate that min price is less than max price if both are provided
        if (!empty($validatedData['room_min_price']) && !empty($validatedData['room_max_price'])) {
            if ($validatedData['room_min_price'] > $validatedData['room_max_price']) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['room_min_price' => 'Minimum price cannot be greater than maximum price']);
            }
        }

        // Save the new category
        $category = new UserCategory(); // Assuming your model is UserCategory
        $category->category_code = $validatedData['category'];
        $category->name = $validatedData['name'];
        $category->description = $validatedData['description'] ?? null;
        $category->room_min_price = $validatedData['room_min_price'] ?? null;
        $category->room_max_price = $validatedData['room_max_price'] ?? null;
        $category->allowed_vehicles = json_encode($validatedData['vehicle_id']); // Store as JSON
        $category->save();

        return redirect()->route('billing.settings')->with('success', 'Category added successfully.');
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
            \Log::info('Request Data: Update category', $request->all());

            $validatedData = $request->validate([
                'category_code' => 'required|string|max:255',
                'vehicle_id' => 'required|array',
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
