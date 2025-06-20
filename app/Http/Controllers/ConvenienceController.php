<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Conveyance;
use App\Models\dailyfare;
use App\Models\HotelExpense;
use App\Models\Journey;
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
        $pendingclaimcount = Tada::where('status', null)->count();
        $trips = Tada::count();
        $totalMiscAmount = 0;

        foreach ($tadas as $tada) {
            if (!empty($tada->miscellaneous)) {
                $miscItems = json_decode($tada->miscellaneous, true);
                if (is_array($miscItems)) {
                    foreach ($miscItems as $item) {
                        $totalMiscAmount += isset($item['amount']) ? $item['amount'] : 0;
                    }
                }
            }
        }
        $hotelamount = HotelExpense::sum('amount');
        $diningcost = HotelExpense::sum('dining_cost');
        $travelcost = Journey::sum('amount');
        $total_amount = $hotelamount + $diningcost + $travelcost + $totalMiscAmount;
        return view('billing.tada', compact('tadas', 'pendingclaimcount', 'trips', 'total_amount'));
    }

    public function viewtadaDetails(String $id)
    {
        $tadas = Tada::with('journey', 'hotelExpense', 'user')->where('id', $id)->first();
        $travelfares = Journey::where('tada_id', $tadas->id)->get();
        $dailyfares = HotelExpense::where('tada_id', $tadas->id)->get();
        $miscData = json_decode($tadas->miscellaneous, true);
        $otherExpense = collect($miscData)->sum('amount');
        $travelfare = collect($travelfares)->sum('amount');
        $hotelfare = collect($dailyfares)->sum('amount');
        $hoteldiningcost = collect($dailyfares)->sum('dining_cost');

        $conveyance = $travelfare + $hotelfare + $hoteldiningcost;
        $totalamount = $conveyance + $otherExpense;
        return view('billing.tadaDetails', compact('tadas', 'travelfares', 'dailyfares', 'conveyance', 'otherExpense', 'totalamount'));
    }

    // public function updateTadaStatus(Request $request, $id)
    // {
    //     try {
    //         // Validate request
    //         $validated = $request->validate([
    //             'status' => 'required|boolean',
    //         ]);

    //         // Find the TADA record
    //         $tada = Tada::findOrFail($id);

    //         // Update the status
    //         $tada->status = $validated['status'];
    //         $tada->update();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'TADA status updated successfully',
    //             'data' => $tada
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Failed to update TADA status: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function bulkUpdateStatus(Request $request)
{
    $validated = $request->validate([
        'ids' => 'required|array',
        'status' => 'required|in:0,1'
    ]);

    Tada::whereIn('id', $validated['ids'])->update(['status' => $validated['status']]);

    return response()->json(['message' => 'Status updated successfully.']);
}


    public function settings()
    {
        $vehicles = Vehicle::get();
        $users = User::with('usercategory')->where('role', '!=', 3)->get();

        $categories = UserCategory::get();
        $vehicleNames = $vehicles->pluck('name', 'id')->toArray();

        $cities = City::with('usercategory')->get();

        return view('billing.settings', compact('vehicles', 'users', 'categories', 'vehicleNames', 'cities'));
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
    Log::info('Request Data: Add category', $request->all());

    $validatedData = $request->validate([
        'category' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'vehicle_id' => 'required|array',
        'vehicle_id.*' => 'exists:vehicles,id',
        'city_category' => 'required|numeric',
        'daily_amount' => 'required|numeric'
    ]);

    // Custom check: ensure category + city combination is unique
    $existing = UserCategory::where('category_code', $validatedData['category'])
        ->where('city_category', $validatedData['city_category'])
        ->exists();

    if ($existing) {
        return redirect()->back()
            ->withInput()
            ->withErrors(['category' => 'User category and city category already exists.']);
    }

    try {
        // Save the new category
        $category = new UserCategory();
        $category->category_code = $validatedData['category'];
        $category->name = $validatedData['name'];
        $category->description = $validatedData['description'] ?? null;
        $category->allowed_vehicles = json_encode($validatedData['vehicle_id']);
        $category->city_category = $validatedData['city_category'];
        $category->dailyamount = $validatedData['daily_amount'];
        $category->save();

        return redirect()->route('billing.settings', ['tab' => 'category'])->with('success', 'Category added successfully.');

    } catch (\Exception $e) {
        Log::error('Error saving category: ' . $e->getMessage());
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'An unexpected error occurred while saving the category. Please try again.']);
    }
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

    public function editAllowedExpense($id){
        $city = City::with('usercategory')->findOrFail($id);
        $usercategory = UserCategory::where('id', $city->user_category_id)->first();
        $usercategories = UserCategory::get();
        return view('billing.editAllowedExpense', compact('city', 'usercategory', 'usercategories'));
    }

    public function updateAllowedExpense(Request $request, $id)
{
    // Validate the request data
    $validated = $request->validate([
        'city_category' => 'required|in:0,1,2',
        'user_category' => 'required|exists:user_categories,id',
        'hotel_bill' => 'required|numeric|min:0',
    ]);

    // Find the city
    $city = City::findOrFail($id);

    // Update city model fields
    $city->category = $validated['city_category'];
    $city->user_category_id = $validated['user_category']; // related to UserCategory
    $city->room_max_price = $validated['hotel_bill'];

    // Save the city
    $city->save();

    // Redirect back with success message
    return redirect()->route('billing.settings')
                     ->with('success', 'Allowed expense updated successfully.');
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
